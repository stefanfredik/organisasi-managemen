<script setup>
import { cn } from '@/lib/utils';
const props = defineProps({
    src: { type: String, default: '' },
    alt: { type: String, default: '' },
    class: { type: [String, Array, Object], default: '' },
});
</script>

<template>
    <img v-if="src" :src="src" :alt="alt" :class="cn('aspect-square h-full w-full', props.class)" />
</template>
