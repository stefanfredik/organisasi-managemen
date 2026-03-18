<script setup>
import { Primitive } from 'radix-vue';
import { computed } from 'vue';
import { cn } from '@/lib/utils';
import { buttonVariants } from './buttonVariants';

const props = defineProps({
    variant: {
        type: String,
        default: 'default',
    },
    size: {
        type: String,
        default: 'default',
    },
    as: {
        type: [String, Object],
        default: 'button',
    },
    asChild: {
        type: Boolean,
        default: false,
    },
    class: {
        type: [String, Array, Object],
        default: '',
    },
});

const delegatedProps = computed(() => {
    const { class: _, ...rest } = props;
    return rest;
});
</script>

<template>
    <Primitive
        :as="as"
        :as-child="asChild"
        :class="cn(buttonVariants({ variant, size }), props.class)"
        v-bind="delegatedProps"
    >
        <slot />
    </Primitive>
</template>
