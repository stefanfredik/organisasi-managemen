<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';

const props = defineProps({
    notifications: {
        type: Array,
        default: () => []
    }
});

const unreadCount = computed(() => {
    // For now, let's just use the count of notifications passed
    return props.notifications.length;
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffInHours = Math.abs(now - date) / 36e5;
    
    if (diffInHours < 24) {
        if (diffInHours < 1) return 'Baru saja';
        return `${Math.floor(diffInHours)} jam yang lalu`;
    }
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
    });
};
</script>

<template>
    <Dropdown align="right" width="80">
        <template #trigger>
            <button class="relative p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-full transition-all duration-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span v-if="unreadCount > 0" class="absolute top-1 right-1 flex h-4 w-4">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-4 w-4 bg-red-500 text-[10px] text-white flex items-center justify-center font-bold">
                        {{ unreadCount }}
                    </span>
                </span>
            </button>
        </template>

        <template #content>
            <div class="py-2">
                <div class="px-4 py-2 border-b border-gray-100 flex justify-between items-center">
                    <span class="text-sm font-bold text-gray-900">Notifikasi</span>
                    <span v-if="unreadCount > 0" class="text-[10px] bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full font-bold">
                        {{ unreadCount }} Baru
                    </span>
                </div>
                
                <div class="max-h-96 overflow-y-auto">
                    <div v-if="notifications.length > 0" class="divide-y divide-gray-50">
                        <Link
                            v-for="notification in notifications"
                            :key="notification.id"
                            :href="notification.link || '#'"
                            class="block px-4 py-3 hover:bg-gray-50 transition-colors"
                        >
                            <div class="flex gap-3">
                                <div :class="[
                                    'shrink-0 w-10 h-10 rounded-full flex items-center justify-center',
                                    notification.type === 'announcement' ? 'bg-blue-100 text-blue-600' : 'bg-indigo-100 text-indigo-600'
                                ]">
                                    <svg v-if="notification.type === 'announcement'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                                    </svg>
                                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-900 font-medium line-clamp-1">{{ notification.title }}</p>
                                    <p class="text-xs text-gray-500 line-clamp-2 mt-0.5">{{ notification.message }}</p>
                                    <p class="text-[10px] text-gray-400 mt-1 font-medium">{{ formatDate(notification.created_at) }}</p>
                                </div>
                            </div>
                        </Link>
                    </div>
                    <div v-else class="py-12 flex flex-col items-center justify-center text-center px-6">
                        <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                            <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </div>
                        <p class="text-sm text-gray-400">Tidak ada notifikasi baru</p>
                    </div>
                </div>
                
                <div class="px-4 py-2 border-t border-gray-100 text-center">
                    <Link :href="route('announcements.index')" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                        Lihat Semua Pengumuman
                    </Link>
                </div>
            </div>
        </template>
    </Dropdown>
</template>
