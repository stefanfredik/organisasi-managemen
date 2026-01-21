<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { ref, onMounted, onUnmounted } from 'vue';

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
        <div class="relative bg-white overflow-hidden h-[600px] lg:h-[700px]">
            <!-- Slides -->
            <div class="absolute inset-0 z-0">
                <TransitionGroup 
                    enter-active-class="transition duration-1000 ease-out"
                    enter-from-class="opacity-0 scale-105"
                    enter-to-class="opacity-100 scale-100"
                    leave-active-class="transition duration-1000 ease-in absolute inset-0"
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
                        <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/40 to-transparent"></div>
                    </div>

                    <!-- Fallback Slide if no photos -->
                    <div v-if="!latestPhotos || latestPhotos.length === 0" class="absolute inset-0">
                        <img src="https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/20 to-transparent"></div>
                    </div>
                </TransitionGroup>
            </div>

            <!-- Hero Content -->
            <div class="relative z-10 h-full max-w-7xl mx-auto px-6 lg:px-8 flex flex-col justify-center">
                <div class="max-w-2xl text-white">
                    <Transition
                        appear
                        enter-active-class="transition duration-700 delay-300 ease-out"
                        enter-from-class="opacity-0 translate-y-8"
                        enter-to-class="opacity-100 translate-y-0"
                    >
                        <h1 class="text-5xl lg:text-7xl font-black tracking-tighter uppercase leading-tight">
                            {{ $page.props.appSettings.name.split(' ')[0] }} <span class="text-indigo-400">{{ $page.props.appSettings.name.split(' ').slice(1).join(' ') }}</span>
                        </h1>
                    </Transition>
                    
                    <Transition
                        appear
                        enter-active-class="transition duration-700 delay-500 ease-out"
                        enter-from-class="opacity-0 translate-y-8"
                        enter-to-class="opacity-100 translate-y-0"
                    >
                        <p class="mt-6 text-xl text-gray-200 leading-relaxed font-medium">
                            {{ $page.props.appSettings.welcome_text }}
                        </p>
                    </Transition>

                    <Transition
                        appear
                        enter-active-class="transition duration-700 delay-700 ease-out"
                        enter-from-class="opacity-0 translate-y-8"
                        enter-to-class="opacity-100 translate-y-0"
                    >
                        <div class="mt-10 flex gap-4">
                            <Link :href="route('login')" class="px-8 py-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-bold shadow-xl transition-all active:scale-95 text-lg uppercase tracking-widest">
                                Mulai Sekarang
                            </Link>
                            <Link :href="route('public.about')" class="px-8 py-4 bg-white/10 hover:bg-white/20 backdrop-blur-md text-white rounded-2xl font-bold transition-all active:scale-95 text-lg border border-white/30 uppercase tracking-widest">
                                Tentang Kami
                            </Link>
                        </div>
                    </Transition>
                </div>
            </div>

            <!-- Slider Controls -->
            <div v-if="latestPhotos?.length > 1" class="absolute bottom-10 right-10 z-20 flex gap-3">
                <button 
                    v-for="(_, index) in latestPhotos" 
                    :key="index"
                    @click="currentSlide = index; stopSlide(); startSlide();"
                    class="h-2 rounded-full transition-all duration-500"
                    :class="currentSlide === index ? 'w-12 bg-indigo-500' : 'w-3 bg-white/30 hover:bg-white/50'"
                ></button>
            </div>
        </div>

        <!-- Latest Announcements Section -->
        <div class="bg-slate-50 py-24">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
                    <div>
                        <h2 class="text-3xl lg:text-5xl font-black text-slate-900 uppercase tracking-tight">Kabar Terbaru</h2>
                        <p class="mt-4 text-lg text-slate-500 font-medium">Informasi dan agenda terkini dari pengurus organisasi.</p>
                    </div>
                    <Link :href="route('public.events.index')" class="text-indigo-600 font-bold hover:text-indigo-700 transition-colors uppercase tracking-widest text-sm flex items-center gap-2">
                        Lihat Semua Kegiatan
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                    </Link>
                </div>

                <div class="grid gap-10 md:grid-cols-3">
                    <!-- Announcements & Events Mix -->
                    <div v-for="announcement in latestAnnouncements" :key="announcement.id" class="bg-white rounded-3xl p-8 border border-slate-100 shadow-xl shadow-slate-200/50 flex flex-col hover:-translate-y-2 transition-transform h-full group">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 mb-6 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-3 leading-snug">{{ announcement.title }}</h3>
                        <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-4">{{ formatDate(announcement.created_at) }}</p>
                        <div class="text-slate-600 line-clamp-4 mb-8 text-sm leading-relaxed overflow-hidden" v-html="announcement.content"></div>
                        <div class="mt-auto pt-6 border-t border-slate-50">
                            <Link :href="route('announcements.show', announcement.id)" class="text-indigo-600 font-black text-xs uppercase tracking-[0.2em] hover:text-indigo-700 flex items-center gap-2">
                                Baca Detail
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </Link>
                        </div>
                    </div>
                </div>
                
                <div v-if="latestAnnouncements.length === 0" class="text-center py-20 bg-white rounded-3xl border-2 border-dashed border-slate-200">
                    <p class="text-slate-400 italic">Belum ada pengumuman terbaru.</p>
                </div>
            </div>
        </div>

        <!-- Featured Albums Slider Section -->
        <div v-if="$page.props.appSettings.features.gallery" class="bg-white py-24 overflow-hidden">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <h2 class="text-2xl font-black text-slate-900 uppercase tracking-widest mb-12 text-center">Momen Berharga Kami</h2>
                
                <div class="flex gap-8 overflow-x-auto pb-8 snap-x no-scrollbar">
                    <div v-for="album in featuredAlbums" :key="album.id" class="snap-center shrink-0 w-[400px] h-[500px] group relative rounded-[2.5rem] overflow-hidden shadow-2xl">
                        <img 
                            :src="album.cover_image_url || 'https://images.unsplash.com/photo-1542038784456-1ea8e935640e?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80'" 
                            class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                        >
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent"></div>
                        <div class="absolute bottom-0 left-0 right-0 p-10">
                            <span class="inline-block px-3 py-1 bg-white/10 backdrop-blur-md text-white text-[10px] font-black uppercase tracking-widest mb-4 rounded-lg">
                                {{ album.category }} &bull; {{ album.photos_count }} Foto
                            </span>
                            <h3 class="text-3xl font-black text-white mb-6 uppercase tracking-tight">{{ album.name }}</h3>
                            <Link :href="route('public.gallery.show', album.slug)" class="w-14 h-14 rounded-full bg-white flex items-center justify-center text-indigo-600 shadow-xl hover:scale-110 transition-transform active:scale-95">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" /></svg>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Support Section -->
        <div class="bg-indigo-900 py-32 relative overflow-hidden">
            <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
            <div class="max-w-4xl mx-auto text-center px-6 relative z-10">
                <h2 class="text-4xl lg:text-6xl font-black text-white uppercase tracking-tighter mb-8 italic">Bangun Bersama Kami.</h2>
                <p class="text-xl text-indigo-100 mb-12 leading-relaxed">Dukungan Anda dalam bentuk iuran dan donasi adalah energi utama pergerakan organisasi kami.</p>
                <div class="flex flex-wrap justify-center gap-6">
                    <Link 
                        v-if="$page.props.appSettings.features.donations"
                        :href="route('public.donations.index')" 
                        class="px-10 py-5 bg-white text-indigo-900 rounded-3xl font-black uppercase tracking-[0.2em] shadow-2xl hover:bg-indigo-50 transition-all active:scale-95"
                    >
                        Donasi Program
                    </Link>
                    <Link :href="route('login')" class="px-10 py-5 bg-indigo-700/50 backdrop-blur-md border border-white/20 text-white rounded-3xl font-black uppercase tracking-[0.2em] hover:bg-indigo-700 transition-all active:scale-95">
                        Bayar Iuran
                    </Link>
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
