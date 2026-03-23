<x-layouts.app :title="'Cek Transaksi'">
    <div class="panel" style="max-width:680px; margin:0 auto;">
        <h1>Cek Transaksi</h1>
        <p class="muted">Masukkan kode order untuk melihat status terbaru transaksi.</p>

        <form method="post" action="{{ route('public.check-transaction.submit') }}" class="grid" style="margin-top:12px;">
            @csrf
            <div>
                <label for="order_code">Order Code</label>
                <input id="order_code" name="order_code" type="text" placeholder="contoh: ORD-20260323-XXXXXX" required>
            </div>
            <div style="display:flex; justify-content:flex-end;">
                <button class="btn" type="submit">Lihat Status</button>
            </div>
        </form>
    </div>
</x-layouts.app>
