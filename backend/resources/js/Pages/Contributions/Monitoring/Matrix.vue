<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, watch } from 'vue';

const props = defineProps({
    contributionTypes: Array,
    years: Array,
    filters: Object,
    matrixData: Object, // { periods: [], members: [{ id, name, status_by_period: { '2024-01-01': 'paid' } }] }
});

const form = ref({
    year: props.filters.year || new Date().getFullYear(),
    type_id: props.filters.type_id || (props.contributionTypes.length > 0 ? props.contributionTypes[0].id : ''),
});

const isLoading = ref(false);

const applyFilter = () => {
    isLoading.value = true;
    router.get(route('contributions.matrix'), form.value, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => isLoading.value = false,
    });
};

const exportExcel = () => {
    const url = route('contributions.matrix.export', {
        type_id: form.value.type_id,
        year: form.value.year,
    });
    window.open(url, '_blank');
};

watch(() => form.value.type_id, () => {
    // Optional: auto-apply when type changes? Maybe better to wait for button or use applyFilter strictly.
    // For now let's make it manual to avoid too many requests or add debounce.
});

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
    <Head title="Matrix Monitoring Iuran" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Matrix Pembayaran
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                
                <!-- Navigation Tabs -->
                <div class="flex space-x-2 border-b border-gray-200 pb-2 overflow-x-auto">
                    <Link :href="route('contributions.monitoring')" class="px-4 py-2 text-sm font-bold rounded-lg text-gray-600 hover:bg-gray-50">
                        Dashboard
                    </Link>
                    <Link :href="route('contributions.verification')" class="px-4 py-2 text-sm font-bold rounded-lg text-gray-600 hover:bg-gray-50">
                        Verifikasi
                    </Link>
                    <Link :href="route('contributions.matrix')" class="px-4 py-2 text-sm font-bold rounded-lg bg-indigo-50 text-indigo-700">
                        Matrix
                    </Link>
                    <Link :href="route('contributions.index')" class="px-4 py-2 text-sm font-bold rounded-lg text-gray-600 hover:bg-gray-50">
                        Riwayat Transaksi
                    </Link>
                </div>

                <!-- Filters -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <div class="col-span-1">
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Jenis Iuran</label>
                            <select v-model="form.type_id" class="w-full rounded-xl border-gray-200 text-sm font-bold text-gray-700 focus:ring-indigo-500 focus:border-indigo-500">
                                <option v-for="type in contributionTypes" :key="type.id" :value="type.id">
                                    {{ type.name }}
                                </option>
                            </select>
                        </div>
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
    </AuthenticatedLayout>
</template>
