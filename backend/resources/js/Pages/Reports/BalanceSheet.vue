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
                    <Link :href="route('reports.index')" class="text-muted-foreground hover:text-foreground">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <h2 class="text-xl font-semibold text-foreground">Neraca Keuangan</h2>
                </div>
                <a
                    :href="route('reports.balance-sheet.pdf', { as_of_date: asOfDate })"
                    target="_blank"
                    class="inline-flex items-center px-3 py-1.5 bg-destructive border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-destructive/90 transition"
                >
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    PDF
                </a>
            </div>
        </template>

        <div class="py-4">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                
                <!-- Filters - inline compact -->
                <div class="bg-card rounded-lg shadow-sm border border p-3 mb-4">
                    <div class="flex items-end gap-3">
                        <div class="flex-1 max-w-xs">
                            <label class="block text-xs font-medium text-muted-foreground mb-1">Per Tanggal</label>
                            <input 
                                type="date" 
                                v-model="asOfDate"
                                class="w-full text-sm rounded-md border-input shadow-sm focus:border-ring focus:ring-ring"
                            />
                        </div>
                        <button
                            @click="applyFilters"
                            class="px-4 py-2 text-sm bg-primary text-white rounded-md hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-ring"
                        >
                            Perbarui
                        </button>
                    </div>
                </div>

                <div class="text-center mb-4">
                    <h3 class="text-lg font-bold text-foreground uppercase">Neraca Keuangan</h3>
                    <p class="text-xs text-muted-foreground">Per {{ formatDate(asOfDate) }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Assets Column -->
                    <div class="bg-card border border rounded-lg overflow-hidden">
                        <div class="bg-muted px-4 py-2 border-b border">
                            <h4 class="text-sm font-bold text-foreground">AKTIVA (ASET)</h4>
                        </div>
                        <div class="p-4 space-y-3">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-muted-foreground">Kas dan Setara Kas</span>
                                <span class="text-foreground font-medium">{{ formatCurrency(assets.cash) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm border-b border pb-2">
                                <span class="text-muted-foreground">Piutang Iuran (Pending)</span>
                                <span class="text-foreground font-medium">{{ formatCurrency(assets.receivables) }}</span>
                            </div>
                            <div class="flex justify-between items-center pt-1">
                                <span class="text-sm font-bold text-foreground">TOTAL AKTIVA</span>
                                <span class="text-sm font-bold text-primary">{{ formatCurrency(assets.total) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Liabilities and Equity Column -->
                    <div class="bg-card border border rounded-lg overflow-hidden">
                        <div class="bg-muted px-4 py-2 border-b border">
                            <h4 class="text-sm font-bold text-foreground">PASSIVA (KEWAJIBAN & EKUITAS)</h4>
                        </div>
                        <div class="p-4 space-y-4">
                            <div>
                                <h5 class="text-[10px] font-black text-muted-foreground uppercase tracking-widest mb-2">Kewajiban</h5>
                                <div class="flex justify-between items-center text-sm border-b border pb-2">
                                    <span class="text-muted-foreground">Komitmen Donasi Aktif</span>
                                    <span class="text-foreground font-medium">{{ formatCurrency(liabilities.donationCommitments) }}</span>
                                </div>
                                <div class="flex justify-between items-center mt-2">
                                    <span class="text-sm font-bold text-foreground">Total Kewajiban</span>
                                    <span class="text-sm font-bold text-foreground">{{ formatCurrency(liabilities.total) }}</span>
                                </div>
                            </div>

                            <div>
                                <h5 class="text-[10px] font-black text-muted-foreground uppercase tracking-widest mb-2">Ekuitas</h5>
                                <div class="flex justify-between items-center text-sm border-b border pb-2">
                                    <span class="text-muted-foreground">Saldo Laba Ditahan</span>
                                    <span class="text-foreground font-medium">{{ formatCurrency(equity.retainedEarnings) }}</span>
                                </div>
                                <div class="flex justify-between items-center mt-2">
                                    <span class="text-sm font-bold text-foreground">Total Ekuitas</span>
                                    <span class="text-sm font-bold text-foreground">{{ formatCurrency(equity.total) }}</span>
                                </div>
                            </div>

                            <div class="pt-3 border-t border">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-bold text-foreground">TOTAL PASSIVA</span>
                                    <span class="text-sm font-bold text-primary">{{ formatCurrency(liabilities.total + equity.total) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Note -->
                <div class="mt-4 p-3 bg-primary/10 rounded-lg border border-primary-100">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-primary-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-xs text-primary">Total Aktiva harus selalu sama dengan Total Passiva (Kewajiban + Ekuitas).</p>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
