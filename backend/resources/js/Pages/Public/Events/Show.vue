<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

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
    <Head :title="event.title" />

    <PublicLayout>
        <div class="bg-gray-50 min-h-screen pb-16">
            <!-- Hero Banner -->
            <div class="relative h-96 lg:h-[500px] overflow-hidden">
                <img 
                    :src="event.banner_url || 'https://images.unsplash.com/photo-1523580494863-6f3031224c94?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80'" 
                    alt="" 
                    class="w-full h-full object-cover"
                >
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-6 sm:p-12">
                    <div class="max-w-7xl mx-auto">
                        <span class="inline-block px-3 py-1 bg-indigo-600 text-white rounded-full text-xs font-bold uppercase mb-4">
                            {{ event.category }}
                        </span>
                        <h1 class="text-3xl sm:text-5xl font-extrabold text-white mb-6">
                            {{ event.title }}
                        </h1>
                        <div class="flex flex-wrap gap-6 text-white text-sm sm:text-base">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 00-2 2z" />
                                </svg>
                                {{ formatDate(event.start_date) }}
                            </div>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ formatTime(event.start_date) }} - Selesai
                            </div>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {{ event.location || 'Online' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-6 -mt-10 relative z-10 transition-all">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-8">
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 lg:p-12">
                            <h2 class="text-2xl font-bold text-gray-900 mb-6 border-b border-gray-100 pb-4">Detail Kegiatan</h2>
                            <div class="prose prose-indigo max-w-none text-gray-700 leading-relaxed" v-html="event.content"></div>
                        </div>

                        <!-- Documentation Gallery -->
                        <div v-if="event.documentations && event.documentations.length > 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                            <h2 class="text-2xl font-bold text-gray-900 mb-6">Dokumentasi</h2>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                                <div v-for="doc in event.documentations" :key="doc.id" class="aspect-square rounded-xl overflow-hidden group cursor-pointer">
                                    <img :src="doc.file_url" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" alt="">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-8">
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 sticky top-8">
                            <h3 class="text-xl font-bold text-gray-900 mb-6">Informasi Tambahan</h3>
                            <div class="space-y-4 mb-8">
                                <div v-if="event.is_public" class="flex items-center py-3 border-b border-gray-50">
                                    <span class="p-2 bg-green-50 rounded-lg mr-3">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                    </span>
                                    <span class="text-sm font-medium text-gray-700">Terbuka untuk Umum</span>
                                </div>
                                <div class="flex items-center py-3 border-b border-gray-50">
                                    <span class="p-2 bg-indigo-50 rounded-lg mr-3">
                                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                    </span>
                                    <span class="text-sm font-medium text-gray-700">{{ event.category }}</span>
                                </div>
                            </div>
                            
                            <Link href="/contact" class="w-full flex justify-center py-3 px-4 border border-indigo-600 text-indigo-600 font-bold rounded-xl hover:bg-indigo-50 transition-colors">
                                Tanya Panitia
                            </Link>

                            <div class="mt-8 pt-8 border-t border-gray-100">
                                <p class="text-xs text-gray-400 uppercase font-black tracking-widest mb-4 text-center">Bagikan</p>
                                <div class="flex justify-center gap-4">
                                    <!-- Social Share Placeholder -->
                                    <button class="w-10 h-10 bg-gray-50 rounded-full flex items-center justify-center text-gray-400 hover:text-indigo-600 transition-colors">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                    </button>
                                    <button class="w-10 h-10 bg-gray-50 rounded-full flex items-center justify-center text-gray-400 hover:text-indigo-600 transition-colors">
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
