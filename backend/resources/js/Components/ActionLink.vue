<script setup>
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

defineProps({
    href: { type: String, required: true },
    variant: {
        type: String,
        default: 'primary',
        validator: (v) => ['primary', 'secondary', 'danger', 'success'].includes(v),
    },
    size: { type: String, default: 'md' },
});

const variantMap = {
    primary: 'default',
    secondary: 'outline',
    danger: 'destructive',
    success: 'success',
};

const sizeMap = { sm: 'sm', md: 'default', lg: 'lg' };
</script>

<template>
    <Button
        :variant="variantMap[variant] || 'default'"
        :size="sizeMap[size] || 'default'"
        as-child
    >
        <Link :href="href">
            <slot />
        </Link>
    </Button>
</template>
