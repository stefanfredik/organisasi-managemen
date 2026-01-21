<script setup>
defineProps({
    modelValue: String,
    label: String,
    error: String,
    type: {
        type: String,
        default: 'date',
    },
    min: String,
    max: String,
});

defineEmits(['update:modelValue']);
</script>

<template>
    <div class="w-full">
        <label v-if="label" class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide text-xs">{{ label }}</label>
        <div class="relative">
            <input 
                :value="modelValue"
                :type="type"
                :min="min"
                :max="max"
                @input="$emit('update:modelValue', $event.target.value)"
                class="block w-full px-4 py-3 bg-white border border-slate-300 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all text-slate-900 font-medium placeholder-slate-400"
                :class="{ 'border-rose-300 focus:ring-rose-500 focus:border-rose-500': error }"
            >
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <svg v-if="!modelValue" class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        </div>
        <p v-if="error" class="mt-2 text-sm text-rose-600 font-medium flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            {{ error }}
        </p>
    </div>
</template>
