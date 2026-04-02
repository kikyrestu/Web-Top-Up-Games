@extends('layouts.admin')

@section('title', 'Monitoring API & Bot')
@section('header', 'Monitoring API & Bot')

@section('content')
<div class="space-y-6" x-data="monitoringData()" @recheck-bot.window="checkBot()">
    <!-- Header -->
    <div class="bg-dark-800/40 backdrop-blur-xl rounded-2xl border border-dark-600/50 shadow-2xl p-6">
        <h2 class="text-xl font-black text-white tracking-tight">System Status & Monitoring</h2>
        <p class="text-sm text-gray-400 mt-1">Pantau status koneksi Bot Telegram, Bot WA, Payment Gateway, dan API Provider secara real-time.</p>
        
        <button @click="refreshAll" class="mt-4 px-4 py-2 bg-gradient-to-r from-brand-500 to-accent-500 text-white font-bold rounded-xl text-sm shadow-lg shadow-brand-500/30 hover:opacity-90 flex items-center gap-2 transition">
            <i class="fas fa-sync-alt" :class="isRefreshing ? 'animate-spin' : ''"></i> 
            <span x-text="isRefreshing ? 'Sedang mengecek...' : 'Refresh Semua Status'"></span>
        </button>
    </div>

    <!-- Bots Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- WA Bot -->
        <div class="bg-dark-900 border border-dark-700 rounded-xl p-5 relative overflow-hidden" x-data="waBotControl()">
            <div class="absolute top-0 right-0 p-4 opacity-5 pointer-events-none">
                <i class="fab fa-whatsapp text-6xl text-white"></i>
            </div>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-green-500/20 flex items-center justify-center rounded-lg text-green-400 text-xl"><i class="fab fa-whatsapp"></i></div>
                <div>
                    <h3 class="font-bold text-white leading-tight">WhatsApp Bot Sidecar</h3>
                    <p class="text-xs text-gray-400">Notifikasi Transaksi via WA</p>
                </div>
            </div>

            {{-- PM2 Status & Controls --}}
            <div class="bg-dark-800/50 border border-dark-700 rounded-lg p-3 mb-3">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">Status PM2 Proses</p>
                        <div class="flex items-center gap-1.5 mt-1">
                            <template x-if="pm2Loading">
                                <i class="fas fa-circle-notch fa-spin text-gray-400 text-xs"></i>
                            </template>
                            <template x-if="!pm2Loading">
                                <span class="inline-flex items-center gap-1.5 text-xs font-bold px-2 py-0.5 rounded-full"
                                    :class="{
                                        'bg-emerald-500/15 text-emerald-400': pm2Status === 'online',
                                        'bg-rose-500/15 text-rose-400': pm2Status === 'stopped' || pm2Status === 'errored',
                                        'bg-amber-500/15 text-amber-400': pm2Status === 'unknown'
                                    }">
                                    <i class="fas fa-circle text-[7px]"></i>
                                    <span x-text="pm2Status === 'online' ? '● Online (Running)' : (pm2Status === 'stopped' ? '● Stopped' : (pm2Status === 'errored' ? '● Error' : '● Tidak Diketahui'))"></span>
                                </span>
                            </template>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <button @click="controlBot('start')" :disabled="controlling || pm2Status === 'online'"
                            class="text-xs px-3 py-1.5 rounded-lg font-bold transition disabled:opacity-40 disabled:cursor-not-allowed bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-400 border border-emerald-500/30">
                            <i class="fas fa-play mr-1"></i> Start
                        </button>
                        <button @click="controlBot('restart')" :disabled="controlling"
                            class="text-xs px-3 py-1.5 rounded-lg font-bold transition disabled:opacity-40 disabled:cursor-not-allowed bg-blue-500/15 hover:bg-blue-500/25 text-blue-400 border border-blue-500/30">
                            <i class="fas fa-redo mr-1" :class="controlling ? 'fa-spin' : ''"></i> Restart
                        </button>
                        <button @click="controlBot('stop')" :disabled="controlling || pm2Status === 'stopped'"
                            class="text-xs px-3 py-1.5 rounded-lg font-bold transition disabled:opacity-40 disabled:cursor-not-allowed bg-rose-500/15 hover:bg-rose-500/25 text-rose-400 border border-rose-500/30">
                            <i class="fas fa-stop mr-1"></i> Stop
                        </button>
                    </div>
                </div>
                <template x-if="controlMessage">
                    <p class="text-xs mt-2 font-medium" :class="controlSuccess ? 'text-emerald-400' : 'text-rose-400'" x-text="controlMessage"></p>
                </template>
            </div>
            
            <div class="border-t border-dark-700/50 pt-3">
                <div class="flex justify-between items-center mb-2">
                    <div>
                        <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">Koneksi WhatsApp</p>
                        <div class="flex items-center gap-2 mt-1">
                            <template x-if="loading.bot">
                                <i class="fas fa-spinner fa-spin text-gray-400"></i>
                            </template>
                            <template x-if="!loading.bot">
                                <span class="flex items-center gap-1.5 text-sm font-bold" :class="bot.whatsapp.connected ? 'text-emerald-400' : 'text-rose-400'">
                                    <i class="fas" :class="bot.whatsapp.connected ? 'fa-check-circle' : 'fa-times-circle'"></i>
                                    <span x-text="bot.whatsapp.status_text"></span>
                                </span>
                            </template>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-1 rounded bg-dark-800 border font-bold text-xs" :class="bot.whatsapp.enabled ? 'border-brand-500/30 text-brand-400' : 'border-dark-600 text-gray-500'" x-text="bot.whatsapp.enabled ? 'Enabled' : 'Disabled'"></span>
                        {{-- Show Scan QR button only when not connected --}}
                        <template x-if="!loading.bot && !bot.whatsapp.connected && pm2Status === 'online'">
                            <button @click="showQr = !showQr; fetchQr()" 
                                class="text-xs px-3 py-1.5 rounded-lg font-bold bg-amber-500/15 hover:bg-amber-500/25 text-amber-400 border border-amber-500/30 transition">
                                <i class="fas fa-qrcode mr-1"></i> Scan QR
                            </button>
                        </template>
                    </div>
                </div>

                {{-- QR Code Panel --}}
                <template x-if="showQr">
                    <div class="mt-3 p-4 bg-dark-800 rounded-xl border border-dark-700 text-center">
                        <template x-if="qrLoading">
                            <div class="py-6 text-gray-400 text-sm"><i class="fas fa-circle-notch fa-spin mr-2"></i>Memuat QR Code...</div>
                        </template>
                        <template x-if="!qrLoading && qrCode">
                            <div>
                                <p class="text-xs text-amber-400 font-bold mb-3">📱 Buka WhatsApp → Menu → Perangkat Tertaut → Tautkan Perangkat → Scan QR ini</p>
                                <img :src="qrCode" class="mx-auto rounded-lg border border-dark-600" style="max-width:220px" alt="WhatsApp QR Code">
                                <button @click="fetchQr()" class="mt-3 text-xs text-gray-400 hover:text-white transition"><i class="fas fa-sync-alt mr-1"></i> Refresh QR</button>
                            </div>
                        </template>
                        <template x-if="!qrLoading && !qrCode">
                            <p class="text-xs text-gray-400 py-4"><i class="fas fa-info-circle mr-1"></i> QR Code belum tersedia. Bot masih booting atau WA sudah terhubung. Tunggu 10 detik lalu refresh.</p>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        <!-- Telegram Bot -->
        <div class="bg-dark-900 border border-dark-700 rounded-xl p-5 relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4 opacity-5 pointer-events-none">
                <i class="fab fa-telegram-plane text-6xl text-white"></i>
            </div>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-blue-500/20 flex items-center justify-center rounded-lg text-blue-400 text-xl"><i class="fab fa-telegram-plane"></i></div>
                <div>
                    <h3 class="font-bold text-white leading-tight">Telegram Bot</h3>
                    <p class="text-xs text-gray-400">Notifikasi Transaksi via Telegram</p>
                </div>
            </div>
            
            <div class="flex justify-between items-end border-t border-dark-700/50 pt-3">
                <div>
                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">Status Koneksi</p>
                    <div class="flex items-center gap-2 mt-1">
                        <template x-if="loading.bot">
                            <i class="fas fa-spinner fa-spin text-gray-400"></i>
                        </template>
                        <template x-if="!loading.bot">
                            <span class="flex items-center gap-1.5 text-sm font-bold" :class="bot.telegram.connected ? 'text-emerald-400' : 'text-rose-400'">
                                <i class="fas" :class="bot.telegram.connected ? 'fa-check-circle' : 'fa-times-circle'"></i>
                                <span x-text="bot.telegram.status_text"></span>
                            </span>
                        </template>
                    </div>
                </div>
                <div class="text-xs">
                    <span class="px-2 py-1 rounded bg-dark-800 border font-bold" :class="bot.telegram.enabled ? 'border-brand-500/30 text-brand-400' : 'border-dark-600 text-gray-500'" x-text="bot.telegram.enabled ? 'Enabled di Sistem' : 'Disabled di Sistem'"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- API Providers & Payment Gateways -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- API Providers -->
        <div>
            <h3 class="text-lg font-bold text-white tracking-tight flex items-center gap-2 mb-4">
                <i class="fas fa-plug text-accent-400"></i> API Providers (DigiFlazz, dkk)
            </h3>
            
            <div class="space-y-3">
                @foreach($apiProviders as $provider)
                <div class="bg-dark-900 border border-dark-700/60 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded bg-dark-800 flex items-center justify-center border border-dark-600">
                            <i class="fas fa-server text-gray-400 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-white font-bold text-sm leading-tight">{{ $provider->name }}</p>
                            <p class="text-[10px] text-gray-500 mt-0.5">{{ $provider->base_url }}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4 border-t sm:border-none border-dark-700 pt-3 sm:pt-0">
                        <div>
                            <p class="text-[9px] text-gray-500 font-bold uppercase tracking-wide">Saldo</p>
                            <p class="text-xs font-mono font-bold text-gray-300" x-text="providers[{{ $provider->id }}] ? providers[{{ $provider->id }}].balance : 'Mengecek...'"></p>
                        </div>
                        <div>
                            <p class="text-[9px] text-gray-500 font-bold uppercase tracking-wide">Status</p>
                            <div class="flex items-center gap-1">
                                <template x-if="loading.providers[{{ $provider->id }}]">
                                    <i class="fas fa-circle-notch fa-spin text-gray-400 text-xs"></i>
                                </template>
                                <template x-if="!loading.providers[{{ $provider->id }}]">
                                    <div class="flex items-center gap-1" :class="providers[{{ $provider->id }}] && providers[{{ $provider->id }}].status ? 'text-emerald-400' : 'text-rose-400'">
                                        <i class="fas fa-circle text-[8px]"></i>
                                        <span class="text-xs font-bold" x-text="providers[{{ $provider->id }}] && providers[{{ $provider->id }}].status ? 'OK' : 'Error/Timeout'"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
                @if($apiProviders->isEmpty())
                    <p class="text-sm text-gray-500 italic p-4 text-center border border-dashed border-dark-600 rounded-xl">Belum ada API Provider yang aktif.</p>
                @endif
            </div>
        </div>

        <!-- Payment Gateways -->
        <div>
            <h3 class="text-lg font-bold text-white tracking-tight flex items-center gap-2 mb-4">
                <i class="fas fa-credit-card text-brand-400"></i> Payment Gateways
            </h3>
            
            <div class="space-y-3">
                @foreach($paymentGateways as $gateway)
                <div class="bg-dark-900 border border-dark-700/60 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded bg-dark-800 flex items-center justify-center border border-dark-600">
                            <i class="fas fa-money-bill-wave text-gray-400 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-white font-bold text-sm leading-tight">{{ $gateway->name }} <span class="text-[10px] bg-dark-800 px-1.5 py-0.5 rounded text-gray-400 ml-1">{{ strtoupper($gateway->mode) }}</span></p>
                            <p class="text-[10px] text-gray-500 mt-0.5">Code: {{ strtoupper($gateway->code) }}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-end border-t sm:border-none border-dark-700 pt-3 sm:pt-0">
                        <div>
                            <p class="text-[9px] text-gray-500 font-bold uppercase tracking-wide text-right">Ping API</p>
                            <div class="flex items-center gap-1 justify-end mt-1">
                                <template x-if="loading.gateways[{{ $gateway->id }}]">
                                    <i class="fas fa-circle-notch fa-spin text-gray-400 text-xs"></i>
                                </template>
                                <template x-if="!loading.gateways[{{ $gateway->id }}]">
                                    <div class="flex items-center gap-1" :class="gateways[{{ $gateway->id }}] && gateways[{{ $gateway->id }}].status ? 'text-emerald-400' : 'text-rose-400'">
                                        <i class="fas fa-circle text-[8px]"></i>
                                        <span class="text-xs font-bold" x-text="gateways[{{ $gateway->id }}] && gateways[{{ $gateway->id }}].status ? 'API Terhubung' : 'Gagal / Invalid Key'"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
                @if($paymentGateways->isEmpty())
                    <p class="text-sm text-gray-500 italic p-4 text-center border border-dashed border-dark-600 rounded-xl">Belum ada Payment Gateway yang aktif.</p>
                @endif
            </div>
        </div>

    </div>
</div>

<script>
function monitoringData() {
    return {
        isRefreshing: false,
        loading: {
            bot: true,
            providers: {},
            gateways: {}
        },
        bot: {
            whatsapp: { enabled: false, connected: false, status_text: 'Mengecek...' },
            telegram: { enabled: false, connected: false, status_text: 'Mengecek...' }
        },
        providers: {},
        gateways: {},

        init() {
            @foreach($apiProviders as $p)
                this.loading.providers[{{ $p->id }}] = true;
            @endforeach
            @foreach($paymentGateways as $g)
                this.loading.gateways[{{ $g->id }}] = true;
            @endforeach
            
            this.refreshAll();
        },

        async refreshAll() {
            if (this.isRefreshing) return;
            this.isRefreshing = true;

            // Mark all as loading
            this.loading.bot = true;
            for(let id in this.loading.providers) this.loading.providers[id] = true;
            for(let id in this.loading.gateways) this.loading.gateways[id] = true;

            // Fetch bot
            this.checkBot();
            
            // Fetch providers (parallel slightly delayed to not hammer server)
            let pgQueue = [];
            @foreach($apiProviders as $p)
                pgQueue.push(this.checkProvider({{ $p->id }}));
            @endforeach
            
            @foreach($paymentGateways as $g)
                pgQueue.push(this.checkGateway({{ $g->id }}));
            @endforeach

            await Promise.allSettled(pgQueue);
            
            this.isRefreshing = false;
        },

        async checkBot() {
            try {
                let res = await fetch("{{ route('admin.monitoring.bots') }}");
                let data = await res.json();
                this.bot = data;
                this.loading.bot = false;
            } catch(e) {
                this.loading.bot = false;
            }
        },

        async checkProvider(id) {
            try {
                let res = await fetch("/admin/monitoring/provider/" + id);
                let data = await res.json();
                this.providers[id] = data;
                this.loading.providers[id] = false;
            } catch(e) {
                this.providers[id] = { status: false, balance: 'Error' };
                this.loading.providers[id] = false;
            }
        },

        async checkGateway(id) {
            try {
                let res = await fetch("/admin/monitoring/gateway/" + id);
                let data = await res.json();
                this.gateways[id] = data;
                this.loading.gateways[id] = false;
            } catch(e) {
                this.gateways[id] = { status: false };
                this.loading.gateways[id] = false;
            }
        }
    }
}
function waBotControl() {
    return {
        pm2Status: 'unknown',
        pm2Loading: true,
        controlling: false,
        controlMessage: '',
        controlSuccess: false,
        showQr: false,
        qrCode: null,
        qrLoading: false,

        init() {
            this.fetchPm2Status();
        },

        async fetchPm2Status() {
            this.pm2Loading = true;
            try {
                const res = await fetch("{{ route('admin.monitoring.wa-bot.pm2-status') }}");
                const data = await res.json();
                this.pm2Status = data.pm2_status ?? (data.pm2_running ? 'online' : 'stopped');
            } catch(e) {
                this.pm2Status = 'unknown';
            } finally {
                this.pm2Loading = false;
            }
        },

        async fetchQr() {
            this.qrLoading = true;
            this.qrCode = null;
            try {
                const res = await fetch('/admin/monitoring/wa-bot/qr');
                const data = await res.json();
                this.qrCode = data.qr ?? null;
            } catch(e) {
                this.qrCode = null;
            } finally {
                this.qrLoading = false;
            }
        },

        async controlBot(action) {
            this.controlling = true;
            this.controlMessage = '';
            try {
                const res = await fetch("{{ route('admin.monitoring.wa-bot.control') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ action })
                });
                const data = await res.json();
                this.controlSuccess = data.success;
                this.controlMessage = data.message ?? 'Selesai.';
                if (data.pm2_status) this.pm2Status = data.pm2_status;

                // Re-fetch bot connection status after control action
                // so WA connection status syncs with PM2 status
                setTimeout(() => {
                    this.$dispatch('recheck-bot');
                }, 2000);
            } catch(e) {
                this.controlSuccess = false;
                this.controlMessage = 'Gagal menghubungi server.';
            } finally {
                this.controlling = false;
            }
        }
    }
}
</script>
@endsection
