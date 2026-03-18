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
            <div class="relative overflow-hidden bg-gradient-to-br from-primary/90 via-primary to-primary/80 pt-24 sm:pt-32">
                <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PGNpcmNsZSBjeD0iMzAiIGN5PSIzMCIgcj0iMiIvPjwvZz48L2c+PC9zdmc+')] opacity-60"></div>
                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24 text-center">
                    <div data-reveal="scale" class="inline-flex items-center justify-center w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white/15 backdrop-blur-sm mb-6 sm:mb-8">
                        <Calendar class="w-8 h-8 sm:w-10 sm:h-10 text-white" />
                    </div>
                    <h1 data-reveal class="text-3xl sm:text-4xl lg:text-6xl font-black text-white uppercase tracking-tight mb-4">
                        Kegiatan Organisasi
                    </h1>
                    <p data-reveal data-reveal-delay="100" class="text-lg text-white/80 max-w-2xl mx-auto font-medium leading-relaxed">
                        Ikuti berbagai agenda kegiatan menarik yang kami selenggarakan untuk pemberdayaan dan pengembangan bersama.
                    </p>
                </div>
                <div class="absolute bottom-0 left-0 right-0 h-16 bg-gradient-to-t from-muted to-transparent"></div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
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
                            <div class="absolute bottom-4 left-6">
                                <span class="px-3 py-1 bg-card/20 backdrop-blur-md text-white border border-white/30 rounded-lg text-xs font-black uppercase tracking-widest shadow-sm">
                                    {{ event.category || 'Kegiatan' }}
                                </span>
                            </div>
                        </div>
                        <div class="flex-1 p-8 flex flex-col">
                            <div class="flex-1">
                                <Link :href="route('public.events.show', event.slug)" class="block group/link">
                                    <h3 class="text-lg sm:text-2xl font-black text-foreground line-clamp-2 group-hover/link:text-primary transition-colors mb-3 uppercase tracking-tight">
                                        {{ event.name }}
                                    </h3>
                                    <p class="text-muted-foreground text-sm line-clamp-3 mb-6 leading-relaxed">
                                        {{ event.description }}
                                    </p>
                                </Link>
                            </div>
                            <div class="pt-6 border-t border flex items-center justify-between text-xs font-bold text-muted-foreground uppercase tracking-widest">
                                <div class="flex items-center">
                                    <Calendar class="h-4 w-4 mr-2 text-primary" />
                                    {{ formatDate(event.start_date) }}
                                </div>
                                <div class="flex items-center">
                                    <MapPin class="h-4 w-4 mr-2 text-primary" />
                                    {{ event.location || 'Online' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else data-reveal="scale" class="text-center py-20 bg-card rounded-2xl border-2 border-dashed border shadow-sm">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-muted mb-4">
                        <CalendarOff class="h-8 w-8 text-muted-foreground" />
                    </div>
                    <h3 class="text-lg font-bold text-foreground">Belum ada kegiatan</h3>
                    <p class="mt-1 text-sm text-muted-foreground max-w-sm mx-auto">Silakan kembali lagi nanti untuk melihat update kegiatan terbaru.</p>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
