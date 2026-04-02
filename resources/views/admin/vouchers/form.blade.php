@extends('layouts.admin')
@section('title', $voucher->id ? 'Edit Voucher' : 'Buat Voucher')
@section('header', $voucher->id ? 'Edit Voucher: ' . $voucher->code : 'Buat Voucher Baru')

@section('content')
<div class="max-w-2xl">
    <form action="{{ $voucher->id ? route('admin.vouchers.update', $voucher) : route('admin.vouchers.store') }}" method="POST">
        @csrf
        @if($voucher->id) @method('PUT') @endif

        <div class="bg-dark-800/40 backdrop-blur-xl rounded-2xl border border-dark-600/50 p-6 space-y-5">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-bold text-gray-400 uppercase mb-1 block">Kode Voucher *</label>
                    <input type="text" name="code" value="{{ old('code', strtoupper($voucher->code)) }}"
                           style="text-transform:uppercase" placeholder="DISKON10"
                           class="w-full bg-dark-900 border border-dark-600 text-white rounded-lg p-3 text-sm font-mono font-black tracking-wider focus:border-[#f97316] focus:outline-none">
                    @error('code') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-400 uppercase mb-1 block">Deskripsi</label>
                    <input type="text" name="description" value="{{ old('description', $voucher->description) }}" placeholder="Diskon spesial lebaran..."
                           class="w-full bg-dark-900 border border-dark-600 text-white rounded-lg p-3 text-sm focus:border-[#f97316] focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-bold text-gray-400 uppercase mb-1 block">Tipe Diskon *</label>
                    <select name="type" class="w-full bg-dark-900 border border-dark-600 text-white rounded-lg p-3 text-sm focus:border-[#f97316] focus:outline-none">
                        <option value="percentage" {{ old('type', $voucher->type) === 'percentage' ? 'selected' : '' }}>Persentase (%)</option>
                        <option value="flat" {{ old('type', $voucher->type) === 'flat' ? 'selected' : '' }}>Nominal (Rp)</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-400 uppercase mb-1 block">Nilai Diskon *</label>
                    <input type="number" name="value" value="{{ old('value', $voucher->value) }}" step="0.01" min="0" placeholder="10"
                           class="w-full bg-dark-900 border border-dark-600 text-white rounded-lg p-3 text-sm focus:border-[#f97316] focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-bold text-gray-400 uppercase mb-1 block">Maks. Potongan (Rp) <span class="text-gray-600 normal-case font-normal">untuk %, opsional</span></label>
                    <input type="number" name="max_discount" value="{{ old('max_discount', $voucher->max_discount) }}" step="1000" min="0" placeholder="50000"
                           class="w-full bg-dark-900 border border-dark-600 text-white rounded-lg p-3 text-sm focus:border-[#f97316] focus:outline-none">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-400 uppercase mb-1 block">Min. Pembelian (Rp) *</label>
                    <input type="number" name="min_purchase" value="{{ old('min_purchase', $voucher->min_purchase ?? 0) }}" step="1000" min="0" placeholder="0"
                           class="w-full bg-dark-900 border border-dark-600 text-white rounded-lg p-3 text-sm focus:border-[#f97316] focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-bold text-gray-400 uppercase mb-1 block">Maks. Total Penggunaan <span class="text-gray-600 font-normal normal-case">kosong = unlimited</span></label>
                    <input type="number" name="max_uses" value="{{ old('max_uses', $voucher->max_uses) }}" min="1" placeholder="100"
                           class="w-full bg-dark-900 border border-dark-600 text-white rounded-lg p-3 text-sm focus:border-[#f97316] focus:outline-none">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-400 uppercase mb-1 block">Maks. Pemakaian per User *</label>
                    <input type="number" name="max_uses_per_user" value="{{ old('max_uses_per_user', $voucher->max_uses_per_user ?? 1) }}" min="1"
                           class="w-full bg-dark-900 border border-dark-600 text-white rounded-lg p-3 text-sm focus:border-[#f97316] focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-bold text-gray-400 uppercase mb-1 block">Mulai Berlaku</label>
                    <input type="datetime-local" name="starts_at" value="{{ old('starts_at', $voucher->starts_at?->format('Y-m-d\TH:i')) }}"
                           class="w-full bg-dark-900 border border-dark-600 text-white rounded-lg p-3 text-sm focus:border-[#f97316] focus:outline-none">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-400 uppercase mb-1 block">Expired</label>
                    <input type="datetime-local" name="expires_at" value="{{ old('expires_at', $voucher->expires_at?->format('Y-m-d\TH:i')) }}"
                           class="w-full bg-dark-900 border border-dark-600 text-white rounded-lg p-3 text-sm focus:border-[#f97316] focus:outline-none">
                </div>
            </div>

            <div class="flex items-center gap-3">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $voucher->is_active ?? true) ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-dark-600 bg-dark-900 text-[#f97316] focus:ring-[#f97316]/40">
                    <span class="text-sm text-gray-300 font-bold">Voucher Aktif</span>
                </label>
            </div>
        </div>

        <div class="mt-4 flex gap-3">
            <button type="submit" class="bg-gradient-to-r from-[#f97316] to-[#ea580c] text-white font-bold py-3 px-6 rounded-xl text-sm hover:-translate-y-0.5 transform transition">
                <i class="fas fa-save mr-2"></i>{{ $voucher->id ? 'Simpan Perubahan' : 'Buat Voucher' }}
            </button>
            <a href="{{ route('admin.vouchers.index') }}" class="bg-dark-700 text-gray-300 hover:text-white font-bold py-3 px-6 rounded-xl text-sm transition">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
