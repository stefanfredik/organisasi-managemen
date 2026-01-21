<script setup>
import { ref } from 'vue';

const props = defineProps({
    modelValue: [File, String, null],
    label: String,
    accept: {
        type: String,
        default: '.pdf,.doc,.docx,.xls,.xlsx'
    },
    error: String,
});

const emit = defineEmits(['update:modelValue']);
const fileInput = ref(null);

const handleFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        emit('update:modelValue', file);
    }
};

const triggerBrowse = () => {
    fileInput.value.click();
};

const clearFile = () => {
    emit('update:modelValue', null);
    if(fileInput.value) fileInput.value.value = '';
};
</script>

<template>
    <div class="w-full">
        <label v-if="label" class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide text-xs">{{ label }}</label>
        
        <div 
            v-if="!modelValue"
            class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-300 border-dashed rounded-2xl hover:border-indigo-500 hover:bg-indigo-50/30 transition-all cursor-pointer group"
            @click="triggerBrowse"
        >
            <div class="space-y-1 text-center">
                <svg class="mx-auto h-12 w-12 text-slate-400 group-hover:text-indigo-500 transition-colors" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <div class="flex text-sm text-slate-600 justify-center">
                    <span class="relative cursor-pointer rounded-md font-bold text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                        Upload file
                    </span>
                    <p class="pl-1">atau drag & drop</p>
                </div>
                <p class="text-xs text-slate-500 font-medium">
                    {{ accept.replace(/\./g, ' ').toUpperCase() }} (Max 10MB)
                </p>
            </div>
        </div>

        <div v-else class="mt-1 flex items-center justify-between p-4 border border-slate-200 rounded-2xl bg-white shadow-sm">
            <div class="flex items-center space-x-4 overflow-hidden">
                <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center text-indigo-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 011.414.586l5.414 5.414a1 1 0 01.586 1.414V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-slate-900 truncate">
                        {{ modelValue.name || (typeof modelValue === 'string' ? modelValue.split('/').pop() : 'File Terpilih') }}
                    </p>
                    <p v-if="modelValue.size" class="text-xs text-slate-500 font-medium">
                        {{ (modelValue.size / 1024 / 1024).toFixed(2) }} MB
                    </p>
                </div>
            </div>
            <button 
                type="button" 
                @click="clearFile"
                class="ml-4 flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full text-slate-400 hover:text-white hover:bg-rose-500 transition-all"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <input 
            ref="fileInput" 
            type="file" 
            :accept="accept" 
            class="hidden" 
            @change="handleFileChange"
        >
        
        <p v-if="error" class="mt-2 text-sm text-rose-600 font-medium flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            {{ error }}
        </p>
    </div>
</template>
