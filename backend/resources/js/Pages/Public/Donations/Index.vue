<script setup>
import ActionLink from '@/Components/ActionLink.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useScrollReveal } from '@/composables/useScrollReveal';
import { Search, Gift, Heart, PackageX } from 'lucide-vue-next';

import { ref, watch } from 'vue';

useScrollReveal();

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
        <div class="bg-muted min-h-screen">
            <!-- Hero Section -->
            <div class="relative bg-gradient-to-br from-primary to-primary/80 pt-24 pb-10 px-4 sm:px-6 lg:px-8 text-center overflow-hidden">
                <div class="relative z-10" data-reveal="scale">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-white/15 backdrop-blur-sm text-white rounded-full text-sm font-medium mb-4">
                        <Heart class="w-4 h-4" />
                        <span>Donasi & Fundraising</span>
                    </div>
                    <h2 class="text-2xl font-bold text-white sm:text-3xl lg:text-4xl tracking-tight">
                        Dukung Program Kami
                    </h2>
                    <p class="mt-2 text-base text-white/80 max-w-2xl mx-auto leading-relaxed">
                        Setiap kontribusi Anda membantu kami mewujudkan visi dan misi organisasi untuk masyarakat.
                    </p>
                </div>

                <div class="relative z-10 mt-6 max-w-xl mx-auto" data-reveal data-reveal-delay="150">
                    <div class="relative group">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari program donasi..."
                            class="w-full bg-white/15 backdrop-blur-sm border border-white/20 text-white placeholder-white/60 rounded-xl py-3 pl-10 pr-4 focus:ring-2 focus:ring-white/30 focus:border-white/40 transition-all"
                        >
                        <Search class="absolute left-3 top-3 w-5 h-5 text-white/60" />
                    </div>
                </div>
            </div>

            <!-- Donation Cards -->
            <div class="mx-auto max-w-7xl px-4 sm:px-6 py-10 sm:py-14 lg:px-8">
                <div v-if="donations.data.length > 0" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="(donation, index) in donations.data"
                        :key="donation.id"
                        data-reveal
                        :data-reveal-delay="index * 100"
                        class="bg-card rounded-2xl shadow-sm border overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-500 group flex flex-col"
                    >
                        <div class="flex-1 p-5 flex flex-col">
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-primary/10 text-primary rounded-full text-xs font-semibold tracking-wide">
                                    <Heart class="w-3 h-3" />
                                    {{ getProgress(donation).toFixed(0) }}% Terpenuhi
                                </span>
                            </div>
                            <div class="flex-1">
                                <Link :href="route('public.donations.show', donation.slug)" class="block group/link">
                                    <h3 class="text-lg font-bold text-foreground line-clamp-2 group-hover/link:text-primary transition-colors mb-2 tracking-tight">
                                        {{ donation.program_name }}
                                    </h3>
                                    <p class="text-muted-foreground text-sm line-clamp-3 mb-4 leading-relaxed">
                                        {{ donation.description }}
                                    </p>
                                </Link>
                            </div>

                            <div class="space-y-3">
                                <!-- Progress bar -->
                                <div class="w-full bg-muted rounded-full h-2 overflow-hidden">
                                    <div
                                        class="bg-gradient-to-r from-primary to-primary/80 h-full rounded-full transition-all duration-1000 ease-out relative"
                                        :style="{ width: `${getProgress(donation)}%` }"
                                    >
                                        <div class="absolute inset-0 bg-white/20 animate-pulse rounded-full"></div>
                                    </div>
                                </div>

                                <div class="flex justify-between items-end">
                                    <div>
                                        <p class="text-xs text-muted-foreground font-medium mb-0.5">Terkumpul</p>
                                        <p class="text-base font-bold text-primary leading-none">
                                            {{ formatCurrency(donation.collected_amount) }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-muted-foreground font-medium mb-0.5">Target</p>
                                        <p class="text-sm font-semibold text-foreground leading-none">
                                            {{ formatCurrency(donation.target_amount) }}
                                        </p>
                                    </div>
                                </div>

                                <ActionLink :href="route('public.donations.show', donation.slug)" size="lg" class="w-full">
                                    <Gift class="w-4 h-4 mr-2" />
                                    Donasi Sekarang
                                </ActionLink>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-16 bg-card rounded-2xl border shadow-sm" data-reveal="scale">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-muted rounded-2xl mb-4">
                        <PackageX class="w-8 h-8 text-muted-foreground" />
                    </div>
                    <h3 class="text-lg font-bold text-foreground">Program Tidak Ditemukan</h3>
                    <p class="mt-1 text-sm text-muted-foreground max-w-md mx-auto">Coba gunakan kata kunci pencarian yang berbeda.</p>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
