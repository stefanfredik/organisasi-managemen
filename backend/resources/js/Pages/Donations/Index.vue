<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SearchBar from '@/Components/SearchBar.vue';
import FilterDropdown from '@/Components/FilterDropdown.vue';
import { Button } from '@/components/ui/button';
import { Plus } from 'lucide-vue-next';

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
        active: 'bg-success/20 text-success-foreground',
        completed: 'bg-primary/20 text-primary',
        cancelled: 'bg-destructive/20 text-destructive',
    };
    return badges[status] || 'bg-muted text-foreground';
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
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold leading-tight text-foreground">Donasi</h2>
                <div class="flex gap-2">
                    <Button v-if="$page.props.auth.user.role !== 'anggota'" variant="outline" size="sm" as-child>
                        <Link :href="route('donations.report')">Laporan</Link>
                    </Button>
                    <Button v-if="$page.props.auth.user.role !== 'anggota'" size="sm" as-child>
                        <Link :href="route('donations.create')">
                            <Plus class="w-4 h-4 mr-1" />
                            <span class="hidden sm:inline">Buat Program</span>
                        </Link>
                    </Button>
                </div>
            </div>
        </template>

        <div class="py-4 sm:py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="bg-card border rounded-xl overflow-hidden">
                    <!-- Compact Filters -->
                    <div class="p-3 sm:p-4 border-b">
                        <div class="flex flex-col sm:flex-row gap-2">
                            <div class="flex-1">
                                <SearchBar v-model="search" placeholder="Cari donasi..." />
                            </div>
                            <div class="sm:w-36">
                                <FilterDropdown v-model="status" :options="statusOptions" label="Status" />
                            </div>
                        </div>
                    </div>

                    <!-- Grid Layout for Donations -->
                    <div class="p-3 sm:p-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <Link 
                            v-for="donation in donations.data" 
                            :key="donation.id" 
                            :href="route('donations.show', donation)"
                            class="block border rounded-lg overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 bg-card group cursor-pointer h-full flex flex-col"
                        >
                            <div class="p-5 flex-1 flex flex-col">
                                <div class="flex justify-between items-start mb-3">
                                    <span :class="getStatusBadge(donation.status)" class="px-2.5 py-0.5 rounded-full text-xs font-medium">
                                        {{ donation.status.charAt(0).toUpperCase() + donation.status.slice(1) }}
                                    </span>
                                    <span v-if="donation.is_public" class="text-xs text-muted-foreground flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/></svg>
                                        Publik
                                    </span>
                                </div>
                                
                                <h3 class="text-lg font-bold text-foreground mb-2 truncate group-hover:text-primary transition-colors" :title="donation.program_name">
                                    {{ donation.program_name }}
                                </h3>
                                
                                <p class="text-sm text-muted-foreground mb-4 line-clamp-3 flex-1">
                                    {{ donation.description || 'Tidak ada deskripsi.' }}
                                </p>
                                
                                <div class="mb-4 bg-muted p-3 rounded-md border border">
                                    <div class="flex justify-between text-xs font-medium mb-2">
                                        <div class="flex flex-col">
                                            <span class="text-muted-foreground text-[10px] uppercase">Terkumpul</span>
                                            <span class="text-primary font-bold text-sm">{{ formatCurrency(donation.collected_amount) }}</span>
                                        </div>
                                        <div class="flex flex-col text-right">
                                             <span class="text-muted-foreground text-[10px] uppercase">Target</span>
                                            <span class="text-foreground font-semibold text-sm">{{ formatCurrency(donation.target_amount) }}</span>
                                        </div>
                                    </div>
                                    <div class="w-full bg-muted rounded-full h-2.5 mb-1">
                                        <div 
                                            class="bg-primary h-2.5 rounded-full transition-all duration-500" 
                                            :style="{ width: calculateProgress(donation.collected_amount, donation.target_amount) + '%' }"
                                        ></div>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-[10px] font-bold text-primary bg-primary/10 px-2 py-0.5 rounded-full">
                                            {{ calculateProgress(donation.collected_amount, donation.target_amount) }}% Tercapai
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="flex items-center justify-between text-xs text-muted-foreground mt-auto pt-3 border-t border">
                                    <div class="flex items-center" title="Periode Donasi">
                                        <svg class="w-4 h-4 mr-1.5 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        <span>{{ formatDate(donation.start_date) }}</span>
                                        <span v-if="donation.end_date" class="mx-1">-</span>
                                        <span v-if="donation.end_date">{{ formatDate(donation.end_date) }}</span>
                                    </div>
                                </div>
                            </div>
                        </Link>

                        <div v-if="donations.data.length === 0" class="col-span-1 md:col-span-2 lg:col-span-3 py-12 text-center bg-muted rounded-lg border-2 border-dashed">
                            <svg class="mx-auto h-12 w-12 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-foreground">Tidak ada program donasi</h3>
                            <p class="mt-1 text-sm text-muted-foreground">Mulai dengan membuat program donasi baru.</p>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="p-6 border-t border">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-foreground">
                                Menampilkan {{ donations.from || 0 }} sampai {{ donations.to || 0 }} dari {{ donations.total }} hasil
                            </span>
                            <div class="flex gap-2">
                                <Link
                                    v-if="donations.prev_page_url"
                                    :href="donations.prev_page_url"
                                    class="px-3 py-1 border rounded bg-card hover:bg-muted text-sm font-medium"
                                >
                                    Sebelumnya
                                </Link>
                                <Link
                                    v-if="donations.next_page_url"
                                    :href="donations.next_page_url"
                                    class="px-3 py-1 border rounded bg-card hover:bg-muted text-sm font-medium"
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
