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
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

const getStatusColor = (status) => {
    const colors = {
        paid: 'text-success-600 bg-success/10',
        pending: 'text-warning-600 bg-warning-50',
        rejected: 'text-destructive bg-destructive/10',
    };
    return colors[status] || 'text-muted-foreground bg-muted';
};
</script>

<template>
    <Head title="Laporan Iuran Anggota" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <Link :href="route('reports.index')" class="text-muted-foreground hover:text-foreground">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <h2 class="text-xl font-semibold text-foreground">Laporan Iuran</h2>
                </div>
                <div class="flex items-center space-x-2">
                    <a :href="route('reports.contributions.pdf', { start_date: startDate, end_date: endDate, status: status })" target="_blank"
                        class="inline-flex items-center px-3 py-1.5 bg-destructive rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-destructive/90 transition">
                        PDF
                    </a>
                    <a :href="route('reports.contributions.excel', { start_date: startDate, end_date: endDate, status: status })"
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
                        <div class="min-w-[130px]">
                            <label class="block text-xs font-medium text-muted-foreground mb-1">Dari</label>
                            <input type="date" v-model="startDate" class="w-full text-sm rounded-md border-input shadow-sm focus:border-ring focus:ring-ring" />
                        </div>
                        <div class="min-w-[130px]">
                            <label class="block text-xs font-medium text-muted-foreground mb-1">Sampai</label>
                            <input type="date" v-model="endDate" class="w-full text-sm rounded-md border-input shadow-sm focus:border-ring focus:ring-ring" />
                        </div>
                        <div class="min-w-[120px]">
                            <label class="block text-xs font-medium text-muted-foreground mb-1">Status</label>
                            <select v-model="status" class="w-full text-sm rounded-md border-input shadow-sm focus:border-ring focus:ring-ring">
                                <option value="">Semua</option>
                                <option value="paid">Dibayar</option>
                                <option value="pending">Pending</option>
                                <option value="rejected">Ditolak</option>
                            </select>
                        </div>
                        <button @click="applyFilters" class="px-4 py-2 text-sm bg-primary text-white rounded-md hover:bg-primary/90">
                            Terapkan
                        </button>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
                    <div class="bg-card rounded-lg shadow-sm border border p-3">
                        <p class="text-xs text-muted-foreground mb-0.5">Total Dibayar</p>
                        <p class="text-lg font-bold text-success-600">{{ formatCurrency(summary.totalPaid) }}</p>
                    </div>
                    <div class="bg-card rounded-lg shadow-sm border border p-3">
                        <p class="text-xs text-muted-foreground mb-0.5">Total Pending</p>
                        <p class="text-lg font-bold text-warning-600">{{ formatCurrency(summary.totalPending) }}</p>
                    </div>
                    <div class="bg-card rounded-lg shadow-sm border border p-3">
                        <p class="text-xs text-muted-foreground mb-0.5">Total Ditolak</p>
                        <p class="text-lg font-bold text-destructive">{{ formatCurrency(summary.totalRejected) }}</p>
                    </div>
                    <div class="bg-card rounded-lg shadow-sm border border p-3">
                        <p class="text-xs text-muted-foreground mb-0.5">Total Penagihan</p>
                        <p class="text-lg font-bold text-primary">{{ formatCurrency(summary.total) }}</p>
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-card rounded-lg shadow-sm border border overflow-hidden">
                    <div v-if="contributions && contributions.length > 0" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-border">
                            <thead class="bg-muted">
                                <tr>
                                    <th class="px-4 py-2.5 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Anggota</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Jenis</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Tanggal</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-2.5 text-right text-xs font-medium text-muted-foreground uppercase tracking-wider">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody class="bg-card divide-y divide-border">
                                <tr v-for="c in contributions" :key="c.id" class="hover:bg-muted">
                                    <td class="px-4 py-2.5 whitespace-nowrap">
                                        <div class="text-sm font-medium text-foreground">{{ c.member?.full_name }}</div>
                                        <div class="text-xs text-muted-foreground">{{ c.member?.member_number }}</div>
                                    </td>
                                    <td class="px-4 py-2.5 whitespace-nowrap text-sm text-foreground">{{ c.type?.name }}</td>
                                    <td class="px-4 py-2.5 whitespace-nowrap text-sm text-muted-foreground">{{ formatDate(c.payment_date) }}</td>
                                    <td class="px-4 py-2.5 whitespace-nowrap">
                                        <span :class="[getStatusColor(c.status), 'px-2 py-0.5 text-xs font-bold rounded-full uppercase']">
                                            {{ c.status === 'paid' ? 'Dibayar' : (c.status === 'pending' ? 'Pending' : 'Ditolak') }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2.5 whitespace-nowrap text-sm text-right font-bold text-foreground">{{ formatCurrency(c.amount) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="p-8 text-center text-muted-foreground">
                        <p class="text-sm">Tidak ada data iuran ditemukan.</p>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
