<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
    upcomingEvents: Array,
    latestAnnouncements: Array,
    activeDonations: Array,
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};
</script>

<template>
    <Head title="Beranda" />

    <PublicLayout>
        <!-- Hero Section -->
        <div class="relative bg-white overflow-hidden">
            <div class="max-w-7xl mx-auto">
                <div class="relative z-10 pb-8 bg-white sm:pb-16 md:pb-20 lg:max-w-2xl lg:w-full lg:pb-28 xl:pb-32">
                    <svg class="hidden lg:block absolute right-0 inset-y-0 h-full w-48 text-white transform translate-x-1/2" fill="currentColor" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
                        <polygon points="50,0 100,0 50,100 0,100" />
                    </svg>

                    <main class="mt-10 mx-auto max-w-7xl px-4 sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8 xl:mt-28">
                        <div class="sm:text-center lg:text-left">
                            <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl">
                                <span class="block xl:inline">Sistem Manajemen</span>
                                {{ ' ' }}
                                <span class="block text-indigo-600 xl:inline">Organisasi</span>
                            </h1>
                            <p class="mt-3 text-base text-gray-500 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl lg:mx-0">
                                Mengelola organisasi menjadi lebih mudah, transparan, dan terintegrasi. Akses informasi kegiatan, laporan keuangan, dan iuran dalam satu genggaman.
                            </p>
                            <div class="mt-5 sm:mt-8 sm:flex sm:justify-center lg:justify-start">
                                <div class="rounded-md shadow">
                                    <Link :href="route('login')" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 md:py-4 md:text-lg md:px-10">
                                        Mulai Sekarang
                                    </Link>
                                </div>
                                <div class="mt-3 sm:mt-0 sm:ml-3">
                                    <Link :href="route('public.about')" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 md:py-4 md:text-lg md:px-10">
                                        Tentang Kami
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </main>
                </div>
            </div>
            <div class="lg:absolute lg:inset-y-0 lg:right-0 lg:w-1/2">
                <img class="h-56 w-full object-cover sm:h-72 md:h-96 lg:w-full lg:h-full" src="https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" alt="Team meeting">
            </div>
        </div>

        <!-- Latest Announcements -->
        <div class="bg-gray-50 py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-extrabold text-gray-900">Pengumuman Terbaru</h2>
                    <p class="mt-4 text-lg text-gray-500">Informasi penting terkait kegiatan organisasi.</p>
                </div>

                <div class="mt-10 grid gap-8 md:grid-cols-3">
                    <div v-for="announcement in latestAnnouncements" :key="announcement.id" class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 flex flex-col">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">{{ announcement.title }}</h3>
                        <p class="text-sm text-gray-500 mb-4">{{ formatDate(announcement.created_at) }}</p>
                        <div class="text-gray-600 line-clamp-3 mb-4" v-html="announcement.content"></div>
                        <div class="mt-auto">
                            <Link :href="route('announcements.show', announcement.id)" class="text-indigo-600 font-medium hover:text-indigo-500">
                                Selengkapnya &rarr;
                            </Link>
                        </div>
                    </div>
                </div>
                
                <div v-if="latestAnnouncements.length === 0" class="text-center py-12 text-gray-500">
                    Belum ada pengumuman saat ini.
                </div>
            </div>
        </div>

        <!-- Upcoming Events -->
        <div class="bg-white py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-3xl font-extrabold text-gray-900">Kegiatan Mendatang</h2>
                    <Link :href="route('public.events.index')" class="text-indigo-600 font-medium hover:text-indigo-500">
                        Lihat Semua &rarr;
                    </Link>
                </div>

                <div class="grid gap-8 md:grid-cols-3">
                    <div v-for="event in upcomingEvents" :key="event.id" class="flex flex-col rounded-lg shadow-lg overflow-hidden border border-gray-100">
                        <div class="flex-shrink-0">
                            <img class="h-48 w-full object-cover" :src="event.banner_url || 'https://images.unsplash.com/photo-1523580494863-6f3031224c94?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80'" alt="Event banner">
                        </div>
                        <div class="flex-1 bg-white p-6 flex flex-col justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-indigo-600">
                                    {{ event.category }}
                                </p>
                                <Link :href="route('public.events.show', event.slug)" class="block mt-2">
                                    <p class="text-xl font-semibold text-gray-900">{{ event.title }}</p>
                                    <p class="mt-3 text-base text-gray-500 line-clamp-2">{{ event.description }}</p>
                                </Link>
                            </div>
                            <div class="mt-6 flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 00-2 2z" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ formatDate(event.start_date) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="upcomingEvents.length === 0" class="text-center py-12 text-gray-500">
                    Belum ada kegiatan mendatang.
                </div>
            </div>
        </div>

        <!-- Call to Action: Donations -->
        <div class="bg-indigo-700">
            <div class="max-w-2xl mx-auto text-center py-16 px-4 sm:py-20 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-extrabold text-white sm:text-4xl">
                    <span class="block">Bersama membangun organisasi.</span>
                    <span class="block">Dukung program kami.</span>
                </h2>
                <p class="mt-4 text-lg leading-6 text-indigo-200">
                    Setiap dukungan Anda sangat berarti bagi kelancaran program dan keberlangsungan organisasi kami.
                </p>
                <Link :href="route('public.donations.index')" class="mt-8 w-full inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-indigo-700 bg-white hover:bg-indigo-50 sm:w-auto">
                    Donasi Sekarang
                </Link>
            </div>
        </div>
    </PublicLayout>
</template>
