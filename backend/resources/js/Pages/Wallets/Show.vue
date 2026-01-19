<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, reactive, watch } from 'vue';
import WalletCharts from './Partials/WalletCharts.vue';
import { debounce } from 'lodash';

const props = defineProps({
    wallet: Object,
    finances: Object,
    contributions: Object,
    stats: Object,
    chart_data: Object,
    filters: Object // Receive current filters back from server if needed, or just init defaults
});

const activeTab = ref('charts');

// Filter State
const financeFilters = reactive({
    finance_start_date: props.filters.finance_start_date || '',
    finance_end_date: props.filters.finance_end_date || '',
    finance_type: props.filters.finance_type || '',
    finance_search: props.filters.finance_search || '',
});

const contributionFilters = reactive({
    contrib_start_date: props.filters.contrib_start_date || '',
    contrib_end_date: props.filters.contrib_end_date || '',
    contrib_search: props.filters.contrib_search || '',
});

// Watchers for automatic filtering
const applyFilters = debounce(() => {
    router.get(route('wallets.show', props.wallet.id), {
        ...financeFilters,
        ...contributionFilters,
        ...chartFilter
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}, 500);

watch(financeFilters, applyFilters);
watch(contributionFilters, applyFilters);

const chartFilter = reactive({
    chart_year: props.filters.chart_year || new Date().getFullYear(),
});

watch(() => chartFilter.chart_year, () => {
     router.get(route('wallets.show', props.wallet.id), {
        ...financeFilters,
        ...contributionFilters,
        ...chartFilter
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
};
</script>

<template>
    <Head :title="`Detail Dompet ${wallet.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <Link :href="route('wallets.index')" class="bg-white p-2 rounded-full shadow-sm text-gray-500 hover:text-indigo-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        Detail Dompet: {{ wallet.name }}
                    </h2>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <!-- Top Section: Card & Quick Stats side-by-side -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                    <!-- The Card (Reusing the Premium Design) -->
                    <div class="group relative perspective-1000 h-full">
                        <div class="relative w-full h-full min-h-[220px] rounded-2xl shadow-xl overflow-hidden text-white flex flex-col justify-between p-6 transition-transform"
                             :class="[
                                wallet.is_active 
                                    ? 'bg-gradient-to-br from-cyan-500 via-blue-600 to-blue-800' 
                                    : 'bg-gradient-to-br from-gray-600 via-gray-700 to-gray-800 grayscale'
                             ]">
                             
                            <!-- Background patterns -->
                            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 50% -20%, rgba(255,255,255,0.8), transparent 50%);"></div>
                            <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-white opacity-5 blur-3xl"></div>
                            
                            <!-- Card Content -->
                            <div class="relative z-10 flex flex-col justify-between h-full">
                                <div class="flex justify-between items-start">
                                    <h3 class="font-bold text-xl tracking-wider uppercase font-mono">{{ wallet.name }}</h3>
                                    <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" /></svg>
                                </div>
                                
                                <div class="my-4">
                                     <div class="w-12 h-9 bg-yellow-200 rounded-md relative overflow-hidden shadow-sm flex items-center justify-center opacity-90 mb-4">
                                        <div class="absolute inset-0 border-[0.5px] border-yellow-600 opacity-40 rounded-md"></div>
                                        <div class="w-full h-[1px] bg-yellow-600 opacity-40 absolute top-1/3"></div>
                                        <div class="w-full h-[1px] bg-yellow-600 opacity-40 absolute bottom-1/3"></div>
                                    </div>
                                    <p class="text-3xl font-mono font-bold tracking-widest text-shadow-sm">{{ formatCurrency(wallet.balance) }}</p>
                                </div>

                                <div class="flex justify-between items-end">
                                    <div class="flex flex-col">
                                        <span class="text-[8px] uppercase text-blue-100 opacity-70">DIBUAT</span>
                                        <span class="text-xs font-mono font-bold">{{ formatDate(wallet.created_at) }}</span>
                                    </div>
                                    <h4 class="text-xl font-bold italic tracking-tighter opacity-90">VISA</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Stats Grid -->
                    <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                            <div class="flex items-center gap-4 mb-2">
                                <div class="p-3 bg-green-50 rounded-lg text-green-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" /></svg>
                                </div>
                                <h4 class="font-bold text-gray-500 uppercase text-xs tracking-wider">Total Pemasukan</h4>
                            </div>
                            <p class="text-2xl font-black text-gray-800">{{ formatCurrency(stats.total_income) }}</p>
                            <div class="mt-4 space-y-2">
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-500">Dari Transaksi Umum</span>
                                    <span class="font-bold text-gray-700">{{ formatCurrency(stats.finance_income) }}</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-500">Dari Iuran Anggota</span>
                                    <span class="font-bold text-gray-700">{{ formatCurrency(stats.contribution_income) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                            <div class="flex items-center gap-4 mb-2">
                                <div class="p-3 bg-red-50 rounded-lg text-red-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6" /></svg>
                                </div>
                                <h4 class="font-bold text-gray-500 uppercase text-xs tracking-wider">Total Pengeluaran</h4>
                            </div>
                            <p class="text-2xl font-black text-gray-800">{{ formatCurrency(stats.total_expense) }}</p>
                             <div class="mt-4 text-xs text-gray-400">
                                Total dana yang dikeluarkan dari dompet ini untuk berbagai keperluan operasional.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs & Tables -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="flex border-b border-gray-100 overflow-x-auto">
                        <button 
                            @click="activeTab = 'charts'"
                            :class="['px-6 py-4 text-sm font-bold uppercase tracking-wider transition-colors whitespace-nowrap', activeTab === 'charts' ? 'border-b-2 border-indigo-600 text-indigo-600 bg-indigo-50/50' : 'text-gray-400 hover:text-gray-600']"
                        >
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" /></svg>
                                Analisis & Grafik
                            </div>
                        </button>
                        <button 
                            @click="activeTab = 'finances'"
                            :class="['px-6 py-4 text-sm font-bold uppercase tracking-wider transition-colors whitespace-nowrap', activeTab === 'finances' ? 'border-b-2 border-indigo-600 text-indigo-600 bg-indigo-50/50' : 'text-gray-400 hover:text-gray-600']"
                        >
                            Riwayat Transaksi Umum
                        </button>
                        <button 
                            @click="activeTab = 'contributions'"
                            :class="['px-6 py-4 text-sm font-bold uppercase tracking-wider transition-colors whitespace-nowrap', activeTab === 'contributions' ? 'border-b-2 border-indigo-600 text-indigo-600 bg-indigo-50/50' : 'text-gray-400 hover:text-gray-600']"
                        >
                            Riwayat Iuran Masuk
                        </button>
                    </div>

                    <div class="p-6">
                        <!-- Charts Tab -->
                        <div v-if="activeTab === 'charts'">
                            <!-- Filter Toolbar -->
                            <div class="mb-4 bg-gray-50 p-4 rounded-lg flex flex-wrap gap-4 items-end border border-gray-100 justify-end">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tahun Analisis</label>
                                    <select v-model="chartFilter.chart_year" class="text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 min-w-[120px]">
                                        <option v-for="year in props.available_years" :key="year" :value="year">{{ year }}</option>
                                    </select>
                                </div>
                            </div>

                            <WalletCharts :data="chart_data" />
                        </div>

                        <!-- Finances Table -->
                        <div v-if="activeTab === 'finances'">
                            <!-- Filter Toolbar -->
                            <div class="mb-4 bg-gray-50 p-4 rounded-lg flex flex-wrap gap-4 items-end border border-gray-100">
                                <div class="flex-1 min-w-[200px]">
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Cari Keterangan / Kategori</label>
                                    <input v-model="financeFilters.finance_search" type="text" placeholder="Cari transaksi..." class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Jenis</label>
                                    <select v-model="financeFilters.finance_type" class="text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="">Semua</option>
                                        <option value="in">Pemasukan</option>
                                        <option value="out">Pengeluaran</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Dari Tanggal</label>
                                    <input v-model="financeFilters.finance_start_date" type="date" class="text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Sampai Tanggal</label>
                                    <input v-model="financeFilters.finance_end_date" type="date" class="text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase">Tanggal</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase">Kategori & Ket</th>
                                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-400 uppercase">Jumlah</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase">Oleh</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <tr v-for="finance in finances.data" :key="finance.id" class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ formatDate(finance.transaction_date) }}</td>
                                            <td class="px-4 py-3 text-sm">
                                                <div class="font-bold text-gray-800">{{ finance.category }}</div>
                                                <div class="text-xs text-gray-500 italic">{{ finance.description }}</div>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-right font-mono font-bold" :class="finance.type === 'in' ? 'text-green-600' : 'text-red-600'">
                                                {{ finance.type === 'in' ? '+' : '-' }} {{ formatCurrency(finance.amount) }}
                                            </td>
                                            <td class="px-4 py-3 text-xs text-gray-500">{{ finance.creator?.name || '-' }}</td>
                                        </tr>
                                        <tr v-if="finances.data.length === 0">
                                            <td colspan="4" class="px-4 py-8 text-center text-gray-400 text-sm">Belum ada riwayat transaksi umum.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                             <!-- Pagination for Finances -->
                            <div class="mt-4 flex justify-end" v-if="finances.links.length > 3">
                                <div class="flex gap-1">
                                    <Link v-for="(link, k) in finances.links" :key="k" 
                                        :href="link.url" 
                                        v-html="link.label"
                                        class="px-3 py-1 border rounded text-xs transition"
                                        :class="link.active ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'"
                                        :preserve-scroll="true"
                                        />
                                </div>
                            </div>
                        </div>

                        <!-- Contributions Table -->
                        <div v-if="activeTab === 'contributions'">
                             <!-- Filter Toolbar -->
                            <div class="mb-4 bg-gray-50 p-4 rounded-lg flex flex-wrap gap-4 items-end border border-gray-100">
                                <div class="flex-1 min-w-[200px]">
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Cari Anggota</label>
                                    <input v-model="contributionFilters.contrib_search" type="text" placeholder="Nama atau Kode Anggota..." class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Dari Tanggal</label>
                                    <input v-model="contributionFilters.contrib_start_date" type="date" class="text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Sampai Tanggal</label>
                                    <input v-model="contributionFilters.contrib_end_date" type="date" class="text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                            </div>
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase">Tgl Bayar</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase">Anggota</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase">Jenis Iuran</th>
                                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-400 uppercase">Jumlah</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <tr v-for="contrib in contributions.data" :key="contrib.id" class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ formatDate(contrib.payment_date) }}</td>
                                            <td class="px-4 py-3 text-sm">
                                                <div class="font-bold text-gray-800">{{ contrib.member?.full_name }}</div>
                                                <div class="text-xs text-gray-500">{{ contrib.member?.member_code }}</div>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ contrib.type?.name }}</td>
                                            <td class="px-4 py-3 text-sm text-right font-mono font-bold text-green-600">
                                                + {{ formatCurrency(contrib.amount) }}
                                            </td>
                                        </tr>
                                        <tr v-if="contributions.data.length === 0">
                                            <td colspan="4" class="px-4 py-8 text-center text-gray-400 text-sm">Belum ada riwayat iuran masuk.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <!-- Pagination for Contributions -->
                            <div class="mt-4 flex justify-end" v-if="contributions.links.length > 3">
                                <div class="flex gap-1">
                                    <Link v-for="(link, k) in contributions.links" :key="k" 
                                        :href="link.url" 
                                        v-html="link.label"
                                        class="px-3 py-1 border rounded text-xs transition"
                                        :class="link.active ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'"
                                        :preserve-scroll="true"
                                        />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
