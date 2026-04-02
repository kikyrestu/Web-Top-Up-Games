<x-guest-layout>
    <div x-data="{
        showModal: false,
        channel: '',
        emailVal: '',
        waVal: '',
        checkForm() {
            const form = this.$refs.registerForm;
            if (form.reportValidity()) {
                this.emailVal = form.email.value;
                this.waVal = form.whatsapp.value;
                this.showModal = true;
            }
        },
        submitForm(selectedChannel) {
            this.channel = selectedChannel;
            this.$nextTick(() => {
                this.$refs.registerForm.submit();
            });
        }
    }">
        <div class="text-center mb-6">
            <div class="w-14 h-14 bg-[#f97316]/10 border border-[#f97316]/20 rounded-full flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-user-plus text-xl text-[#f97316]"></i>
            </div>
            <h2 class="text-xl font-extrabold text-white">Buat Akun Baru</h2>
            <p class="text-sm text-gray-500 mt-1">Daftar gratis untuk pengalaman top up terbaik</p>
        </div>

        <form method="POST" action="{{ route('register') }}" x-ref="registerForm">
            @csrf
            <input type="hidden" name="otp_channel" x-model="channel">

        <!-- Name -->
        <div>
            <label for="name" class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">Nama Lengkap</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                   class="w-full bg-[#0f1118] border border-[#2d2d2d] text-white text-sm rounded-lg px-4 py-3 focus:outline-none focus:border-[#f97316] focus:ring-1 focus:ring-[#f97316] transition placeholder-gray-600"
                   placeholder="Nama kamu">
            @error('name') <p class="text-red-400 text-xs mt-1.5">{{ $message }}</p> @enderror
        </div>

        <!-- Username -->
        <div class="mt-4">
            <label for="username" class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">Username</label>
            <input id="username" type="text" name="username" value="{{ old('username') }}" required autocomplete="username"
                   class="w-full bg-[#0f1118] border border-[#2d2d2d] text-white text-sm rounded-lg px-4 py-3 focus:outline-none focus:border-[#f97316] focus:ring-1 focus:ring-[#f97316] transition placeholder-gray-600"
                   placeholder="username_unik">
            @error('username') <p class="text-red-400 text-xs mt-1.5">{{ $message }}</p> @enderror
        </div>

        <!-- WhatsApp -->
        <div class="mt-4">
            <label for="whatsapp" class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">Nomor WhatsApp</label>
            <input id="whatsapp" type="text" name="whatsapp" value="{{ old('whatsapp') }}" required autocomplete="tel"
                   class="w-full bg-[#0f1118] border border-[#2d2d2d] text-white text-sm rounded-lg px-4 py-3 focus:outline-none focus:border-[#f97316] focus:ring-1 focus:ring-[#f97316] transition placeholder-gray-600"
                   placeholder="08123456789">
            @error('whatsapp') <p class="text-red-400 text-xs mt-1.5">{{ $message }}</p> @enderror
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <label for="email" class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                   class="w-full bg-[#0f1118] border border-[#2d2d2d] text-white text-sm rounded-lg px-4 py-3 focus:outline-none focus:border-[#f97316] focus:ring-1 focus:ring-[#f97316] transition placeholder-gray-600"
                   placeholder="email@gmail.com">
            @error('email') <p class="text-red-400 text-xs mt-1.5">{{ $message }}</p> @enderror
        </div>

        <!-- Referral Code (Optional) -->
        {{--
        <div class="mt-4">
            <label for="referral_code" class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">Kode Referral (Opsional)</label>
            <input id="referral_code" type="text" name="referral_code" value="{{ old('referral_code', request()->query('ref')) }}" autocomplete="off"
                   class="w-full bg-[#0f1118] border border-[#2d2d2d] text-white text-sm rounded-lg px-4 py-3 focus:outline-none focus:border-[#f97316] focus:ring-1 focus:ring-[#f97316] transition placeholder-gray-600"
                   placeholder="Punya kode rujukan? (Opsional)">
            @error('referral_code') <p class="text-red-400 text-xs mt-1.5">{{ $message }}</p> @enderror
        </div>
        --}}

        <!-- Password -->
        <div class="mt-4" x-data="{ 
            show: false, 
            pwd: '',
            get score() {
                 let s = 0;
                 if(this.pwd.length >= 8) s++;
                 if(/[A-Z]/.test(this.pwd)) s++;
                 if(/[a-z]/.test(this.pwd)) s++;
                 if(/[0-9]/.test(this.pwd)) s++;
                 if(/[^A-Za-z0-9]/.test(this.pwd)) s++;
                 return s;
             },
             get strengthText() {
                 if(this.pwd.length === 0) return '';
                 if(this.score <= 2) return 'Sangat Lemah';
                 if(this.score <= 4) return 'Sedang';
                 return 'Kuat';
             },
             get strengthColor() {
                 if(this.score <= 2) return 'text-red-400';
                 if(this.score <= 4) return 'text-yellow-400';
                 return 'text-green-400';
             },
             get barColor() {
                 if(this.score === 0) return 'bg-gray-700';
                 if(this.score <= 2) return 'bg-red-500';
                 if(this.score <= 4) return 'bg-yellow-500';
                 return 'bg-green-500';
             }
        }">
            <label for="password" class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">Password</label>
            <div class="relative">
                <input id="password" :type="show ? 'text' : 'password'" name="password" required autocomplete="new-password"
                       x-model="pwd"
                       pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}" 
                       title="Minimal 8 karakter, 1 huruf besar, 1 huruf kecil, 1 angka, dan 1 simbol."
                       class="w-full bg-[#0f1118] border border-[#2d2d2d] text-white text-sm rounded-lg px-4 py-3 pr-10 focus:outline-none focus:border-[#f97316] focus:ring-1 focus:ring-[#f97316] transition placeholder-gray-600"
                       placeholder="Minimal 8 karakter">
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-[#f97316] transition-colors focus:outline-none">
                    <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                </button>
            </div>
            
            <!-- Password Strength Meter -->
            <div x-show="pwd.length > 0" class="mt-3 bg-black/40 p-3 rounded-lg border border-[#2d2d2d]" x-cloak x-transition>
                <div class="flex justify-between items-center text-[10px] font-bold uppercase tracking-wider mb-2">
                    <span class="text-gray-400">Kekuatan Sandi:</span>
                    <span :class="strengthColor" x-text="strengthText"></span>
                </div>
                <div class="w-full h-1.5 bg-[#1a1c23] rounded-full overflow-hidden flex gap-1 mb-3">
                    <div class="h-full w-1/3 transition-all duration-300" :class="score >= 1 ? barColor : 'bg-transparent'"></div>
                    <div class="h-full w-1/3 transition-all duration-300" :class="score >= 3 ? barColor : 'bg-transparent'"></div>
                    <div class="h-full w-1/3 transition-all duration-300" :class="score >= 5 ? barColor : 'bg-transparent'"></div>
                </div>
                <ul class="grid grid-cols-2 gap-1.5 text-[10px] text-gray-500 font-medium">
                    <li :class="pwd.length >= 8 ? 'text-green-400' : ''"><i class="fas max-w-[12px] text-center" :class="pwd.length >= 8 ? 'fa-check' : 'fa-circle text-[6px]'"></i> Min 8 Karakter</li>
                    <li :class="/[A-Z]/.test(pwd) ? 'text-green-400' : ''"><i class="fas max-w-[12px] text-center" :class="/[A-Z]/.test(pwd) ? 'fa-check' : 'fa-circle text-[6px]'"></i> Huruf Besar</li>
                    <li :class="/[a-z]/.test(pwd) ? 'text-green-400' : ''"><i class="fas max-w-[12px] text-center" :class="/[a-z]/.test(pwd) ? 'fa-check' : 'fa-circle text-[6px]'"></i> Huruf Kecil</li>
                    <li :class="/[0-9]/.test(pwd) ? 'text-green-400' : ''"><i class="fas max-w-[12px] text-center" :class="/[0-9]/.test(pwd) ? 'fa-check' : 'fa-circle text-[6px]'"></i> Angka</li>
                    <li :class="/[^A-Za-z0-9]/.test(pwd) ? 'text-green-400' : ''"><i class="fas max-w-[12px] text-center" :class="/[^A-Za-z0-9]/.test(pwd) ? 'fa-check' : 'fa-circle text-[6px]'"></i> Simbol Khusus</li>
                </ul>
            </div>

            @error('password') <p class="text-red-400 text-xs mt-1.5">{{ $message }}</p> @enderror
        </div>

        <!-- Confirm Password -->
        <div class="mt-4" x-data="{ show: false }">
            <label for="password_confirmation" class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">Konfirmasi Password</label>
            <div class="relative">
                <input id="password_confirmation" :type="show ? 'text' : 'password'" name="password_confirmation" required autocomplete="new-password"
                       class="w-full bg-[#0f1118] border border-[#2d2d2d] text-white text-sm rounded-lg px-4 py-3 pr-10 focus:outline-none focus:border-[#f97316] focus:ring-1 focus:ring-[#f97316] transition placeholder-gray-600"
                       placeholder="Ulangi password">
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-[#f97316] transition-colors focus:outline-none">
                    <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                </button>
            </div>
            @error('password_confirmation') <p class="text-red-400 text-xs mt-1.5">{{ $message }}</p> @enderror
        </div>

        <button type="button" @click="checkForm()" class="w-full mt-6 bg-gradient-to-r from-[#f97316] to-[#ea580c] hover:from-[#ea580c] hover:to-[#c2410c] text-white font-bold py-3 rounded-xl text-sm transition-all shadow-[0_0_20px_rgba(249,115,22,0.3)] hover:shadow-[0_0_25px_rgba(249,115,22,0.5)] transform hover:-translate-y-0.5">
            <i class="fas fa-user-plus mr-2"></i> Daftar Sekarang
        </button>

        <p class="text-center text-sm text-gray-500 mt-5">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="text-[#f97316] font-semibold hover:text-[#ff983f] transition">Masuk</a>
        </p>
    </form>

    <!-- Popup Modal -->
    <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm" style="display: none;" x-transition>
        <div class="bg-[#161925] border border-[#2d2d2d] rounded-2xl p-6 w-[90%] max-w-sm shadow-2xl relative" @click.away="showModal = false">
            <button @click="showModal = false" class="absolute top-4 right-4 text-gray-500 hover:text-white transition">
                <i class="fas fa-times"></i>
            </button>
            <div class="text-center mb-5">
                <div class="w-14 h-14 bg-[#f97316]/10 rounded-full flex items-center justify-center mx-auto mb-3 text-[#f97316]">
                    <i class="fas fa-shield-alt text-xl"></i>
                </div>
                <h3 class="text-lg font-bold text-white">Kirim Kode Verifikasi</h3>
                <p class="text-xs text-gray-400 mt-1">Kami akan mengirimkan 6 digit kode OTP. Pilih metode pengiriman:</p>
            </div>
            <div class="space-y-3">
                <button type="button" @click="submitForm('whatsapp')" class="w-full flex items-center justify-between p-3.5 rounded-xl border border-[#2d2d2d] bg-[#0f1118] hover:border-[#25D366] hover:bg-[#25D366]/5 group transition-all text-left">
                    <div class="flex items-center gap-3">
                        <i class="fab fa-whatsapp text-2xl text-gray-500 group-hover:text-[#25D366] transition-colors"></i>
                        <div>
                            <div class="text-sm font-bold text-gray-300 group-hover:text-white transition-colors">WhatsApp</div>
                            <div class="text-xs text-gray-500" x-text="waVal"></div>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-xs text-gray-600 group-hover:text-[#25D366] transition-colors"></i>
                </button>
                <button type="button" @click="submitForm('email')" class="w-full flex items-center justify-between p-3.5 rounded-xl border border-[#2d2d2d] bg-[#0f1118] hover:border-[#f97316] hover:bg-[#f97316]/5 group transition-all text-left">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-envelope text-xl text-gray-500 group-hover:text-[#f97316] ml-0.5 transition-colors"></i>
                        <div>
                            <div class="text-sm font-bold text-gray-300 group-hover:text-white transition-colors">Email</div>
                            <div class="text-xs text-gray-500" x-text="emailVal"></div>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-xs text-gray-600 group-hover:text-[#f97316] transition-colors"></i>
                </button>
            </div>
        </div>
    </div>
    </div>
</x-guest-layout>
