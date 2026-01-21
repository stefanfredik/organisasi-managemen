<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    assets: Object,
    liabilities: Object,
    equity: Object,
    summary: Object,
    filters: Object,
});

const asOfDate = ref(props.filters.as_of_date);

const applyFilters = () => {
    router.get(route('reports.balance-sheet'), {
        as_of_date: asOfDate.value,
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
        month: 'long',
        year: 'numeric',
    });
};
</script>

<template>
    <Head title="Neraca Keuangan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <Link :href="route('reports.index')" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <h2 class="text-xl font-semibold text-gray-800">Neraca Keuangan</h2>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                
                <!-- Filters -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
                    <div class="flex flex-col md:flex-row md:items-end gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Per Tanggal</label>
                            <input 
                                type="date" 
                                v-model="asOfDate"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>
                        <div class="flex-shrink-0">
                            <button
                                @click="applyFilters"
                                class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Perbarui
                            </button>
                        </div>
                    </div>
                </div>

                <div class="text-center mb-8">
                    <h3 class="text-2xl font-bold text-gray-900 uppercase">Neraca Keuangan</h3>
                    <p class="text-gray-600">Per {{ formatDate(asOfDate) }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Assets Column -->
                    <div class="space-y-6">
                        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                            <div class="bg-gray-50 px-6 py-3 border-b border-gray-200">
                                <h4 class="text-lg font-bold text-gray-900">AKTIVA (ASET)</h4>
                            </div>
                            <div class="p-6 space-y-4">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-600 uppercase">Kas dan Setara Kas</span>
                                    <span class="text-gray-900 font-medium">{{ formatCurrency(assets.cash) }}</span>
                                </div>
                                <div class="flex justify-between items-center text-sm border-b border-gray-100 pb-2">
                                    <span class="text-gray-600 uppercase">Piutang Iuran (Pending)</span>
                                    <span class="text-gray-900 font-medium">{{ formatCurrency(assets.receivables) }}</span>
                                </div>
                                <div class="flex justify-between items-center pt-2">
                                    <span class="text-lg font-bold text-gray-900">TOTAL AKTIVA</span>
                                    <span class="text-lg font-bold text-indigo-600">{{ formatCurrency(assets.total) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Liabilities and Equity Column -->
                    <div class="space-y-6">
                        <!-- Liabilities -->
                        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                            <div class="bg-gray-50 px-6 py-3 border-b border-gray-200">
                                <h4 class="text-lg font-bold text-gray-900">PASSIVA (KEWAJIBAN & EKUITAS)</h4>
                            </div>
                            <div class="p-6 space-y-6">
                                <div>
                                    <h5 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Kewajiban</h5>
                                    <div class="flex justify-between items-center text-sm border-b border-gray-100 pb-2">
                                        <span class="text-gray-600 uppercase">Komitmen Donasi Aktif</span>
                                        <span class="text-gray-900 font-medium">{{ formatCurrency(liabilities.donationCommitments) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center mt-3">
                                        <span class="text-sm font-bold text-gray-900">Total Kewajiban</span>
                                        <span class="text-sm font-bold text-gray-900">{{ formatCurrency(liabilities.total) }}</span>
                                    </div>
                                </div>

                                <div>
                                    <h5 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Ekuitas</h5>
                                    <div class="flex justify-between items-center text-sm border-b border-gray-100 pb-2">
                                        <span class="text-gray-600 uppercase">Saldo Laba Ditahan</span>
                                        <span class="text-gray-900 font-medium">{{ formatCurrency(equity.retainedEarnings) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center mt-3">
                                        <span class="text-sm font-bold text-gray-900">Total Ekuitas</span>
                                        <span class="text-sm font-bold text-gray-900">{{ formatCurrency(equity.total) }}</span>
                                    </div>
                                </div>

                                <div class="pt-4 border-t border-gray-200">
                                    <div class="flex justify-between items-center">
                                        <span class="text-lg font-bold text-gray-900">TOTAL PASSIVA</span>
                                        <span class="text-lg font-bold text-indigo-600">{{ formatCurrency(liabilities.total + equity.total) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Note -->
                <div class="mt-8 p-6 bg-blue-50 rounded-lg border border-blue-100">
                    <div class="flex gap-4">
                        <svg class="w-6 h-6 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-sm text-blue-800">
                            <p class="font-bold mb-1">Catatan Penting:</p>
                            <p>Neraca Keuangan menunjukkan posisi keuangan organisasi pada satu titik waktu tertentu. Total Aktiva harus selalu sama dengan Total Passiva (Kewajiban + Ekuitas).</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
