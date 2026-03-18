<script setup>
import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const visible = ref(false);
const type = computed(() => page.props.flash.error ? 'error' : 'success');
const message = computed(() => page.props.flash.error || page.props.flash.success);

watch(message, (val) => {
    if (val) {
        visible.value = true;
        setTimeout(() => {
            visible.value = false;
        }, 5000);
    }
});

const dismiss = () => {
    visible.value = false;
};
</script>

<template>
    <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0 translate-y-[-1rem]"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-[-1rem]"
    >
        <div v-if="visible && message" class="fixed top-4 right-4 z-50 max-w-sm w-full">
            <div :class="[
                'rounded-card shadow-card-hover border p-4 flex items-start gap-3',
                type === 'error' ? 'bg-destructive/10 border-danger-200 text-danger-800' : 'bg-success/10 border-success-200 text-success-800'
            ]">
                <div :class="type === 'error' ? 'text-danger-500' : 'text-success-500'">
                    <svg v-if="type === 'success'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-sm">{{ type === 'error' ? 'Gagal' : 'Berhasil' }}</h3>
                    <p class="text-xs mt-1">{{ message }}</p>
                </div>
                <button @click="dismiss" class="text-muted-foreground hover:text-muted-foreground transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </Transition>
</template>
