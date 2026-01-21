<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { ref, onMounted, onUnmounted } from 'vue';

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
    currentIndex.ref = index; // This is a mistake in my thought, should be currentIndex.value
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
        <div class="bg-gray-50 min-h-screen pb-20">
            <!-- Header -->
            <div class="bg-white border-b border-gray-100">
                <div class="max-w-7xl mx-auto px-6 py-12 lg:py-16">
                    <nav class="flex mb-8" aria-label="Breadcrumb">
                        <ol class="flex items-center space-x-2 text-sm text-gray-400 font-bold uppercase tracking-widest">
                            <li><Link :href="route('public.gallery.index')" class="hover:text-indigo-600 transition-colors">Galeri</Link></li>
                            <li><svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg></li>
                            <li class="text-gray-900">{{ album.name }}</li>
                        </ol>
                    </nav>

                    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                        <div class="max-w-3xl">
                            <span class="inline-block px-3 py-1 bg-indigo-50 text-indigo-600 rounded-full text-[10px] font-black uppercase tracking-[0.2em] mb-4">
                                {{ album.category }}
                            </span>
                            <h1 class="text-3xl lg:text-5xl font-black text-gray-900 mb-4 leading-tight">
                                {{ album.name }}
                            </h1>
                            <p class="text-gray-500 text-lg leading-relaxed">
                                {{ album.description }}
                            </p>
                        </div>
                        <div class="shrink-0 flex items-center gap-4 text-sm text-gray-400 border-l border-gray-100 pl-6">
                            <div class="text-right">
                                <p class="font-bold text-gray-900">{{ formatDate(album.created_at) }}</p>
                                <p class="uppercase tracking-tighter text-[10px]">Tgl Diunggah</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Photo Grid -->
            <div class="max-w-7xl mx-auto px-6 py-12 lg:py-20">
                <div v-if="album.photos && album.photos.length > 0" class="columns-1 sm:columns-2 lg:columns-3 gap-6 space-y-6">
                    <div 
                        v-for="(photo, index) in album.photos" 
                        :key="photo.id" 
                        @click="openLightbox(index)"
                        class="break-inside-avoid rounded-3xl overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500 group cursor-zoom-in relative bg-gray-100 min-h-[200px]"
                    >
                        <img 
                            :src="photo.url" 
                            :alt="album.name"
                            class="w-full h-auto object-cover group-hover:scale-105 transition-transform duration-700"
                            loading="lazy"
                            decoding="async"
                        >
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors duration-500 flex items-center justify-center opacity-0 group-hover:opacity-100">
                            <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center text-white">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                </svg>
                            </div>
                        </div>
                        <div v-if="photo.description" class="p-4 bg-white border-t border-gray-50">
                            <p class="text-sm text-gray-600 italic text-center">{{ photo.description }}</p>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-20 bg-white rounded-3xl border border-gray-100 shadow-sm">
                    <p class="text-gray-400 italic">Tidak ada foto dalam album ini.</p>
                </div>

                <div class="mt-20 text-center">
                    <Link :href="route('public.gallery.index')" class="inline-flex items-center gap-2 px-8 py-4 bg-gray-900 text-white font-bold rounded-2xl hover:bg-gray-800 transition-all shadow-xl active:scale-95 uppercase tracking-widest text-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
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
                    <button @click="closeLightbox" class="w-12 h-12 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 transition-colors backdrop-blur-md group">
                        <svg class="w-6 h-6 group-hover:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Slider Area -->
                <div class="flex-1 relative flex items-center justify-center p-4 sm:p-12 overflow-hidden">
                    <!-- Navigation -->
                    <button @click="prevPhoto" class="absolute left-4 sm:left-8 z-20 w-14 h-14 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white transition-all active:scale-90 backdrop-blur-md">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    
                    <button @click="nextPhoto" class="absolute right-4 sm:right-8 z-20 w-14 h-14 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white transition-all active:scale-90 backdrop-blur-md">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
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
                <div class="h-24 bg-black/40 backdrop-blur-md border-t border-white/5 flex items-center justify-center px-4 overflow-x-auto gap-3">
                    <button 
                        v-for="(photo, index) in album.photos" 
                        :key="photo.id"
                        @click="currentIndex = index"
                        class="shrink-0 w-16 h-16 rounded-lg overflow-hidden border-2 transition-all duration-300"
                        :class="currentIndex === index ? 'border-indigo-500 scale-110 shadow-lg shadow-indigo-500/50' : 'border-transparent opacity-40 hover:opacity-100'"
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
