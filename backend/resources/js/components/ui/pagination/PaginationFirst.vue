<script setup>
import { PaginationFirst } from 'radix-vue';
import { cn } from '@/lib/utils';
import { buttonVariants } from '@/components/ui/button';
import { ChevronsLeft } from 'lucide-vue-next';
const props = defineProps({ class: { type: [String, Array, Object], default: '' } });
</script>

<template>
    <PaginationFirst :class="cn(buttonVariants({ variant: 'outline', size: 'icon' }), 'h-9 w-9', props.class)">
        <ChevronsLeft class="h-4 w-4" />
    </PaginationFirst>
</template>
