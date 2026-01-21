<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    contributions: Array,
    summary: Object,
    filters: Object,
});

const startDate = ref(props.filters.start_date);
const endDate = ref(props.filters.end_date);
const status = ref(props.filters.status || '');

const applyFilters = () => {
    router.get(route('reports.contributions'), {
        start_date: startDate.value,
        end_date: endDate.value,
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

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

const getStatusColor = (status) => {
    const colors = {
        paid: 'text-green-600 bg-green-50',
        pending: 'text-yellow-600 bg-yellow-50',
        rejected: 'text-red-600 bg-red-50',
    };
    return colors[status] || 'text-gray-600 bg-gray-50';
};
</script>

<template>
    <Head title="Laporan Iuran Anggota" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <Link :href="route('reports.index')" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <h2 class="text-xl font-semibold text-gray-800">Laporan Iuran Anggota</h2>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                
                <!-- Filters -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Filter Periode & Status</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select 
                                v-model="status"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">Semua Status</option>
                                <option value="paid">Dibayar</option>
                                <option value="pending">Pending</option>
                                <option value="rejected">Ditolak</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button
                                @click="applyFilters"
                                class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Terapkan
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <p class="text-sm text-gray-600 mb-1">Total Dibayar</p>
                        <p class="text-2xl font-bold text-green-600">{{ formatCurrency(summary.totalPaid) }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <p class="text-sm text-gray-600 mb-1">Total Pending</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ formatCurrency(summary.totalPending) }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <p class="text-sm text-gray-600 mb-1">Total Ditolak</p>
                        <p class="text-2xl font-bold text-red-600">{{ formatCurrency(summary.totalRejected) }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <p class="text-sm text-gray-600 mb-1">Total Penagihan</p>
                        <p class="text-2xl font-bold text-indigo-600">{{ formatCurrency(summary.total) }}</p>
                    </div>
                </div>

                <!-- List Table -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Detail Iuran</h3>
                    </div>
                    
                    <div v-if="contributions && contributions.length > 0" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Anggota</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Iuran</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="c in contributions" :key="c.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ c.member?.full_name }}</div>
                                        <div class="text-xs text-gray-500">{{ c.member?.member_number }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ c.type?.name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ formatDate(c.payment_date) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="[getStatusColor(c.status), 'px-2 py-1 text-xs font-bold rounded-full uppercase']">
                                            {{ c.status === 'paid' ? 'Dibayar' : (c.status === 'pending' ? 'Pending' : 'Ditolak') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-gray-900">
                                        {{ formatCurrency(c.amount) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div v-else class="p-12 text-center text-gray-500">
                        <p>Tidak ada data iuran ditemukan untuk filter ini.</p>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
