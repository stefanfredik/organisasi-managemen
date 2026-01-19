<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import SearchBar from '@/Components/SearchBar.vue';

const props = defineProps({
    donations: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');

watch(search, (newSearch) => {
    router.get(route('public.donations.index'), {
        search: newSearch,
    }, {
        preserveState: true,
        replace: true,
    });
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(amount);
};

const calculateProgress = (collected, target) => {
    if (!target || target <= 0) return 0;
    return Math.min(Math.round((collected / target) * 100), 100);
};
</script>

<template>
    <Head title="Program Donasi" />

    <PublicLayout>
        <!-- Hero Section -->
        <div class="relative bg-indigo-900 py-16 sm:py-24">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-900 via-indigo-800 to-indigo-900 opacity-90"></div>
                <div class="absolute inset-0" style="background-image: url('https://www.transparenttextures.com/patterns/cubes.png');"></div>
            </div>
            <div class="relative mx-auto max-w-7xl px-6 lg:px-8 text-center">
                <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl">
                    Mari Berbagi Kebaikan
                </h1>
                <p class="mt-6 max-w-2xl mx-auto text-xl text-indigo-100">
                    Bantu sesama melalui program-program donasi resmi kami. Setiap kontribusi Anda sangat berarti bagi mereka yang membutuhkan.
                </p>
            </div>
        </div>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <!-- Search & Filters -->
                <div class="mb-12 max-w-xl mx-auto">
                    <SearchBar 
                        v-model="search" 
                        placeholder="Cari program donasi..." 
                        class="shadow-lg rounded-full overflow-hidden border-2 border-indigo-100 focus-within:border-indigo-500 transition-all"
                    />
                </div>

                <!-- Donations Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="donation in donations.data" :key="donation.id" class="flex flex-col bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 group">
                        <div class="relative h-48 bg-indigo-100 flex items-center justify-center overflow-hidden">
                            <span class="text-indigo-300 transform group-hover:scale-110 transition-transform duration-500">
                                <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z" />
                                </svg>
                            </span>
                            <div class="absolute top-4 right-4 bg-white/90 backdrop-blur px-3 py-1 rounded-full text-xs font-bold text-indigo-600 shadow-sm">
                                AKTIF
                            </div>
                        </div>
                        
                        <div class="p-6 flex-grow flex flex-col">
                            <h3 class="text-xl font-bold text-gray-900 mb-2 line-clamp-1 group-hover:text-indigo-600 transition-colors">
                                {{ donation.program_name }}
                            </h3>
                            <p class="text-gray-600 text-sm mb-6 line-clamp-3 h-15">
                                {{ donation.description || 'Tidak ada deskripsi tersedia untuk program ini.' }}
                            </p>

                            <div class="mt-auto">
                                <div class="flex justify-between items-end mb-2">
                                    <span class="text-sm font-bold text-indigo-600">{{ formatCurrency(donation.collected_amount) }}</span>
                                    <span class="text-xs text-gray-400 font-medium">Target: {{ formatCurrency(donation.target_amount) }}</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2.5 mb-2">
                                    <div 
                                        class="bg-gradient-to-r from-indigo-500 to-indigo-600 h-2.5 rounded-full transition-all duration-1000" 
                                        :style="{ width: calculateProgress(donation.collected_amount, donation.target_amount) + '%' }"
                                    ></div>
                                </div>
                                <div class="flex justify-between items-center text-[10px] font-black uppercase tracking-widest text-gray-400">
                                    <span>Progress</span>
                                    <span>{{ calculateProgress(donation.collected_amount, donation.target_amount) }}%</span>
                                </div>
                                
                                <Link 
                                    :href="route('public.donations.show', donation.slug)"
                                    class="mt-6 block w-full text-center py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold transition-all shadow-md shadow-indigo-100 hover:shadow-lg hover:shadow-indigo-200 active:scale-95"
                                >
                                    Donasi Sekarang
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="donations.data.length === 0" class="text-center py-24">
                    <div class="mx-auto h-24 w-24 text-indigo-200">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-xl font-bold text-gray-900">Belum ada program donasi aktif</h3>
                    <p class="mt-2 text-gray-500">Silakan kembali lagi nanti untuk melihat program terbaru kami.</p>
                </div>

                <!-- Pagination -->
                <div v-if="donations.links && donations.total > donations.per_page" class="mt-12 flex justify-center">
                    <nav class="flex gap-2">
                        <Link 
                            v-for="(link, k) in donations.links" 
                            :key="k"
                            :href="link.url || '#'"
                            class="px-4 py-2 rounded-lg text-sm font-bold transition-all border"
                            :class="{
                                'bg-indigo-600 text-white border-transparent shadow-md': link.active,
                                'bg-white text-gray-700 border-gray-200 hover:border-indigo-300 hover:bg-indigo-50': !link.active && link.url,
                                'opacity-50 cursor-not-allowed hidden': !link.url
                            }"
                            v-html="link.label"
                        />
                    </nav>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>

<style scoped>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;  
    overflow: hidden;
}
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;  
    overflow: hidden;
}
</style>
