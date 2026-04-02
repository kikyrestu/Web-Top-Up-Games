<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #{{ $transaction->invoice_number }}</title>
    <meta name="robots" content="noindex,nofollow,noarchive">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; background: #111; color: #fff; min-height: 100vh; display: flex; flex-direction: column; align-items: center; padding: 24px 16px; }
        
        .receipt { width: 100%; max-width: 420px; background: #1a1a1a; border: 1px solid #2d2d2d; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.5); }
        
        /* Header */
        .receipt-header { background: linear-gradient(135deg, #1c1c1c, #222); padding: 28px 24px 20px; text-align: center; border-bottom: 1px dashed #333; position: relative; }
        .receipt-header::after { content: ''; position: absolute; bottom: -1px; left: 0; right: 0; height: 1px; background: repeating-linear-gradient(90deg, #333 0, #333 6px, transparent 6px, transparent 12px); }
        .logo-area { margin-bottom: 12px; }
        .logo-area img { height: 40px; width: auto; }
        .logo-area .text-logo { font-size: 22px; font-weight: 900; color: #f97316; letter-spacing: 1px; text-transform: uppercase; font-style: italic; }
        .receipt-header .subtitle { font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: 2px; margin-top: 4px; }
        
        /* Status badge */
        .status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 16px; border-radius: 20px; font-size: 12px; font-weight: 700; margin-top: 14px; text-transform: uppercase; letter-spacing: 1px; }
        .status-success { background: rgba(34,197,94,0.12); color: #4ade80; border: 1px solid rgba(34,197,94,0.25); }
        .status-process { background: rgba(249,115,22,0.12); color: #f97316; border: 1px solid rgba(249,115,22,0.25); }
        .status-failed { background: rgba(239,68,68,0.12); color: #ef4444; border: 1px solid rgba(239,68,68,0.25); }
        .status-pending { background: rgba(234,179,8,0.12); color: #eab308; border: 1px solid rgba(234,179,8,0.25); }
        
        /* Body */
        .receipt-body { padding: 20px 24px; }
        
        /* Invoice number */
        .invoice-id { text-align: center; padding: 14px 0; margin-bottom: 16px; }
        .invoice-id .label { font-size: 10px; color: #666; text-transform: uppercase; letter-spacing: 2px; }
        .invoice-id .value { font-size: 15px; font-weight: 700; font-family: 'Courier New', monospace; color: #fff; margin-top: 4px; word-break: break-all; }
        
        /* Detail rows */
        .detail-section { margin-bottom: 16px; }
        .detail-section .section-title { font-size: 10px; font-weight: 700; color: #666; text-transform: uppercase; letter-spacing: 2px; padding-bottom: 8px; border-bottom: 1px solid #2d2d2d; margin-bottom: 10px; }
        .detail-row { display: flex; justify-content: space-between; align-items: flex-start; padding: 5px 0; font-size: 13px; }
        .detail-row .key { color: #888; flex-shrink: 0; }
        .detail-row .val { color: #e5e5e5; font-weight: 600; text-align: right; word-break: break-all; max-width: 60%; }
        .detail-row .val.mono { font-family: 'Courier New', monospace; }
        .detail-row .val.highlight { color: #f97316; font-weight: 800; font-size: 15px; }
        
        /* Divider */
        .divider { border: none; border-top: 1px dashed #333; margin: 16px 0; position: relative; }
        .divider::before { content: ''; position: absolute; top: -8px; left: -25px; width: 16px; height: 16px; background: #111; border-radius: 50%; }
        .divider::after { content: ''; position: absolute; top: -8px; right: -25px; width: 16px; height: 16px; background: #111; border-radius: 50%; }

        /* SN Box */
        .sn-box { background: rgba(34,197,94,0.08); border: 1px solid rgba(34,197,94,0.2); border-radius: 10px; padding: 12px 16px; margin-top: 12px; text-align: center; }
        .sn-box .sn-label { font-size: 10px; color: #4ade80; text-transform: uppercase; letter-spacing: 2px; font-weight: 700; }
        .sn-box .sn-value { font-size: 14px; font-family: 'Courier New', monospace; color: #fff; font-weight: 700; margin-top: 4px; word-break: break-all; }
        
        /* Footer */
        .receipt-footer { padding: 16px 24px 24px; text-align: center; border-top: 1px dashed #333; position: relative; }
        .receipt-footer::before { content: ''; position: absolute; top: -1px; left: 0; right: 0; height: 1px; background: repeating-linear-gradient(90deg, #333 0, #333 6px, transparent 6px, transparent 12px); }
        .receipt-footer .thank-you { font-size: 14px; font-weight: 700; color: #f97316; margin-bottom: 4px; }
        .receipt-footer .footer-text { font-size: 11px; color: #666; line-height: 1.5; }
        .receipt-footer .timestamp { font-size: 10px; color: #555; font-family: 'Courier New', monospace; margin-top: 10px; }
        
        /* Action buttons (hidden on print) */
        .actions { margin-top: 20px; display: flex; gap: 10px; width: 100%; max-width: 420px; }
        .actions a, .actions button { flex: 1; padding: 12px 16px; border-radius: 10px; font-size: 13px; font-weight: 700; text-decoration: none; text-align: center; cursor: pointer; border: none; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 6px; }
        .btn-print { background: #2563eb; color: #fff; }
        .btn-print:hover { background: #1d4ed8; }
        .btn-back { background: #222; color: #ccc; border: 1px solid #333; }
        .btn-back:hover { background: #333; color: #fff; }
        .btn-share { background: #25D366; color: #fff; }
        .btn-share:hover { background: #1da851; }
        
        /* Print styles */
        @media print {
            body { background: #fff; color: #000; padding: 0; }
            .receipt { max-width: 100%; box-shadow: none; border: none; background: #fff; border-radius: 0; }
            .receipt-header { background: #fff; }
            .receipt-header .subtitle { color: #666; }
            .logo-area .text-logo { color: #f97316; }
            .invoice-id .value { color: #000; }
            .detail-row .key { color: #666; }
            .detail-row .val { color: #000; }
            .detail-row .val.highlight { color: #f97316; }
            .detail-section .section-title { color: #666; border-color: #ddd; }
            .divider { border-color: #ccc; }
            .divider::before, .divider::after { background: #fff; }
            .receipt-footer { border-color: #ccc; }
            .receipt-footer::before { background: repeating-linear-gradient(90deg, #ccc 0, #ccc 6px, transparent 6px, transparent 12px); }
            .receipt-header::after { background: repeating-linear-gradient(90deg, #ccc 0, #ccc 6px, transparent 6px, transparent 12px); }
            .status-success { background: #f0fdf4; color: #16a34a; border-color: #bbf7d0; }
            .status-process { background: #fff7ed; color: #ea580c; border-color: #fed7aa; }
            .status-failed { background: #fef2f2; color: #dc2626; border-color: #fecaca; }
            .status-pending { background: #fefce8; color: #ca8a04; border-color: #fef08a; }
            .sn-box { background: #f0fdf4; border-color: #bbf7d0; }
            .sn-box .sn-label { color: #16a34a; }
            .sn-box .sn-value { color: #000; }
            .receipt-footer .thank-you { color: #f97316; }
            .receipt-footer .footer-text { color: #666; }
            .receipt-footer .timestamp { color: #999; }
            .actions { display: none !important; }
        }
        
        @media (max-width: 480px) {
            body { padding: 12px 8px; }
            .receipt-body { padding: 16px 18px; }
            .receipt-header { padding: 22px 18px 16px; }
            .receipt-footer { padding: 14px 18px 20px; }
            .actions { flex-wrap: wrap; }
            .actions a, .actions button { flex-basis: calc(50% - 5px); }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous">
</head>
<body>

@php
    $item = $transaction->items->first();
    $txStatus = $transaction->transaction_status;
    $payStatus = $transaction->payment_status;

    // Try to extract SN from api_response
    $sn = null;
    if ($transaction->api_response) {
        $apiResp = json_decode($transaction->api_response, true);
        $sn = $apiResp['sn'] ?? $apiResp['serial_number'] ?? $apiResp['token'] ?? $apiResp['SN'] ?? $apiResp['SERIAL'] ?? null;
    }
    
    $siteName = $global_site_name ?? 'PPOBKu';
    $siteLogo = $global_site_logo ?? null;
@endphp

<div class="receipt">
    <!-- Header -->
    <div class="receipt-header">
        <div class="logo-area">
            @if(!empty($siteLogo))
                <img src="{{ asset('storage/' . $siteLogo) }}" alt="{{ $siteName }}">
            @else
                <div class="text-logo">{{ $siteName }}</div>
            @endif
        </div>
        <div class="subtitle">Bukti Transaksi</div>
        
        @if($txStatus === 'success')
            <div class="status-badge status-success"><i class="fas fa-check-circle"></i> Berhasil</div>
        @elseif($txStatus === 'processing')
            <div class="status-badge status-process"><i class="fas fa-spinner fa-spin"></i> Diproses</div>
        @elseif($txStatus === 'failed')
            <div class="status-badge status-failed"><i class="fas fa-times-circle"></i> Gagal</div>
        @else
            <div class="status-badge status-pending"><i class="fas fa-clock"></i> Menunggu</div>
        @endif
    </div>

    <div class="receipt-body">
        <!-- Invoice ID -->
        <div class="invoice-id">
            <div class="label">Nomor Invoice</div>
            <div class="value">{{ $transaction->invoice_number }}</div>
        </div>

        <!-- Detail Produk -->
        <div class="detail-section">
            <div class="section-title">Detail Pesanan</div>
            <div class="detail-row">
                <span class="key">Produk</span>
                <span class="val">{{ $item->product_name ?? 'Produk' }}</span>
            </div>
            <div class="detail-row">
                <span class="key">Jumlah</span>
                <span class="val">{{ $item->quantity ?? 1 }}x</span>
            </div>
            <div class="detail-row">
                <span class="key">Tujuan / ID</span>
                <span class="val mono">{{ $transaction->target_input }}</span>
            </div>
            @if($transaction->customer_name)
            <div class="detail-row">
                <span class="key">Pelanggan</span>
                <span class="val">{{ $transaction->customer_name }}</span>
            </div>
            @endif
            <div class="detail-row">
                <span class="key">Tanggal</span>
                <span class="val">{{ $transaction->created_at->format('d M Y, H:i') }} WIB</span>
            </div>
        </div>

        <hr class="divider">

        <!-- Rincian Pembayaran -->
        <div class="detail-section">
            <div class="section-title">Rincian Pembayaran</div>
            <div class="detail-row">
                <span class="key">Metode Bayar</span>
                <span class="val">{{ $transaction->paymentGateway->name ?? 'Saldo Wallet' }}</span>
            </div>
            <div class="detail-row">
                <span class="key">Harga</span>
                <span class="val">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span>
            </div>
            @if($transaction->fee_amount > 0)
            <div class="detail-row">
                <span class="key">Biaya Layanan</span>
                <span class="val">Rp {{ number_format($transaction->fee_amount, 0, ',', '.') }}</span>
            </div>
            @endif
            <div class="detail-row" style="padding-top: 10px; border-top: 1px solid #2d2d2d;">
                <span class="key" style="font-weight:700; color:#ccc;">Total Bayar</span>
                <span class="val highlight">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Status -->
        <hr class="divider">
        <div class="detail-section">
            <div class="section-title">Status</div>
            <div class="detail-row">
                <span class="key">Pembayaran</span>
                <span class="val" style="color: {{ $payStatus === 'paid' ? '#4ade80' : ($payStatus === 'failed' ? '#ef4444' : '#eab308') }}">
                    {{ $payStatus === 'paid' ? '✅ Lunas' : ($payStatus === 'failed' ? '❌ Gagal' : '⏳ Belum Bayar') }}
                </span>
            </div>
            <div class="detail-row">
                <span class="key">Transaksi</span>
                <span class="val" style="color: {{ $txStatus === 'success' ? '#4ade80' : ($txStatus === 'failed' ? '#ef4444' : '#f97316') }}">
                    {{ $txStatus === 'success' ? '✅ Berhasil' : ($txStatus === 'failed' ? '❌ Gagal' : ($txStatus === 'processing' ? '⏳ Diproses' : '⏳ Menunggu')) }}
                </span>
            </div>
        </div>

        <!-- SN / Serial Number (if available) -->
        @if($sn)
        <div class="sn-box">
            <div class="sn-label"><i class="fas fa-key"></i> Serial Number / Token</div>
            <div class="sn-value">{{ $sn }}</div>
        </div>
        @endif
    </div>

    <!-- Footer -->
    <div class="receipt-footer">
        <div class="thank-you">Terima kasih! 🙏</div>
        <div class="footer-text">
            Simpan struk ini sebagai bukti transaksi yang sah.<br>
            Hubungi CS jika ada kendala.
        </div>
        <div class="timestamp">{{ $transaction->created_at->format('d/m/Y H:i:s') }} • {{ $siteName }}</div>
    </div>
</div>

<!-- Action Buttons -->
<div class="actions">
    <a href="{{ route('transaction.show', $transaction->invoice_number) }}" class="btn-back">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
    <button onclick="window.print()" class="btn-print">
        <i class="fas fa-print"></i> Cetak
    </button>
    <a href="https://wa.me/?text={{ urlencode('Struk Transaksi ' . ($global_site_name ?? 'PPOBKu') . "\n\nInvoice: " . $transaction->invoice_number . "\nProduk: " . ($item->product_name ?? '-') . "\nTujuan: " . $transaction->target_input . "\nTotal: Rp " . number_format($transaction->total_amount, 0, ',', '.') . "\nStatus: " . ($txStatus === 'success' ? 'Berhasil ✅' : ($txStatus === 'failed' ? 'Gagal ❌' : 'Diproses ⏳')) . ($sn ? "\nSN: " . $sn : '') . "\n\nCek: " . route('transaction.show', $transaction->invoice_number)) }}" 
       target="_blank" class="btn-share">
        <i class="fab fa-whatsapp"></i> Share
    </a>
</div>

</body>
</html>
