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
    router.get(route('reports.donations'), { status: status.value }, { preserveState: true, preserveScroll: true });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};

const getStatusColor = (status) => {
    const colors = {
        active: 'text-success-600 bg-success/10',
        completed: 'text-primary bg-primary/10',
        cancelled: 'text-destructive bg-destructive/10',
    };
    return colors[status] || 'text-muted-foreground bg-muted';
};
</script>

<template>
    <Head title="Laporan Donasi" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <Link :href="route('reports.index')" class="text-muted-foreground hover:text-foreground">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <h2 class="text-xl font-semibold text-foreground">Laporan Donasi</h2>
                </div>
                <div class="flex items-center space-x-2">
                    <a :href="route('reports.donations.pdf', { status: status })" target="_blank"
                        class="inline-flex items-center px-3 py-1.5 bg-destructive rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-destructive/90 transition">
                        PDF
                    </a>
                    <a :href="route('reports.donations.excel', { status: status })"
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
                    <div class="flex items-end gap-3">
                        <div class="min-w-[150px]">
                            <label class="block text-xs font-medium text-muted-foreground mb-1">Status</label>
                            <select v-model="status" class="w-full text-sm rounded-md border-input shadow-sm focus:border-ring focus:ring-ring">
                                <option value="">Semua</option>
                                <option value="active">Aktif</option>
                                <option value="completed">Selesai</option>
                                <option value="cancelled">Dibatalkan</option>
                            </select>
                        </div>
                        <button @click="applyFilters" class="px-4 py-2 text-sm bg-primary text-white rounded-md hover:bg-primary/90">
                            Filter
                        </button>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
                    <div class="bg-card rounded-lg shadow-sm border border p-3">
                        <p class="text-xs text-muted-foreground mb-0.5">Total Target</p>
                        <p class="text-lg font-bold text-foreground">{{ formatCurrency(summary.totalTarget) }}</p>
                    </div>
                    <div class="bg-card rounded-lg shadow-sm border border p-3">
                        <p class="text-xs text-muted-foreground mb-0.5">Terkumpul</p>
                        <p class="text-lg font-bold text-success-600">{{ formatCurrency(summary.totalCollected) }}</p>
                    </div>
                    <div class="bg-card rounded-lg shadow-sm border border p-3">
                        <p class="text-xs text-muted-foreground mb-0.5">Sisa</p>
                        <p class="text-lg font-bold text-destructive">{{ formatCurrency(summary.totalRemaining) }}</p>
                    </div>
                    <div class="bg-card rounded-lg shadow-sm border border p-3">
                        <p class="text-xs text-muted-foreground mb-0.5">Pencapaian</p>
                        <p class="text-lg font-bold text-primary">{{ summary.completionRate.toFixed(1) }}%</p>
                    </div>
                </div>

                <!-- List -->
                <div class="space-y-3">
                    <div v-for="d in donations" :key="d.id" class="bg-card rounded-lg shadow-sm border border overflow-hidden">
                        <div class="p-4">
                            <div class="flex items-center justify-between gap-3 mb-3">
                                <div class="min-w-0">
                                    <h3 class="text-sm font-bold text-foreground truncate">{{ d.program_name }}</h3>
                                    <p class="text-xs text-muted-foreground truncate">{{ d.description }}</p>
                                </div>
                                <span :class="[getStatusColor(d.status), 'px-2 py-0.5 rounded-full text-xs font-bold uppercase shrink-0']">
                                    {{ d.status === 'active' ? 'Aktif' : (d.status === 'completed' ? 'Selesai' : 'Batal') }}
                                </span>
                            </div>

                            <div class="space-y-1.5 mb-3">
                                <div class="flex justify-between text-xs">
                                    <span class="text-muted-foreground">{{ formatCurrency(d.collected_amount) }} / {{ formatCurrency(d.target_amount) }}</span>
                                    <span class="font-bold text-foreground">{{ ((d.collected_amount / d.target_amount) * 100).toFixed(1) }}%</span>
                                </div>
                                <div class="w-full bg-muted rounded-full h-2 overflow-hidden">
                                    <div class="bg-primary h-full rounded-full transition-all duration-500" :style="{ width: `${(d.collected_amount / d.target_amount) * 100}%` }"></div>
                                </div>
                            </div>

                            <div v-if="d.transactions && d.transactions.length > 0">
                                <h4 class="text-[10px] font-black text-muted-foreground uppercase tracking-widest mb-1.5">Transaksi Terbaru</h4>
                                <div class="bg-muted rounded-md overflow-hidden border border">
                                    <table class="min-w-full divide-y divide-border">
                                        <tbody class="divide-y divide-border">
                                            <tr v-for="t in d.transactions.slice(0, 5)" :key="t.id">
                                                <td class="px-3 py-1.5 text-xs text-muted-foreground">{{ t.transaction_date }}</td>
                                                <td class="px-3 py-1.5 text-xs text-foreground">{{ t.description || 'Donasi masuk' }}</td>
                                                <td class="px-3 py-1.5 text-xs text-right font-bold text-success-600">{{ formatCurrency(t.amount) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="donations.length === 0" class="p-8 text-center text-muted-foreground bg-card rounded-lg border border">
                        <p class="text-sm">Tidak ada data donasi ditemukan.</p>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
