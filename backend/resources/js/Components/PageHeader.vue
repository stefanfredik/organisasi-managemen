<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <div class="flex items-center justify-between gap-3">
        <div class="min-w-0">
            <h2 class="text-lg font-semibold leading-tight text-foreground truncate">
                {{ title }}
            </h2>
            <p v-if="description" class="mt-0.5 text-xs text-muted-foreground hidden sm:block">
                {{ description }}
            </p>
        </div>
        <div v-if="$slots.actions" class="flex items-center gap-2 flex-shrink-0">
            <slot name="actions" />
        </div>
    </div>
</template>
