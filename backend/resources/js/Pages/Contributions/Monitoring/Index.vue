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
                <!-- Global Navigation -->
                 <div class="flex space-x-2 border-b border-gray-200 pb-2 overflow-x-auto">
                    <Link :href="route('contributions.monitoring.index')" class="px-4 py-2 text-sm font-bold rounded-lg bg-indigo-50 text-indigo-700">
                        Jenis Iuran Aktif
                    </Link>
                    <Link :href="route('contributions.verification')" class="px-4 py-2 text-sm font-bold rounded-lg text-gray-600 hover:bg-gray-50">
                        Verifikasi (Global)
                    </Link>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <Link 
                        v-for="type in types" 
                        :key="type.id"
                        :href="route('contributions.monitoring.dashboard', type.id)"
                        class="block bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md hover:border-indigo-100 transition-all group"
                    >
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div class="text-right">
                                <span class="bg-green-100 text-green-700 text-xs font-bold px-2 py-1 rounded">Aktif</span>
                            </div>
                        </div>
                        
                        <h3 class="text-lg font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">{{ type.name }}</h3>
                        <p class="text-sm text-gray-500 mb-4 capitalize">{{ type.period === 'once' ? 'Satu Kali' : 'Periode ' + type.period }}</p>
                        
                        <div class="border-t border-gray-50 pt-4 mt-2 flex justify-between items-center text-sm">
                            <span class="text-gray-400">Nominal:</span>
                            <span class="font-bold text-gray-800">{{ formatCurrency(type.amount) }}</span>
                        </div>
                         <div class="flex justify-between items-center text-sm mt-1">
                            <span class="text-gray-400">Total Transaksi:</span>
                            <span class="font-bold text-gray-800">{{ type.contributions_count }}</span>
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
