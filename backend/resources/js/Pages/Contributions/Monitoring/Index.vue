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
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Monitoring & Verifikasi Iuran
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                
                <!-- Active Types Grid -->
                <!-- <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"> - Original comment removed, will just remove the tabs block -->

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <Link 
                        v-for="type in types" 
                        :key="type.id"
                        :href="route('contributions.monitoring.dashboard', type.id)"
                        class="relative group bg-white rounded-3xl p-6 border border-gray-100 shadow-[0_2px_20px_rgba(0,0,0,0.04)] hover:shadow-[0_12px_30px_rgba(67,56,202,0.1)] hover:-translate-y-1 transition-all duration-300 overflow-hidden"
                    >
                        <!-- Decorative background blob -->
                        <div class="absolute -top-12 -right-12 w-32 h-32 bg-indigo-50/50 rounded-full blur-3xl group-hover:bg-indigo-100/60 transition-colors duration-500"></div>

                        <div class="relative z-10 flex flex-col h-full">
                            <!-- Header -->
                            <div class="flex justify-between items-start mb-6">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 flex items-center justify-center bg-indigo-50 text-indigo-600 rounded-2xl group-hover:scale-110 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300 shadow-sm">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                    <div>
                                        <h3 class="font-black text-gray-900 text-lg leading-tight group-hover:text-indigo-700 transition-colors line-clamp-1">{{ type.name }}</h3>
                                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mt-1">{{ type.period === 'once' ? 'Sekali Bayar' : type.period }}</p>
                                    </div>
                                </div>
                                <span class="flex h-3 w-3 relative">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                                </span>
                            </div>
                            
                            <!-- Main Stats -->
                            <div class="mt-auto space-y-5">
                                <div class="bg-gray-50/80 rounded-2xl p-4 border border-gray-100/50">
                                    <div class="flex justify-between items-end mb-2">
                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Terkumpul</p>
                                        <div class="text-right">
                                            <span class="text-xs font-bold text-indigo-600">
                                                {{ ((type.current_period_paid || 0) / (type.target_count || 1) * 100).toFixed(0) }}%
                                            </span>
                                            <span class="text-[10px] text-gray-400 font-medium ml-1">Pelunasan ({{ type.current_period_label || 'Periode Ini' }})</span>
                                        </div>
                                    </div>
                                    <div class="flex items-baseline space-x-1 mb-3">
                                         <p class="text-3xl font-black text-gray-900 tracking-tight">{{ formatCurrency(type.collected_amount || 0).replace('Rp', '').trim() }}</p>
                                         <p class="text-xs font-bold text-gray-400">IDR</p>
                                    </div>
                                    
                                    <!-- Progress Line -->
                                    <div class="w-full bg-gray-200 rounded-full h-1.5 overflow-hidden">
                                        <div class="bg-indigo-600 h-1.5 rounded-full transition-all duration-500" 
                                            :style="{ width: `${Math.min(((type.current_period_paid || 0) / (type.target_count || 1) * 100), 100)}%` }">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="flex items-center space-x-3 p-3 rounded-xl bg-white border border-gray-100 shadow-sm">
                                        <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center text-green-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-bold text-gray-400 uppercase">Lunas</p>
                                            <p class="text-sm font-black text-gray-800">{{ type.paid_count || 0 }}</p>
                                        </div>
                                    </div>
                                    
                                     <div class="flex items-center space-x-3 p-3 rounded-xl bg-white border border-gray-100 shadow-sm">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center" :class="(type.pending_count || 0) > 0 ? 'bg-amber-50 text-amber-600' : 'bg-gray-50 text-gray-300'">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        </div>
                                         <div>
                                            <p class="text-[10px] font-bold uppercase" :class="(type.pending_count || 0) > 0 ? 'text-amber-500' : 'text-gray-400'">Pending</p>
                                            <p class="text-sm font-black" :class="(type.pending_count || 0) > 0 ? 'text-gray-800' : 'text-gray-400'">{{ type.pending_count || 0 }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>

                <div v-if="types.length === 0" class="text-center py-12">
                     <p class="text-gray-400">Belum ada jenis iuran yang aktif.</p>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
