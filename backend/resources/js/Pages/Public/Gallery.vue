<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useScrollReveal } from '@/composables/useScrollReveal';
import { Camera, ImageOff, Images } from 'lucide-vue-next';

useScrollReveal();

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
        <div class="bg-muted min-h-screen">
            <!-- Hero Section -->
            <div class="relative bg-gradient-to-br from-primary/10 via-card to-card border-b border pt-32 pb-16 px-4 sm:pt-36 sm:pb-20 sm:px-6 lg:pt-40 lg:pb-24 lg:px-8 text-center overflow-hidden">
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-primary/5 via-transparent to-transparent"></div>
                <div class="relative z-10">
                    <div data-reveal="scale" class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-primary/10 text-primary mb-6">
                        <Images class="w-8 h-8" />
                    </div>
                    <h2 data-reveal class="text-3xl font-extrabold text-foreground sm:text-5xl uppercase tracking-tight">
                        Galeri Kegiatan
                    </h2>
                    <p data-reveal data-reveal-delay="100" class="mt-4 text-lg text-muted-foreground max-w-2xl mx-auto italic">
                        Kumpulan momen berharga dari berbagai kegiatan yang telah kami laksanakan.
                    </p>
                </div>
            </div>

            <div class="mx-auto max-w-7xl px-4 sm:px-6 py-16 lg:px-8">
                <div v-if="albums.data.length > 0" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="(album, index) in albums.data"
                        :key="album.id"
                        data-reveal
                        :data-reveal-delay="(index % 6) * 100"
                        class="relative group bg-card rounded-3xl overflow-hidden shadow-sm hover:shadow-2xl hover:-translate-y-1 transition-all duration-500"
                    >
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
                                <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/30 to-transparent opacity-80 group-hover:opacity-90 transition-opacity duration-500"></div>

                                <!-- Photo count badge -->
                                <div class="absolute top-4 right-4 flex items-center gap-1.5 bg-black/30 backdrop-blur-md text-white text-xs font-bold px-3 py-1.5 rounded-full">
                                    <Camera class="w-3.5 h-3.5" />
                                    {{ album.photos_count }}
                                </div>

                                <div class="absolute bottom-0 left-0 right-0 p-5 sm:p-8">
                                    <p class="text-primary/70 text-xs font-black uppercase tracking-widest mb-2">
                                        {{ album.category }}
                                    </p>
                                    <h3 class="text-2xl font-bold text-white mb-2 leading-tight group-hover:text-primary/90 transition-colors duration-300">
                                        {{ album.name }}
                                    </h3>
                                    <p class="text-white/50 text-sm italic">
                                        {{ formatDate(album.created_at) }}
                                    </p>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else data-reveal="scale" class="text-center py-20 bg-card rounded-3xl border-2 border-dashed border">
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-muted mb-4">
                        <ImageOff class="h-10 w-10 text-muted-foreground" />
                    </div>
                    <h3 class="mt-2 text-xl font-bold text-foreground">Belum Ada Foto</h3>
                    <p class="mt-2 text-muted-foreground italic">Kami akan segera mengunggah momen terbaru kegiatan kami.</p>
                </div>

                <!-- Pagination -->
                <div v-if="albums.links.length > 3" data-reveal="fade" class="mt-16 flex justify-center">
                    <div class="flex gap-2">
                        <Link
                            v-for="link in albums.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            class="min-w-[44px] min-h-[44px] px-4 py-2 rounded-full text-sm font-bold transition-all duration-300 flex items-center justify-center"
                            :class="link.active ? 'bg-primary text-primary-foreground shadow-lg shadow-primary/25' : 'bg-card text-muted-foreground hover:bg-muted hover:text-foreground'"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
