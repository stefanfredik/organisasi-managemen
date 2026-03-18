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
            <div class="relative bg-gradient-to-br from-primary/10 via-card to-primary/5 border-b pt-28 pb-16 px-4 sm:pb-20 sm:px-6 lg:pb-24 lg:px-8 text-center overflow-hidden">
                <!-- Decorative background elements -->
                <div class="absolute inset-0 overflow-hidden pointer-events-none">
                    <div class="absolute -top-24 -right-24 w-96 h-96 bg-primary/5 rounded-full blur-3xl"></div>
                    <div class="absolute -bottom-32 -left-32 w-96 h-96 bg-primary/5 rounded-full blur-3xl"></div>
                </div>

                <div class="relative z-10" data-reveal="scale">
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-primary/10 text-primary rounded-full text-sm font-semibold mb-6">
                        <Heart class="w-4 h-4" />
                        <span>Donasi & Fundraising</span>
                    </div>
                    <h2 class="text-3xl font-extrabold text-foreground sm:text-4xl lg:text-5xl uppercase tracking-tight">
                        Dukung Program Kami
                    </h2>
                    <p class="mt-4 text-lg text-muted-foreground max-w-2xl mx-auto leading-relaxed">
                        Setiap kontribusi Anda membantu kami mewujudkan visi dan misi organisasi untuk masyarakat.
                    </p>
                </div>

                <div class="relative z-10 mt-10 max-w-xl mx-auto" data-reveal data-reveal-delay="150">
                    <div class="relative group">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari program donasi..."
                            class="w-full bg-card border border-border rounded-2xl py-4 pl-12 pr-4 focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all shadow-sm group-hover:shadow-md"
                        >
                        <Search class="absolute left-4 top-4 w-5 h-5 text-muted-foreground" />
                    </div>
                </div>
            </div>

            <!-- Donation Cards -->
            <div class="mx-auto max-w-7xl px-4 sm:px-6 py-16 lg:py-20 lg:px-8">
                <div v-if="donations.data.length > 0" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="(donation, index) in donations.data"
                        :key="donation.id"
                        data-reveal
                        :data-reveal-delay="index * 100"
                        class="bg-card rounded-3xl shadow-sm border overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-500 group flex flex-col"
                    >
                        <div class="shrink-0 relative aspect-[16/10] overflow-hidden">
                            <img
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
                                :src="donation.banner_url || 'https://images.unsplash.com/photo-1532629345422-7515f3d16bb8?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80'"
                                alt=""
                            >
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
                            <div class="absolute bottom-4 left-5">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white/15 backdrop-blur-md text-white border border-white/20 rounded-full text-xs font-bold tracking-wide shadow-sm">
                                    <Heart class="w-3 h-3" />
                                    {{ getProgress(donation).toFixed(0) }}% Terpenuhi
                                </span>
                            </div>
                        </div>

                        <div class="flex-1 p-6 lg:p-8 flex flex-col">
                            <div class="flex-1">
                                <Link :href="route('public.donations.show', donation.slug)" class="block group/link">
                                    <h3 class="text-xl font-bold text-foreground line-clamp-2 group-hover/link:text-primary transition-colors mb-3 tracking-tight">
                                        {{ donation.program_name }}
                                    </h3>
                                    <p class="text-muted-foreground text-sm line-clamp-3 mb-6 leading-relaxed">
                                        {{ donation.description }}
                                    </p>
                                </Link>
                            </div>

                            <div class="space-y-4">
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
                                        <p class="text-xs text-muted-foreground uppercase font-bold tracking-widest mb-1">Terkumpul</p>
                                        <p class="text-lg font-bold text-primary leading-none">
                                            {{ formatCurrency(donation.collected_amount) }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-muted-foreground uppercase font-bold tracking-widest mb-1">Target</p>
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
                <div v-else class="text-center py-20 bg-card rounded-3xl border shadow-sm" data-reveal="scale">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-muted rounded-2xl mb-6">
                        <PackageX class="w-10 h-10 text-muted-foreground" />
                    </div>
                    <h3 class="text-xl font-bold text-foreground">Program Tidak Ditemukan</h3>
                    <p class="mt-2 text-muted-foreground max-w-md mx-auto">Coba gunakan kata kunci pencarian yang berbeda.</p>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
