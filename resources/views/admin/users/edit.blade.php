@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Edit Pengguna: {{ $user->name }}</h1>
        <a href="{{ route('admin.users.index') }}" class="text-blue-600 hover:text-blue-800"><i class="fas fa-arrow-left mt-2"></i> Kembali ke Daftar Pengguna</a>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif
    
    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-gray-800 rounded-lg shadow-lg border border-gray-700 overflow-hidden p-6 max-w-3xl">
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Data Pribadi -->
                <div>
                    <h3 class="text-lg font-medium text-gray-100 border-b border-gray-700 pb-2 mb-4">Data Profil</h3>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-300 mb-1">Nama Lengkap</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full rounded-md border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:ring-blue-500 bg-gray-900/50 text-gray-100 border px-3 py-2" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-300 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full rounded-md border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:ring-blue-500 bg-gray-900/50 text-gray-100 border px-3 py-2" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-300 mb-1">Nomor WhatsApp/HP</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full rounded-md border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:ring-blue-500 bg-gray-900/50 text-gray-100 border px-3 py-2">
                    </div>
                </div>
                
                <!-- Pengaturan Akun -->
                <div>
                    <h3 class="text-lg font-medium text-gray-100 border-b border-gray-700 pb-2 mb-4">Pengaturan Akun</h3>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-300 mb-1">Role/Akses</label>
                        <select name="role" class="w-full rounded-md border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:ring-blue-500 bg-gray-900/50 text-gray-100 border px-3 py-2" {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                            <option value="member" {{ old('role', $user->role) == 'member' ? 'selected' : '' }}>Member Biasa</option>
                            <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Administrator</option>
                        </select>
                        @if($user->id === auth()->id())
                            <div class="text-xs text-red-500 mt-1">Anda tidak dapat mengubah role akun Anda sendiri.</div>
                            <input type="hidden" name="role" value="{{ $user->role }}">
                        @endif
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-300 mb-1">Saldo (Rp)</label>
                        <input type="number" name="balance" value="{{ old('balance', $user->balance) }}" class="w-full rounded-md border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:ring-blue-500 bg-gray-900/50 text-gray-100 border px-3 py-2" min="0">
                        <div class="text-xs text-gray-400 mt-1">Ubah manual saldo pengguna.</div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-300 mb-1">Password Baru (Opsional)</label>
                        <input type="password" name="password" class="w-full rounded-md border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:ring-blue-500 bg-gray-900/50 text-gray-100 border px-3 py-2">
                        <div class="text-xs text-gray-400 mt-1">Kosongkan jika tidak ingin mengubah password.</div>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 border-t pt-4">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded focus:outline-none focus:shadow-outline">
                    <i class="fas fa-save mr-1"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
