<script setup>
import {
    AlertDialog, AlertDialogCancel, AlertDialogContent,
    AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { AlertTriangle } from 'lucide-vue-next';

const props = defineProps({
    open: { type: Boolean, default: false },
    title: { type: String, default: 'Konfirmasi Hapus' },
    description: { type: String, default: 'Apakah Anda yakin? Tindakan ini tidak dapat dibatalkan.' },
    confirmText: { type: String, default: 'Hapus' },
    cancelText: { type: String, default: 'Batal' },
});

const emit = defineEmits(['confirm', 'cancel']);

const onConfirm = () => {
    emit('confirm');
};
</script>

<template>
    <AlertDialog :open="open" @update:open="val => { if (!val) emit('cancel'); }">
        <AlertDialogContent>
            <AlertDialogHeader>
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-destructive/10 flex items-center justify-center">
                        <AlertTriangle class="w-5 h-5 text-destructive" />
                    </div>
                    <div>
                        <AlertDialogTitle>{{ title }}</AlertDialogTitle>
                    </div>
                </div>
                <AlertDialogDescription class="pt-2">
                    <slot name="description">{{ description }}</slot>
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel @click="emit('cancel')">{{ cancelText }}</AlertDialogCancel>
                <Button variant="destructive" @click="onConfirm">{{ confirmText }}</Button>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
