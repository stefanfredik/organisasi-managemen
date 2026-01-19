<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: 'Cari...',
    },
    debounce: {
        type: Number,
        default: 300,
    },
});

const emit = defineEmits(['update:modelValue']);

const localValue = ref(props.modelValue);
let debounceTimeout = null;

watch(localValue, (newValue) => {
    if (debounceTimeout) {
        clearTimeout(debounceTimeout);
    }

    debounceTimeout = setTimeout(() => {
        emit('update:modelValue', newValue);
    }, props.debounce);
});

watch(() => props.modelValue, (newValue) => {
    if (newValue !== localValue.value) {
        localValue.value = newValue;
    }
});

const clearSearch = () => {
    localValue.value = '';
    emit('update:modelValue', '');
};
</script>

<template>
    <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg
                class="h-5 w-5 text-gray-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                />
            </svg>
        </div>
        <input
            v-model="localValue"
            type="text"
            :placeholder="placeholder"
            class="block w-full pl-10 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
        />
        <div
            v-if="localValue"
            class="absolute inset-y-0 right-0 pr-3 flex items-center"
        >
            <button
                type="button"
                class="text-gray-400 hover:text-gray-500"
                @click="clearSearch"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"
                    />
                </svg>
            </button>
        </div>
    </div>
</template>
