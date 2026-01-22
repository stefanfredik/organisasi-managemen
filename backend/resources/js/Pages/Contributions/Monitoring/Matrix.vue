<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, watch } from 'vue';

const props = defineProps({
    type: Object, // The specific contribution type context
    years: Array,
    filters: Object,
    matrixData: Object, 
});

const form = ref({
    year: props.filters.year || new Date().getFullYear(),
});

const isLoading = ref(false);

const applyFilter = () => {
    isLoading.value = true;
    router.get(route('contributions.monitoring.matrix', props.type.id), form.value, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => isLoading.value = false,
    });
};

const exportExcel = () => {
    const url = route('contributions.monitoring.matrix.export', {
        contributionType: props.type.id,
        year: form.value.year,
    });
    window.open(url, '_blank');
};

// Helper for cell classes
const getStatusClass = (status) => {
    switch (status) {
        case 'paid': return 'bg-green-100 text-green-700';
        case 'pending': return 'bg-amber-100 text-amber-700';
        case 'partial': return 'bg-blue-100 text-blue-700';
        default: return 'bg-red-50 text-red-300'; // Unpaid/Empty
    }
};

const getStatusLabel = (status) => {
    switch (status) {
        case 'paid': return 'Lunas';
        case 'pending': return 'Proses';
        case 'partial': return 'Sebagian';
        default: return '-';
    }
};

</script>

<template>
    <Head :title="`Matrix - ${type.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                 <Link :href="route('contributions.monitoring.index')" class="mr-3 text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Matrix: {{ type.name }}
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
                    <!-- Filters -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            <div class="col-span-1">
                                <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Tahun</label>
                                <select v-model="form.year" class="w-full rounded-xl border-gray-200 text-sm font-bold text-gray-700 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                                </select>
                            </div>
                            <div class="col-span-1 flex gap-2">
                                <button @click="applyFilter" :disabled="isLoading" class="flex-1 h-[42px] bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center">
                                    <span v-if="isLoading" class="mr-2">
                                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </span>
                                    {{ isLoading ? '...' : 'Tampil' }}
                                </button>
                                <button @click="exportExcel" class="w-12 h-[42px] bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl shadow-sm transition-colors flex items-center justify-center" title="Export Excel">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Matrix Table -->
                    <div v-if="matrixData && matrixData.periods && matrixData.periods.length > 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-center text-sm">
                                <thead class="bg-gray-50 text-xs uppercase font-bold text-gray-500">
                                    <tr>
                                        <th class="px-4 py-4 text-left border-b border-gray-100 sticky left-0 bg-gray-50 z-10 w-48 shadow-sm">
                                            Anggota
                                        </th>
                                        <th v-for="period in matrixData.periods" :key="period.key" class="px-2 py-4 border-b border-gray-100 min-w-[60px]">
                                            {{ period.label }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="member in matrixData.members" :key="member.id" class="hover:bg-gray-50 transition-colors">
                                        <td class="px-4 py-3 text-left bg-white font-bold text-gray-800 sticky left-0 z-10 border-r border-gray-100 shadow-sm">
                                            {{ member.name }}
                                            <div class="text-[10px] text-gray-400 font-normal">{{ member.member_code }}</div>
                                        </td>
                                        <td v-for="period in matrixData.periods" :key="period.key" class="px-1 py-3 bg-white border-l border-gray-50">
                                            <div 
                                                class="inline-flex items-center justify-center w-full h-8 rounded-lg text-xs font-bold"
                                                :class="getStatusClass(member.status_by_period[period.key])"
                                                :title="period.label"
                                            >
                                                {{ getStatusLabel(member.status_by_period[period.key]) }}
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div v-else class="text-center py-12">
                        <p class="text-gray-400 font-medium">Silakan pilih jenis iuran dan tahun untuk melihat data.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
