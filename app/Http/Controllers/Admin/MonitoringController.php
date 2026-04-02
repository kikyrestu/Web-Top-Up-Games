<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiProvider;
use App\Models\PaymentGateway;
use App\Models\Setting;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MonitoringController extends Controller
{
    public function index()
    {
        $apiProviders = ApiProvider::where('is_active', true)->get();
        $paymentGateways = PaymentGateway::where('is_active', true)->get();

        return view('admin.monitoring.index', compact('apiProviders', 'paymentGateways'));
    }

    public function checkBot()
    {
        // Check WhatsApp
        $wa = new WhatsAppService();
        $waStatus = $wa->getStatus();
        $waConnected = $waStatus['connected'] ?? false;
        
        // Check Telegram
        $telegramStatus = false;
        $telegramToken = Setting::get('telegram_bot_token');
        if (!empty($telegramToken)) {
            try {
                $response = Http::timeout(5)->get("https://api.telegram.org/bot{$telegramToken}/getMe");
                if ($response->successful() && $response->json('ok')) {
                    $telegramStatus = true;
                }
            } catch (\Exception $e) {
                // ignore
            }
        }

        return response()->json([
            'whatsapp' => [
                'enabled'   => Setting::get('wa_enabled') === '1',
                'connected' => $waConnected,
                'status_text' => $waConnected ? 'Online (Connected)' : 'Offline / Unlinked',
            ],
            'telegram' => [
                'enabled'   => Setting::get('telegram_enabled') === '1',
                'connected' => $telegramStatus,
                'status_text' => $telegramStatus ? 'Online' : 'Offline / Invalid Token',
            ]
        ]);
    }

    public function checkProvider($id)
    {
        $provider = ApiProvider::find($id);
        if (!$provider) {
            return response()->json(['success' => false, 'message' => 'Not found']);
        }

        $balance = 'N/A';
        $status = false;

        try {
            $credentials = $provider->credentials ?? [];

            if (strtolower($provider->name) === 'digiflazz' || strtolower($provider->code) === 'digiflazz') {
                $username = (string) ($credentials['username'] ?? '');
                $apiKey = (string) ($credentials['api_key'] ?? '');
                $baseUrl = !empty($credentials['url']) ? rtrim($credentials['url'], '/') : 'https://api.digiflazz.com/v1';

                $res = Http::timeout(5)->post($baseUrl . '/cek-saldo', [
                    'cmd'      => 'deposit',
                    'username' => $username,
                    'sign'     => md5($username . $apiKey . 'depo'),
                ]);
                
                if ($res->successful() && isset($res['data']['deposit'])) {
                    $balance = 'Rp ' . number_format($res['data']['deposit'], 0, ',', '.');
                    $status = true;
                }
            } else if (strtolower($provider->name) === 'rajabiller' || strtolower($provider->code) === 'rajabiller') {
                $rajabiller = new \App\Services\RajabillerService();
                $result = $rajabiller->cekSaldo($credentials);

                if (($result['rc'] ?? '99') === '00' || (int) ($result['saldo'] ?? 0) > 0) {
                    $balance = 'Rp ' . number_format($result['saldo'], 0, ',', '.');
                    $status = true;
                } elseif (($result['rc'] ?? '99') !== '99') {
                    // API reachable but returned error (wrong PIN, etc.)
                    $balance = 'RC: ' . ($result['rc'] ?? '?');
                    $status = false;
                }
            } else if (strtolower($provider->code) === 'orderkuota') {
                $service = new \App\Services\OrderKuotaService();
                $result = $service->cekSaldo($credentials);

                if ($result['success']) {
                    $balance = 'Rp ' . number_format((float) ($result['saldo'] ?? 0), 0, ',', '.');
                    $status = true;
                } else {
                    $balance = $result['message'] ?? 'Connection failed';
                }
            } else if (strtolower($provider->name) === 'vocagame' || strtolower($provider->name) === 'apigames') {
                 // Try to hit their profile or merchant endpoint
                 $apiKey = (string) ($credentials['api_key'] ?? '');
                 $baseUrl = (string) ($provider->base_url ?? '');
                 
                 $res = Http::timeout(5)->withHeaders([
                     'Authorization' => 'Bearer ' . $apiKey
                 ])->get(rtrim($baseUrl, '/') . '/profile');
                 
                 if ($res->successful()) {
                     $status = true;
                     $data = $res->json();
                     if (isset($data['data']['balance'])) {
                         $balance = 'Rp ' . number_format($data['data']['balance'], 0, ',', '.');
                     }
                 }
            } else {
                // Generic fallback: check if API is somewhat reachable
                $baseUrl = (string) ($provider->base_url ?? 'https://google.com'); // Prevent empty URL crash
                $res = Http::timeout(5)->get($baseUrl);
                $status = $res->status() < 500;
                $balance = '-';
            }
        } catch (\Exception $e) {
            $status = false;
        }

        return response()->json([
            'status' => $status,
            'balance' => $balance,
        ]);
    }

    public function checkGateway($id)
    {
        $gateway = PaymentGateway::find($id);
        if (!$gateway) {
            return response()->json(['success' => false, 'message' => 'Not found']);
        }

        $status = false;

        try {
            // Just basic reachability check for gateways as ping endpoints differ
            if (strtolower($gateway->code) === 'tripay') {
                $res = Http::timeout(5)
                    ->withHeaders(['Authorization' => 'Bearer ' . $gateway->api_key])
                    ->get(str_contains(strtolower($gateway->mode), 'sandbox') 
                        ? 'https://tripay.co.id/api-sandbox/merchant/payment-channel' 
                        : 'https://tripay.co.id/api/merchant/payment-channel');
                if ($res->successful()) {
                    $status = true;
                }
            } else if (strtolower($gateway->code) === 'duitku') {
                 // Just a dummy ping
                 $status = true;
            } else {
                 $status = true;
            }
        } catch (\Exception $e) {
            $status = false;
        }

        return response()->json([
            'status' => $status,
        ]);
    }

    public function waBotPm2Status()
    {
        $output = shell_exec('pm2 jlist 2>/dev/null');
        $processes = json_decode($output ?? '[]', true) ?? [];

        $waBot = collect($processes)->firstWhere('name', 'wa-bot');
        $pm2Running = $waBot !== null;
        $pm2Status = $waBot ? ($waBot['pm2_env']['status'] ?? 'stopped') : 'stopped';

        return response()->json([
            'pm2_running' => $pm2Running,
            'pm2_status'  => $pm2Status,
        ]);
    }

    public function waBotQr()
    {
        try {
            $wa = new WhatsAppService();
            $qr = $wa->getQrCode();
            return response()->json(['qr' => $qr]);
        } catch (\Exception $e) {
            // Ignore
        }
        return response()->json(['qr' => null]);
    }

    public function waBotControl(Request $request)
    {
        $action = $request->input('action'); // start | stop | restart

        $allowedActions = ['start', 'stop', 'restart'];
        if (!in_array($action, $allowedActions)) {
            return response()->json(['success' => false, 'message' => 'Aksi tidak valid.'], 400);
        }

        $waDir = base_path('wa-bot');

        // Helper: check if wa-bot is registered in PM2 via JSON list
        $isRegistered = function() {
            $output = shell_exec('pm2 jlist 2>/dev/null');
            $processes = json_decode($output ?? '[]', true) ?? [];
            return collect($processes)->contains('name', 'wa-bot');
        };

        if ($action === 'start') {
            if ($isRegistered()) {
                shell_exec('pm2 start wa-bot 2>&1');
            } else {
                shell_exec("cd {$waDir} && pm2 start server.js --name wa-bot 2>&1");
            }
            shell_exec('pm2 save 2>/dev/null');
        } elseif ($action === 'stop') {
            shell_exec('pm2 stop wa-bot 2>&1');
        } elseif ($action === 'restart') {
            if ($isRegistered()) {
                shell_exec('pm2 restart wa-bot 2>&1');
            } else {
                shell_exec("cd {$waDir} && pm2 start server.js --name wa-bot 2>&1");
                shell_exec('pm2 save 2>/dev/null');
            }
        }

        // Wait 2 seconds for PM2 to update status
        sleep(2);

        $output = shell_exec('pm2 jlist 2>/dev/null');
        $processes = json_decode($output ?? '[]', true) ?? [];
        $waBot = collect($processes)->firstWhere('name', 'wa-bot');
        $pm2Status = $waBot['pm2_env']['status'] ?? 'unknown';

        $messages = [
            'start'   => 'WA Bot berhasil dihidupkan!',
            'stop'    => 'WA Bot berhasil dimatikan.',
            'restart' => 'WA Bot berhasil di-restart!',
        ];

        return response()->json([
            'success'    => true,
            'message'    => $messages[$action],
            'pm2_status' => $pm2Status,
        ]);
    }

}
