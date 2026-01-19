<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: File,
        default: null,
    },
    currentImage: {
        type: String,
        default: null,
    },
    maxSize: {
        type: Number,
        default: 2048, // 2MB in KB
    },
    accept: {
        type: String,
        default: 'image/*',
    },
});

const emit = defineEmits(['update:modelValue']);

const fileInput = ref(null);
const isDragging = ref(false);
const previewUrl = ref(props.currentImage);
const error = ref('');

const hasImage = computed(() => {
    return props.modelValue || previewUrl.value;
});

const handleFileSelect = (event) => {
    const file = event.target.files[0];
    processFile(file);
};

const handleDrop = (event) => {
    isDragging.value = false;
    const file = event.dataTransfer.files[0];
    processFile(file);
};

const processFile = (file) => {
    error.value = '';

    if (!file) return;

    // Validate file type
    if (!file.type.startsWith('image/')) {
        error.value = 'File harus berupa gambar';
        return;
    }

    // Validate file size
    const fileSizeKB = file.size / 1024;
    if (fileSizeKB > props.maxSize) {
        error.value = `Ukuran file maksimal ${props.maxSize / 1024}MB`;
        return;
    }

    // Create preview
    const reader = new FileReader();
    reader.onload = (e) => {
        previewUrl.value = e.target.result;
    };
    reader.readAsDataURL(file);

    emit('update:modelValue', file);
};

const removeImage = () => {
    previewUrl.value = null;
    emit('update:modelValue', null);
    if (fileInput.value) {
        fileInput.value.value = '';
    }
    error.value = '';
};

const triggerFileInput = () => {
    fileInput.value?.click();
};
</script>

<template>
    <div class="space-y-2">
        <!-- Upload Area -->
        <div
            v-if="!hasImage"
            class="relative border-2 border-dashed rounded-lg transition-colors"
            :class="{
                'border-indigo-500 bg-indigo-50': isDragging,
                'border-gray-300 hover:border-gray-400': !isDragging,
            }"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleDrop"
        >
            <input
                ref="fileInput"
                type="file"
                :accept="accept"
                class="hidden"
                @change="handleFileSelect"
            />

            <div class="p-6 text-center cursor-pointer" @click="triggerFileInput">
                <svg
                    class="mx-auto h-12 w-12 text-gray-400"
                    stroke="currentColor"
                    fill="none"
                    viewBox="0 0 48 48"
                >
                    <path
                        d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
                <p class="mt-2 text-sm text-gray-600">
                    <span class="font-semibold text-indigo-600">Klik untuk upload</span>
                    atau drag & drop
                </p>
                <p class="mt-1 text-xs text-gray-500">
                    PNG, JPG, GIF hingga {{ maxSize / 1024 }}MB
                </p>
            </div>
        </div>

        <!-- Preview -->
        <div v-else class="relative">
            <img
                :src="previewUrl"
                alt="Preview"
                class="w-full h-48 object-cover rounded-lg"
            />
            <button
                type="button"
                class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-2 hover:bg-red-600 transition-colors"
                @click="removeImage"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"
                    />
                </svg>
            </button>
        </div>

        <!-- Error Message -->
        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
    </div>
</template>
