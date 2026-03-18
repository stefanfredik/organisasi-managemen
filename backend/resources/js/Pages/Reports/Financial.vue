<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    transactions: Array,
    summary: Object,
    filters: Object,
});

const startDate = ref(props.filters.start_date);
const endDate = ref(props.filters.end_date);

const applyFilters = () => {
    router.get(route('reports.financial'), { start_date: startDate.value, end_date: endDate.value }, { preserveState: true, preserveScroll: true });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

const getTypeLabel = (type) => type === 'in' ? 'Masuk' : 'Keluar';
const getTypeColor = (type) => type === 'in' ? 'text-success-600 bg-success/10' : 'text-destructive bg-destructive/10';
</script>

<template>
    <Head title="Laporan Keuangan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <Link :href="route('reports.index')" class="text-muted-foreground hover:text-foreground">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <h2 class="text-xl font-semibold text-foreground">Laporan Keuangan</h2>
                </div>
                <div class="flex items-center space-x-2">
                    <a :href="route('reports.financial.pdf', { start_date: startDate, end_date: endDate })" target="_blank"
                        class="inline-flex items-center px-3 py-1.5 bg-destructive rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-destructive/90 transition">
                        PDF
                    </a>
                    <a :href="route('reports.financial.excel', { start_date: startDate, end_date: endDate })"
                        class="inline-flex items-center px-3 py-1.5 bg-success-600 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-success-700 transition">
                        Excel
                    </a>
                </div>
            </div>
        </template>

        <div class="py-4">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                
                <!-- Filters - compact inline -->
                <div class="bg-card rounded-lg shadow-sm border border p-3 mb-4">
                    <div class="flex flex-wrap items-end gap-3">
                        <div class="min-w-[140px]">
                            <label class="block text-xs font-medium text-muted-foreground mb-1">Dari</label>
                            <input type="date" v-model="startDate" class="w-full text-sm rounded-md border-input shadow-sm focus:border-ring focus:ring-ring" />
                        </div>
                        <div class="min-w-[140px]">
                            <label class="block text-xs font-medium text-muted-foreground mb-1">Sampai</label>
                            <input type="date" v-model="endDate" class="w-full text-sm rounded-md border-input shadow-sm focus:border-ring focus:ring-ring" />
                        </div>
                        <button @click="applyFilters" class="px-4 py-2 text-sm bg-primary text-white rounded-md hover:bg-primary/90">
                            Terapkan
                        </button>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
                    <div class="bg-card rounded-lg shadow-sm border border p-3">
                        <p class="text-xs text-muted-foreground mb-0.5">Pemasukan</p>
                        <p class="text-lg font-bold text-success-600">{{ formatCurrency(summary.totalIncome) }}</p>
                    </div>
                    <div class="bg-card rounded-lg shadow-sm border border p-3">
                        <p class="text-xs text-muted-foreground mb-0.5">Pengeluaran</p>
                        <p class="text-lg font-bold text-destructive">{{ formatCurrency(summary.totalExpense) }}</p>
                    </div>
                    <div class="bg-card rounded-lg shadow-sm border border p-3">
                        <p class="text-xs text-muted-foreground mb-0.5">Laba/Rugi</p>
                        <p :class="['text-lg font-bold', summary.netIncome >= 0 ? 'text-success-600' : 'text-destructive']">
                            {{ formatCurrency(summary.netIncome) }}
                        </p>
                    </div>
                    <div class="bg-card rounded-lg shadow-sm border border p-3">
                        <p class="text-xs text-muted-foreground mb-0.5">Saldo</p>
                        <p class="text-lg font-bold text-primary">{{ formatCurrency(summary.currentBalance) }}</p>
                    </div>
                </div>

                <!-- Transactions Table -->
                <div class="bg-card rounded-lg shadow-sm border border overflow-hidden">
                    <div v-if="transactions && transactions.length > 0" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-border">
                            <thead class="bg-muted">
                                <tr>
                                    <th class="px-4 py-2.5 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Tanggal</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Tipe</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Kategori</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Deskripsi</th>
                                    <th class="px-4 py-2.5 text-right text-xs font-medium text-muted-foreground uppercase tracking-wider">Jumlah</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Oleh</th>
                                </tr>
                            </thead>
                            <tbody class="bg-card divide-y divide-border">
                                <tr v-for="transaction in transactions" :key="transaction.id" class="hover:bg-muted">
                                    <td class="px-4 py-2.5 whitespace-nowrap text-sm text-foreground">{{ formatDate(transaction.transaction_date) }}</td>
                                    <td class="px-4 py-2.5 whitespace-nowrap">
                                        <span :class="[getTypeColor(transaction.type), 'px-2 py-0.5 text-xs font-medium rounded-full']">
                                            {{ getTypeLabel(transaction.type) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2.5 whitespace-nowrap text-sm text-foreground">{{ transaction.category }}</td>
                                    <td class="px-4 py-2.5 text-sm text-muted-foreground max-w-[200px] truncate">{{ transaction.description || '-' }}</td>
                                    <td class="px-4 py-2.5 whitespace-nowrap text-sm text-right font-medium" :class="transaction.type === 'in' ? 'text-success-600' : 'text-destructive'">
                                        {{ formatCurrency(transaction.amount) }}
                                    </td>
                                    <td class="px-4 py-2.5 whitespace-nowrap text-sm text-muted-foreground">{{ transaction.creator?.name || '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="p-8 text-center text-muted-foreground">
                        <p class="text-sm">Tidak ada transaksi pada periode ini</p>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
