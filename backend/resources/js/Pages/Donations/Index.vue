<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SearchBar from '@/Components/SearchBar.vue';
import FilterDropdown from '@/Components/FilterDropdown.vue';

const props = defineProps({
    donations: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');

const statusOptions = [
    { value: 'active', label: 'Aktif' },
    { value: 'completed', label: 'Selesai' },
    { value: 'cancelled', label: 'Dibatalkan' },
];

watch([search, status], ([newSearch, newStatus]) => {
    router.get(route('donations.index'), {
        search: newSearch,
        status: newStatus,
    }, {
        preserveState: true,
        replace: true,
    });
});

const getStatusBadge = (status) => {
    const badges = {
        active: 'bg-green-100 text-green-800',
        completed: 'bg-blue-100 text-blue-800',
        cancelled: 'bg-red-100 text-red-800',
    };
    return badges[status] || 'bg-gray-100 text-gray-800';
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(amount);
};

const calculateProgress = (collected, target) => {
    if (!target || target <= 0) return 0;
    const progress = (collected / target) * 100;
    return Math.min(Math.round(progress), 100);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};
</script>

<template>
    <Head title="Manajemen Donasi" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Manajemen Donasi
                </h2>
                <div class="flex gap-2">
                    <Link
                        v-if="$page.props.auth.user.role !== 'anggota'"
                        :href="route('donations.report')"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Laporan
                    </Link>
                    <Link
                        v-if="$page.props.auth.user.role !== 'anggota'"
                        :href="route('donations.create')"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Buat Program Donasi
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <!-- Filters -->
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex flex-col sm:flex-row gap-4">
                            <div class="flex-grow">
                                <SearchBar
                                    v-model="search"
                                    placeholder="Cari nama program donasi..."
                                />
                            </div>
                            <div class="w-full sm:w-64">
                                <FilterDropdown
                                    v-model="status"
                                    :options="statusOptions"
                                    label="Semua Status"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Grid Layout for Donations -->
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="donation in donations.data" :key="donation.id" class="border rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-300 bg-white">
                            <div class="p-5">
                                <div class="flex justify-between items-start mb-3">
                                    <span :class="getStatusBadge(donation.status)" class="px-2.5 py-0.5 rounded-full text-xs font-medium">
                                        {{ donation.status.charAt(0).toUpperCase() + donation.status.slice(1) }}
                                    </span>
                                    <span v-if="donation.is_public" class="text-xs text-gray-500 flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/></svg>
                                        Publik
                                    </span>
                                </div>
                                
                                <h3 class="text-lg font-bold text-gray-900 mb-2 truncate" :title="donation.program_name">
                                    {{ donation.program_name }}
                                </h3>
                                
                                <p class="text-sm text-gray-600 mb-4 line-clamp-2 h-10">
                                    {{ donation.description || 'Tidak ada deskripsi.' }}
                                </p>
                                
                                <div class="mb-4">
                                    <div class="flex justify-between text-xs font-medium mb-1">
                                        <span class="text-indigo-600">{{ formatCurrency(donation.collected_amount) }}</span>
                                        <span class="text-gray-500">Target: {{ formatCurrency(donation.target_amount) }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div 
                                            class="bg-indigo-600 h-2 rounded-full transition-all duration-500" 
                                            :style="{ width: calculateProgress(donation.collected_amount, donation.target_amount) + '%' }"
                                        ></div>
                                    </div>
                                    <div class="text-right mt-1">
                                        <span class="text-[10px] font-bold text-gray-700">
                                            {{ calculateProgress(donation.collected_amount, donation.target_amount) }}%
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="flex items-center text-xs text-gray-500 mb-4">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    {{ formatDate(donation.start_date) }} 
                                    <span v-if="donation.end_date" class="mx-1">-</span>
                                    {{ donation.end_date ? formatDate(donation.end_date) : '' }}
                                </div>
                                
                                <div class="flex justify-between items-center mt-auto border-t pt-4">
                                    <Link 
                                        :href="route('donations.show', donation)"
                                        class="text-sm font-semibold text-indigo-600 hover:text-indigo-800"
                                    >
                                        Lihat Detail
                                    </Link>
                                    <Link 
                                        v-if="$page.props.auth.user.role !== 'anggota'"
                                        :href="route('donations.edit', donation)"
                                        class="text-sm font-semibold text-orange-600 hover:text-orange-800"
                                    >
                                        Edit
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <div v-if="donations.data.length === 0" class="col-span-1 md:col-span-2 lg:col-span-3 py-12 text-center bg-gray-50 rounded-lg border-2 border-dashed">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada program donasi</h3>
                            <p class="mt-1 text-sm text-gray-500">Mulai dengan membuat program donasi baru.</p>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="p-6 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-700">
                                Menampilkan {{ donations.from || 0 }} sampai {{ donations.to || 0 }} dari {{ donations.total }} hasil
                            </span>
                            <div class="flex gap-2">
                                <Link
                                    v-if="donations.prev_page_url"
                                    :href="donations.prev_page_url"
                                    class="px-3 py-1 border rounded bg-white hover:bg-gray-100 text-sm font-medium"
                                >
                                    Sebelumnya
                                </Link>
                                <Link
                                    v-if="donations.next_page_url"
                                    :href="donations.next_page_url"
                                    class="px-3 py-1 border rounded bg-white hover:bg-gray-100 text-sm font-medium"
                                >
                                    Selanjutnya
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;  
    overflow: hidden;
}
</style>
