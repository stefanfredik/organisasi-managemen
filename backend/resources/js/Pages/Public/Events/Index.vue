<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
    events: Array,
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};
</script>

<template>
    <Head title="Kegiatan Organisasi" />

    <PublicLayout>
        <div class="py-12 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
                        Kegiatan Mendatang
                    </h2>
                    <p class="mt-3 max-w-2xl mx-auto text-xl text-gray-500 sm:mt-4">
                        Ikuti berbagai kegiatan seru dan bermanfaat bersama organisasi kami.
                    </p>
                </div>

                <div class="mt-12 grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    <div v-for="event in events" :key="event.id" class="flex flex-col rounded-lg shadow-lg overflow-hidden bg-white">
                        <div class="flex-1 p-6 flex flex-col justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-indigo-600">
                                    {{ formatDate(event.start_date) }}
                                </p>
                                <div class="block mt-2">
                                    <p class="text-xl font-semibold text-gray-900">
                                        {{ event.name }}
                                    </p>
                                    <p class="mt-3 text-base text-gray-500 line-clamp-3">
                                        {{ event.description }}
                                    </p>
                                </div>
                            </div>
                            <div class="mt-6 flex items-center">
                                <div class="flex-shrink-0">
                                    <span class="sr-only">{{ event.location }}</span>
                                    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ event.location || 'Online / Remote' }}
                                    </p>
                                </div>
                            </div>
                            <div class="mt-6">
                                <Link
                                    :href="route('public.events.show', event.slug)"
                                    class="text-base font-semibold text-indigo-600 hover:text-indigo-500"
                                >
                                    Lihat Detail &rarr;
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="events.length === 0" class="mt-12 text-center text-gray-500">
                    Belum ada kegiatan yang direncanakan.
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
