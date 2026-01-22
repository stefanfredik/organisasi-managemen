<script setup>
import { onMounted, ref, watch } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: 0,
    },
});

const emit = defineEmits(['update:modelValue']);

const input = ref(null);
const displayValue = ref('');

const format = (value) => {
    if (value === null || value === undefined || value === '') return '';
    // Convert to string and remove non-digit characters
    let numStr = value.toString().replace(/\D/g, '');
    // Remove leading zeros
    if (numStr.length > 1 && numStr.startsWith('0')) {
        numStr = numStr.replace(/^0+/, '');
    }
    // Add thousand separators
    return numStr.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
};

watch(() => props.modelValue, (newVal) => {
    displayValue.value = format(newVal);
}, { immediate: true });

const handleInput = (e) => {
    let value = e.target.value;
    // Get raw numeric value
    let numeric = value.replace(/\D/g, '');
    
    // Handle empty case
    if (numeric === '') {
        emit('update:modelValue', 0);
        displayValue.value = '';
        return;
    }

    // Handle leading zeros for the model value
    if (numeric.length > 1 && numeric.startsWith('0')) {
        numeric = numeric.replace(/^0+/, '');
    }
    
    emit('update:modelValue', Number(numeric));
    
    // Update display value
    // We force update the input value to match formatted value
    // This might cause cursor jump in middle editing but is acceptable for simple amount inputs
    displayValue.value = format(numeric);
    e.target.value = displayValue.value;
};

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        ref="input"
        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        :value="displayValue"
        @input="handleInput"
        type="text"
        inputmode="numeric"
    />
</template>
