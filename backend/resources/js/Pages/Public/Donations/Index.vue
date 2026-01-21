<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { ref, watch } from 'vue';

const props = defineProps({
    donations: Object,
    filters: Object,
});

const search = ref(props.filters.search);

watch(search, (value) => {
    router.get(route('public.donations.index'), { search: value }, {
        preserveState: true,
        replace: true,
    });
});

const page = usePage();

const formatCurrency = (value) => {
    const currency = page.props.appSettings?.financial?.currency || 'Rp';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value).replace('Rp', currency);
};

const getProgress = (donation) => {
    return Math.min(100, (donation.collected_amount / donation.target_amount) * 100);
};
</script>

<template>
    <Head title="Donasi & Fundraising" />

    <PublicLayout>
        <div class="bg-gray-50 min-h-screen">
            <div class="bg-white border-b border-gray-100 py-16 px-6 sm:py-20 lg:px-8 text-center">
                <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl uppercase tracking-tight">Dukung Program Kami</h2>
                <p class="mt-4 text-lg text-gray-500 max-w-2xl mx-auto italic">
                    Setiap kontribusi Anda membantu kami mewujudkan visi dan misi organisasi untuk masyarakat.
                </p>
                
                <div class="mt-10 max-w-xl mx-auto">
                    <div class="relative">
                        <input 
                            v-model="search"
                            type="text" 
                            placeholder="Cari program donasi..."
                            class="w-full bg-gray-50 border-gray-200 rounded-2xl py-4 pl-12 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all shadow-sm"
                        >
                        <svg class="absolute left-4 top-4 w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="mx-auto max-w-7xl px-6 py-20 lg:px-8">
                <div v-if="donations.data.length > 0" class="grid gap-10 md:grid-cols-2 lg:grid-cols-3">
                    <div v-for="donation in donations.data" :key="donation.id" class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden hover:shadow-2xl transition-all duration-500 group flex flex-col">
                        <div class="shrink-0 relative aspect-[16/10]">
                            <img class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" :src="donation.banner_url || 'https://images.unsplash.com/photo-1532629345422-7515f3d16bb8?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80'" alt="">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 to-transparent"></div>
                            <div class="absolute bottom-6 left-8">
                                <span class="px-3 py-1 bg-white/20 backdrop-blur-md text-white border border-white/30 rounded-lg text-[10px] font-black uppercase tracking-widest shadow-sm">
                                    {{ getProgress(donation).toFixed(0) }}% Terpenuhi
                                </span>
                            </div>
                        </div>
                        <div class="flex-1 p-8 lg:p-10 flex flex-col">
                            <div class="flex-1">
                                <Link :href="route('public.donations.show', donation.slug)" class="block group/link">
                                    <h3 class="text-2xl font-black text-slate-900 line-clamp-2 group-hover/link:text-indigo-600 transition-colors mb-4 uppercase tracking-tight">
                                        {{ donation.program_name }}
                                    </h3>
                                    <p class="text-slate-500 text-sm line-clamp-3 mb-8 leading-relaxed">
                                        {{ donation.description }}
                                    </p>
                                </Link>
                            </div>

                            <div class="space-y-6">
                                <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                                    <div 
                                        class="bg-indigo-600 h-full rounded-full transition-all duration-1000 ease-out"
                                        :style="{ width: `${getProgress(donation)}%` }"
                                    ></div>
                                </div>
                                <div class="flex justify-between items-end">
                                    <div>
                                        <p class="text-[10px] text-slate-400 uppercase font-black tracking-widest mb-1.5 ml-0.5">Terkumpul</p>
                                        <p class="text-xl font-black text-indigo-600 leading-none">
                                            {{ formatCurrency(donation.collected_amount) }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[10px] text-slate-400 uppercase font-black tracking-widest mb-1.5 mr-0.5">Target</p>
                                        <p class="text-sm font-bold text-slate-900 leading-none">
                                            {{ formatCurrency(donation.target_amount) }}
                                        </p>
                                    </div>
                                </div>
                                <Link :href="route('public.donations.show', donation.slug)" class="w-full mt-4 flex justify-center py-5 px-4 bg-indigo-600 text-white font-black rounded-2xl hover:bg-slate-900 transition-all shadow-xl shadow-indigo-100 active:scale-95 uppercase tracking-[0.2em] text-xs">
                                    Donasi Sekarang
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-20 bg-white rounded-3xl border border-gray-100 shadow-sm">
                    <svg class="mx-auto h-16 w-16 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <h3 class="mt-4 text-xl font-bold text-gray-900">Program Tidak Ditemukan</h3>
                    <p class="mt-2 text-gray-500">Coba gunakan kata kunci pencarian yang berbeda.</p>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
