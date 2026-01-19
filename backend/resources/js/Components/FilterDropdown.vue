<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: '',
    },
    options: {
        type: Array,
        required: true,
        // Expected format: [{ value: 'key', label: 'Display Text' }, ...]
    },
    label: {
        type: String,
        default: 'Filter',
    },
});

const emit = defineEmits(['update:modelValue']);

const selectedLabel = computed(() => {
    if (!props.modelValue) return props.label;
    const option = props.options.find(opt => opt.value === props.modelValue);
    return option ? option.label : props.label;
});

const handleSelect = (value) => {
    emit('update:modelValue', value);
};
</script>

<template>
    <div class="relative inline-block text-left">
        <div>
            <select
                :value="modelValue"
                class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                @change="handleSelect($event.target.value)"
            >
                <option value="">{{ label }}</option>
                <option
                    v-for="option in options"
                    :key="option.value"
                    :value="option.value"
                >
                    {{ option.label }}
                </option>
            </select>
        </div>
    </div>
</template>
