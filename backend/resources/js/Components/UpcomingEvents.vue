<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    events: {
        type: Array,
        default: () => []
    }
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};
</script>

<template>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Event Mendatang</h3>
            <Link :href="route('events.index')" class="text-sm text-indigo-600 hover:text-indigo-700">
                Lihat Semua
            </Link>
        </div>
        
        <div v-if="events && events.length > 0" class="space-y-3">
            <Link
                v-for="event in events"
                :key="event.id"
                :href="route('events.show', event.id)"
                class="flex items-start p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors group"
            >
                <div class="flex-shrink-0 w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mr-3 group-hover:bg-indigo-200 transition-colors">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate group-hover:text-indigo-600 transition-colors">{{ event.name }}</p>
                    <p class="text-xs text-gray-500">{{ formatDate(event.start_date) }}</p>
                    <div v-if="event.location" class="flex items-center mt-1 text-[10px] text-gray-400">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="truncate">{{ event.location }}</span>
                    </div>
                </div>
            </Link>
        </div>
        <div v-else class="flex flex-col items-center justify-center py-8 text-center">
            <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <p class="text-sm text-gray-400">Tidak ada event mendatang</p>
        </div>
    </div>
</template>
