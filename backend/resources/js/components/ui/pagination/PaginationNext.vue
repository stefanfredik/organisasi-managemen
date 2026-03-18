<script setup>
import { PaginationNext } from 'radix-vue';
import { cn } from '@/lib/utils';
import { buttonVariants } from '@/components/ui/button';
import { ChevronRight } from 'lucide-vue-next';
const props = defineProps({ class: { type: [String, Array, Object], default: '' } });
</script>

<template>
    <PaginationNext :class="cn(buttonVariants({ variant: 'outline', size: 'icon' }), 'h-9 w-9', props.class)">
        <ChevronRight class="h-4 w-4" />
    </PaginationNext>
</template>
