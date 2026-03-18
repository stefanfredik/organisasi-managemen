<script setup>
import { ref, computed, watch } from 'vue';
import { ImagePlus, X, Upload, RefreshCw } from 'lucide-vue-next';

const props = defineProps({
    modelValue: {
        type: [File, null],
        default: null,
    },
    currentImage: {
        type: String,
        default: null,
    },
    maxSize: {
        type: Number,
        default: 2048, // KB
    },
    accept: {
        type: String,
        default: 'image/*',
    },
    label: {
        type: String,
        default: 'Upload Gambar',
    },
    hint: {
        type: String,
        default: '',
    },
    error: {
        type: String,
        default: '',
    },
    shape: {
        type: String,
        default: 'rectangle', // 'rectangle' | 'circle'
        validator: (v) => ['rectangle', 'circle'].includes(v),
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue']);

const fileInput = ref(null);
const isDragging = ref(false);
const previewUrl = ref(props.currentImage);
const internalError = ref('');

const displayError = computed(() => props.error || internalError.value);

const hasImage = computed(() => props.modelValue || previewUrl.value);

const hintText = computed(() => {
    if (props.hint) return props.hint;
    const sizeMB = props.maxSize / 1024;
    return `PNG, JPG, WEBP hingga ${sizeMB}MB`;
});

const shapeClasses = computed(() => ({
    dropzone: props.shape === 'circle'
        ? 'rounded-full aspect-square w-48 mx-auto'
        : 'rounded-xl',
    preview: props.shape === 'circle'
        ? 'rounded-full aspect-square w-48 mx-auto'
        : 'rounded-xl',
    image: props.shape === 'circle'
        ? 'w-full h-full object-cover rounded-full'
        : 'w-full h-52 object-cover rounded-xl',
}));

watch(() => props.currentImage, (val) => {
    if (val && !props.modelValue) {
        previewUrl.value = val;
    }
});

const handleFileSelect = (event) => {
    const file = event.target.files[0];
    processFile(file);
};

const handleDrop = (event) => {
    isDragging.value = false;
    if (props.disabled) return;
    const file = event.dataTransfer.files[0];
    processFile(file);
};

const processFile = (file) => {
    internalError.value = '';
    if (!file) return;

    if (!file.type.startsWith('image/')) {
        internalError.value = 'File harus berupa gambar';
        return;
    }

    const fileSizeKB = file.size / 1024;
    if (fileSizeKB > props.maxSize) {
        internalError.value = `Ukuran file maksimal ${props.maxSize / 1024}MB`;
        return;
    }

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
    internalError.value = '';
};

const triggerFileInput = () => {
    if (!props.disabled) {
        fileInput.value?.click();
    }
};
</script>

<template>
    <div class="space-y-2">
        <!-- Upload Area -->
        <div
            v-if="!hasImage"
            class="relative border-2 border-dashed transition-all duration-300 overflow-hidden"
            :class="[
                shapeClasses.dropzone,
                isDragging
                    ? 'border-primary bg-primary/5 scale-[1.02]'
                    : 'border-muted-foreground/25 hover:border-primary/50 hover:bg-muted/50',
                disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer',
            ]"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleDrop"
            @click="triggerFileInput"
        >
            <input
                ref="fileInput"
                type="file"
                :accept="accept"
                class="hidden"
                :disabled="disabled"
                @change="handleFileSelect"
            />

            <div class="flex flex-col items-center justify-center p-8 gap-3">
                <div
                    class="w-14 h-14 rounded-2xl flex items-center justify-center transition-all duration-300"
                    :class="isDragging ? 'bg-primary/10 text-primary scale-110' : 'bg-muted text-muted-foreground'"
                >
                    <Upload v-if="isDragging" class="w-7 h-7 animate-bounce" />
                    <ImagePlus v-else class="w-7 h-7" />
                </div>
                <div class="text-center">
                    <p class="text-sm font-medium text-foreground">
                        {{ label }}
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        Klik atau seret file ke sini
                    </p>
                    <p class="mt-0.5 text-xs text-muted-foreground/70">
                        {{ hintText }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Preview -->
        <div
            v-else
            class="relative group overflow-hidden"
            :class="shapeClasses.preview"
        >
            <img
                :src="previewUrl"
                alt="Preview"
                :class="shapeClasses.image"
            />

            <!-- Hover overlay -->
            <div
                class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-all duration-300 flex items-center justify-center gap-2"
                :class="shape === 'circle' ? 'rounded-full' : 'rounded-xl'"
            >
                <button
                    type="button"
                    class="p-2.5 bg-white/90 text-foreground rounded-full opacity-0 group-hover:opacity-100 transition-all duration-300 hover:bg-white hover:scale-110 shadow-lg transform translate-y-2 group-hover:translate-y-0"
                    @click="triggerFileInput"
                    title="Ganti gambar"
                >
                    <RefreshCw class="w-4 h-4" />
                </button>
                <button
                    type="button"
                    class="p-2.5 bg-destructive/90 text-white rounded-full opacity-0 group-hover:opacity-100 transition-all duration-300 hover:bg-destructive hover:scale-110 shadow-lg transform translate-y-2 group-hover:translate-y-0 delay-75"
                    @click="removeImage"
                    title="Hapus gambar"
                >
                    <X class="w-4 h-4" />
                </button>
            </div>

            <input
                ref="fileInput"
                type="file"
                :accept="accept"
                class="hidden"
                :disabled="disabled"
                @change="handleFileSelect"
            />
        </div>

        <!-- Error Message -->
        <p v-if="displayError" class="text-sm text-destructive flex items-center gap-1">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            {{ displayError }}
        </p>
    </div>
</template>
