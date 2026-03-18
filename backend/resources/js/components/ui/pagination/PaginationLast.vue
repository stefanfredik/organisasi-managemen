<script setup>
import { PaginationLast } from 'radix-vue';
import { cn } from '@/lib/utils';
import { buttonVariants } from '@/components/ui/button';
import { ChevronsRight } from 'lucide-vue-next';
const props = defineProps({ class: { type: [String, Array, Object], default: '' } });
</script>

<template>
    <PaginationLast :class="cn(buttonVariants({ variant: 'outline', size: 'icon' }), 'h-9 w-9', props.class)">
        <ChevronsRight class="h-4 w-4" />
    </PaginationLast>
</template>
