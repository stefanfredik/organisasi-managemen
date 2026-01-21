<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineProps({
    albums: Object,
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        month: 'long',
        year: 'numeric',
    });
};
</script>

<template>
    <Head title="Galeri Foto" />

    <PublicLayout>
        <div class="bg-gray-50 min-h-screen">
            <div class="bg-white border-b border-gray-100 py-16 px-6 sm:py-24 lg:px-8 text-center">
                <h2 class="text-3xl font-extrabold text-gray-900 sm:text-5xl uppercase tracking-tight">Galeri Kegiatan</h2>
                <p class="mt-4 text-lg text-gray-500 max-w-2xl mx-auto italic">
                    Kumpulan momen berharga dari berbagai kegiatan yang telah kami laksanakan.
                </p>
            </div>

            <div class="mx-auto max-w-7xl px-6 py-16 lg:px-8">
                <div v-if="albums.data.length > 0" class="grid gap-10 md:grid-cols-2 lg:grid-cols-3">
                    <div v-for="album in albums.data" :key="album.id" class="relative group bg-white rounded-3xl overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500">
                        <Link :href="route('public.gallery.show', album.slug)" class="block">
                            <!-- Image Container -->
                            <div class="aspect-[4/5] relative overflow-hidden">
                                <img 
                                    :src="album.cover_image_url || 'https://images.unsplash.com/photo-1542038784456-1ea8e935640e?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80'" 
                                    :alt="album.name"
                                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                                    loading="lazy"
                                    decoding="async"
                                >
                                <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent"></div>
                                
                                <div class="absolute bottom-0 left-0 right-0 p-8">
                                    <p class="text-indigo-400 text-xs font-black uppercase tracking-widest mb-2">
                                        {{ album.category }} &bull; {{ album.photos_count }} Foto
                                    </p>
                                    <h3 class="text-2xl font-bold text-white mb-2 leading-tight">
                                        {{ album.name }}
                                    </h3>
                                    <p class="text-gray-300 text-sm italic">
                                        {{ formatDate(album.created_at) }}
                                    </p>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>

                <div v-else class="text-center py-20 bg-white rounded-3xl border-2 border-dashed border-gray-100">
                    <svg class="mx-auto h-20 w-20 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <h3 class="mt-4 text-xl font-bold text-gray-900">Belum Ada Foto</h3>
                    <p class="mt-2 text-gray-500 italic">Kami akan segera mengunggah momen terbaru kegiatan kami.</p>
                </div>

                <!-- Pagination Placeholder -->
                <div v-if="albums.links.length > 3" class="mt-16 flex justify-center">
                    <!-- Basic pagination style -->
                    <div class="flex gap-2">
                        <Link 
                            v-for="link in albums.links" 
                            :key="link.label"
                            :href="link.url || '#'"
                            class="px-4 py-2 rounded-xl text-sm font-bold transition-all"
                            :class="link.active ? 'bg-indigo-600 text-white shadow-lg' : 'bg-white text-gray-600 hover:bg-gray-100'"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
