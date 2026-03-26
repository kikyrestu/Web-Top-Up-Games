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
}
