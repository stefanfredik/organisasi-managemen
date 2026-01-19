<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const show = computed(() => !!page.props.flash.success || !!page.props.flash.error);
const type = computed(() => page.props.flash.error ? 'error' : 'success');
const message = computed(() => page.props.flash.error || page.props.flash.success);
</script>

<template>
    <div v-if="show" class="fixed top-4 right-4 z-50 max-w-sm w-full transition-all duration-300 transform translate-y-0 opacity-100">
        <div :class="[
            'rounded-xl shadow-lg border p-4 flex items-start gap-3',
            type === 'error' ? 'bg-red-50 border-red-100 text-red-800' : 'bg-green-50 border-green-100 text-green-800'
        ]">
            <div :class="type === 'error' ? 'text-red-500' : 'text-green-500'">
                <svg v-if="type === 'success'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="font-bold text-sm">{{ type === 'error' ? 'Gagal' : 'Berhasil' }}</h3>
                <p class="text-xs mt-1">{{ message }}</p>
            </div>
            <button @click="$page.props.flash.success = null; $page.props.flash.error = null" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
</template>
