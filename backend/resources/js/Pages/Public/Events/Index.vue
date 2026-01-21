<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

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
        <div class="bg-white">
            <div class="mx-auto max-w-7xl px-6 py-16 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">Kegiatan Organisasi</h2>
                    <p class="mt-4 text-lg text-gray-500 max-w-2xl mx-auto">
                        Ikuti berbagai agenda kegiatan menarik yang kami selenggarakan untuk pemberdayaan dan pengembangan bersama.
                    </p>
                </div>

                <div v-if="events.length > 0" class="grid gap-10 md:grid-cols-2 lg:grid-cols-3">
                    <div v-for="event in events" :key="event.id" class="flex flex-col rounded-3xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-2xl transition-all duration-500 group bg-white">
                        <div class="shrink-0 relative aspect-[16/10]">
                            <img class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" :src="event.banner_url || 'https://images.unsplash.com/photo-1523580494863-6f3031224c94?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80'" :alt="event.name">
                            <div class="absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-black/60 to-transparent"></div>
                            <div class="absolute bottom-4 left-6">
                                <span class="px-3 py-1 bg-white/20 backdrop-blur-md text-white border border-white/30 rounded-lg text-[10px] font-black uppercase tracking-widest shadow-sm">
                                    {{ event.category || 'Kegiatan' }}
                                </span>
                            </div>
                        </div>
                        <div class="flex-1 p-8 flex flex-col">
                            <div class="flex-1">
                                <Link :href="route('public.events.show', event.slug)" class="block group/link">
                                    <h3 class="text-2xl font-black text-slate-900 line-clamp-2 group-hover/link:text-indigo-600 transition-colors mb-3 uppercase tracking-tight">
                                        {{ event.name }}
                                    </h3>
                                    <p class="text-slate-500 text-sm line-clamp-3 mb-6 leading-relaxed">
                                        {{ event.description }}
                                    </p>
                                </Link>
                            </div>
                            <div class="pt-6 border-t border-slate-50 flex items-center justify-between text-[11px] font-bold text-slate-400 uppercase tracking-widest">
                                <div class="flex items-center">
                                    <svg class="h-4 w-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 00-2 2z" />
                                    </svg>
                                    {{ formatDate(event.start_date) }}
                                </div>
                                <div class="flex items-center">
                                    <svg class="h-4 w-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ event.location || 'Online' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-20 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 00-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada kegiatan</h3>
                    <p class="mt-1 text-sm text-gray-500">Silakan kembali lagi nanti untuk melihat update kegiatan terbaru.</p>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
