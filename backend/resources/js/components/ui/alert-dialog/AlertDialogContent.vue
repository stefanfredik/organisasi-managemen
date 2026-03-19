<script setup>
import { AlertDialogContent, AlertDialogOverlay, AlertDialogPortal } from 'radix-vue';
import { cn } from '@/lib/utils';
const props = defineProps({ class: { type: [String, Array, Object], default: '' } });
</script>

<template>
    <AlertDialogPortal>
        <AlertDialogOverlay class="fixed inset-0 z-50 bg-black/60" />
        <AlertDialogContent
            :class="cn(
                'fixed left-[50%] top-[50%] z-50 grid w-full max-w-lg translate-x-[-50%] translate-y-[-50%] gap-4 border bg-background p-6 shadow-lg sm:rounded-lg',
                props.class,
            )"
        >
            <slot />
        </AlertDialogContent>
    </AlertDialogPortal>
</template>
