@extends('layouts.front')
@section('title', 'Invoice #' . $transaction->invoice_number)
@section('meta_description', 'Detail invoice dan status pembayaran transaksi Anda.')
@section('canonical', route('transaction.show', $transaction->invoice_number))
@section('robots', 'noindex,nofollow,noarchive')

@section('content')
<div class="container mx-auto px-4 mt-20 md:mt-24 pb-20" x-data="invoiceApp()" x-init="init()">
    <div class="max-w-3xl mx-auto">

        <!-- Status Banner Header -->
        <div class="bg-[#1c1c1c] rounded-t-2xl border border-[#2d2d2d] border-b-0 p-6 md:p-8 text-center relative overflow-hidden">
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-[#f97316] rounded-full mix-blend-multiply filter blur-3xl opacity-10 animate-blob"></div>
            <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl opacity-10 animate-blob animation-delay-2000"></div>

            {{-- Status Icon --}}
            <div class="relative z-10">
                {{-- SUCCESS --}}
                <template x-if="txStatus === 'success'">
                    <i class="fas fa-check-circle text-green-400 text-5xl mb-4 block"></i>
                </template>
                {{-- PROCESSING / PENDING waiting payment --}}
                <template x-if="txStatus === 'processing' || txStatus === 'pending'">
                    <i class="fas fa-clock text-[#f97316] text-5xl mb-4 block animate-pulse"></i>
                </template>
                {{-- FAILED --}}
                <template x-if="txStatus === 'failed'">
                    <i class="fas fa-times-circle text-red-500 text-5xl mb-4 block"></i>
                </template>

                <h1 class="text-2xl font-black text-white uppercase italic tracking-wider mb-2" x-text="statusLabel"></h1>
                <p class="text-gray-400 text-sm">Order ID: <span class="font-mono font-bold text-white">{{ $transaction->invoice_number }}</span></p>

                {{-- Live indicator for pending --}}
                <div x-show="isPolling" class="mt-3 flex items-center justify-center gap-2 text-xs text-amber-400" x-transition>
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping inline-block"></span>
                    Memantau status pembayaran secara real-time...
                </div>
            </div>
        </div>

        <!-- Invoice Details -->
        <div class="bg-[#151515] border border-[#2d2d2d] p-6 md:p-8">

            {{-- Pending Payment CTA --}}
            <div x-show="payStatus === 'unpaid'" class="bg-[#f97316]/10 border border-[#f97316]/20 rounded-xl p-5 text-center mb-8" x-transition>
                <p class="text-sm text-gray-400 mb-2">Total yang harus dibayar:</p>
                <h2 class="text-4xl font-black text-[#f97316] mb-3">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</h2>
                <p class="text-xs text-gray-500 mb-4">Mohon selesaikan pembayaran sebelum batas waktu berakhir.</p>

                @if(isset($pgData->qr_image) || isset($pgData->qr_url))
                <div class="flex flex-col items-center gap-3 mt-2">
                    @if(isset($pgData->qr_image))
                    <img src="{{ $pgData->qr_image }}" alt="QR Code Pembayaran" class="w-48 h-48 rounded-xl border-4 border-white/10">
                    @elseif(isset($pgData->qr_url))
                    <img src="{{ $pgData->qr_url }}" alt="QR Code Pembayaran" class="w-48 h-48 rounded-xl border-4 border-white/10">
                    @endif
                    <p class="text-xs text-gray-400">Scan dengan aplikasi mobile banking / e-wallet kamu</p>
                </div>
                @elseif(isset($pgData->checkout_url))
                <a href="{{ $pgData->checkout_url }}" target="_blank" class="inline-flex bg-[#f97316] hover:bg-[#ea580c] text-white font-bold py-3 px-8 rounded-xl text-sm transition-all shadow-[0_0_15px_rgba(249,115,22,0.3)]">
                    <i class="fas fa-wallet mr-2 mt-0.5"></i> Lanjutkan Pembayaran
                </a>
                @endif
                <button @click="checkStatus()" class="mt-3 text-xs text-amber-400 hover:text-amber-300 underline flex items-center gap-1">
                    <i class="fas fa-sync-alt text-[10px]"></i> Saya Sudah Bayar — Cek Status Sekarang
                </button>
            </div>

            {{-- Success Celebration --}}
            <div x-show="txStatus === 'success'" class="bg-green-500/10 border border-green-500/20 rounded-xl p-5 text-center mb-8" x-transition>
                <p class="text-green-400 font-bold text-lg mb-1">🎉 Transaksi Selesai!</p>
                <p class="text-gray-400 text-sm">Item sudah masuk ke akun kamu. Terima kasih telah berbelanja!</p>
                @if(($canReview ?? false) && ($productId ?? null))
                <button @click="showReviewPopup = true" class="mt-3 inline-flex items-center gap-2 px-5 py-2.5 bg-[#f97316] hover:bg-[#ea580c] text-white text-sm font-bold rounded-xl transition-all shadow-[0_0_15px_rgba(249,115,22,0.3)]">
                    <i class="fas fa-star"></i> Beri Ulasan
                </button>
                @endif
            </div>

            {{-- Review Popup Modal --}}
            @if(($canReview ?? false) && ($productId ?? null))
            <div x-show="showReviewPopup" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
                <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="showReviewPopup = false"></div>
                <div class="relative bg-[#1c1c1c] border border-[#2d2d2d] rounded-2xl w-full max-w-md p-6 shadow-2xl" @click.stop>
                    <button @click="showReviewPopup = false" class="absolute top-4 right-4 text-gray-500 hover:text-white transition-colors">
                        <i class="fas fa-times text-lg"></i>
                    </button>

                    <div class="text-center mb-6">
                        <div class="w-14 h-14 bg-[#f97316]/10 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-star text-[#f97316] text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-white">Beri Ulasan</h3>
                        <p class="text-sm text-gray-400 mt-1">Bagaimana pengalaman kamu dengan <span class="text-white font-semibold">{{ $productName ?? 'produk ini' }}</span>?</p>
                    </div>

                    {{-- Star Rating --}}
                    <div class="flex justify-center gap-2 mb-5">
                        <template x-for="star in 5" :key="star">
                            <button @click="reviewRating = star" class="text-3xl transition-all duration-150 hover:scale-110 focus:outline-none"
                                    :class="star <= reviewRating ? 'text-yellow-400' : 'text-gray-600 hover:text-gray-400'">
                                <i class="fas fa-star"></i>
                            </button>
                        </template>
                    </div>
                    <p class="text-center text-xs mb-4" :class="reviewRating > 0 ? 'text-yellow-400' : 'text-gray-500'" x-text="ratingLabels[reviewRating] || 'Tap bintang untuk memberi rating'"></p>

                    {{-- Comment --}}
                    <textarea x-model="reviewComment" rows="3" maxlength="1000"
                              class="w-full bg-[#0d0d0d] border border-[#333] rounded-xl px-4 py-3 text-white text-sm placeholder-gray-600 focus:outline-none focus:border-[#f97316] transition-colors resize-none"
                              placeholder="Tulis ulasan kamu (opsional)..."></textarea>

                    {{-- Submit --}}
                    <button @click="submitReview()" :disabled="reviewRating === 0 || reviewSubmitting"
                            class="w-full mt-4 py-3 rounded-xl font-bold text-sm transition-all flex items-center justify-center gap-2"
                            :class="reviewRating > 0 && !reviewSubmitting ? 'bg-[#f97316] hover:bg-[#ea580c] text-white shadow-[0_0_15px_rgba(249,115,22,0.3)]' : 'bg-[#222] text-gray-500 cursor-not-allowed'">
                        <i class="fas" :class="reviewSubmitting ? 'fa-spinner fa-spin' : 'fa-paper-plane'"></i>
                        <span x-text="reviewSubmitting ? 'Mengirim...' : 'Kirim Ulasan'"></span>
                    </button>

                    {{-- Success/Error Message --}}
                    <div x-show="reviewMessage" x-transition class="mt-3 text-center text-sm rounded-lg p-3"
                         :class="reviewSuccess ? 'bg-green-500/10 text-green-400 border border-green-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20'"
                         x-text="reviewMessage"></div>
                </div>
            </div>
            @endif

            <!-- Order & Payment Details -->
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
                            <span class="text-gray-200 font-semibold">{{ $transaction->paymentGateway->name ?? 'Saldo Wallet' }}</span>
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

            <!-- Status Timeline -->
            <div class="mt-6 mb-6">
                <h3 class="text-sm font-bold text-white mb-4 uppercase tracking-wider border-b border-[#333] pb-2">Timeline Status</h3>
                <ol class="relative border-l border-[#2d2d2d] ml-3">
                    <li class="mb-5 ml-6">
                        <span class="absolute flex items-center justify-center w-6 h-6 bg-green-900/50 rounded-full -left-3 ring-4 ring-[#151515] border border-green-500">
                            <i class="fas fa-check text-green-400 text-[9px]"></i>
                        </span>
                        <p class="text-xs font-bold text-white">Pesanan Dibuat</p>
                        <p class="text-[10px] text-gray-500">{{ $transaction->created_at->format('d M Y H:i:s') }}</p>
                    </li>
                    <li class="mb-5 ml-6">
                        <span class="absolute flex items-center justify-center w-6 h-6 rounded-full -left-3 ring-4 ring-[#151515]"
                              :class="payStatus === 'paid' ? 'bg-green-900/50 border border-green-500' : 'bg-amber-900/50 border border-amber-500'">
                            <i class="text-[9px]" :class="payStatus === 'paid' ? 'fas fa-check text-green-400' : 'fas fa-clock text-amber-400'"></i>
                        </span>
                        <p class="text-xs font-bold" :class="payStatus === 'paid' ? 'text-white' : 'text-amber-400'">Pembayaran</p>
                        <p class="text-[10px] text-gray-500" x-text="payStatus === 'paid' ? 'Lunas ✅' : 'Menunggu pembayaran...'"></p>
                    </li>
                    <li class="ml-6">
                        <span class="absolute flex items-center justify-center w-6 h-6 rounded-full -left-3 ring-4 ring-[#151515]"
                              :class="txStatus === 'success' ? 'bg-blue-900/50 border border-blue-400' : txStatus === 'failed' ? 'bg-red-900/50 border border-red-400' : 'bg-[#333] border border-[#444]'">
                            <i class="text-[9px]" :class="txStatus === 'success' ? 'fas fa-check text-blue-400' : txStatus === 'failed' ? 'fas fa-times text-red-400' : 'fas fa-ellipsis-h text-gray-500'"></i>
                        </span>
                        <p class="text-xs font-bold" :class="txStatus === 'success' ? 'text-blue-400' : txStatus === 'failed' ? 'text-red-400' : 'text-gray-500'">Proses Pesanan</p>
                        <p class="text-[10px] text-gray-500" x-text="txStatus === 'success' ? 'Selesai & terkirim! 🎉' : txStatus === 'failed' ? 'Gagal ❌' : 'Diproses...'"></p>
                    </li>
                </ol>
            </div>

            <!-- Status Badges -->
            <div class="flex flex-wrap gap-4 pt-6 border-t border-[#333]">
                <div class="flex-1 min-w-[200px] bg-[#1c1c1c] border border-[#2d2d2d] rounded-lg p-4 flex items-center justify-between">
                    <span class="text-xs text-gray-500 uppercase font-bold">Status Pembayaran</span>
                    <span x-text="payStatus.toUpperCase()"
                          :class="payStatus === 'paid' ? 'bg-green-500/10 text-green-500 border-green-500/20' : payStatus === 'failed' ? 'bg-red-500/10 text-red-500 border-red-500/20' : 'bg-[#f97316]/10 text-[#f97316] border-[#f97316]/20'"
                          class="text-xs font-bold px-3 py-1 rounded border"></span>
                </div>
                <div class="flex-1 min-w-[200px] bg-[#1c1c1c] border border-[#2d2d2d] rounded-lg p-4 flex items-center justify-between">
                    <span class="text-xs text-gray-500 uppercase font-bold">Status Pesanan</span>
                    <span x-text="txStatus.toUpperCase()"
                          :class="txStatus === 'success' ? 'bg-blue-500/10 text-blue-400 border-blue-500/20' : txStatus === 'failed' ? 'bg-red-500/10 text-red-500 border-red-500/20' : 'bg-yellow-500/10 text-yellow-500 border-yellow-500/20'"
                          class="text-xs font-bold px-3 py-1 rounded border"></span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-[#1c1c1c] rounded-b-2xl border border-[#2d2d2d] border-t-0 p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-xs text-gray-500 font-mono">Simpan struk ini sebagai bukti pembayaran yang sah.</p>
            <div class="flex gap-3">
                <a href="{{ route('home') }}" class="px-5 py-2.5 bg-[#222] hover:bg-[#333] border border-[#444] text-white text-xs font-bold rounded-lg transition-colors">
                    Kembali ke Beranda
                </a>
                <a href="{{ route('transaction.receipt', $transaction->invoice_number) }}" class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white text-xs font-bold rounded-lg transition-colors flex items-center">
                    <i class="fas fa-receipt mr-2"></i> Lihat Struk
                </a>
                <button onclick="window.print()" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition-colors flex items-center">
                    <i class="fas fa-print mr-2"></i> Cetak PDF
                </button>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
function invoiceApp() {
    return {
        txStatus: '{{ $transaction->transaction_status }}',
        payStatus: '{{ $transaction->payment_status }}',
        isPolling: false,
        pollInterval: null,
        pollCount: 0,
        maxPolls: 60, // Max 5 min (60 x 5s)

        // Review state
        showReviewPopup: false,
        reviewRating: 0,
        reviewComment: '',
        reviewSubmitting: false,
        reviewMessage: '',
        reviewSuccess: false,
        ratingLabels: { 1: '😞 Sangat Buruk', 2: '😕 Buruk', 3: '😐 Cukup', 4: '😊 Bagus', 5: '🤩 Sangat Bagus!' },

        get statusLabel() {
            if (this.txStatus === 'success') return 'Transaksi Berhasil';
            if (this.txStatus === 'failed') return 'Transaksi Gagal';
            if (this.payStatus === 'unpaid') return 'Menunggu Pembayaran';
            return 'Sedang Diproses';
        },

        init() {
            // Start polling if not yet final
            if (this.txStatus !== 'success' && this.txStatus !== 'failed') {
                this.startPolling();
            }
        },

        startPolling() {
            this.isPolling = true;
            this.pollInterval = setInterval(() => {
                this.pollCount++;
                if (this.pollCount >= this.maxPolls) {
                    this.stopPolling();
                    return;
                }
                this.checkStatus();
            }, 5000); // Every 5 seconds
        },

        stopPolling() {
            if (this.pollInterval) {
                clearInterval(this.pollInterval);
                this.pollInterval = null;
            }
            this.isPolling = false;
        },

        async checkStatus() {
            try {
                const response = await fetch('/api/v1/transaction/{{ $transaction->invoice_number }}/status');
                const data = await response.json();

                if (data.transaction_status) {
                    this.txStatus = data.transaction_status;
                }
                if (data.payment_status) {
                    this.payStatus = data.payment_status;
                }

                // Stop polling once final
                if (this.txStatus === 'success' || this.txStatus === 'failed' || this.payStatus === 'paid') {
                    this.stopPolling();
                    // If paid but still processing, reload page to show latest state
                    if (this.payStatus === 'paid' && this.txStatus !== 'success' && this.txStatus !== 'failed') {
                        window.location.reload();
                    }
                    // Auto-show review popup on success
                    if (this.txStatus === 'success') {
                        this.autoShowReview();
                    }
                }
            } catch (e) {
                console.error('Status polling error:', e);
            }
        },

        autoShowReview() {
            @if(($canReview ?? false) && ($productId ?? null))
            setTimeout(() => { this.showReviewPopup = true; }, 1500);
            @endif
        },

        async submitReview() {
            if (this.reviewRating === 0 || this.reviewSubmitting) return;
            this.reviewSubmitting = true;
            this.reviewMessage = '';
            try {
                const res = await fetch('{{ route("review.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        product_id: {{ $productId ?? 'null' }},
                        transaction_id: {{ $transaction->id }},
                        rating: this.reviewRating,
                        comment: this.reviewComment,
                    })
                });
                const data = await res.json();
                this.reviewMessage = data.message || (data.success ? 'Ulasan terkirim!' : 'Gagal mengirim ulasan.');
                this.reviewSuccess = !!data.success;
                if (data.success) {
                    setTimeout(() => { this.showReviewPopup = false; }, 2000);
                }
            } catch (e) {
                this.reviewMessage = 'Terjadi kesalahan. Coba lagi nanti.';
                this.reviewSuccess = false;
            } finally {
                this.reviewSubmitting = false;
            }
        }
    };
}
</script>
@endpush
@endsection