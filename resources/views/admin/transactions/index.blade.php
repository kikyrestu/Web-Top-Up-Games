@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Riwayat Transaksi</h1>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Filter Form -->
    <div class="bg-gray-800 rounded-lg shadow-lg border border-gray-700 p-4 mb-6">
        <form method="GET" action="{{ route('admin.transactions.index') }}" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Cari Invoice</label>
                <input type="text" name="invoice_number" value="{{ request('invoice_number') }}" class="rounded-md border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:ring-blue-500 bg-gray-900/50 text-gray-100 border px-3 py-2" placeholder="INV-XXXX">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Target HP/ID</label>
                <input type="text" name="customer_id" value="{{ request('customer_id') }}" class="rounded-md border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:ring-blue-500 bg-gray-900/50 text-gray-100 border px-3 py-2" placeholder="Nomor HP/ID">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Status</label>
                <select name="status" class="rounded-md border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:ring-blue-500 bg-gray-900/50 text-gray-100 border px-3 py-2">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>PENDING</option>
                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>PROCESSING</option>
                    <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>SUCCESS</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>FAILED</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>EXPIRED</option>
                </select>
            </div>
            <div>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">
                    <i class="fas fa-search mr-1"></i> Filter
                </button>
                <a href="{{ route('admin.transactions.index') }}" class="bg-gray-900/500 hover:bg-gray-600 text-white px-4 py-2 rounded ml-2">Reset</a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-gray-800 rounded-lg shadow-lg border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-700 whitespace-nowrap">
                <thead class="bg-gray-900/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Invoice</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Target</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-gray-800 text-white divide-y divide-gray-700">
                    @forelse($transactions as $trx)
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-100">{{ $trx->created_at->format('d M Y H:i') }}</td>
                        <td class="px-6 py-4 text-sm font-medium text-blue-600">{{ $trx->invoice_number }}</td>
                        <td class="px-6 py-4 text-sm text-gray-400">{{ $trx->customer_id ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-100">Rp {{ number_format($trx->total_amount, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-sm">
                            @php
                                $color = match($trx->transaction_status) {
                                    'success' => 'bg-emerald-900/30 text-emerald-400 border border-emerald-800',
                                    'pending' => 'bg-amber-900/30 text-amber-400 border border-amber-800',
                                    'processing' => 'bg-indigo-900/30 text-indigo-400 border border-indigo-800',
                                    'failed', 'expired' => 'bg-rose-900/30 text-rose-400 border border-rose-800',
                                    default => 'bg-gray-900 text-white'
                                };
                            @endphp
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $color }}">
                                {{ strtoupper($trx->transaction_status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <a href="{{ route('admin.transactions.show', $trx->id) }}" class="inline-flex items-center px-3 py-1 bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200 transition">
                                <i class="fas fa-eye mr-1"></i> Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-400">Tidak ada data transaksi.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="mt-4">
        {{ $transactions->links() }}
    </div>
</div>
@endsection
