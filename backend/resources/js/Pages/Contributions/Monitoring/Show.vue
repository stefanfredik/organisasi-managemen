<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

import { Button } from '@/components/ui/button';
import ChartWidget from '@/Components/ChartWidget.vue';
import { ref, watch } from 'vue';

const props = defineProps({
    type: Object,
    stats: Object,
    charts: Object,
    filters: Object,
});

const form = ref({
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
    period_filter: props.filters.period_filter || '',
});

const formatCurrency = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(val);

const applyFilter = () => {
    router.get(route('contributions.monitoring.dashboard', props.type.id), form.value, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Monitoring - ${type.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                 <Link :href="route('contributions.monitoring.index')" class="mr-3 text-muted-foreground hover:text-foreground">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-foreground">
                    Monitoring: {{ type.name }}
                </h2>
            </div>
        </template>

        <div class="py-4">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row gap-4">
                
                <!-- Sidebar Navigation -->
                <div class="w-full md:w-56 shrink-0">
                    <div class="bg-card rounded-lg shadow-sm border border overflow-hidden sticky top-4">
                        <div class="px-3 py-2 bg-muted border-b border">
                            <h3 class="font-bold text-foreground text-sm">Menu</h3>
                        </div>
                        <div class="p-1.5 space-y-0.5">
                            <Link :href="route('contributions.monitoring.dashboard', type.id)" 
                                class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors"
                                :class="route().current('contributions.monitoring.dashboard') ? 'bg-primary/10 text-primary' : 'text-muted-foreground hover:bg-muted'"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                                Dashboard
                            </Link>
                            <Link :href="route('contributions.monitoring.matrix', type.id)" 
                                class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors"
                                :class="route().current('contributions.monitoring.matrix') ? 'bg-primary/10 text-primary' : 'text-muted-foreground hover:bg-muted'"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7-8v8m14-8v8M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                Matrix
                            </Link>
                            <Link :href="route('contributions.monitoring.history', type.id)" 
                                class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors"
                                :class="route().current('contributions.monitoring.history') ? 'bg-primary/10 text-primary' : 'text-muted-foreground hover:bg-muted'"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Riwayat
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="flex-1 space-y-4">
                    <!-- Member Status Stats -->
                    <div class="grid grid-cols-3 gap-3">
                        <div class="bg-card p-3 rounded-lg shadow-sm border border">
                            <div class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mb-1">Lunas</div>
                            <div class="text-xl font-black text-success-600">{{ stats.member_status?.paid || 0 }}</div>
                            <div class="text-[10px] text-success-600 font-bold">Periode Ini</div>
                        </div>
                        <div class="bg-card p-3 rounded-lg shadow-sm border border">
                            <div class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mb-1">Belum Bayar</div>
                            <div class="text-xl font-black text-foreground">{{ stats.member_status?.unpaid || 0 }}</div>
                            <div class="text-[10px] text-muted-foreground font-bold">Belum</div>
                        </div>
                        <div class="bg-card p-3 rounded-lg shadow-sm border border">
                            <div class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mb-1">Tunggak</div>
                            <div class="text-xl font-black text-destructive">{{ stats.member_status?.arrears || 0 }}</div>
                            <div class="text-[10px] text-destructive font-bold">Menunggak</div>
                        </div>
                    </div>

                    <!-- Financial Stats -->
                    <div class="grid grid-cols-3 gap-3">
                        <div class="bg-card p-3 rounded-lg shadow-sm border border">
                            <div class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mb-1">Terkumpul</div>
                            <div class="text-lg font-black text-primary">{{ formatCurrency(stats.total_collected) }}</div>
                        </div>
                        <div class="bg-card p-3 rounded-lg shadow-sm border border">
                            <div class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mb-1">Pending</div>
                            <div class="text-lg font-black text-warning-500">{{ stats.total_pending }} <span class="text-xs text-muted-foreground font-bold">Trx</span></div>
                        </div>
                        <div class="bg-card p-3 rounded-lg shadow-sm border border">
                            <div class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mb-1">Total Trx</div>
                            <div class="text-lg font-black text-foreground">{{ stats.total_transactions }}</div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="bg-card p-3 rounded-lg shadow-sm border border">
                        <div class="flex flex-wrap items-end gap-3">
                            <div v-if="['monthly', 'yearly', 'weekly'].includes(type.period)">
                                <label class="block text-xs font-medium text-muted-foreground mb-1">Periode</label>
                                <input v-if="type.period === 'monthly'" type="month" v-model="form.period_filter" class="text-sm rounded-md border-input focus:ring-ring focus:border-ring">
                                <input v-else-if="type.period === 'weekly'" type="week" v-model="form.period_filter" class="text-sm rounded-md border-input focus:ring-ring focus:border-ring">
                                <input v-else-if="type.period === 'yearly'" type="number" placeholder="YYYY" v-model="form.period_filter" class="w-24 text-sm rounded-md border-input focus:ring-ring focus:border-ring">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-muted-foreground mb-1">Dari</label>
                                <input type="date" v-model="form.start_date" class="text-sm rounded-md border-input focus:ring-ring focus:border-ring">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-muted-foreground mb-1">Sampai</label>
                                <input type="date" v-model="form.end_date" class="text-sm rounded-md border-input focus:ring-ring focus:border-ring">
                            </div>
                            <Button size="sm" @click="applyFilter">Filter</Button>
                        </div>
                    </div>

                    <!-- Charts -->
                    <div v-if="charts" class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <ChartWidget 
                            title="Tren Pembayaran (Per Bulan)" 
                            :data="charts.monthly" 
                            type="line" 
                            color="primary" 
                            height="250px" 
                        />
                        <ChartWidget 
                            v-if="charts.status_distribution"
                            title="Distribusi Status"
                            :data="charts.status_distribution"
                            type="doughnut"
                            height="250px"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
