<script setup>
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

defineProps({
    visionMission: Object,
    history: Array,
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
    <Head title="Visi & Misi" />

    <PublicLayout>
        <div class="bg-white">
            <!-- Header -->
            <div class="bg-indigo-700 py-16 px-6 sm:py-24 lg:px-8 text-center">
                <h2 class="text-3xl font-bold tracking-tight text-white sm:text-5xl uppercase">Visi & Misi</h2>
                <p class="mt-4 text-lg text-indigo-100 max-w-2xl mx-auto">
                    Arah dan landasan perjuangan kami dalam mewujudkan cita-cita organisasi.
                </p>
            </div>

            <div class="mx-auto max-w-7xl px-6 py-16 lg:px-8">
                <div v-if="visionMission" class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                    <!-- Vision Card -->
                    <div class="bg-white border-2 border-indigo-50 rounded-2xl p-8 shadow-sm">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-3 bg-indigo-100 rounded-lg">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900">Visi Kami</h3>
                        </div>
                        <p class="text-xl italic text-gray-700 leading-relaxed">
                            "{{ visionMission.vision }}"
                        </p>
                    </div>

                    <!-- Mission Card -->
                    <div class="bg-white border-2 border-green-50 rounded-2xl p-8 shadow-sm">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-3 bg-green-100 rounded-lg">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900">Misi Kami</h3>
                        </div>
                        <ul class="space-y-4 text-gray-700">
                            <li v-for="(mission, index) in visionMission.missions" :key="index" class="flex gap-4">
                                <span class="flex-shrink-0 w-8 h-8 rounded-full bg-green-50 text-green-600 flex items-center justify-center font-bold text-sm">
                                    {{ index + 1 }}
                                </span>
                                <p class="text-lg">{{ mission }}</p>
                            </li>
                        </ul>
                    </div>
                </div>

                <div v-else class="text-center py-20 text-gray-500">
                    Data visi dan misi belum tersedia.
                </div>

                <!-- History Timeline -->
                <div v-if="history && history.length > 1" class="mt-24">
                    <h3 class="text-3xl font-bold text-gray-900 text-center mb-12">Sejarah Perubahan</h3>
                    <div class="space-y-8 max-w-4xl mx-auto">
                        <div v-for="item in history" :key="item.id" class="relative pl-8 border-l-2 border-gray-200 py-4">
                            <div class="absolute -left-2 top-6 w-4 h-4 rounded-full" :class="item.status === 'active' ? 'bg-indigo-600 ring-4 ring-indigo-100' : 'bg-gray-300'"></div>
                            <div>
                                <p class="text-sm font-bold" :class="item.status === 'active' ? 'text-indigo-600' : 'text-gray-500'">
                                    {{ formatDate(item.created_at) }} {{ item.status === 'active' ? '(Aktif)' : '' }}
                                </p>
                                <h4 class="text-lg font-bold text-gray-800 mt-1 line-clamp-1">{{ item.vision }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
