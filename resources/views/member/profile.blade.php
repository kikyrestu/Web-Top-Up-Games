@extends('layouts.front')

@section('title', 'Profil Member')

@section('content')
<div class="max-w-[1280px] mx-auto px-4 mt-8">
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar -->
        <div class="w-full lg:w-1/4">
            <x-member-sidebar />
        </div>

        <!-- Main Content -->
        <div class="w-full lg:w-3/4 space-y-6">

            @if(session('success'))
                <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 p-4 rounded-xl flexitems-center">
                    <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                </div>
            @endif

            <div class="bg-[#1d2235] border border-[#2d3748] rounded-2xl shadow-lg overflow-hidden">
                <div class="p-6 border-b border-[#2d3748] bg-[#252b43]">
                    <h2 class="text-xl font-bold text-white"><i class="fas fa-user-edit text-[#f97316] mr-2"></i> Pengaturan Profil</h2>
                </div>

                <div class="p-6">
                    <form action="{{ route('member.profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">Nama Lengkap</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                                    class="w-full bg-[#0f1118] border border-[#2d3748] text-white text-sm rounded-xl px-4 py-3 focus:outline-none focus:border-[#f97316] focus:ring-1 focus:ring-[#f97316] transition">
                                @error('name') <p class="text-red-400 text-xs mt-1.5">{{ $message }}</p> @enderror
                            </div>

                            <!-- WhatsApp -->
                            <div>
                                <label for="whatsapp" class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">Nomor WhatsApp</label>
                                <input type="text" name="whatsapp" id="whatsapp" value="{{ old('whatsapp', $user->whatsapp) }}" required
                                    class="w-full bg-[#0f1118] border border-[#2d3748] text-white text-sm rounded-xl px-4 py-3 focus:outline-none focus:border-[#f97316] focus:ring-1 focus:ring-[#f97316] transition">
                                @error('whatsapp') <p class="text-red-400 text-xs mt-1.5">{{ $message }}</p> @enderror
                            </div>

                            <!-- Email -->
                            <div class="md:col-span-2">
                                <label for="email" class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">Alamat Email</label>
                                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                                    class="w-full bg-[#0f1118] border border-[#2d3748] text-white text-sm rounded-xl px-4 py-3 focus:outline-none focus:border-[#f97316] focus:ring-1 focus:ring-[#f97316] transition">
                                @error('email') <p class="text-red-400 text-xs mt-1.5">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="px-6 py-3 bg-[#f97316] hover:bg-[#ea580c] text-white font-bold rounded-xl transition-all shadow-lg shadow-[#f97316]/20">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Update Password -->
            <div class="bg-[#1d2235] border border-[#2d3748] rounded-2xl shadow-lg overflow-hidden">
                <div class="p-6 border-b border-[#2d3748] bg-[#252b43]">
                    <h2 class="text-xl font-bold text-white"><i class="fas fa-lock text-[#f97316] mr-2"></i> Ganti Password</h2>
                </div>

                <div class="p-6">
                    <form action="{{ route('member.profile.password') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="space-y-6">
                            <!-- Current Password -->
                            <div x-data="{ show: false }">
                                <label for="current_password" class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">Password Saat Ini</label>
                                <div class="relative md:w-1/2">
                                    <input :type="show ? 'text' : 'password'" name="current_password" id="current_password" required
                                        class="w-full bg-[#0f1118] border border-[#2d3748] text-white text-sm rounded-xl px-4 py-3 pr-10 focus:outline-none focus:border-[#f97316] focus:ring-1 focus:ring-[#f97316] transition">
                                    <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-[#f97316] transition-colors focus:outline-none">
                                        <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                                    </button>
                                </div>
                                @error('current_password') <p class="text-red-400 text-xs mt-1.5">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- New Password -->
                                <div x-data="{ show: false }">
                                    <label for="password" class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">Password Baru</label>
                                    <div class="relative">
                                        <input :type="show ? 'text' : 'password'" name="password" id="password" required
                                            class="w-full bg-[#0f1118] border border-[#2d3748] text-white text-sm rounded-xl px-4 py-3 pr-10 focus:outline-none focus:border-[#f97316] focus:ring-1 focus:ring-[#f97316] transition">
                                        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-[#f97316] transition-colors focus:outline-none">
                                            <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                                        </button>
                                    </div>
                                    @error('password') <p class="text-red-400 text-xs mt-1.5">{{ $message }}</p> @enderror
                                </div>

                                <!-- Confirm Password -->
                                <div x-data="{ show: false }">
                                    <label for="password_confirmation" class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">Konfirmasi Password</label>
                                    <div class="relative">
                                        <input :type="show ? 'text' : 'password'" name="password_confirmation" id="password_confirmation" required
                                            class="w-full bg-[#0f1118] border border-[#2d3748] text-white text-sm rounded-xl px-4 py-3 pr-10 focus:outline-none focus:border-[#f97316] focus:ring-1 focus:ring-[#f97316] transition">
                                        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-[#f97316] transition-colors focus:outline-none">
                                            <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="px-6 py-3 bg-[#2d3748] hover:bg-[#3f4a61] text-white font-bold rounded-xl transition-all border border-[#3f4a61]">
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
