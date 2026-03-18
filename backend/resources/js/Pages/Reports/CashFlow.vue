<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    monthlyData: Array,
    incomeByCategory: Array,
    expenseByCategory: Array,
    filters: Object,
});

const startDate = ref(props.filters.start_date);
const endDate = ref(props.filters.end_date);

const applyFilters = () => {
    router.get(route('reports.cash-flow'), {
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

const maxAmount = computed(() => {
    const allValues = props.monthlyData.flatMap(d => [d.income, d.expense]);
    return Math.max(...allValues, 1000000);
});

const getBarHeight = (amount) => {
    return (amount / maxAmount.value) * 100;
};
</script>

<template>
    <Head title="Laporan Arus Kas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <Link :href="route('reports.index')" class="text-muted-foreground hover:text-foreground">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <h2 class="text-xl font-semibold text-foreground">Laporan Arus Kas</h2>
                </div>
                <div class="flex items-center space-x-2">
                    <a
                        :href="route('reports.cash-flow.pdf', { start_date: startDate, end_date: endDate })"
                        target="_blank"
                        class="inline-flex items-center px-3 py-1.5 bg-destructive rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-destructive/90 transition"
                    >
                        PDF
                    </a>
                    <a
                        :href="route('reports.cash-flow.excel', { start_date: startDate, end_date: endDate })"
                        class="inline-flex items-center px-3 py-1.5 bg-success-600 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-success-700 transition"
                    >
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

                <!-- Monthly Trend Chart -->
                <div class="bg-card rounded-lg shadow-sm border border p-4 mb-4">
                    <h3 class="text-sm font-semibold text-foreground mb-4">Tren Arus Kas Bulanan</h3>
                    <div class="h-48 flex items-end justify-between gap-1 px-2">
                        <div v-for="item in monthlyData" :key="item.month" class="flex-1 flex flex-col items-center group relative">
                            <div class="absolute bottom-full mb-2 hidden group-hover:block z-10 bg-foreground text-white text-xs rounded p-2 whitespace-nowrap">
                                <p class="font-bold">{{ item.month }}</p>
                                <p class="text-success-400">In: {{ formatCurrency(item.income) }}</p>
                                <p class="text-destructive/60">Out: {{ formatCurrency(item.expense) }}</p>
                                <p class="border-t border-border mt-1 pt-1" :class="item.net >= 0 ? 'text-success-400' : 'text-destructive/60'">
                                    Net: {{ formatCurrency(item.net) }}
                                </p>
                            </div>
                            <div class="w-full flex justify-center gap-0.5">
                                <div class="w-3 bg-success/100 rounded-t transition-all duration-300" :style="{ height: `${getBarHeight(item.income)}%` }"></div>
                                <div class="w-3 bg-destructive/100 rounded-t transition-all duration-300" :style="{ height: `${getBarHeight(item.expense)}%` }"></div>
                            </div>
                            <span class="text-[9px] text-muted-foreground mt-1 rotate-45 origin-left whitespace-nowrap">{{ item.month }}</span>
                        </div>
                    </div>
                    <div class="flex justify-center gap-4 mt-8 text-xs">
                        <div class="flex items-center gap-1.5">
                            <div class="w-2.5 h-2.5 bg-success/100 rounded-sm"></div>
                            <span class="text-muted-foreground">Pemasukan</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="w-2.5 h-2.5 bg-destructive/100 rounded-sm"></div>
                            <span class="text-muted-foreground">Pengeluaran</span>
                        </div>
                    </div>
                </div>

                <!-- Category Breakdown Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-card rounded-lg shadow-sm border border p-4">
                        <h3 class="text-sm font-semibold text-foreground mb-3">Pemasukan per Kategori</h3>
                        <div class="space-y-2.5">
                            <div v-for="cat in incomeByCategory" :key="cat.category" class="space-y-1">
                                <div class="flex justify-between text-xs">
                                    <span class="text-foreground font-medium">{{ cat.category }}</span>
                                    <span class="text-foreground font-bold">{{ formatCurrency(cat.total) }}</span>
                                </div>
                                <div class="w-full bg-muted rounded-full h-1.5">
                                    <div class="bg-success/100 h-1.5 rounded-full" :style="{ width: `${(cat.total / incomeByCategory.reduce((acc, curr) => acc + curr.total, 0)) * 100}%` }"></div>
                                </div>
                            </div>
                            <p v-if="incomeByCategory.length === 0" class="text-xs text-muted-foreground text-center py-3">Tidak ada data</p>
                        </div>
                    </div>

                    <div class="bg-card rounded-lg shadow-sm border border p-4">
                        <h3 class="text-sm font-semibold text-foreground mb-3">Pengeluaran per Kategori</h3>
                        <div class="space-y-2.5">
                            <div v-for="cat in expenseByCategory" :key="cat.category" class="space-y-1">
                                <div class="flex justify-between text-xs">
                                    <span class="text-foreground font-medium">{{ cat.category }}</span>
                                    <span class="text-foreground font-bold">{{ formatCurrency(cat.total) }}</span>
                                </div>
                                <div class="w-full bg-muted rounded-full h-1.5">
                                    <div class="bg-destructive/100 h-1.5 rounded-full" :style="{ width: `${(cat.total / expenseByCategory.reduce((acc, curr) => acc + curr.total, 0)) * 100}%` }"></div>
                                </div>
                            </div>
                            <p v-if="expenseByCategory.length === 0" class="text-xs text-muted-foreground text-center py-3">Tidak ada data</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
