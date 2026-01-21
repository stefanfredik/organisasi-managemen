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

// Simple bar chart calculations
const maxAmount = computed(() => {
    const allValues = props.monthlyData.flatMap(d => [d.income, d.expense]);
    return Math.max(...allValues, 1000000); // Minimum 1M for scale
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
                    <Link :href="route('reports.index')" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <h2 class="text-xl font-semibold text-gray-800">Laporan Arus Kas</h2>
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

                <!-- Monthly Trend Chart -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Tren Arus Kas Bulanan</h3>
                    <div class="h-64 flex items-end justify-between gap-2 px-4">
                        <div v-for="item in monthlyData" :key="item.month" class="flex-1 flex flex-col items-center group relative">
                            <!-- Tooltip -->
                            <div class="absolute bottom-full mb-2 hidden group-hover:block z-10 bg-gray-900 text-white text-xs rounded p-2 whitespace-nowrap">
                                <p class="font-bold">{{ item.month }}</p>
                                <p class="text-green-400">In: {{ formatCurrency(item.income) }}</p>
                                <p class="text-red-400">Out: {{ formatCurrency(item.expense) }}</p>
                                <p class="border-t border-gray-700 mt-1 pt-1" :class="item.net >= 0 ? 'text-green-400' : 'text-red-400'">
                                    Net: {{ formatCurrency(item.net) }}
                                </p>
                            </div>
                            
                            <!-- Bars -->
                            <div class="w-full flex justify-center gap-1">
                                <div 
                                    class="w-4 bg-green-500 rounded-t transition-all duration-300"
                                    :style="{ height: `${getBarHeight(item.income)}%` }"
                                ></div>
                                <div 
                                    class="w-4 bg-red-500 rounded-t transition-all duration-300"
                                    :style="{ height: `${getBarHeight(item.expense)}%` }"
                                ></div>
                            </div>
                            
                            <!-- Month Label -->
                            <span class="text-[10px] text-gray-500 mt-2 rotate-45 origin-left whitespace-nowrap">{{ item.month }}</span>
                        </div>
                    </div>
                    <div class="flex justify-center gap-6 mt-12 text-sm">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-green-500 rounded-sm"></div>
                            <span class="text-gray-600">Pemasukan</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-red-500 rounded-sm"></div>
                            <span class="text-gray-600">Pengeluaran</span>
                        </div>
                    </div>
                </div>

                <!-- Category Breakdown Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Income by Category -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Pemasukan per Kategori</h3>
                        <div class="space-y-4">
                            <div v-for="cat in incomeByCategory" :key="cat.category" class="space-y-1">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-700 font-medium">{{ cat.category }}</span>
                                    <span class="text-gray-900 font-bold">{{ formatCurrency(cat.total) }}</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div 
                                        class="bg-green-500 h-2 rounded-full" 
                                        :style="{ width: `${(cat.total / incomeByCategory.reduce((acc, curr) => acc + curr.total, 0)) * 100}%` }"
                                    ></div>
                                </div>
                            </div>
                            <p v-if="incomeByCategory.length === 0" class="text-sm text-gray-500 text-center py-4">Tidak ada data pemasukan</p>
                        </div>
                    </div>

                    <!-- Expense by Category -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Pengeluaran per Kategori</h3>
                        <div class="space-y-4">
                            <div v-for="cat in expenseByCategory" :key="cat.category" class="space-y-1">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-700 font-medium">{{ cat.category }}</span>
                                    <span class="text-gray-900 font-bold">{{ formatCurrency(cat.total) }}</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div 
                                        class="bg-red-500 h-2 rounded-full" 
                                        :style="{ width: `${(cat.total / expenseByCategory.reduce((acc, curr) => acc + curr.total, 0)) * 100}%` }"
                                    ></div>
                                </div>
                            </div>
                            <p v-if="expenseByCategory.length === 0" class="text-sm text-gray-500 text-center py-4">Tidak ada data pengeluaran</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
