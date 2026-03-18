<script setup>
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

const props = defineProps({
    modelValue: { type: [String, Number], default: '' },
    options: { type: Array, required: true },
    label: { type: String, default: 'Filter' },
});

const emit = defineEmits(['update:modelValue']);

const handleSelect = (value) => {
    // shadcn select doesn't support empty string well for "all", so handle it
    emit('update:modelValue', value === '__all__' ? '' : value);
};
</script>

<template>
    <Select
        :model-value="modelValue || '__all__'"
        @update:model-value="handleSelect"
    >
        <SelectTrigger class="w-full">
            <SelectValue :placeholder="label" />
        </SelectTrigger>
        <SelectContent>
            <SelectItem value="__all__">{{ label }}</SelectItem>
            <SelectItem
                v-for="option in options"
                :key="option.value"
                :value="String(option.value)"
            >
                {{ option.label }}
            </SelectItem>
        </SelectContent>
    </Select>
</template>
