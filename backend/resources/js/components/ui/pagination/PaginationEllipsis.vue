<script setup>
import { cn } from '@/lib/utils';
import { MoreHorizontal } from 'lucide-vue-next';
const props = defineProps({ class: { type: [String, Array, Object], default: '' } });
</script>

<template>
    <span :class="cn('flex h-9 w-9 items-center justify-center', props.class)">
        <MoreHorizontal class="h-4 w-4" />
        <span class="sr-only">More pages</span>
    </span>
</template>
