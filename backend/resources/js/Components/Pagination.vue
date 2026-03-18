<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { cn } from '@/lib/utils';
import { buttonVariants } from '@/components/ui/button';

const props = defineProps({
    links: { type: Array, required: true },
});

const filteredLinks = computed(() => {
    return props.links.map((link) => {
        let label = link.label;
        if (label.includes('Previous')) label = '←';
        if (label.includes('Next')) label = '→';
        return { ...link, label };
    });
});
</script>

<template>
    <div v-if="links.length > 3" class="flex flex-wrap justify-center gap-1">
        <template v-for="(link, key) in filteredLinks" :key="key">
            <div
                v-if="link.url === null"
                :class="cn(
                    buttonVariants({ variant: 'outline', size: 'icon' }),
                    'h-9 w-9 cursor-not-allowed opacity-50',
                )"
                v-html="link.label"
            />
            <Link
                v-else
                :href="link.url"
                :class="cn(
                    buttonVariants({ variant: link.active ? 'default' : 'outline', size: 'icon' }),
                    'h-9 w-9',
                )"
                v-html="link.label"
            />
        </template>
    </div>
</template>
