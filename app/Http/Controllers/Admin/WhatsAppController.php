<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    public function qrCode(WhatsAppService $wa)
    {
        $qr = $wa->getQrCode();

        return response()->json([
            'success' => $qr !== null,
            'qr'      => $qr,
        ]);
    }

    public function status(WhatsAppService $wa)
    {
        return response()->json($wa->getStatus());
    }

    public function testSend(Request $request, WhatsAppService $wa)
    {
        $request->validate([
            'number'  => 'required|string',
            'message' => 'required|string|max:500',
        ]);

        $sent = $wa->sendMessage($request->number, $request->message);

        return response()->json([
            'success' => $sent,
            'message' => $sent ? 'Pesan berhasil dikirim!' : 'Gagal mengirim pesan. Pastikan WA Bot berjalan.',
        ]);
    }

    public function startBot()
    {
        $waDir = base_path('wa-bot');

        // Check if wa-bot is already registered in PM2 via JSON list
        $isRegistered = $this->isWaBotRegistered();

        if ($isRegistered) {
            shell_exec('pm2 start wa-bot 2>&1');
        } else {
            shell_exec("cd {$waDir} && pm2 start server.js --name wa-bot 2>&1");
        }
        shell_exec('pm2 save 2>/dev/null');

        sleep(2);

        $output = shell_exec('pm2 jlist 2>/dev/null');
        $processes = json_decode($output ?? '[]', true) ?? [];
        $waBot = collect($processes)->firstWhere('name', 'wa-bot');
        $pm2Status = $waBot['pm2_env']['status'] ?? 'unknown';

        return response()->json([
            'success' => $pm2Status === 'online',
            'message' => $pm2Status === 'online' ? 'Bot berhasil dihidupkan (PM2).' : 'Gagal menghidupkan bot. Status: ' . $pm2Status,
            'pm2_status' => $pm2Status,
        ]);
    }

    public function stopBot()
    {
        shell_exec('pm2 stop wa-bot 2>&1');

        sleep(2);

        $output = shell_exec('pm2 jlist 2>/dev/null');
        $processes = json_decode($output ?? '[]', true) ?? [];
        $waBot = collect($processes)->firstWhere('name', 'wa-bot');
        $pm2Status = $waBot['pm2_env']['status'] ?? 'stopped';

        return response()->json([
            'success' => true,
            'message' => 'Bot berhasil dimatikan (PM2).',
            'pm2_status' => $pm2Status,
        ]);
    }

    /**
     * Check if wa-bot is registered in PM2 via JSON list (more reliable than pm2 id).
     */
    private function isWaBotRegistered(): bool
    {
        $output = shell_exec('pm2 jlist 2>/dev/null');
        $processes = json_decode($output ?? '[]', true) ?? [];
        return collect($processes)->contains('name', 'wa-bot');
    }
}
