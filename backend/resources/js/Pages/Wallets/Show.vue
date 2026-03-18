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
    filters: Object,
});

const activeTab = ref('charts');

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
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};

const deleteWallet = () => {
    if (confirm('Apakah Anda yakin ingin menghapus dompet ini?')) {
        router.delete(route('wallets.destroy', props.wallet.id));
    }
};
</script>

<template>
    <Head :title="`Detail Dompet ${wallet.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="route('wallets.index')" class="text-muted-foreground hover:text-foreground">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-foreground">
                        {{ wallet.name }}
                    </h2>
                </div>
                <div class="flex gap-2">
                    <Link
                        v-if="hasPermission('manage_wallets')"
                        :href="route('wallets.edit', wallet)"
                        class="inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary/90 active:bg-primary/80 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                    </Link>
                    <button
                        v-if="hasPermission('manage_wallets')"
                        @click="deleteWallet"
                        class="inline-flex items-center px-4 py-2 bg-destructive border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-destructive/90 active:bg-danger-900 focus:outline-none focus:ring-2 focus:ring-danger-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1 1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                </div>
            </div>
        </template>

        <div class="py-4">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                
                <!-- Top: Wallet Card + Stats -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
                    <!-- Wallet Card - compact -->
                    <div class="relative w-full min-h-[160px] rounded-lg shadow-lg overflow-hidden text-white flex flex-col justify-between p-4"
                         :class="[
                            wallet.is_active 
                                ? 'bg-gradient-to-br from-cyan-500 via-blue-600 to-blue-800' 
                                : 'bg-gradient-to-br from-muted-foreground via-muted-foreground to-foreground/90 grayscale'
                         ]">
                        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 50% -20%, rgba(255,255,255,0.8), transparent 50%);"></div>
                        <div class="relative z-10 flex flex-col justify-between h-full">
                            <div class="flex justify-between items-start">
                                <h3 class="font-bold text-base tracking-wider uppercase font-mono">{{ wallet.name }}</h3>
                                <svg class="w-6 h-6 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" /></svg>
                            </div>
                            <div class="my-2">
                                <div class="w-10 h-7 bg-yellow-200 rounded-sm relative overflow-hidden shadow-sm flex items-center justify-center opacity-90 mb-2">
                                    <div class="absolute inset-0 border-[0.5px] border-yellow-600 opacity-40 rounded-sm"></div>
                                </div>
                                <p class="text-xl font-mono font-bold tracking-widest">{{ formatCurrency(wallet.balance) }}</p>
                            </div>
                            <div class="flex justify-between items-end">
                                <div>
                                    <span class="text-[8px] uppercase text-blue-100 opacity-70">DIBUAT</span>
                                    <span class="text-[10px] font-mono font-bold ml-1">{{ formatDate(wallet.created_at) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="bg-card p-4 rounded-lg shadow-sm border border">
                            <div class="flex items-center gap-3 mb-1">
                                <div class="p-2 bg-success/10 rounded-md text-success-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" /></svg>
                                </div>
                                <h4 class="font-bold text-muted-foreground uppercase text-[10px] tracking-wider">Total Pemasukan</h4>
                            </div>
                            <p class="text-xl font-black text-foreground">{{ formatCurrency(stats.total_income) }}</p>
                            <div class="mt-2 space-y-1">
                                <div class="flex justify-between text-xs">
                                    <span class="text-muted-foreground">Transaksi Umum</span>
                                    <span class="font-bold text-foreground">{{ formatCurrency(stats.finance_income) }}</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-muted-foreground">Iuran Anggota</span>
                                    <span class="font-bold text-foreground">{{ formatCurrency(stats.contribution_income) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-card p-4 rounded-lg shadow-sm border border">
                            <div class="flex items-center gap-3 mb-1">
                                <div class="p-2 bg-destructive/10 rounded-md text-destructive">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6" /></svg>
                                </div>
                                <h4 class="font-bold text-muted-foreground uppercase text-[10px] tracking-wider">Total Pengeluaran</h4>
                            </div>
                            <p class="text-xl font-black text-foreground">{{ formatCurrency(stats.total_expense) }}</p>
                            <div class="mt-2 text-xs text-muted-foreground">
                                Total dana yang dikeluarkan dari dompet ini.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs & Tables -->
                <div class="bg-card rounded-lg shadow-sm border border overflow-hidden">
                    <div class="flex border-b border overflow-x-auto">
                        <button 
                            @click="activeTab = 'charts'"
                            :class="['px-4 py-2.5 text-xs font-bold uppercase tracking-wider transition-colors whitespace-nowrap', activeTab === 'charts' ? 'border-b-2 border-primary text-primary' : 'text-muted-foreground hover:text-foreground']"
                        >
                            Analisis
                        </button>
                        <button 
                            @click="activeTab = 'finances'"
                            :class="['px-4 py-2.5 text-xs font-bold uppercase tracking-wider transition-colors whitespace-nowrap', activeTab === 'finances' ? 'border-b-2 border-primary text-primary' : 'text-muted-foreground hover:text-foreground']"
                        >
                            Transaksi Umum
                        </button>
                        <button 
                            @click="activeTab = 'contributions'"
                            :class="['px-4 py-2.5 text-xs font-bold uppercase tracking-wider transition-colors whitespace-nowrap', activeTab === 'contributions' ? 'border-b-2 border-primary text-primary' : 'text-muted-foreground hover:text-foreground']"
                        >
                            Iuran Masuk
                        </button>
                    </div>

                    <div class="p-4">
                        <!-- Charts Tab -->
                        <div v-if="activeTab === 'charts'">
                            <div class="mb-3 bg-muted p-3 rounded-md flex flex-wrap gap-3 items-end border border justify-end">
                                <div>
                                    <label class="block text-xs font-medium text-muted-foreground mb-1">Tahun</label>
                                    <select v-model="chartFilter.chart_year" class="text-sm border-input rounded-md focus:ring-ring focus:border-ring min-w-[100px]">
                                        <option v-for="year in props.available_years" :key="year" :value="year">{{ year }}</option>
                                    </select>
                                </div>
                            </div>
                            <WalletCharts :data="chart_data" />
                        </div>

                        <!-- Finances Table -->
                        <div v-if="activeTab === 'finances'">
                            <div class="mb-3 bg-muted p-3 rounded-md flex flex-wrap gap-3 items-end border border">
                                <div class="flex-1 min-w-[160px]">
                                    <label class="block text-xs font-medium text-muted-foreground mb-1">Cari</label>
                                    <input v-model="financeFilters.finance_search" type="text" placeholder="Cari transaksi..." class="w-full text-sm border-input rounded-md focus:ring-ring focus:border-ring">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-muted-foreground mb-1">Jenis</label>
                                    <select v-model="financeFilters.finance_type" class="text-sm border-input rounded-md focus:ring-ring focus:border-ring">
                                        <option value="">Semua</option>
                                        <option value="in">Masuk</option>
                                        <option value="out">Keluar</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-muted-foreground mb-1">Dari</label>
                                    <input v-model="financeFilters.finance_start_date" type="date" class="text-sm border-input rounded-md focus:ring-ring focus:border-ring">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-muted-foreground mb-1">Sampai</label>
                                    <input v-model="financeFilters.finance_end_date" type="date" class="text-sm border-input rounded-md focus:ring-ring focus:border-ring">
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-border">
                                    <thead>
                                        <tr>
                                            <th class="px-3 py-2.5 text-left text-xs font-bold text-muted-foreground uppercase">Tanggal</th>
                                            <th class="px-3 py-2.5 text-left text-xs font-bold text-muted-foreground uppercase">Kategori & Ket</th>
                                            <th class="px-3 py-2.5 text-right text-xs font-bold text-muted-foreground uppercase">Jumlah</th>
                                            <th class="px-3 py-2.5 text-left text-xs font-bold text-muted-foreground uppercase">Oleh</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-border">
                                        <tr v-for="finance in finances.data" :key="finance.id" class="hover:bg-muted">
                                            <td class="px-3 py-2 text-sm text-muted-foreground whitespace-nowrap">{{ formatDate(finance.transaction_date) }}</td>
                                            <td class="px-3 py-2 text-sm">
                                                <div class="font-bold text-foreground">{{ finance.category }}</div>
                                                <div class="text-xs text-muted-foreground italic">{{ finance.description }}</div>
                                            </td>
                                            <td class="px-3 py-2 text-sm text-right font-mono font-bold" :class="finance.type === 'in' ? 'text-success-600' : 'text-destructive'">
                                                {{ finance.type === 'in' ? '+' : '-' }} {{ formatCurrency(finance.amount) }}
                                            </td>
                                            <td class="px-3 py-2 text-xs text-muted-foreground">{{ finance.creator?.name || '-' }}</td>
                                        </tr>
                                        <tr v-if="finances.data.length === 0">
                                            <td colspan="4" class="px-3 py-6 text-center text-muted-foreground text-sm">Belum ada riwayat.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3 flex justify-end" v-if="finances.links.length > 3">
                                <div class="flex gap-1">
                                    <Link v-for="(link, k) in finances.links" :key="k" 
                                        :href="link.url" v-html="link.label"
                                        class="px-2.5 py-1 border rounded text-xs transition"
                                        :class="link.active ? 'bg-primary text-white border-primary' : 'bg-card text-muted-foreground border hover:bg-muted'"
                                        :preserve-scroll="true" />
                                </div>
                            </div>
                        </div>

                        <!-- Contributions Table -->
                        <div v-if="activeTab === 'contributions'">
                            <div class="mb-3 bg-muted p-3 rounded-md flex flex-wrap gap-3 items-end border border">
                                <div class="flex-1 min-w-[160px]">
                                    <label class="block text-xs font-medium text-muted-foreground mb-1">Cari Anggota</label>
                                    <input v-model="contributionFilters.contrib_search" type="text" placeholder="Nama atau Kode..." class="w-full text-sm border-input rounded-md focus:ring-ring focus:border-ring">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-muted-foreground mb-1">Dari</label>
                                    <input v-model="contributionFilters.contrib_start_date" type="date" class="text-sm border-input rounded-md focus:ring-ring focus:border-ring">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-muted-foreground mb-1">Sampai</label>
                                    <input v-model="contributionFilters.contrib_end_date" type="date" class="text-sm border-input rounded-md focus:ring-ring focus:border-ring">
                                </div>
                            </div>
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-border">
                                    <thead>
                                        <tr>
                                            <th class="px-3 py-2.5 text-left text-xs font-bold text-muted-foreground uppercase">Tgl Bayar</th>
                                            <th class="px-3 py-2.5 text-left text-xs font-bold text-muted-foreground uppercase">Anggota</th>
                                            <th class="px-3 py-2.5 text-left text-xs font-bold text-muted-foreground uppercase">Jenis</th>
                                            <th class="px-3 py-2.5 text-right text-xs font-bold text-muted-foreground uppercase">Jumlah</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-border">
                                        <tr v-for="contrib in contributions.data" :key="contrib.id" class="hover:bg-muted">
                                            <td class="px-3 py-2 text-sm text-muted-foreground whitespace-nowrap">{{ formatDate(contrib.payment_date) }}</td>
                                            <td class="px-3 py-2 text-sm">
                                                <div class="font-bold text-foreground">{{ contrib.member?.full_name }}</div>
                                                <div class="text-[10px] text-muted-foreground">{{ contrib.member?.member_code }}</div>
                                            </td>
                                            <td class="px-3 py-2 text-sm text-muted-foreground">{{ contrib.type?.name }}</td>
                                            <td class="px-3 py-2 text-sm text-right font-mono font-bold text-success-600">+ {{ formatCurrency(contrib.amount) }}</td>
                                        </tr>
                                        <tr v-if="contributions.data.length === 0">
                                            <td colspan="4" class="px-3 py-6 text-center text-muted-foreground text-sm">Belum ada riwayat.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3 flex justify-end" v-if="contributions.links.length > 3">
                                <div class="flex gap-1">
                                    <Link v-for="(link, k) in contributions.links" :key="k" 
                                        :href="link.url" v-html="link.label"
                                        class="px-2.5 py-1 border rounded text-xs transition"
                                        :class="link.active ? 'bg-primary text-white border-primary' : 'bg-card text-muted-foreground border hover:bg-muted'"
                                        :preserve-scroll="true" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
