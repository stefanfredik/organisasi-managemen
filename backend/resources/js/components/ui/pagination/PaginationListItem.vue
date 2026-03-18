<script setup>
import { PaginationListItem } from 'radix-vue';
import { cn } from '@/lib/utils';
import { buttonVariants } from '@/components/ui/button';

const props = defineProps({
    value: { type: Number, required: true },
    class: { type: [String, Array, Object], default: '' },
});
</script>

<template>
    <PaginationListItem
        :value="value"
        :class="cn(
            buttonVariants({ variant: 'outline', size: 'icon' }),
            'h-9 w-9',
            'data-[selected]:bg-primary data-[selected]:text-primary-foreground',
            props.class,
        )"
    >
        {{ value }}
    </PaginationListItem>
</template>
