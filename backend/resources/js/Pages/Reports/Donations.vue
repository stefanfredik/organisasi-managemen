<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    donations: Array,
    summary: Object,
    filters: Object,
});

const status = ref(props.filters.status || '');

const applyFilters = () => {
    router.get(route('reports.donations'), {
        status: status.value,
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

const getStatusColor = (status) => {
    const colors = {
        active: 'text-green-600 bg-green-50',
        completed: 'text-blue-600 bg-blue-50',
        cancelled: 'text-red-600 bg-red-50',
    };
    return colors[status] || 'text-gray-600 bg-gray-50';
};
</script>

<template>
    <Head title="Laporan Donasi" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <Link :href="route('reports.index')" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <h2 class="text-xl font-semibold text-gray-800">Laporan Donasi</h2>
                </div>
                <div class="flex items-center space-x-2">
                    <a
                        :href="route('reports.donations.pdf', { status: status })"
                        target="_blank"
                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        PDF
                    </a>
                    <a
                        :href="route('reports.donations.excel', { status: status })"
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Excel
                    </a>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                
                <!-- Filters -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <div class="flex flex-col md:flex-row md:items-end gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status Donasi</label>
                            <select 
                                v-model="status"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">Semua Status</option>
                                <option value="active">Aktif</option>
                                <option value="completed">Selesai</option>
                                <option value="cancelled">Dibatalkan</option>
                            </select>
                        </div>
                        <div class="flex-shrink-0">
                            <button
                                @click="applyFilters"
                                class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Filter
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <p class="text-sm text-gray-600 mb-1">Total Target</p>
                        <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(summary.totalTarget) }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <p class="text-sm text-gray-600 mb-1">Total Terkumpul</p>
                        <p class="text-2xl font-bold text-green-600">{{ formatCurrency(summary.totalCollected) }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <p class="text-sm text-gray-600 mb-1">Total Sisa</p>
                        <p class="text-2xl font-bold text-red-600">{{ formatCurrency(summary.totalRemaining) }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <p class="text-sm text-gray-600 mb-1">Tingkat Pencapaian</p>
                        <p class="text-2xl font-bold text-indigo-600">{{ summary.completionRate.toFixed(1) }}%</p>
                    </div>
                </div>

                <!-- List -->
                <div class="space-y-6">
                    <div v-for="d in donations" :key="d.id" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">{{ d.program_name }}</h3>
                                    <p class="text-sm text-gray-500">{{ d.description }}</p>
                                </div>
                                <span :class="[getStatusColor(d.status), 'px-3 py-1 rounded-full text-xs font-bold uppercase w-fit']">
                                    {{ d.status === 'active' ? 'Aktif' : (d.status === 'completed' ? 'Selesai' : 'Dibatalkan') }}
                                </span>
                            </div>

                            <div class="space-y-2 mb-6">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Progress: {{ formatCurrency(d.collected_amount) }} / {{ formatCurrency(d.target_amount) }}</span>
                                    <span class="font-bold text-gray-900">{{ ((d.collected_amount / d.target_amount) * 100).toFixed(1) }}%</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                                    <div 
                                        class="bg-indigo-600 h-full rounded-full transition-all duration-500"
                                        :style="{ width: `${(d.collected_amount / d.target_amount) * 100}%` }"
                                    ></div>
                                </div>
                            </div>

                            <!-- Transactions Breakdown -->
                            <div v-if="d.transactions && d.transactions.length > 0">
                                <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Transaksi Terbaru</h4>
                                <div class="bg-gray-50 rounded-lg overflow-hidden border border-gray-100">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <tbody class="divide-y divide-gray-200">
                                            <tr v-for="t in d.transactions.slice(0, 5)" :key="t.id">
                                                <td class="px-4 py-2 text-xs text-gray-600">{{ t.transaction_date }}</td>
                                                <td class="px-4 py-2 text-xs text-gray-900">{{ t.description || 'Donasi masuk' }}</td>
                                                <td class="px-4 py-2 text-xs text-right font-bold text-green-600">{{ formatCurrency(t.amount) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="donations.length === 0" class="p-12 text-center text-gray-500 bg-white rounded-xl border border-gray-200">
                        <p>Tidak ada data donasi ditemukan.</p>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
