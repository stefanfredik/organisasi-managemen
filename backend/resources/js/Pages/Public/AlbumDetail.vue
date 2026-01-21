<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineProps({
    album: Object,
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};
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
                    <div v-for="photo in album.photos" :key="photo.id" class="break-inside-avoid rounded-3xl overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500 group cursor-zoom-in">
                        <img 
                            :src="photo.file_path" 
                            :alt="album.name"
                            class="w-full h-auto object-cover group-hover:scale-105 transition-transform duration-700"
                        >
                        <div v-if="photo.caption" class="p-4 bg-white border-t border-gray-50">
                            <p class="text-sm text-gray-600 italic text-center">{{ photo.caption }}</p>
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
    </PublicLayout>
</template>
