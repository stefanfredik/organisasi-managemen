<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    donations: Array,
    stats: Object,
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(amount);
};

const getStatusBadge = (status) => {
    const badges = {
        active: 'bg-green-100 text-green-800',
        completed: 'bg-blue-100 text-blue-800',
        cancelled: 'bg-red-100 text-red-800',
    };
    return badges[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Laporan Donasi" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-black text-gray-800 uppercase tracking-tight">Laporan Donasi</h2>
                <Link :href="route('donations.index')" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md text-sm font-semibold hover:bg-gray-200 transition">
                    Kembali
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-8">
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Total Target</p>
                        <p class="text-xl font-black text-gray-900">{{ formatCurrency(stats.total_target) }}</p>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Total Terkumpul</p>
                        <p class="text-xl font-black text-indigo-600">{{ formatCurrency(stats.total_collected) }}</p>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Program Aktif</p>
                        <p class="text-xl font-black text-gray-900">{{ stats.active_programs }} Program</p>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Total Donatur</p>
                        <p class="text-xl font-black text-gray-900">{{ stats.total_donators }} Orang</p>
                    </div>
                </div>

                <!-- Detailed Table -->
                <div class="bg-white shadow-sm sm:rounded-2xl overflow-hidden border border-gray-100">
                    <div class="p-6 border-b">
                        <h3 class="font-bold text-gray-900">Breakdown per Program</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 text-[10px] font-black uppercase tracking-widest text-gray-400">
                                <tr>
                                    <th class="px-6 py-4 text-left font-black">Nama Program</th>
                                    <th class="px-6 py-4 text-center font-black">Status</th>
                                    <th class="px-6 py-4 text-right font-black">Target</th>
                                    <th class="px-6 py-4 text-right font-black">Terkumpul</th>
                                    <th class="px-6 py-4 text-right font-black">Progres</th>
                                    <th class="px-6 py-4 text-center font-black">Donatur</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="donation in donations" :key="donation.id" class="text-sm text-gray-700 hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 font-bold text-gray-900">{{ donation.program_name }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span :class="getStatusBadge(donation.status)" class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase">
                                            {{ donation.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right font-medium text-gray-500">{{ formatCurrency(donation.target_amount) }}</td>
                                    <td class="px-6 py-4 text-right font-bold text-indigo-600">{{ formatCurrency(donation.collected_amount) }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <div class="w-16 bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                                <div class="bg-indigo-600 h-1.5 rounded-full" :style="{ width: donation.progress + '%' }"></div>
                                            </div>
                                            <span class="font-bold text-xs">{{ donation.progress }}%</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center font-bold text-gray-500">{{ donation.transactions_count }}</td>
                                </tr>
                                <tr v-if="donations.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-400">Belum ada data donasi.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
