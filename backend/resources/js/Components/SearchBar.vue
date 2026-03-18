<script setup>
import { ref, watch } from 'vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Search, X } from 'lucide-vue-next';

const props = defineProps({
    modelValue: { type: String, default: '' },
    placeholder: { type: String, default: 'Cari...' },
    debounce: { type: Number, default: 300 },
});

const emit = defineEmits(['update:modelValue']);

const localValue = ref(props.modelValue);
let debounceTimeout = null;

watch(localValue, (newValue) => {
    if (debounceTimeout) clearTimeout(debounceTimeout);
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
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground pointer-events-none" />
        <Input
            v-model="localValue"
            type="text"
            :placeholder="placeholder"
            class="pl-9 pr-9"
        />
        <Button
            v-if="localValue"
            variant="ghost"
            size="icon"
            class="absolute right-0 top-0 h-full w-9 hover:bg-transparent"
            @click="clearSearch"
        >
            <X class="h-4 w-4 text-muted-foreground" />
        </Button>
    </div>
</template>
