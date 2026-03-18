<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useScrollReveal } from '@/composables/useScrollReveal';
import { ZoomIn, X, ChevronLeft, ChevronRight, ArrowLeft } from 'lucide-vue-next';

import { ref, onMounted, onUnmounted } from 'vue';

useScrollReveal();

const props = defineProps({
    album: Object,
});

const isLightboxOpen = ref(false);
const currentIndex = ref(0);

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};

const openLightbox = (index) => {
    currentIndex.value = index;
    isLightboxOpen.value = true;
    document.body.style.overflow = 'hidden';
};

const closeLightbox = () => {
    isLightboxOpen.value = false;
    document.body.style.overflow = 'auto';
};

const nextPhoto = () => {
    if (props.album.photos && props.album.photos.length > 0) {
        currentIndex.value = (currentIndex.value + 1) % props.album.photos.length;
    }
};

const prevPhoto = () => {
    if (props.album.photos && props.album.photos.length > 0) {
        currentIndex.value = (currentIndex.value - 1 + props.album.photos.length) % props.album.photos.length;
    }
};

const handleKeydown = (e) => {
    if (!isLightboxOpen.value) return;
    if (e.key === 'ArrowRight') nextPhoto();
    if (e.key === 'ArrowLeft') prevPhoto();
    if (e.key === 'Escape') closeLightbox();
};

onMounted(() => window.addEventListener('keydown', handleKeydown));
onUnmounted(() => window.removeEventListener('keydown', handleKeydown));

</script>

<template>
    <Head :title="album.name" />

    <PublicLayout>
        <div class="bg-muted min-h-screen pb-20">
            <!-- Header -->
            <div class="bg-gradient-to-br from-primary/5 via-card to-card border-b border">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-32 pb-12 sm:pt-36 lg:pt-40 lg:pb-16">
                    <nav data-reveal="fade" class="flex mb-8" aria-label="Breadcrumb">
                        <ol class="flex items-center space-x-2 text-sm text-muted-foreground font-bold uppercase tracking-widest">
                            <li>
                                <Link :href="route('public.gallery.index')" class="hover:text-primary transition-colors duration-300">Galeri</Link>
                            </li>
                            <li>
                                <ChevronRight class="h-4 w-4" />
                            </li>
                            <li class="text-foreground">{{ album.name }}</li>
                        </ol>
                    </nav>

                    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                        <div class="max-w-3xl" data-reveal>
                            <span class="inline-block px-3 py-1 bg-primary/10 text-primary rounded-full text-xs font-black uppercase tracking-[0.2em] mb-4">
                                {{ album.category }}
                            </span>
                            <h1 class="text-3xl lg:text-5xl font-black text-foreground mb-4 leading-tight">
                                {{ album.name }}
                            </h1>
                            <p class="text-muted-foreground text-lg leading-relaxed">
                                {{ album.description }}
                            </p>
                        </div>
                        <div data-reveal="right" class="shrink-0 flex items-center gap-4 text-sm text-muted-foreground border-l border pl-6">
                            <div class="text-right">
                                <p class="font-bold text-foreground">{{ formatDate(album.created_at) }}</p>
                                <p class="uppercase tracking-tighter text-xs">Tgl Diunggah</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Photo Grid -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 py-12 lg:py-20">
                <div v-if="album.photos && album.photos.length > 0" class="columns-1 sm:columns-2 lg:columns-3 gap-6 space-y-6">
                    <div
                        v-for="(photo, index) in album.photos"
                        :key="photo.id"
                        @click="openLightbox(index)"
                        data-reveal
                        :data-reveal-delay="(index % 9) * 80"
                        class="break-inside-avoid rounded-3xl overflow-hidden shadow-sm hover:shadow-2xl hover:-translate-y-1 transition-all duration-500 group cursor-zoom-in relative bg-muted min-h-[200px]"
                    >
                        <img
                            :src="photo.url"
                            :alt="album.name"
                            class="w-full h-auto object-cover group-hover:scale-105 transition-transform duration-700"
                            loading="lazy"
                            decoding="async"
                        >
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-colors duration-500 flex items-center justify-center opacity-0 group-hover:opacity-100">
                            <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center text-white scale-75 group-hover:scale-100 transition-transform duration-500">
                                <ZoomIn class="w-6 h-6" />
                            </div>
                        </div>
                        <div v-if="photo.description" class="p-4 bg-card border-t border">
                            <p class="text-sm text-muted-foreground italic text-center">{{ photo.description }}</p>
                        </div>
                    </div>
                </div>

                <div v-else data-reveal="scale" class="text-center py-20 bg-card rounded-3xl border border shadow-sm">
                    <p class="text-muted-foreground italic">Tidak ada foto dalam album ini.</p>
                </div>

                <div data-reveal="fade" class="mt-20 text-center">
                    <Link :href="route('public.gallery.index')" class="inline-flex items-center gap-2 px-8 py-4 bg-foreground text-background font-bold rounded-full hover:bg-foreground/90 transition-all duration-300 shadow-xl hover:shadow-2xl active:scale-95 uppercase tracking-widest text-xs">
                        <ArrowLeft class="w-4 h-4" />
                        Kembali ke Galeri
                    </Link>
                </div>
            </div>
        </div>

        <!-- Lightbox Slider -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="isLightboxOpen" class="fixed inset-0 z-[100] flex flex-col bg-black/95 backdrop-blur-sm">
                <!-- Toolbar -->
                <div class="flex items-center justify-between p-6 text-white relative z-10">
                    <div class="text-sm font-bold uppercase tracking-widest bg-white/10 px-4 py-2 rounded-full backdrop-blur-md">
                        {{ currentIndex + 1 }} / {{ album.photos.length }}
                    </div>
                    <button @click="closeLightbox" class="w-12 h-12 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 transition-all duration-300 backdrop-blur-md group">
                        <X class="w-5 h-5 group-hover:rotate-90 transition-transform duration-300" />
                    </button>
                </div>

                <!-- Slider Area -->
                <div class="flex-1 relative flex items-center justify-center p-4 sm:p-12 overflow-hidden">
                    <!-- Navigation -->
                    <button @click="prevPhoto" class="absolute left-4 sm:left-8 z-20 w-14 h-14 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white transition-all duration-300 active:scale-90 backdrop-blur-md">
                        <ChevronLeft class="w-7 h-7" />
                    </button>

                    <button @click="nextPhoto" class="absolute right-4 sm:right-8 z-20 w-14 h-14 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white transition-all duration-300 active:scale-90 backdrop-blur-md">
                        <ChevronRight class="w-7 h-7" />
                    </button>

                    <!-- Large Image -->
                    <Transition
                        mode="out-in"
                        enter-active-class="transition duration-500 ease-out"
                        enter-from-class="opacity-0 scale-95 translate-x-12"
                        enter-to-class="opacity-100 scale-100 translate-x-0"
                        leave-active-class="transition duration-300 ease-in"
                        leave-from-class="opacity-100 scale-100 translate-x-0"
                        leave-to-class="opacity-0 scale-95 -translate-x-12"
                    >
                        <div :key="currentIndex" class="relative max-w-full max-h-full flex flex-col items-center">
                            <img
                                :src="album.photos[currentIndex].url"
                                class="max-w-full max-h-[80vh] object-contain shadow-2xl rounded-lg"
                                @click.stop
                            >
                            <div v-if="album.photos[currentIndex].description" class="mt-8 text-white text-center max-w-2xl px-6">
                                <p class="text-lg italic font-medium drop-shadow-lg">{{ album.photos[currentIndex].description }}</p>
                            </div>
                        </div>
                    </Transition>
                </div>

                <!-- Thumbnails Bar -->
                <div class="h-16 sm:h-24 bg-black/40 backdrop-blur-md border-t border-white/5 flex items-center justify-center px-4 overflow-x-auto gap-2 sm:gap-3">
                    <button
                        v-for="(photo, index) in album.photos"
                        :key="photo.id"
                        @click="currentIndex = index"
                        class="shrink-0 w-11 h-11 sm:w-16 sm:h-16 rounded-lg overflow-hidden border-2 transition-all duration-300"
                        :class="currentIndex === index ? 'border-primary-500 scale-110 shadow-lg shadow-primary-500/50' : 'border-transparent opacity-40 hover:opacity-100'"
                    >
                        <img :src="photo.url" class="w-full h-full object-cover">
                    </button>
                </div>
            </div>
        </Transition>
    </PublicLayout>
</template>

<style scoped>
/* Glass effect for active scale */
.cursor-zoom-in {
    cursor: zoom-in;
}
</style>
