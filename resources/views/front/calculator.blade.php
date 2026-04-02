@extends('layouts.front')
@section('title', 'Kalkulator Mobile Legends - Win Rate, Magic Wheel & Zodiac')
@section('meta_description', 'Kalkulator Mobile Legends: hitung win rate, estimasi diamond Magic Wheel, dan biaya skin Zodiac secara akurat.')
@section('canonical', route('front.calculator'))

@section('content')

<section class="relative pt-32 pb-20 overflow-hidden min-h-screen bg-[#121212]">
    <div class="absolute inset-0 bg-gradient-to-b from-[#1c1c1c] via-[#121212] to-[#121212] z-0"></div>
    <div class="absolute top-0 right-0 w-64 h-64 bg-[#f97316]/10 rounded-full blur-[100px]"></div>
    <div class="absolute bottom-0 left-0 w-64 h-64 bg-[#f97316]/5 rounded-full blur-[80px]"></div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10 max-w-5xl">
        {{-- Page Header --}}
        <div class="text-center mb-12">
            <h1 class="text-3xl md:text-5xl font-extrabold text-white mb-4">
                <i class="fas fa-calculator text-[#f97316] mr-2"></i>
                Kalkulator <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#f97316] to-[#ff983f]">Mobile Legends</span>
            </h1>
            <p class="text-gray-400 max-w-2xl mx-auto text-lg">
                Hitung win rate, estimasi biaya spin Magic Wheel, dan biaya skin Zodiac kamu dengan mudah.
            </p>
        </div>

        {{-- Tab Navigation --}}
        <div x-data="{ activeTab: 'winrate' }" class="space-y-8">
            <div class="flex flex-wrap justify-center gap-3 mb-8">
                <button @click="activeTab = 'winrate'" :class="activeTab === 'winrate' ? 'bg-gradient-to-r from-[#f97316] to-[#ff983f] text-white shadow-lg shadow-[#f97316]/30' : 'bg-[#1c1c1c] text-gray-400 border border-gray-700 hover:border-[#f97316]/50'" class="px-5 py-3 rounded-xl font-bold text-sm transition-all duration-200">
                    <i class="fas fa-chart-line mr-2"></i> Win Rate
                </button>
                <button @click="activeTab = 'magicwheel'" :class="activeTab === 'magicwheel' ? 'bg-gradient-to-r from-[#f97316] to-[#ff983f] text-white shadow-lg shadow-[#f97316]/30' : 'bg-[#1c1c1c] text-gray-400 border border-gray-700 hover:border-[#f97316]/50'" class="px-5 py-3 rounded-xl font-bold text-sm transition-all duration-200">
                    <i class="fas fa-dharmachakra mr-2"></i> Magic Wheel
                </button>
                <button @click="activeTab = 'zodiac'" :class="activeTab === 'zodiac' ? 'bg-gradient-to-r from-[#f97316] to-[#ff983f] text-white shadow-lg shadow-[#f97316]/30' : 'bg-[#1c1c1c] text-gray-400 border border-gray-700 hover:border-[#f97316]/50'" class="px-5 py-3 rounded-xl font-bold text-sm transition-all duration-200">
                    <i class="fas fa-star mr-2"></i> Zodiac
                </button>
            </div>

            {{-- ====================== WIN RATE CALCULATOR ====================== --}}
            <div x-show="activeTab === 'winrate'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                x-data="{
                    totalMatch: '',
                    totalWin: '',
                    targetWR: '',
                    result: null,
                    calculate() {
                        let tm = parseInt(this.totalMatch);
                        let tw = parseInt(this.totalWin);
                        let target = parseFloat(this.targetWR);

                        if (isNaN(tm) || isNaN(tw) || isNaN(target) || tm <= 0 || tw < 0 || tw > tm || target <= 0 || target > 100) {
                            this.result = { error: 'Pastikan semua input valid. Total Win tidak boleh lebih dari Total Match, dan Target WR harus antara 0-100%.' };
                            return;
                        }

                        let currentWR = (tw / tm * 100).toFixed(2);

                        if (parseFloat(currentWR) >= target) {
                            this.result = {
                                currentWR: currentWR,
                                message: 'Selamat! Win rate kamu sudah mencapai atau melebihi target. 🎉',
                                needed: 0
                            };
                            return;
                        }

                        // Formula: (tw + x) / (tm + x) >= target/100
                        // tw + x >= (target/100) * (tm + x)
                        // tw + x >= target*tm/100 + target*x/100
                        // x - target*x/100 >= target*tm/100 - tw
                        // x * (1 - target/100) >= target*tm/100 - tw
                        // x >= (target*tm/100 - tw) / (1 - target/100)

                        let needed = Math.ceil((target * tm / 100 - tw) / (1 - target / 100));

                        if (needed < 0 || !isFinite(needed)) {
                            this.result = { error: 'Target win rate tidak dapat dicapai dengan pengaturan ini.' };
                            return;
                        }

                        let newTotalMatch = tm + needed;
                        let newTotalWin = tw + needed;
                        let newWR = (newTotalWin / newTotalMatch * 100).toFixed(2);

                        this.result = {
                            currentWR: currentWR,
                            needed: needed,
                            newTotalMatch: newTotalMatch,
                            newTotalWin: newTotalWin,
                            newWR: newWR,
                            message: null
                        };
                    }
                }">

                <div class="bg-[#1c1c1c] p-6 md:p-10 rounded-3xl border border-gray-800 shadow-[0_20px_50px_rgba(0,0,0,0.5)] relative overflow-hidden">
                    <div class="absolute -top-10 -right-10 text-[150px] text-[#f97316]/5 pointer-events-none">
                        <i class="fas fa-chart-line"></i>
                    </div>

                    <h2 class="text-2xl font-bold text-white mb-2"><i class="fas fa-chart-line text-[#f97316] mr-2"></i> Kalkulator Win Rate</h2>
                    <p class="text-gray-400 text-sm mb-6">Hitung berapa match menang berturut-turut yang dibutuhkan untuk mencapai target win rate kamu.</p>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 relative z-10">
                        <div>
                            <label class="block mb-2 text-sm font-semibold text-gray-300">Total Match</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-[#f97316]"><i class="fas fa-gamepad"></i></div>
                                <input type="number" x-model="totalMatch" min="1" placeholder="Contoh: 1000" class="bg-[#121212] border border-gray-700 text-white text-sm rounded-xl focus:ring-[#f97316] focus:border-[#f97316] block w-full pl-10 p-4 transition-colors">
                            </div>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-semibold text-gray-300">Total Win</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-[#f97316]"><i class="fas fa-trophy"></i></div>
                                <input type="number" x-model="totalWin" min="0" placeholder="Contoh: 520" class="bg-[#121212] border border-gray-700 text-white text-sm rounded-xl focus:ring-[#f97316] focus:border-[#f97316] block w-full pl-10 p-4 transition-colors">
                            </div>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-semibold text-gray-300">Target Win Rate (%)</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-[#f97316]"><i class="fas fa-bullseye"></i></div>
                                <input type="number" x-model="targetWR" min="0.01" max="100" step="0.01" placeholder="Contoh: 60" class="bg-[#121212] border border-gray-700 text-white text-sm rounded-xl focus:ring-[#f97316] focus:border-[#f97316] block w-full pl-10 p-4 transition-colors">
                            </div>
                        </div>
                    </div>

                    <button @click="calculate()" class="mt-6 w-full md:w-auto px-8 py-4 bg-gradient-to-r from-[#f97316] to-[#ff983f] text-white font-bold rounded-xl hover:shadow-lg hover:shadow-[#f97316]/30 transition-all duration-200">
                        <i class="fas fa-calculator mr-2"></i> Hitung Win Rate
                    </button>

                    {{-- Result --}}
                    <template x-if="result">
                        <div class="mt-8 p-6 bg-[#121212] rounded-2xl border border-gray-700">
                            <template x-if="result.error">
                                <div class="flex items-center gap-3 text-red-400">
                                    <i class="fas fa-exclamation-circle text-xl"></i>
                                    <span x-text="result.error"></span>
                                </div>
                            </template>
                            <template x-if="result.message">
                                <div class="flex items-center gap-3 text-green-400 text-lg font-bold">
                                    <i class="fas fa-check-circle text-xl"></i>
                                    <span x-text="result.message"></span>
                                </div>
                            </template>
                            <template x-if="!result.error && !result.message">
                                <div class="space-y-4">
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                        <div class="bg-[#1c1c1c] p-4 rounded-xl text-center">
                                            <p class="text-xs text-gray-400 mb-1">WR Saat Ini</p>
                                            <p class="text-2xl font-bold text-white"><span x-text="result.currentWR"></span>%</p>
                                        </div>
                                        <div class="bg-[#1c1c1c] p-4 rounded-xl text-center">
                                            <p class="text-xs text-gray-400 mb-1">Match Win Streak Dibutuhkan</p>
                                            <p class="text-2xl font-bold text-[#f97316]"><span x-text="result.needed.toLocaleString('id-ID')"></span></p>
                                        </div>
                                        <div class="bg-[#1c1c1c] p-4 rounded-xl text-center">
                                            <p class="text-xs text-gray-400 mb-1">Total Match Baru</p>
                                            <p class="text-2xl font-bold text-white"><span x-text="result.newTotalMatch.toLocaleString('id-ID')"></span></p>
                                        </div>
                                        <div class="bg-[#1c1c1c] p-4 rounded-xl text-center">
                                            <p class="text-xs text-gray-400 mb-1">WR Baru</p>
                                            <p class="text-2xl font-bold text-green-400"><span x-text="result.newWR"></span>%</p>
                                        </div>
                                    </div>
                                    <p class="text-gray-400 text-sm text-center mt-2">
                                        <i class="fas fa-info-circle mr-1"></i> Kamu membutuhkan <span class="text-[#f97316] font-bold" x-text="result.needed.toLocaleString('id-ID')"></span> kali menang berturut-turut untuk mencapai target <span class="text-[#f97316] font-bold" x-text="result.newWR + '%'"></span> win rate.
                                    </p>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            {{-- ====================== MAGIC WHEEL CALCULATOR ====================== --}}
            <div x-show="activeTab === 'magicwheel'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                x-data="{
                    potion: 0,
                    currentSpin: 0,
                    result: null,
                    spinCosts: [100, 100, 200, 200, 300, 300, 400, 400, 500],
                    calculate() {
                        let potionCount = parseInt(this.potion) || 0;
                        let startSpin = parseInt(this.currentSpin) || 0;

                        if (startSpin < 0 || startSpin > 199) {
                            this.result = { error: 'Jumlah spin yang sudah dilakukan harus antara 0-199.' };
                            return;
                        }

                        if (potionCount < 0) {
                            this.result = { error: 'Jumlah potion tidak boleh negatif.' };
                            return;
                        }

                        let totalDiamonds = 0;
                        let totalSpins = 0;
                        let remainingPotion = potionCount;

                        for (let i = startSpin; i < 200; i++) {
                            if (remainingPotion > 0) {
                                remainingPotion--;
                            } else {
                                let cyclePos = i % 5;
                                let costIndex;
                                if (i < 5) costIndex = 0;
                                else if (i < 10) costIndex = 1;
                                else if (i < 15) costIndex = 2;
                                else if (i < 20) costIndex = 3;
                                else if (i < 30) costIndex = 4;
                                else if (i < 40) costIndex = 5;
                                else if (i < 60) costIndex = 6;
                                else if (i < 100) costIndex = 7;
                                else costIndex = 8;

                                totalDiamonds += this.spinCosts[costIndex];
                            }
                            totalSpins++;
                        }

                        this.result = {
                            totalSpins: totalSpins,
                            totalDiamonds: totalDiamonds,
                            potionUsed: potionCount - remainingPotion,
                            diamondSpins: totalSpins - (potionCount - remainingPotion)
                        };
                    }
                }">

                <div class="bg-[#1c1c1c] p-6 md:p-10 rounded-3xl border border-gray-800 shadow-[0_20px_50px_rgba(0,0,0,0.5)] relative overflow-hidden">
                    <div class="absolute -top-10 -right-10 text-[150px] text-[#f97316]/5 pointer-events-none">
                        <i class="fas fa-dharmachakra"></i>
                    </div>

                    <h2 class="text-2xl font-bold text-white mb-2"><i class="fas fa-dharmachakra text-[#f97316] mr-2"></i> Kalkulator Magic Wheel</h2>
                    <p class="text-gray-400 text-sm mb-6">Hitung total diamond yang dibutuhkan untuk mendapatkan skin dari Magic Wheel (Total 200 spin).</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative z-10">
                        <div>
                            <label class="block mb-2 text-sm font-semibold text-gray-300">Jumlah Potion yang Dimiliki</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-[#f97316]"><i class="fas fa-flask"></i></div>
                                <input type="number" x-model="potion" min="0" placeholder="Contoh: 10" class="bg-[#121212] border border-gray-700 text-white text-sm rounded-xl focus:ring-[#f97316] focus:border-[#f97316] block w-full pl-10 p-4 transition-colors">
                            </div>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-semibold text-gray-300">Spin yang Sudah Dilakukan</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-[#f97316]"><i class="fas fa-sync-alt"></i></div>
                                <input type="number" x-model="currentSpin" min="0" max="199" placeholder="Contoh: 0" class="bg-[#121212] border border-gray-700 text-white text-sm rounded-xl focus:ring-[#f97316] focus:border-[#f97316] block w-full pl-10 p-4 transition-colors">
                            </div>
                        </div>
                    </div>

                    <button @click="calculate()" class="mt-6 w-full md:w-auto px-8 py-4 bg-gradient-to-r from-[#f97316] to-[#ff983f] text-white font-bold rounded-xl hover:shadow-lg hover:shadow-[#f97316]/30 transition-all duration-200">
                        <i class="fas fa-calculator mr-2"></i> Hitung Diamond
                    </button>

                    {{-- Result --}}
                    <template x-if="result">
                        <div class="mt-8 p-6 bg-[#121212] rounded-2xl border border-gray-700">
                            <template x-if="result.error">
                                <div class="flex items-center gap-3 text-red-400">
                                    <i class="fas fa-exclamation-circle text-xl"></i>
                                    <span x-text="result.error"></span>
                                </div>
                            </template>
                            <template x-if="!result.error">
                                <div class="space-y-4">
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                        <div class="bg-[#1c1c1c] p-4 rounded-xl text-center">
                                            <p class="text-xs text-gray-400 mb-1">Sisa Spin</p>
                                            <p class="text-2xl font-bold text-white"><span x-text="result.totalSpins.toLocaleString('id-ID')"></span></p>
                                        </div>
                                        <div class="bg-[#1c1c1c] p-4 rounded-xl text-center">
                                            <p class="text-xs text-gray-400 mb-1">Total Diamond</p>
                                            <p class="text-2xl font-bold text-[#f97316]"><span x-text="result.totalDiamonds.toLocaleString('id-ID')"></span> 💎</p>
                                        </div>
                                        <div class="bg-[#1c1c1c] p-4 rounded-xl text-center">
                                            <p class="text-xs text-gray-400 mb-1">Spin via Potion</p>
                                            <p class="text-2xl font-bold text-purple-400"><span x-text="result.potionUsed.toLocaleString('id-ID')"></span></p>
                                        </div>
                                        <div class="bg-[#1c1c1c] p-4 rounded-xl text-center">
                                            <p class="text-xs text-gray-400 mb-1">Spin via Diamond</p>
                                            <p class="text-2xl font-bold text-green-400"><span x-text="result.diamondSpins.toLocaleString('id-ID')"></span></p>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- Price Table --}}
                    <div class="mt-8">
                        <h3 class="text-lg font-bold text-white mb-4"><i class="fas fa-table text-[#f97316] mr-2"></i> Tabel Harga Spin Magic Wheel</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs text-gray-400 uppercase bg-[#121212]">
                                    <tr>
                                        <th class="px-4 py-3 rounded-tl-xl">Spin ke-</th>
                                        <th class="px-4 py-3 rounded-tr-xl">Harga per Spin (Diamond)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-b border-gray-800"><td class="px-4 py-3 text-gray-300">1 - 5</td><td class="px-4 py-3 text-white font-semibold">100 💎</td></tr>
                                    <tr class="border-b border-gray-800 bg-[#1a1a1a]"><td class="px-4 py-3 text-gray-300">6 - 10</td><td class="px-4 py-3 text-white font-semibold">100 💎</td></tr>
                                    <tr class="border-b border-gray-800"><td class="px-4 py-3 text-gray-300">11 - 15</td><td class="px-4 py-3 text-white font-semibold">200 💎</td></tr>
                                    <tr class="border-b border-gray-800 bg-[#1a1a1a]"><td class="px-4 py-3 text-gray-300">16 - 20</td><td class="px-4 py-3 text-white font-semibold">200 💎</td></tr>
                                    <tr class="border-b border-gray-800"><td class="px-4 py-3 text-gray-300">21 - 30</td><td class="px-4 py-3 text-white font-semibold">300 💎</td></tr>
                                    <tr class="border-b border-gray-800 bg-[#1a1a1a]"><td class="px-4 py-3 text-gray-300">31 - 40</td><td class="px-4 py-3 text-white font-semibold">300 💎</td></tr>
                                    <tr class="border-b border-gray-800"><td class="px-4 py-3 text-gray-300">41 - 60</td><td class="px-4 py-3 text-white font-semibold">400 💎</td></tr>
                                    <tr class="border-b border-gray-800 bg-[#1a1a1a]"><td class="px-4 py-3 text-gray-300">61 - 100</td><td class="px-4 py-3 text-white font-semibold">400 💎</td></tr>
                                    <tr><td class="px-4 py-3 text-gray-300 rounded-bl-xl">101 - 200</td><td class="px-4 py-3 text-[#f97316] font-bold rounded-br-xl">500 💎</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ====================== ZODIAC CALCULATOR ====================== --}}
            <div x-show="activeTab === 'zodiac'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                x-data="{
                    currentSpin: 0,
                    result: null,
                    tiers: [
                        { range: '1 - 5', cost: 50, spins: 5 },
                        { range: '6 - 10', cost: 100, spins: 5 },
                        { range: '11 - 20', cost: 150, spins: 10 },
                        { range: '21 - 50', cost: 200, spins: 30 },
                        { range: '51 - 100', cost: 250, spins: 50 },
                    ],
                    calculate() {
                        let startSpin = parseInt(this.currentSpin) || 0;

                        if (startSpin < 0 || startSpin > 99) {
                            this.result = { error: 'Jumlah spin yang sudah dilakukan harus antara 0-99.' };
                            return;
                        }

                        let totalDiamonds = 0;
                        let totalSpins = 0;

                        for (let i = startSpin; i < 100; i++) {
                            let cost;
                            if (i < 5) cost = 50;
                            else if (i < 10) cost = 100;
                            else if (i < 20) cost = 150;
                            else if (i < 50) cost = 200;
                            else cost = 250;

                            totalDiamonds += cost;
                            totalSpins++;
                        }

                        this.result = {
                            totalSpins: totalSpins,
                            totalDiamonds: totalDiamonds
                        };
                    }
                }">

                <div class="bg-[#1c1c1c] p-6 md:p-10 rounded-3xl border border-gray-800 shadow-[0_20px_50px_rgba(0,0,0,0.5)] relative overflow-hidden">
                    <div class="absolute -top-10 -right-10 text-[150px] text-[#f97316]/5 pointer-events-none">
                        <i class="fas fa-star"></i>
                    </div>

                    <h2 class="text-2xl font-bold text-white mb-2"><i class="fas fa-star text-[#f97316] mr-2"></i> Kalkulator Zodiac</h2>
                    <p class="text-gray-400 text-sm mb-6">Hitung total diamond yang dibutuhkan untuk mendapatkan skin Zodiac (Total 100 spin).</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative z-10">
                        <div>
                            <label class="block mb-2 text-sm font-semibold text-gray-300">Spin yang Sudah Dilakukan</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-[#f97316]"><i class="fas fa-sync-alt"></i></div>
                                <input type="number" x-model="currentSpin" min="0" max="99" placeholder="Contoh: 0" class="bg-[#121212] border border-gray-700 text-white text-sm rounded-xl focus:ring-[#f97316] focus:border-[#f97316] block w-full pl-10 p-4 transition-colors">
                            </div>
                        </div>
                    </div>

                    <button @click="calculate()" class="mt-6 w-full md:w-auto px-8 py-4 bg-gradient-to-r from-[#f97316] to-[#ff983f] text-white font-bold rounded-xl hover:shadow-lg hover:shadow-[#f97316]/30 transition-all duration-200">
                        <i class="fas fa-calculator mr-2"></i> Hitung Diamond
                    </button>

                    {{-- Result --}}
                    <template x-if="result">
                        <div class="mt-8 p-6 bg-[#121212] rounded-2xl border border-gray-700">
                            <template x-if="result.error">
                                <div class="flex items-center gap-3 text-red-400">
                                    <i class="fas fa-exclamation-circle text-xl"></i>
                                    <span x-text="result.error"></span>
                                </div>
                            </template>
                            <template x-if="!result.error">
                                <div class="space-y-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="bg-[#1c1c1c] p-4 rounded-xl text-center">
                                            <p class="text-xs text-gray-400 mb-1">Sisa Spin</p>
                                            <p class="text-2xl font-bold text-white"><span x-text="result.totalSpins.toLocaleString('id-ID')"></span></p>
                                        </div>
                                        <div class="bg-[#1c1c1c] p-4 rounded-xl text-center">
                                            <p class="text-xs text-gray-400 mb-1">Total Diamond Dibutuhkan</p>
                                            <p class="text-2xl font-bold text-[#f97316]"><span x-text="result.totalDiamonds.toLocaleString('id-ID')"></span> 💎</p>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- Price Table --}}
                    <div class="mt-8">
                        <h3 class="text-lg font-bold text-white mb-4"><i class="fas fa-table text-[#f97316] mr-2"></i> Tabel Harga Spin Zodiac</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs text-gray-400 uppercase bg-[#121212]">
                                    <tr>
                                        <th class="px-4 py-3 rounded-tl-xl">Spin ke-</th>
                                        <th class="px-4 py-3 rounded-tr-xl">Harga per Spin (Diamond)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-b border-gray-800"><td class="px-4 py-3 text-gray-300">1 - 5</td><td class="px-4 py-3 text-white font-semibold">50 💎</td></tr>
                                    <tr class="border-b border-gray-800 bg-[#1a1a1a]"><td class="px-4 py-3 text-gray-300">6 - 10</td><td class="px-4 py-3 text-white font-semibold">100 💎</td></tr>
                                    <tr class="border-b border-gray-800"><td class="px-4 py-3 text-gray-300">11 - 20</td><td class="px-4 py-3 text-white font-semibold">150 💎</td></tr>
                                    <tr class="border-b border-gray-800 bg-[#1a1a1a]"><td class="px-4 py-3 text-gray-300">21 - 50</td><td class="px-4 py-3 text-white font-semibold">200 💎</td></tr>
                                    <tr><td class="px-4 py-3 text-gray-300 rounded-bl-xl">51 - 100</td><td class="px-4 py-3 text-[#f97316] font-bold rounded-br-xl">250 💎</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div> {{-- end tabs --}}

        {{-- SEO Content --}}
        <div class="mt-16 text-center">
            <div class="bg-[#1c1c1c] p-6 rounded-2xl border border-gray-800 text-left">
                <h2 class="text-lg font-bold text-white mb-3"><i class="fas fa-info-circle text-[#f97316] mr-2"></i> Tentang Kalkulator MLBB</h2>
                <div class="text-gray-400 text-sm space-y-2">
                    <p><strong class="text-gray-300">Win Rate Calculator</strong> — Masukkan total match, total win, dan target win rate untuk mengetahui berapa match yang harus kamu menangkan berturut-turut.</p>
                    <p><strong class="text-gray-300">Magic Wheel</strong> — Fitur gacha di Mobile Legends yang membutuhkan maksimal 200 spin untuk mendapatkan skin. Harga per spin naik secara bertahap.</p>
                    <p><strong class="text-gray-300">Zodiac</strong> — Event skin Zodiac membutuhkan maksimal 100 spin. Lebih murah dibanding Magic Wheel, dengan harga dimulai dari 50 diamond per spin.</p>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
