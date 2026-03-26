@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Detail Transaksi: {{ $transaction->invoice_number }}</h1>
        <a href="{{ route('admin.transactions.index') }}" class="text-blue-500 hover:underline"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Informasi Utama -->
        <div class="bg-gray-800 rounded-lg shadow-lg border border-gray-700-md p-6">
            <h3 class="text-lg font-semibold mb-4 border-b border-gray-700 pb-2">Informasi Transaksi</h3>
            <div class="grid grid-cols-3 gap-y-3 gap-x-2 text-sm">
                <div class="text-gray-400">Tanggal</div>
                <div class="col-span-2 font-medium text-gray-100">: {{ $transaction->created_at->format('d M Y H:i:s') }}</div>

                <div class="text-gray-400">Invoice</div>
                <div class="col-span-2 font-medium text-blue-600">: {{ $transaction->invoice_number }}</div>

                <div class="text-gray-400">SN / Pesan Provider</div>
                <div class="col-span-2 font-medium text-gray-100">: {{ $transaction->sn ?? '-' }}</div>

                <div class="text-gray-400">UID User</div>
                <div class="col-span-2 font-medium text-gray-100">: {{ $transaction->user_id ? 'User ID: '.$transaction->user_id : 'Guest' }}</div>

                <div class="text-gray-400 mt-4 font-bold">Ubah Status</div>
                <div class="col-span-2 mt-4">
                    <form action="{{ route('admin.transactions.update', $transaction->id) }}" method="POST" class="flex gap-2">
                        @csrf
                        @method('PUT')
                        <select name="transaction_status" class="border-gray-600 rounded px-2 py-1 text-sm bg-gray-900/50">
                            <option value="PENDING" {{ $transaction->transaction_status == 'PENDING' ? 'selected' : '' }}>PENDING</option>
                            <option value="PAID" {{ $transaction->transaction_status == 'PAID' ? 'selected' : '' }}>PAID</option>
                            <option value="PROCESSING" {{ $transaction->transaction_status == 'PROCESSING' ? 'selected' : '' }}>PROCESSING</option>
                            <option value="SUCCESS" {{ $transaction->transaction_status == 'SUCCESS' ? 'selected' : '' }}>SUCCESS</option>
                            <option value="FAILED" {{ $transaction->transaction_status == 'FAILED' ? 'selected' : '' }}>FAILED</option>
                            <option value="EXPIRED" {{ $transaction->transaction_status == 'EXPIRED' ? 'selected' : '' }}>EXPIRED</option>
                        </select>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs px-3 py-1 rounded">Update</button>
                    </form>
                    <p class="text-xs text-gray-400 mt-1">Ubah manual jika webhook/sistem gagal update otomatis.</p>
                </div>
            </div>
        </div>

        <!-- Informasi Pembayaran & Pelanggan -->
        <div class="bg-gray-800 rounded-lg shadow-lg border border-gray-700-md p-6">
            <h3 class="text-lg font-semibold mb-4 border-b border-gray-700 pb-2">Pembayaran & Target</h3>
            <div class="grid grid-cols-3 gap-y-3 gap-x-2 text-sm">
                <div class="text-gray-400">Target / Nomor</div>
                <div class="col-span-2 font-medium text-gray-100">: {{ $transaction->customer_id ?? '-' }}</div>

                <div class="text-gray-400">Zone ID</div>
                <div class="col-span-2 font-medium text-gray-100">: {{ $transaction->zone_id ?? '-' }}</div>

                <div class="text-gray-400">Metode Bayar</div>
                <div class="col-span-2 font-medium text-gray-100">: {{ $transaction->payment_method ?? '-' }}</div>

                <div class="text-gray-400">Ref Pembayaran</div>
                <div class="col-span-2 font-medium text-gray-100 break-all">: {{ $transaction->payment_reference ?? '-' }}</div>

                <div class="text-gray-400">Total Harga</div>
                <div class="col-span-2 font-bold text-gray-100">: Rp {{ number_format($transaction->grand_total, 0, ',', '.') }}</div>

                <div class="text-gray-400">Fee</div>
                <div class="col-span-2 font-medium text-gray-100">: Rp {{ number_format($transaction->total_fee, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <!-- Rincian Produk -->
    <div class="mt-6 bg-gray-800 rounded-lg shadow-lg border border-gray-700-md overflow-hidden">
        <h3 class="text-lg font-semibold p-4 bg-gray-900/50 border-b border-gray-700">Rincian Item (Produk)</h3>
        <div class="p-4">
            <table class="min-w-full text-sm text-left">
                <thead class="text-xs text-gray-400 uppercase bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-2">Produk</th>
                        <th class="px-4 py-2">Harga</th>
                        <th class="px-4 py-2">Vendor / Provider</th>
                        <th class="px-4 py-2">Untung/Margin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @foreach($transaction->items as $item)
                    <tr>
                        <td class="px-4 py-3 font-medium text-gray-100">{{ $item->product_name }}</td>
                        <td class="px-4 py-3">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">{{ optional($item->product)->provider->name ?? 'Unknown' }}</td>
                        <td class="px-4 py-3 text-green-600">
                            @if($item->product)
                                +Rp {{ number_format($item->price - $item->product->vendor_price, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
