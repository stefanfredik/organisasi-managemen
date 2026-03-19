<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useScrollReveal } from '@/composables/useScrollReveal';
import { Calendar, MapPin, CalendarOff } from 'lucide-vue-next';

useScrollReveal();

defineProps({
    events: Array,
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
    <Head title="Kegiatan Organisasi" />

    <PublicLayout>
        <div class="bg-muted min-h-screen">
            <!-- Hero Section -->
            <div class="relative overflow-hidden bg-gradient-to-br from-primary to-primary/80 pt-24 pb-10">
                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <div data-reveal="scale" class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-white/15 backdrop-blur-sm mb-4">
                        <Calendar class="w-7 h-7 text-white" />
                    </div>
                    <h1 data-reveal class="text-2xl sm:text-3xl lg:text-4xl font-bold text-white tracking-tight mb-2">
                        Kegiatan Organisasi
                    </h1>
                    <p data-reveal data-reveal-delay="100" class="text-base text-white/80 max-w-2xl mx-auto leading-relaxed">
                        Ikuti berbagai agenda kegiatan menarik yang kami selenggarakan untuk pemberdayaan dan pengembangan bersama.
                    </p>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
                <div v-if="events.length > 0" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="(event, index) in events"
                        :key="event.id"
                        data-reveal
                        :data-reveal-delay="index % 3 * 100"
                        class="flex flex-col rounded-2xl shadow-sm border overflow-hidden bg-card transition-all duration-500 hover:-translate-y-1 hover:shadow-xl group"
                    >
                        <div class="shrink-0 relative aspect-[16/10] overflow-hidden">
                            <img class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" :src="event.banner_url || 'https://images.unsplash.com/photo-1523580494863-6f3031224c94?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80'" :alt="event.name">
                            <div class="absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-black/60 to-transparent"></div>
                            <div class="absolute bottom-3 left-4">
                                <span class="px-2.5 py-1 bg-card/20 backdrop-blur-md text-white border border-white/30 rounded-lg text-xs font-semibold tracking-wide shadow-sm">
                                    {{ event.category || 'Kegiatan' }}
                                </span>
                            </div>
                        </div>
                        <div class="flex-1 p-5 flex flex-col">
                            <div class="flex-1">
                                <Link :href="route('public.events.show', event.slug)" class="block group/link">
                                    <h3 class="text-base sm:text-lg font-bold text-foreground line-clamp-2 group-hover/link:text-primary transition-colors mb-2 tracking-tight">
                                        {{ event.name }}
                                    </h3>
                                    <p class="text-muted-foreground text-sm line-clamp-3 mb-4 leading-relaxed">
                                        {{ event.description }}
                                    </p>
                                </Link>
                            </div>
                            <div class="pt-4 border-t border flex items-center justify-between text-xs font-medium text-muted-foreground">
                                <div class="flex items-center">
                                    <Calendar class="h-3.5 w-3.5 mr-1.5 text-primary" />
                                    {{ formatDate(event.start_date) }}
                                </div>
                                <div class="flex items-center">
                                    <MapPin class="h-3.5 w-3.5 mr-1.5 text-primary" />
                                    {{ event.location || 'Online' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else data-reveal="scale" class="text-center py-16 bg-card rounded-2xl border-2 border-dashed border shadow-sm">
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-muted mb-4">
                        <CalendarOff class="h-7 w-7 text-muted-foreground" />
                    </div>
                    <h3 class="text-lg font-bold text-foreground">Belum ada kegiatan</h3>
                    <p class="mt-1 text-sm text-muted-foreground max-w-sm mx-auto">Silakan kembali lagi nanti untuk melihat update kegiatan terbaru.</p>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
