<script setup>
import ActionLink from '@/Components/ActionLink.vue';
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useScrollReveal } from '@/composables/useScrollReveal';
import { Calendar, Clock, MapPin, ShieldCheck, Users, Share2 } from 'lucide-vue-next';

useScrollReveal();

defineProps({
    event: Object,
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};

const formatTime = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head :title="event.name" />

    <PublicLayout>
        <div class="bg-muted min-h-screen pb-16">
            <!-- Hero Banner -->
            <div class="relative h-[300px] sm:h-96 lg:h-[500px] overflow-hidden">
                <img
                    :src="event.banner_url || 'https://images.unsplash.com/photo-1523580494863-6f3031224c94?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80'"
                    alt=""
                    class="w-full h-full object-cover scale-105"
                >
                <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/40 to-black/10"></div>
                <div class="absolute bottom-0 left-0 right-0 p-4 sm:p-6 lg:p-12">
                    <div class="max-w-7xl mx-auto">
                        <span class="inline-block px-3 py-1 bg-primary text-white rounded-full text-xs font-black uppercase tracking-widest mb-4">
                            {{ event.category || 'Kegiatan' }}
                        </span>
                        <h1 class="text-2xl sm:text-4xl lg:text-6xl font-black text-white mb-4 sm:mb-6 uppercase tracking-tight">
                            {{ event.name }}
                        </h1>
                        <div class="flex flex-wrap gap-6 text-white text-sm sm:text-base">
                            <div class="flex items-center">
                                <Calendar class="w-5 h-5 mr-2 text-primary-foreground/60" />
                                {{ formatDate(event.start_date) }}
                            </div>
                            <div class="flex items-center">
                                <Clock class="w-5 h-5 mr-2 text-primary-foreground/60" />
                                {{ formatTime(event.start_date) }} - Selesai
                            </div>
                            <div class="flex items-center">
                                <MapPin class="w-5 h-5 mr-2 text-primary-foreground/60" />
                                {{ event.location || 'Online' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 -mt-10 relative z-10 transition-all">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-8">
                        <div data-reveal class="bg-card rounded-2xl shadow-sm border p-8 lg:p-12">
                            <h2 class="text-2xl font-bold text-foreground mb-6 border-b border pb-4">Detail Kegiatan</h2>
                            <div class="prose prose-primary max-w-none text-foreground leading-relaxed" v-html="event.content"></div>
                        </div>

                        <!-- Documentation Gallery -->
                        <div v-if="event.documentations && event.documentations.length > 0" data-reveal data-reveal-delay="100" class="bg-card rounded-2xl shadow-sm border p-8">
                            <h2 class="text-2xl font-bold text-foreground mb-6">Dokumentasi</h2>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                                <div
                                    v-for="(doc, index) in event.documentations"
                                    :key="doc.id"
                                    data-reveal="scale"
                                    :data-reveal-delay="index * 80"
                                    class="aspect-square rounded-xl overflow-hidden group cursor-pointer"
                                >
                                    <img :src="doc.url" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" alt="">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-8">
                        <div data-reveal="right" class="bg-card rounded-2xl shadow-sm border p-6 sm:p-8 sticky top-20">
                            <h3 class="text-xl font-bold text-foreground mb-6">Informasi Tambahan</h3>
                            <div class="space-y-4 mb-8">
                                <div v-if="event.is_public" class="flex items-center py-3 border-b border">
                                    <span class="p-2 bg-success/10 rounded-lg mr-3">
                                        <ShieldCheck class="w-5 h-5 text-success-600" />
                                    </span>
                                    <span class="text-sm font-medium text-foreground">Terbuka untuk Umum</span>
                                </div>
                                <div class="flex items-center py-3 border-b border">
                                    <span class="p-2 bg-primary/10 rounded-lg mr-3">
                                        <Users class="w-5 h-5 text-primary" />
                                    </span>
                                    <span class="text-sm font-medium text-foreground">{{ event.category }}</span>
                                </div>
                            </div>

                            <ActionLink href="/contact" variant="secondary">
                                Tanya Panitia
                            </ActionLink>

                            <div class="mt-8 pt-8 border-t border">
                                <p class="text-xs text-muted-foreground uppercase font-black tracking-widest mb-4 text-center flex items-center justify-center gap-2">
                                    <Share2 class="w-3.5 h-3.5" />
                                    Bagikan
                                </p>
                                <div class="flex justify-center gap-4">
                                    <!-- Social Share Placeholder -->
                                    <button class="w-11 h-11 bg-muted rounded-full flex items-center justify-center text-muted-foreground hover:text-primary hover:bg-primary/10 transition-colors">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                    </button>
                                    <button class="w-11 h-11 bg-muted rounded-full flex items-center justify-center text-muted-foreground hover:text-primary hover:bg-primary/10 transition-colors">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm4.441 16.892c-2.102 0-3.809-1.707-3.809-3.809 0-2.102 1.707-3.809 3.809-3.809 2.102 0 3.809 1.707 3.809 3.809 0 2.102-1.707 3.809-3.809 3.809zm-4.441-7.142c-2.102 0-3.809-1.707-3.809-3.809 0-2.102 1.707-3.809 3.809-3.809 2.102 0 3.809 1.707 3.809 3.809 0 2.102-1.707 3.809-3.809 3.809z"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
