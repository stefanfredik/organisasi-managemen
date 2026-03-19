<script setup>
import ActionLink from '@/Components/ActionLink.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useScrollReveal } from '@/composables/useScrollReveal';
import { Megaphone, ChevronRight, ArrowRight, Plus, Heart, CreditCard } from 'lucide-vue-next';

import { ref, onMounted, onUnmounted } from 'vue';

useScrollReveal();

const page = usePage();
const app = page.props.appSettings;
const portal = app.portal || {};

const props = defineProps({
    upcomingEvents: Array,
    latestAnnouncements: Array,
    activeDonations: Array,
    latestPhotos: Array,
    featuredAlbums: Array,
    memberCount: Number,
});

const currentSlide = ref(0);
let slideTimer = null;

const startSlide = () => {
    slideTimer = setInterval(() => {
        if (props.latestPhotos?.length > 0) {
            currentSlide.value = (currentSlide.value + 1) % props.latestPhotos.length;
        }
    }, 5000);
};

const stopSlide = () => {
    if (slideTimer) clearInterval(slideTimer);
};

onMounted(() => { startSlide(); });
onUnmounted(() => { stopSlide(); });

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};

const formatCurrency = (value) => {
    const currency = page.props.appSettings?.financial?.currency || 'Rp';
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value).replace('Rp', currency);
};
</script>

<template>
    <Head title="Beranda" />

    <PublicLayout>
        <!-- Hero Section -->
        <div class="relative bg-black overflow-hidden h-[80vh] min-h-[480px] max-h-[800px]">
            <!-- Slides -->
            <div class="absolute inset-0">
                <TransitionGroup
                    enter-active-class="transition duration-1000 ease-out"
                    enter-from-class="opacity-0 scale-105"
                    enter-to-class="opacity-100 scale-100"
                    leave-active-class="transition duration-1000 ease-in absolute inset-0"
                    leave-from-class="opacity-100 scale-100"
                    leave-to-class="opacity-0 scale-95"
                >
                    <div v-for="(photo, index) in latestPhotos" :key="photo.id" v-show="currentSlide === index" class="absolute inset-0">
                        <img :src="photo.url" class="w-full h-full object-cover" loading="eager">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-black/20"></div>
                    </div>

                    <div v-if="!latestPhotos || latestPhotos.length === 0" class="absolute inset-0">
                        <div class="absolute inset-0 bg-gradient-to-br from-primary/20 via-primary/10 to-black"></div>
                    </div>
                </TransitionGroup>
            </div>

            <!-- Hero Content -->
            <div class="relative z-10 h-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col justify-end pb-16 sm:pb-20">
                <div class="max-w-2xl">
                    <Transition appear enter-active-class="transition duration-700 delay-300" enter-from-class="opacity-0 translate-y-8" enter-to-class="opacity-100 translate-y-0">
                        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-[1.05] text-white">
                            {{ $page.props.appSettings.name }}
                        </h1>
                    </Transition>

                    <Transition appear enter-active-class="transition duration-700 delay-500" enter-from-class="opacity-0 translate-y-8" enter-to-class="opacity-100 translate-y-0">
                        <p class="mt-4 text-base sm:text-lg text-white/70 leading-relaxed max-w-lg">
                            {{ portal.welcome_text || 'Selamat Datang di Portal Resmi Organisasi Kami' }}
                        </p>
                    </Transition>

                    <Transition appear enter-active-class="transition duration-700 delay-700" enter-from-class="opacity-0 translate-y-8" enter-to-class="opacity-100 translate-y-0">
                        <div class="mt-6 flex flex-wrap gap-3">
                            <ActionLink :href="route('login')" size="md">
                                Mulai Sekarang
                            </ActionLink>
                            <ActionLink :href="route('public.about')" variant="secondary" size="md">
                                Tentang Kami
                            </ActionLink>
                        </div>
                    </Transition>
                </div>
            </div>

            <!-- Slider Dots -->
            <div v-if="latestPhotos?.length > 1" class="absolute bottom-6 right-6 z-20 flex items-center gap-2">
                <button
                    v-for="(_, index) in latestPhotos"
                    :key="index"
                    @click="currentSlide = index; stopSlide(); startSlide();"
                    class="h-2 rounded-full transition-all duration-300"
                    :class="currentSlide === index ? 'w-8 bg-primary' : 'w-2 bg-white/40 hover:bg-white/60'"
                />
            </div>
        </div>

        <!-- Announcements -->
        <div class="bg-muted py-14 sm:py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-end justify-between gap-4 mb-8" data-reveal>
                    <div>
                        <p class="text-primary text-xs font-semibold tracking-widest uppercase mb-1">Pengumuman</p>
                        <h2 class="text-2xl sm:text-3xl font-bold text-foreground tracking-tight">Kabar Terbaru</h2>
                    </div>
                    <Link :href="route('public.events.index')" class="text-primary text-sm font-medium hover:text-primary/80 transition-colors flex items-center gap-1 shrink-0">
                        Lihat Semua <ArrowRight class="w-3.5 h-3.5" />
                    </Link>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="(announcement, idx) in latestAnnouncements"
                        :key="announcement.id"
                        data-reveal
                        :data-reveal-delay="idx * 80"
                        class="bg-card rounded-xl p-5 border shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col group"
                    >
                        <div class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center text-primary mb-3 group-hover:bg-primary/15 transition-colors">
                            <Megaphone class="w-4 h-4" />
                        </div>
                        <h3 class="text-base font-semibold text-foreground mb-1 leading-snug group-hover:text-primary transition-colors line-clamp-2">{{ announcement.title }}</h3>
                        <p class="text-muted-foreground text-xs mb-3">{{ formatDate(announcement.created_at) }}</p>
                        <div class="text-muted-foreground line-clamp-2 text-sm leading-relaxed mb-4" v-html="announcement.content"></div>
                        <div class="mt-auto pt-3 border-t">
                            <Link :href="route('announcements.show', announcement.id)" class="text-primary text-xs font-medium hover:text-primary/80 transition-colors flex items-center gap-1">
                                Baca Detail <ChevronRight class="w-3 h-3" />
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-if="latestAnnouncements.length === 0" class="text-center py-12 bg-card rounded-xl border border-dashed" data-reveal="fade">
                    <p class="text-muted-foreground text-sm">Belum ada pengumuman terbaru.</p>
                </div>
            </div>
        </div>

        <!-- Gallery Albums -->
        <div v-if="$page.props.appSettings.features.gallery && featuredAlbums?.length" class="bg-card py-14 sm:py-20 overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-8" data-reveal>
                    <p class="text-primary text-xs font-semibold tracking-widest uppercase mb-1">Galeri</p>
                    <h2 class="text-2xl sm:text-3xl font-bold text-foreground tracking-tight">Momen Berharga</h2>
                </div>

                <div class="flex gap-4 sm:gap-6 overflow-x-auto pb-4 snap-x no-scrollbar" data-reveal="fade">
                    <div
                        v-for="album in featuredAlbums"
                        :key="album.id"
                        class="snap-center shrink-0 w-[260px] sm:w-[320px] h-[340px] sm:h-[400px] group relative rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-shadow duration-300"
                    >
                        <img
                            :src="album.cover_image_url || 'https://images.unsplash.com/photo-1542038784456-1ea8e935640e?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80'"
                            class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                        >
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                        <div class="absolute bottom-0 left-0 right-0 p-5">
                            <span class="text-white/60 text-xs font-medium">{{ album.category }} &middot; {{ album.photos_count }} Foto</span>
                            <h3 class="text-lg font-bold text-white mt-1 leading-tight">{{ album.name }}</h3>
                            <Link :href="route('public.gallery.show', album.slug)" class="mt-3 inline-flex items-center justify-center w-9 h-9 rounded-full bg-white/10 backdrop-blur-sm border border-white/20 text-white hover:bg-primary hover:border-primary transition-all">
                                <Plus class="w-4 h-4" />
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="relative py-16 sm:py-20 bg-gradient-to-br from-primary-900 via-primary-800 to-primary-950" data-reveal="scale">
            <div class="max-w-3xl mx-auto text-center px-4 sm:px-6 relative z-10">
                <div class="inline-flex items-center gap-2 mb-4 px-3 py-1.5 rounded-full bg-white/10 border border-white/10">
                    <Heart class="w-3.5 h-3.5 text-primary-200" />
                    <span class="text-primary-100 text-xs font-medium">Dukung Kami</span>
                </div>
                <h2 class="text-2xl sm:text-4xl font-bold text-white tracking-tight mb-3">
                    Bangun Bersama Masa Depan
                </h2>
                <p class="text-base text-primary-100/80 mb-8 leading-relaxed max-w-xl mx-auto">
                    Dukungan Anda melalui iuran dan donasi menjadi energi utama pergerakan organisasi kami.
                </p>
                <div class="flex flex-wrap justify-center gap-3">
                    <ActionLink
                        v-if="$page.props.appSettings.features.donations"
                        :href="route('public.donations.index')"
                        variant="secondary"
                        size="md"
                    >
                        Donasi Program
                    </ActionLink>
                    <ActionLink :href="route('login')" size="md">
                        Bayar Iuran
                    </ActionLink>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>

<style>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
