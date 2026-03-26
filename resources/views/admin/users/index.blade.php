@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Manajemen Pengguna</h1>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif
    
    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="bg-gray-800 rounded-lg shadow-lg border border-gray-700 p-4 mb-6">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Cari (Nama/Email/HP)</label>
                <input type="text" name="search" value="{{ request('search') }}" class="rounded-md border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:ring-blue-500 bg-gray-900/50 text-gray-100 border px-3 py-2" placeholder="Keyword...">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Role</label>
                <select name="role" class="rounded-md border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:ring-blue-500 bg-gray-900/50 text-gray-100 border px-3 py-2">
                    <option value="">Semua Role</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="member" {{ request('role') == 'member' ? 'selected' : '' }}>Member</option>
                </select>
            </div>
            <div>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">
                    <i class="fas fa-search mr-1"></i> Filter
                </button>
                <a href="{{ route('admin.users.index') }}" class="bg-gray-900/500 hover:bg-gray-600 text-white px-4 py-2 rounded ml-2">Reset</a>
            </div>
        </form>
    </div>

    <div class="bg-gray-800 rounded-lg shadow-lg border border-gray-700 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-900/50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Info Pengguna</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Kontak</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Saldo</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Role</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-gray-800 text-white divide-y divide-gray-700">
                @forelse($users as $user)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-bold text-gray-100">{{ $user->name }}</div>
                        <div class="text-xs text-gray-400">ID: {{ $user->id }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-100">{{ $user->email }}</div>
                        <div class="text-sm text-gray-400">{{ $user->phone ?? '-' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-100 font-medium">
                        Rp {{ number_format($user->balance, 0, ',', '.') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($user->role === 'admin')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">Admin</span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-900 text-white">Member</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-3"><i class="fas fa-edit"></i> Edit</a>
                        @if($user->id !== auth()->id())
                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus pengguna ini beserta datanya?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900"><i class="fas fa-trash"></i> Hapus</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-400">Tidak ada data pengguna.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="mt-4">
        {{ $users->links() }}
    </div>
</div>
@endsection
