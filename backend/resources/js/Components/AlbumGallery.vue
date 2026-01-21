<script setup>
import { ref } from 'vue';

const props = defineProps({
    photos: {
        type: Array,
        default: () => [],
    },
});

const selectedPhoto = ref(null);

const openPhoto = (photo) => {
    selectedPhoto.value = photo;
};

const closePhoto = () => {
    selectedPhoto.value = null;
};
</script>

<template>
    <div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <div 
                v-for="photo in photos" 
                :key="photo.id" 
                class="group relative aspect-square bg-slate-100 rounded-2xl overflow-hidden cursor-pointer shadow-sm hover:shadow-xl transition-all duration-300"
                @click="openPhoto(photo)"
            >
                <img 
                    :src="photo.url" 
                    :alt="photo.caption || 'Gallery Photo'"
                    loading="lazy" 
                    class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                >
                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors duration-300 flex items-center justify-center opacity-0 group-hover:opacity-100">
                    <svg class="w-8 h-8 text-white drop-shadow-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
             <div v-if="photos.length === 0" class="col-span-full py-12 text-center bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200">
                <p class="text-slate-400 font-medium">Belum ada foto dalam galeri.</p>
            </div>
        </div>

        <!-- Lightbox -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="selectedPhoto" class="fixed inset-0 z-50 flex items-center justify-center bg-black/95 backdrop-blur-lg p-4" @click="closePhoto">
                <div class="relative max-w-5xl w-full max-h-screen flex flex-col items-center">
                    <img 
                        :src="selectedPhoto.url" 
                        class="max-w-full max-h-[85vh] rounded-lg shadow-2xl object-contain" 
                        @click.stop
                    >
                    <p v-if="selectedPhoto.caption" class="mt-4 text-white font-medium text-lg text-center max-w-2xl mx-auto">
                        {{ selectedPhoto.caption }}
                    </p>
                    <button class="absolute -top-12 right-0 md:top-0 md:-right-12 text-white/50 hover:text-white transition-colors" @click="closePhoto">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>
