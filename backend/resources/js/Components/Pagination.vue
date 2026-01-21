<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    links: {
        type: Array,
        required: true,
    },
});

const filteredLinks = computed(() => {
    return props.links.map((link) => {
        // Clean up label if it contains entities from Laravel
        let label = link.label;
        if (label.includes('Previous')) label = '←';
        if (label.includes('Next')) label = '→';
        
        return {
            ...link,
            label,
        };
    });
});
</script>

<template>
    <div v-if="links.length > 3" class="flex flex-wrap justify-center gap-2">
        <template v-for="(link, key) in filteredLinks" :key="key">
            <div
                v-if="link.url === null"
                class="px-4 py-2 text-sm leading-4 text-gray-400 border border-gray-100 rounded-xl bg-gray-50/50 cursor-not-allowed font-medium"
                v-html="link.label"
            />
            <Link
                v-else
                class="px-4 py-2 text-sm leading-4 border rounded-xl transition-all duration-300 font-bold"
                :class="{
                    'bg-indigo-600 border-indigo-600 text-white shadow-lg shadow-indigo-100 scale-105 z-10': link.active,
                    'bg-white border-gray-100 text-gray-600 hover:border-indigo-400 hover:text-indigo-600 hover:shadow-md': !link.active
                }"
                :href="link.url"
                v-html="link.label"
            />
        </template>
    </div>
</template>
