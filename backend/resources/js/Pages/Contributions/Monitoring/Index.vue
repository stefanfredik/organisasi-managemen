<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
    types: Array,
});

const formatCurrency = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val);
</script>

<template>
    <Head title="Monitoring Iuran" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-foreground">
                Monitoring & Verifikasi Iuran
            </h2>
        </template>

        <div class="py-4">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-4">
                
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="type in types" 
                        :key="type.id"
                        :href="route('contributions.monitoring.dashboard', type.id)"
                        class="group bg-card rounded-lg p-4 border border shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 overflow-hidden"
                    >
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex items-center space-x-3 min-w-0">
                                <div class="w-9 h-9 flex items-center justify-center bg-primary/10 text-primary rounded-lg group-hover:bg-primary group-hover:text-white transition-colors shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-bold text-foreground text-sm leading-tight group-hover:text-primary transition-colors truncate">{{ type.name }}</h3>
                                    <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">{{ type.period === 'once' ? 'Sekali Bayar' : type.period }}</p>
                                </div>
                            </div>
                            <span class="flex h-2.5 w-2.5 shrink-0 ml-2">
                                <span class="animate-ping absolute inline-flex h-2.5 w-2.5 rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-success/100"></span>
                            </span>
                        </div>
                        
                        <!-- Stats -->
                        <div class="space-y-3">
                            <div class="bg-muted/80 rounded-lg p-3 border border/50">
                                <div class="flex justify-between items-center mb-1.5">
                                    <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Terkumpul</p>
                                    <span class="text-xs font-bold text-primary">
                                        {{ ((type.current_period_paid || 0) / (type.target_count || 1) * 100).toFixed(0) }}%
                                    </span>
                                </div>
                                <p class="text-xl font-black text-foreground tracking-tight mb-2">{{ formatCurrency(type.collected_amount || 0).replace('Rp', '').trim() }} <span class="text-xs font-bold text-muted-foreground">IDR</span></p>
                                <div class="w-full bg-muted rounded-full h-1 overflow-hidden">
                                    <div class="bg-primary h-1 rounded-full transition-all duration-500" 
                                        :style="{ width: `${Math.min(((type.current_period_paid || 0) / (type.target_count || 1) * 100), 100)}%` }">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-2">
                                <div class="flex items-center space-x-2 p-2 rounded-lg bg-card border border">
                                    <div class="w-6 h-6 rounded bg-success/10 flex items-center justify-center text-success-600">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-muted-foreground uppercase">Lunas</p>
                                        <p class="text-sm font-black text-foreground">{{ type.paid_count || 0 }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2 p-2 rounded-lg bg-card border border">
                                    <div class="w-6 h-6 rounded flex items-center justify-center" :class="(type.pending_count || 0) > 0 ? 'bg-warning-50 text-warning-600' : 'bg-muted text-muted-foreground'">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold uppercase" :class="(type.pending_count || 0) > 0 ? 'text-warning-500' : 'text-muted-foreground'">Pending</p>
                                        <p class="text-sm font-black" :class="(type.pending_count || 0) > 0 ? 'text-foreground' : 'text-muted-foreground'">{{ type.pending_count || 0 }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>

                <div v-if="types.length === 0" class="text-center py-8">
                     <p class="text-sm text-muted-foreground">Belum ada jenis iuran yang aktif.</p>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
