<script setup>
import { ref } from 'vue';

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => [],
    },
    label: {
        type: String,
        default: 'Upload Gambar',
    },
    maxFiles: {
        type: Number,
        default: 5,
    },
    maxSize: {
        type: Number,
        default: 2048, // KB
    },
});

const emit = defineEmits(['update:modelValue', 'error']);

const fileInput = ref(null);
const previews = ref([]);

// Initialize previews from modelValue if they are URLs (strings)
// Note: This component assumes modelValue is an array of File objects or URL strings
if (props.modelValue && props.modelValue.length > 0) {
    props.modelValue.forEach(file => {
        if (typeof file === 'string') {
            previews.value.push({
                type: 'url',
                url: file,
            });
        } else if (file instanceof File) {
             const reader = new FileReader();
             reader.onload = (e) => {
                 previews.value.push({
                     type: 'file',
                     url: e.target.result,
                     file: file
                 });
             };
             reader.readAsDataURL(file);
        }
    });
}


const triggerUpload = () => {
    fileInput.value.click();
};

const handleFileChange = (event) => {
    const files = Array.from(event.target.files);
    
    if (props.modelValue.length + files.length > props.maxFiles) {
        emit('error', `Maksimal ${props.maxFiles} file yang diperbolehkan.`);
        return;
    }

    const validFiles = [];

    files.forEach(file => {
        if (file.size > props.maxSize * 1024) {
             emit('error', `File ${file.name} terlalu besar. Maksimal ${props.maxSize}KB.`);
             return;
        }

        if (!file.type.startsWith('image/')) {
             emit('error', `File ${file.name} bukan gambar.`);
             return;
        }

        validFiles.push(file);

        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            previews.value.push({
                type: 'file',
                url: e.target.result,
                file: file
            });
        };
        reader.readAsDataURL(file);
    });

    const newModelValue = [...props.modelValue, ...validFiles];
    emit('update:modelValue', newModelValue);
    
    // Reset input
    if(fileInput.value) fileInput.value.value = '';
};

const removeFile = (index) => {
    previews.value.splice(index, 1);
    const newModelValue = [...props.modelValue];
    newModelValue.splice(index, 1);
    emit('update:modelValue', newModelValue);
};
</script>

<template>
    <div class="space-y-4">
        <div 
            @click="triggerUpload"
            class="border-2 border-dashed border-gray-300 rounded-2xl p-8 hover:border-indigo-500 hover:bg-indigo-50/50 transition-all cursor-pointer group text-center"
        >
            <input 
                ref="fileInput"
                type="file" 
                multiple 
                accept="image/*" 
                class="hidden" 
                @change="handleFileChange"
            >
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-indigo-100 group-hover:text-indigo-600 transition-colors text-gray-400">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <p class="text-sm font-bold text-gray-700 mb-1 group-hover:text-indigo-700">{{ label }}</p>
            <p class="text-xs text-gray-500">Klik untuk memilih gambar (Max: {{ maxFiles }} file)</p>
        </div>

        <!-- Previews -->
        <div v-if="previews.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div v-for="(preview, index) in previews" :key="index" class="relative group aspect-square bg-gray-100 rounded-xl overflow-hidden border border-gray-200">
                <img :src="preview.url" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                    <button 
                        @click.stop="removeFile(index)" 
                        type="button"
                        class="p-2 bg-red-600 text-white rounded-full hover:bg-red-700 transition-transform hover:scale-110 shadow-lg"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
