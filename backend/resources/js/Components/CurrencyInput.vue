<script setup>
import { onMounted, ref, watch } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps({
    modelValue: { type: [String, Number], default: 0 },
});

const emit = defineEmits(['update:modelValue']);

const input = ref(null);
const displayValue = ref('');

const format = (value) => {
    if (value === null || value === undefined || value === '') return '';
    let numStr = value.toString().replace(/\D/g, '');
    if (numStr.length > 1 && numStr.startsWith('0')) {
        numStr = numStr.replace(/^0+/, '');
    }
    return numStr.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
};

watch(() => props.modelValue, (newVal) => {
    displayValue.value = format(newVal);
}, { immediate: true });

const handleInput = (e) => {
    let value = e.target.value;
    let numeric = value.replace(/\D/g, '');

    if (numeric === '') {
        emit('update:modelValue', 0);
        displayValue.value = '';
        return;
    }

    if (numeric.length > 1 && numeric.startsWith('0')) {
        numeric = numeric.replace(/^0+/, '');
    }

    emit('update:modelValue', Number(numeric));
    displayValue.value = format(numeric);
    e.target.value = displayValue.value;
};

onMounted(() => {
    if (input.value?.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <input
        ref="input"
        :class="cn(
            'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
        )"
        :value="displayValue"
        @input="handleInput"
        type="text"
        inputmode="numeric"
    />
</template>
