<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
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
                 <Link :href="route('contributions.monitoring.index')" class="mr-3 text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Monitoring: {{ type.name }}
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 flex flex-col md:flex-row gap-6">
                
                <!-- Sidebar Navigation -->
                <div class="w-full md:w-64 shrink-0">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden sticky top-6">
                        <div class="p-4 bg-gray-50 border-b border-gray-100">
                            <h3 class="font-bold text-gray-700">Menu</h3>
                        </div>
                        <div class="p-2 space-y-1">
                            <Link :href="route('contributions.monitoring.dashboard', type.id)" 
                                class="flex items-center px-4 py-3 text-sm font-bold rounded-xl transition-colors"
                                :class="route().current('contributions.monitoring.dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50'"
                            >
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                                Dashboard
                            </Link>
                            <Link :href="route('contributions.monitoring.matrix', type.id)" 
                                class="flex items-center px-4 py-3 text-sm font-bold rounded-xl transition-colors"
                                :class="route().current('contributions.monitoring.matrix') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50'"
                            >
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7-8v8m14-8v8M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                Matrix
                            </Link>
                            <Link :href="route('contributions.monitoring.history', type.id)" 
                                class="flex items-center px-4 py-3 text-sm font-bold rounded-xl transition-colors"
                                :class="route().current('contributions.monitoring.history') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50'"
                            >
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Riwayat Transaksi
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="flex-1 space-y-6">
                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Total Terkumpul</div>
                            <div class="text-2xl font-black text-indigo-600">{{ formatCurrency(stats.total_collected) }}</div>
                        </div>
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Menunggu Verifikasi</div>
                            <div class="text-2xl font-black text-amber-500">{{ stats.total_pending }} <span class="text-sm text-gray-400 font-bold">Transaksi</span></div>
                        </div>
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Total Transaksi</div>
                            <div class="text-2xl font-black text-gray-700">{{ stats.total_transactions }}</div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            <div class="col-span-1">
                                <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Dari Tanggal</label>
                                <input type="date" v-model="form.start_date" class="w-full rounded-xl border-gray-200 text-sm font-bold text-gray-700 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div class="col-span-1">
                                <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Sampai Tanggal</label>
                                <input type="date" v-model="form.end_date" class="w-full rounded-xl border-gray-200 text-sm font-bold text-gray-700 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                             <div class="col-span-1">
                                <PrimaryButton @click="applyFilter" class="w-full justify-center h-[42px]">Filter</PrimaryButton>
                            </div>
                        </div>
                    </div>

                    <!-- Charts -->
                    <div v-if="charts" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <ChartWidget 
                            title="Tren Pembayaran (Per Bulan)" 
                            :data="charts.monthly" 
                            type="line" 
                            color="indigo" 
                            height="300px" 
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
