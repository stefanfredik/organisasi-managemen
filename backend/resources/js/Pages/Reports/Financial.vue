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
    router.get(route('reports.financial'), {
        start_date: startDate.value,
        end_date: endDate.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

const getTypeLabel = (type) => {
    return type === 'in' ? 'Pemasukan' : 'Pengeluaran';
};

const getTypeColor = (type) => {
    return type === 'in' ? 'text-green-600 bg-green-50' : 'text-red-600 bg-red-50';
};
</script>

<template>
    <Head title="Laporan Keuangan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <Link :href="route('reports.index')" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <h2 class="text-xl font-semibold text-gray-800">Laporan Keuangan</h2>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                
                <!-- Filters -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Filter Periode</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                            <input 
                                type="date" 
                                v-model="startDate"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                            <input 
                                type="date" 
                                v-model="endDate"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>
                        <div class="flex items-end">
                            <button
                                @click="applyFilters"
                                class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Terapkan Filter
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <p class="text-sm text-gray-600 mb-1">Total Pemasukan</p>
                        <p class="text-2xl font-bold text-green-600">{{ formatCurrency(summary.totalIncome) }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <p class="text-sm text-gray-600 mb-1">Total Pengeluaran</p>
                        <p class="text-2xl font-bold text-red-600">{{ formatCurrency(summary.totalExpense) }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <p class="text-sm text-gray-600 mb-1">Laba/Rugi Bersih</p>
                        <p :class="['text-2xl font-bold', summary.netIncome >= 0 ? 'text-green-600' : 'text-red-600']">
                            {{ formatCurrency(summary.netIncome) }}
                        </p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <p class="text-sm text-gray-600 mb-1">Saldo Saat Ini</p>
                        <p class="text-2xl font-bold text-indigo-600">{{ formatCurrency(summary.currentBalance) }}</p>
                    </div>
                </div>

                <!-- Transactions Table -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Detail Transaksi</h3>
                    </div>
                    
                    <div v-if="transactions && transactions.length > 0" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dibuat Oleh</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="transaction in transactions" :key="transaction.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ formatDate(transaction.transaction_date) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="[getTypeColor(transaction.type), 'px-2 py-1 text-xs font-medium rounded-full']">
                                            {{ getTypeLabel(transaction.type) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ transaction.category }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ transaction.description || '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium" :class="transaction.type === 'in' ? 'text-green-600' : 'text-red-600'">
                                        {{ formatCurrency(transaction.amount) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ transaction.creator?.name || '-' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div v-else class="p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">Tidak ada transaksi pada periode ini</p>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
