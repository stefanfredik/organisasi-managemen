<script setup>
import { PaginationPrev } from 'radix-vue';
import { cn } from '@/lib/utils';
import { buttonVariants } from '@/components/ui/button';
import { ChevronLeft } from 'lucide-vue-next';
const props = defineProps({ class: { type: [String, Array, Object], default: '' } });
</script>

<template>
    <PaginationPrev :class="cn(buttonVariants({ variant: 'outline', size: 'icon' }), 'h-9 w-9', props.class)">
        <ChevronLeft class="h-4 w-4" />
    </PaginationPrev>
</template>
