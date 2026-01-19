<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
    event: Object,
});

const formatDate = (date) => {
    return new Date(date).toLocaleString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head :title="event.name" />

    <PublicLayout>
        <div class="py-12 bg-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <nav class="flex mb-8" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-2">
                        <li>
                            <Link href="/events/public" class="text-sm font-medium text-gray-500 hover:text-gray-700">Kegiatan</Link>
                        </li>
                        <li class="flex items-center space-x-2">
                            <svg class="h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 11 7.293 7.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-sm font-medium text-gray-900">{{ event.name }}</span>
                        </li>
                    </ol>
                </nav>

                <div class="space-y-8">
                    <div>
                        <h1 class="text-4xl font-extrabold text-gray-900">{{ event.name }}</h1>
                        <div class="mt-4 flex flex-wrap gap-4 text-sm text-gray-500">
                            <div class="flex items-center">
                                <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                {{ formatDate(event.start_date) }}
                            </div>
                            <div class="flex items-center">
                                <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {{ event.location || 'Lokasi belum ditentukan' }}
                            </div>
                        </div>
                    </div>

                    <div class="prose max-w-none text-gray-700 whitespace-pre-wrap">
                        {{ event.description }}
                    </div>

                    <div v-if="event.documentations.length > 0">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Dokumentasi</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div v-for="doc in event.documentations" :key="doc.id" class="rounded-lg overflow-hidden shadow">
                                <img :src="doc.url" :alt="doc.caption" class="w-full h-64 object-cover" />
                                <div v-if="doc.caption" class="p-2 bg-gray-50 text-sm text-gray-600">
                                    {{ doc.caption }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-indigo-50 rounded-xl p-8 border border-indigo-100">
                        <h2 class="text-xl font-bold text-indigo-900 mb-2">Ingin Bergabung?</h2>
                        <p class="text-indigo-700 mb-6">
                            Kegiatan ini terbuka untuk anggota organisasi. Silakan masuk ke akun Anda atau daftar sebagai anggota untuk berpartisipasi.
                        </p>
                        <div class="flex gap-4">
                            <Link
                                :href="route('login')"
                                class="px-6 py-2 bg-indigo-600 text-white rounded-md font-semibold hover:bg-indigo-700"
                            >
                                Masuk Akun
                            </Link>
                            <Link
                                href="/register"
                                class="px-6 py-2 bg-white text-indigo-600 border border-indigo-200 rounded-md font-semibold hover:bg-gray-50"
                            >
                                Daftar Anggota
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
