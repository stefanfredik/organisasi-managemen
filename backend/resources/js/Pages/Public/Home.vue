<script setup>
import ActionLink from '@/Components/ActionLink.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useScrollReveal } from '@/composables/useScrollReveal';
import { Megaphone, ChevronRight, ArrowRight, Plus, Heart, CreditCard } from 'lucide-vue-next';

import { ref, onMounted, onUnmounted } from 'vue';

useScrollReveal();

const props = defineProps({
    upcomingEvents: Array,
    latestAnnouncements: Array,
    activeDonations: Array,
    latestPhotos: Array,
    featuredAlbums: Array,
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

onMounted(() => {
    startSlide();
});

onUnmounted(() => {
    stopSlide();
});

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};

const page = usePage();

const formatCurrency = (value) => {
    const currency = page.props.appSettings?.financial?.currency || 'Rp';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value).replace('Rp', currency);
};
</script>

<template>
    <Head title="Beranda" />

    <PublicLayout>
        <!-- Hero Slider Section -->
        <div class="relative bg-black overflow-hidden h-screen min-h-[600px] max-h-[1080px]">
            <!-- Slides -->
            <div class="absolute inset-0 z-0">
                <TransitionGroup
                    enter-active-class="transition duration-[1500ms] ease-out"
                    enter-from-class="opacity-0 scale-110"
                    enter-to-class="opacity-100 scale-100"
                    leave-active-class="transition duration-[1500ms] ease-in absolute inset-0"
                    leave-from-class="opacity-100 scale-100"
                    leave-to-class="opacity-0 scale-95"
                >
                    <div
                        v-for="(photo, index) in latestPhotos"
                        :key="photo.id"
                        v-show="currentSlide === index"
                        class="absolute inset-0"
                    >
                        <img
                            :src="photo.url"
                            class="w-full h-full object-cover"
                            loading="eager"
                        >
                        <div class="absolute inset-0 bg-gradient-to-b from-black/50 via-black/30 to-black/80"></div>
                        <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-transparent to-transparent"></div>
                    </div>

                    <!-- Fallback Slide if no photos -->
                    <div v-if="!latestPhotos || latestPhotos.length === 0" class="absolute inset-0">
                        <img src="https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-b from-black/50 via-black/30 to-black/80"></div>
                        <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-transparent to-transparent"></div>
                    </div>
                </TransitionGroup>
            </div>

            <!-- Subtle animated grain overlay -->
            <div class="absolute inset-0 z-[1] opacity-[0.03] pointer-events-none bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMDAiIGhlaWdodD0iMjAwIj48ZmlsdGVyIGlkPSJhIj48ZmVUdXJidWxlbmNlIHR5cGU9ImZyYWN0YWxOb2lzZSIgYmFzZUZyZXF1ZW5jeT0iLjc1IiBzdGl0Y2hUaWxlcz0ic3RpdGNoIi8+PC9maWx0ZXI+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsdGVyPSJ1cmwoI2EpIi8+PC9zdmc+')]"></div>

            <!-- Hero Content -->
            <div class="relative z-10 h-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col justify-end pb-20 sm:pb-32 lg:pb-40">
                <div class="max-w-3xl">
                    <Transition
                        appear
                        enter-active-class="transition duration-1000 delay-300 ease-out"
                        enter-from-class="opacity-0 translate-y-12"
                        enter-to-class="opacity-100 translate-y-0"
                    >
                        <p class="text-primary font-semibold tracking-[0.3em] uppercase text-xs sm:text-sm mb-4 sm:mb-6">
                            Selamat Datang
                        </p>
                    </Transition>

                    <Transition
                        appear
                        enter-active-class="transition duration-1000 delay-500 ease-out"
                        enter-from-class="opacity-0 translate-y-12"
                        enter-to-class="opacity-100 translate-y-0"
                    >
                        <h1 class="text-4xl sm:text-6xl lg:text-8xl font-black tracking-tighter leading-[0.9] text-white">
                            {{ $page.props.appSettings.name.split(' ')[0] }}
                            <span class="block text-primary/80">{{ $page.props.appSettings.name.split(' ').slice(1).join(' ') }}</span>
                        </h1>
                    </Transition>

                    <Transition
                        appear
                        enter-active-class="transition duration-1000 delay-700 ease-out"
                        enter-from-class="opacity-0 translate-y-12"
                        enter-to-class="opacity-100 translate-y-0"
                    >
                        <p class="mt-6 sm:mt-8 text-lg sm:text-xl text-white/70 leading-relaxed max-w-xl">
                            {{ $page.props.appSettings.welcome_text }}
                        </p>
                    </Transition>

                    <Transition
                        appear
                        enter-active-class="transition duration-1000 delay-[900ms] ease-out"
                        enter-from-class="opacity-0 translate-y-12"
                        enter-to-class="opacity-100 translate-y-0"
                    >
                        <div class="mt-8 sm:mt-12 flex flex-col sm:flex-row gap-3 sm:gap-4">
                            <ActionLink :href="route('login')" size="lg">
                                Mulai Sekarang
                            </ActionLink>
                            <ActionLink :href="route('public.about')" variant="secondary" size="lg">
                                Tentang Kami
                            </ActionLink>
                        </div>
                    </Transition>
                </div>
            </div>

            <!-- Slider Dots -->
            <div v-if="latestPhotos?.length > 1" class="absolute bottom-8 left-1/2 -translate-x-1/2 sm:left-auto sm:translate-x-0 sm:right-10 sm:bottom-12 z-20 flex items-center gap-2.5">
                <button
                    v-for="(_, index) in latestPhotos"
                    :key="index"
                    @click="currentSlide = index; stopSlide(); startSlide();"
                    class="relative h-2.5 rounded-full transition-all duration-500 ease-out"
                    :class="currentSlide === index
                        ? 'w-10 bg-primary shadow-lg shadow-primary/30'
                        : 'w-2.5 bg-white/30 hover:bg-white/60'"
                    :aria-label="'Slide ' + (index + 1)"
                ></button>
            </div>

            <!-- Scroll indicator -->
            <div class="absolute bottom-8 left-1/2 -translate-x-1/2 z-20 hidden sm:flex flex-col items-center gap-2 opacity-60">
                <span class="text-white/50 text-xs tracking-[0.3em] uppercase">Scroll</span>
                <div class="w-px h-8 bg-gradient-to-b from-white/50 to-transparent animate-pulse"></div>
            </div>
        </div>

        <!-- Latest Announcements Section -->
        <div class="bg-muted py-20 sm:py-28 lg:py-32">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-14" data-reveal>
                    <div>
                        <p class="text-primary font-semibold tracking-[0.2em] uppercase text-xs mb-3">Pengumuman</p>
                        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-foreground tracking-tight">Kabar Terbaru</h2>
                        <p class="mt-4 text-lg text-muted-foreground max-w-lg">Informasi dan agenda terkini dari pengurus organisasi.</p>
                    </div>
                    <Link :href="route('public.events.index')" class="text-primary font-bold hover:text-primary/80 transition-colors uppercase tracking-widest text-sm flex items-center gap-2 shrink-0">
                        Lihat Semua Kegiatan
                        <ArrowRight class="w-4 h-4" />
                    </Link>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="(announcement, idx) in latestAnnouncements"
                        :key="announcement.id"
                        data-reveal
                        :data-reveal-delay="idx * 100"
                        class="bg-card rounded-2xl p-7 sm:p-8 border border shadow-sm hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 flex flex-col h-full group"
                    >
                        <div class="w-11 h-11 rounded-xl bg-primary/10 flex items-center justify-center text-primary mb-5 group-hover:scale-110 group-hover:bg-primary/20 transition-all duration-300">
                            <Megaphone class="w-5 h-5" />
                        </div>
                        <h3 class="text-lg font-bold text-foreground mb-2 leading-snug group-hover:text-primary transition-colors duration-300">{{ announcement.title }}</h3>
                        <p class="text-muted-foreground text-xs font-semibold uppercase tracking-widest mb-4">{{ formatDate(announcement.created_at) }}</p>
                        <div class="text-muted-foreground line-clamp-3 mb-6 text-sm leading-relaxed overflow-hidden" v-html="announcement.content"></div>
                        <div class="mt-auto pt-5 border-t border">
                            <Link :href="route('announcements.show', announcement.id)" class="text-primary font-bold text-xs uppercase tracking-[0.15em] hover:text-primary/80 transition-colors flex items-center gap-1.5">
                                Baca Detail
                                <ChevronRight class="w-3.5 h-3.5" />
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-if="latestAnnouncements.length === 0" class="text-center py-20 bg-card rounded-2xl border-2 border-dashed border" data-reveal="fade">
                    <p class="text-muted-foreground italic">Belum ada pengumuman terbaru.</p>
                </div>
            </div>
        </div>

        <!-- Featured Albums Slider Section -->
        <div v-if="$page.props.appSettings.features.gallery" class="bg-card py-20 sm:py-28 lg:py-32 overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-14" data-reveal>
                    <p class="text-primary font-semibold tracking-[0.2em] uppercase text-xs mb-3">Galeri</p>
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-foreground tracking-tight">Momen Berharga Kami</h2>
                </div>

                <div class="flex gap-5 sm:gap-8 overflow-x-auto pb-8 snap-x no-scrollbar" data-reveal="fade">
                    <div
                        v-for="album in featuredAlbums"
                        :key="album.id"
                        class="snap-center shrink-0 w-[280px] sm:w-[380px] h-[380px] sm:h-[480px] group relative rounded-3xl overflow-hidden shadow-lg hover:shadow-2xl transition-shadow duration-500"
                    >
                        <img
                            :src="album.cover_image_url || 'https://images.unsplash.com/photo-1542038784456-1ea8e935640e?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80'"
                            class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-out"
                        >
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/30 to-transparent opacity-80 group-hover:opacity-90 transition-opacity duration-300"></div>
                        <div class="absolute bottom-0 left-0 right-0 p-8 sm:p-10">
                            <span class="inline-block px-3 py-1 bg-white/10 backdrop-blur-md text-white text-xs font-bold uppercase tracking-widest mb-3 rounded-lg border border-white/10">
                                {{ album.category }} &bull; {{ album.photos_count }} Foto
                            </span>
                            <h3 class="text-2xl sm:text-3xl font-black text-white mb-5 tracking-tight leading-tight">{{ album.name }}</h3>
                            <Link :href="route('public.gallery.show', album.slug)" class="w-12 h-12 rounded-full bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center text-white shadow-xl hover:bg-primary hover:border-primary hover:scale-110 transition-all duration-300">
                                <Plus class="w-5 h-5" />
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Support CTA Section -->
        <div class="relative py-28 sm:py-36 lg:py-44 overflow-hidden bg-gradient-to-br from-primary-900 via-primary-800 to-primary-950" data-reveal="scale">
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-primary/20 via-transparent to-transparent"></div>
            <div class="absolute inset-0 opacity-[0.04] bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCI+PGNpcmNsZSBjeD0iMzAiIGN5PSIzMCIgcj0iMSIgZmlsbD0iI2ZmZiIvPjwvc3ZnPg==')]"></div>
            <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 relative z-10">
                <div class="inline-flex items-center gap-2 mb-8 px-4 py-2 rounded-full bg-white/10 backdrop-blur-sm border border-white/10">
                    <Heart class="w-4 h-4 text-primary-200" />
                    <span class="text-primary-100 text-xs font-semibold uppercase tracking-widest">Dukung Kami</span>
                </div>
                <h2 class="text-3xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight mb-6 leading-tight">
                    Bangun Bersama<br class="hidden sm:block"> Masa Depan.
                </h2>
                <p class="text-lg sm:text-xl text-primary-100/80 mb-12 leading-relaxed max-w-2xl mx-auto">
                    Dukungan Anda dalam bentuk iuran dan donasi adalah energi utama pergerakan organisasi kami.
                </p>
                <div class="flex flex-wrap justify-center gap-4 sm:gap-6">
                    <ActionLink
                        v-if="$page.props.appSettings.features.donations"
                        :href="route('public.donations.index')"
                        variant="secondary"
                        size="lg"
                    >
                        Donasi Program
                    </ActionLink>
                    <ActionLink :href="route('login')" size="lg">
                        Bayar Iuran
                    </ActionLink>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>

<style>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
