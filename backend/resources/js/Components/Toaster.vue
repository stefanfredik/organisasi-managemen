<script setup>
import { useToast } from '@/composables/useToast';
import { X, CheckCircle, AlertCircle, Info } from 'lucide-vue-next';

const { toasts, removeToast } = useToast();

const iconMap = {
    success: CheckCircle,
    error: AlertCircle,
    default: Info,
};

const styleMap = {
    success: 'border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-950',
    error: 'border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-950',
    default: 'border-border bg-card',
};

const iconStyleMap = {
    success: 'text-green-600 dark:text-green-400',
    error: 'text-red-600 dark:text-red-400',
    default: 'text-muted-foreground',
};

const titleStyleMap = {
    success: 'text-green-800 dark:text-green-200',
    error: 'text-red-800 dark:text-red-200',
    default: 'text-foreground',
};

const descStyleMap = {
    success: 'text-green-700 dark:text-green-300',
    error: 'text-red-700 dark:text-red-300',
    default: 'text-muted-foreground',
};
</script>

<template>
    <Teleport to="body">
        <div class="fixed top-4 left-1/2 -translate-x-1/2 sm:translate-x-0 sm:left-auto sm:top-auto sm:bottom-4 sm:right-4 z-[100] flex flex-col-reverse gap-2 w-[calc(100%-2rem)] sm:w-full max-w-sm pointer-events-none">
            <TransitionGroup
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 translate-y-2 scale-95"
                enter-to-class="opacity-100 translate-y-0 scale-100"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100 translate-y-0 scale-100"
                leave-to-class="opacity-0 translate-y-2 scale-95"
            >
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    :class="[
                        'pointer-events-auto rounded-lg border shadow-lg p-4 flex items-start gap-3',
                        styleMap[toast.type] || styleMap.default,
                    ]"
                >
                    <component
                        :is="iconMap[toast.type] || iconMap.default"
                        :class="['w-5 h-5 shrink-0 mt-0.5', iconStyleMap[toast.type] || iconStyleMap.default]"
                    />
                    <div class="flex-1 min-w-0">
                        <p v-if="toast.title" :class="['text-sm font-semibold', titleStyleMap[toast.type] || titleStyleMap.default]">
                            {{ toast.title }}
                        </p>
                        <p :class="['text-xs mt-0.5', descStyleMap[toast.type] || descStyleMap.default]">
                            {{ toast.description }}
                        </p>
                    </div>
                    <button
                        @click="removeToast(toast.id)"
                        class="shrink-0 p-0.5 rounded hover:bg-black/5 dark:hover:bg-white/10 transition-colors"
                    >
                        <X class="w-3.5 h-3.5 text-muted-foreground" />
                    </button>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>
