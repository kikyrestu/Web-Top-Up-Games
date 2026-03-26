@extends('layouts.front')
@section('title', 'Invoice Transaksi')
@section('meta_description', 'Detail invoice dan status pembayaran transaksi Anda.')
@section('canonical', route('transaction.show', $transaction->invoice_number))
@section('robots', 'noindex,nofollow,noarchive')
@push('jsonld')
<script type="application/ld+json">
{
    "{{ '@' }}context": "https://schema.org",
    "{{ '@' }}type": "WebPage",
    "name": "Invoice Transaksi",
    "url": "{{ route('transaction.show', $transaction->invoice_number) }}",
    "description": "Detail invoice dan status pembayaran transaksi Anda."
}
</script>
@endpush

@section('content')
<div class="container mx-auto px-4 mt-20 md:mt-24 pb-20">
    <div class="max-w-3xl mx-auto">
        <!-- Invoice Header -->
        <div class="bg-[#1c1c1c] rounded-t-2xl border border-[#2d2d2d] border-b-0 p-6 md:p-8 text-center relative overflow-hidden">
            <!-- decorative background elements -->
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-[#f97316] rounded-full mix-blend-multiply filter blur-3xl opacity-10 animate-blob"></div>
            <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl opacity-10 animate-blob animation-delay-2000"></div>

            <i class="fas {{ $transaction->payment_status == 'paid' ? 'fa-check-circle text-green-500' : 'fa-clock text-[#f97316]' }} text-5xl mb-4 relative z-10"></i>
            <h1 class="text-2xl font-black text-white uppercase italic tracking-wider mb-2 relative z-10">
                {{ $transaction->payment_status == 'paid' ? 'Pembayaran Berhasil' : 'Menunggu Pembayaran' }}
            </h1>
            <p class="text-gray-400 text-sm relative z-10">Order ID: <span class="font-mono font-bold text-white">{{ $transaction->invoice_number }}</span></p>
        </div>

        <!-- Invoice Details -->
        <div class="bg-[#151515] border border-[#2d2d2d] p-6 md:p-8">
            @if($transaction->payment_status == 'unpaid')
                <div class="bg-[#f97316]/10 border border-[#f97316]/20 rounded-xl p-5 text-center mb-8">
                    <p class="text-sm text-gray-400 mb-2">Total yang harus dibayar:</p>
                    <h2 class="text-4xl font-black text-[#f97316] mb-3">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</h2>
                    <p class="text-xs text-gray-500 mb-4">Mohon selesaikan pembayaran sebelum batas waktu berakhir.</p>
                    
                    @if(isset($pgData->checkout_url))
                    <a href="{{ $pgData->checkout_url }}" target="_blank" class="inline-flex bg-[#f97316] hover:bg-[#ea580c] text-white font-bold py-3 px-8 rounded-xl text-sm transition-all shadow-[0_0_15px_rgba(249,115,22,0.3)]">
                        <i class="fas fa-wallet mr-2 mt-0.5"></i> Lanjutkan Pembayaran
                    </a>
                    @else
                    <button disabled class="bg-gray-600 cursor-not-allowed text-white font-bold py-3 px-8 rounded-xl text-sm transition-all opacity-70">
                        <i class="fas fa-times-circle mr-2"></i> Link Pembayaran Tidak Tersedia
                    </button>
                    @endif
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <div>
                    <h3 class="text-sm font-bold text-white mb-4 uppercase tracking-wider border-b border-[#333] pb-2">Detail Pesanan</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Item</span>
                            <span class="text-gray-200 font-semibold text-right">{{ $transaction->items->first()->product_name ?? 'Produk' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Kuantitas</span>
                            <span class="text-gray-200 font-semibold">{{ $transaction->items->first()->quantity ?? 1 }}x</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Data Akun (ID)</span>
                            <span class="text-gray-200 font-mono">{{ $transaction->target_input }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Tanggal Order</span>
                            <span class="text-gray-200">{{ $transaction->created_at->format('d M Y H:i') }}</span>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-bold text-white mb-4 uppercase tracking-wider border-b border-[#333] pb-2">Rincian Pembayaran</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Metode</span>
                            <span class="text-gray-200 font-semibold">{{ $transaction->paymentGateway->name ?? 'Unknown' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Harga Item</span>
                            <span class="text-gray-200">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Biaya Layanan</span>
                            <span class="text-gray-200">Rp {{ number_format($transaction->fee_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between pt-2 border-t border-[#333]">
                            <span class="text-gray-400 font-bold">Total</span>
                            <span class="text-[#f97316] font-black text-lg">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status badges -->
            <div class="flex flex-wrap gap-4 pt-6 border-t border-[#333]">
                <div class="flex-1 min-w-[200px] bg-[#1c1c1c] border border-[#2d2d2d] rounded-lg p-4 flex items-center justify-between">
                    <span class="text-xs text-gray-500 uppercase font-bold">Status Pembayaran</span>
                    @if($transaction->payment_status == 'paid')
                        <span class="bg-green-500/10 text-green-500 text-xs font-bold px-3 py-1 rounded border border-green-500/20">PAID</span>
                    @elseif($transaction->payment_status == 'unpaid')
                        <span class="bg-[#f97316]/10 text-[#f97316] text-xs font-bold px-3 py-1 rounded border border-[#f97316]/20">UNPAID</span>
                    @else
                        <span class="bg-red-500/10 text-red-500 text-xs font-bold px-3 py-1 rounded border border-red-500/20 uppercase">{{ $transaction->payment_status }}</span>
                    @endif
                </div>

                <div class="flex-1 min-w-[200px] bg-[#1c1c1c] border border-[#2d2d2d] rounded-lg p-4 flex items-center justify-between">
                    <span class="text-xs text-gray-500 uppercase font-bold">Status Pesanan</span>
                    @if($transaction->transaction_status == 'success')
                        <span class="bg-blue-500/10 text-blue-400 text-xs font-bold px-3 py-1 rounded border border-blue-500/20">SUCCESS</span>
                    @elseif($transaction->transaction_status == 'pending')
                        <span class="bg-yellow-500/10 text-yellow-500 text-xs font-bold px-3 py-1 rounded border border-yellow-500/20">PENDING</span>
                    @else
                        <span class="bg-red-500/10 text-red-500 text-xs font-bold px-3 py-1 rounded border border-red-500/20 uppercase">{{ $transaction->transaction_status }}</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Footer Invoice -->
        <div class="bg-[#1c1c1c] rounded-b-2xl border border-[#2d2d2d] border-t-0 p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-xs text-gray-500 font-mono">Simpan struk ini sebagai bukti pembayaran yang sah.</p>
            <div class="flex gap-3">
                <a href="{{ route('home') }}" class="px-5 py-2.5 bg-[#222] hover:bg-[#333] border border-[#444] text-white text-xs font-bold rounded-lg transition-colors">
                    Kembali ke Beranda
                </a>
                <button onclick="window.print()" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition-colors flex items-center">
                    <i class="fas fa-print mr-2"></i> Cetak PDF
                </button>
            </div>
        </div>

    </div>
</div>
@endsection