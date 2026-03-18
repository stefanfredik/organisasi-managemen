<script setup>
import { ref, computed, watch } from 'vue';
import { Images, X, Plus, Upload } from 'lucide-vue-next';

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => [],
    },
    label: {
        type: String,
        default: 'Upload Gambar',
    },
    hint: {
        type: String,
        default: '',
    },
    maxFiles: {
        type: Number,
        default: 5,
    },
    maxSize: {
        type: Number,
        default: 2048, // KB
    },
    error: {
        type: String,
        default: '',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue', 'error']);

const fileInput = ref(null);
const isDragging = ref(false);
const previews = ref([]);
const internalError = ref('');

const displayError = computed(() => props.error || internalError.value);

const hintText = computed(() => {
    if (props.hint) return props.hint;
    const sizeMB = props.maxSize / 1024;
    return `PNG, JPG, WEBP hingga ${sizeMB}MB per file`;
});

const canAddMore = computed(() => previews.value.length < props.maxFiles);

// Initialize previews from modelValue if they are URLs (strings)
const initPreviews = () => {
    if (props.modelValue && props.modelValue.length > 0) {
        props.modelValue.forEach(file => {
            if (typeof file === 'string') {
                previews.value.push({ type: 'url', url: file });
            } else if (file instanceof File) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    previews.value.push({ type: 'file', url: e.target.result, file });
                };
                reader.readAsDataURL(file);
            }
        });
    }
};
initPreviews();

const triggerUpload = () => {
    if (!props.disabled && canAddMore.value) {
        fileInput.value?.click();
    }
};

const handleDrop = (event) => {
    isDragging.value = false;
    if (props.disabled) return;
    const files = Array.from(event.dataTransfer.files);
    processFiles(files);
};

const handleFileChange = (event) => {
    const files = Array.from(event.target.files);
    processFiles(files);
};

const processFiles = (files) => {
    internalError.value = '';

    if (previews.value.length + files.length > props.maxFiles) {
        internalError.value = `Maksimal ${props.maxFiles} file yang diperbolehkan.`;
        emit('error', internalError.value);
        return;
    }

    const validFiles = [];

    files.forEach(file => {
        if (file.size > props.maxSize * 1024) {
            internalError.value = `File ${file.name} terlalu besar. Maksimal ${props.maxSize / 1024}MB.`;
            emit('error', internalError.value);
            return;
        }

        if (!file.type.startsWith('image/')) {
            internalError.value = `File ${file.name} bukan gambar.`;
            emit('error', internalError.value);
            return;
        }

        validFiles.push(file);

        const reader = new FileReader();
        reader.onload = (e) => {
            previews.value.push({ type: 'file', url: e.target.result, file });
        };
        reader.readAsDataURL(file);
    });

    const newModelValue = [...props.modelValue, ...validFiles];
    emit('update:modelValue', newModelValue);

    if (fileInput.value) fileInput.value.value = '';
};

const removeFile = (index) => {
    previews.value.splice(index, 1);
    const newModelValue = [...props.modelValue];
    newModelValue.splice(index, 1);
    emit('update:modelValue', newModelValue);
    internalError.value = '';
};
</script>

<template>
    <div class="space-y-3">
        <!-- Upload Area -->
        <div
            v-if="canAddMore"
            class="relative border-2 border-dashed rounded-xl transition-all duration-300 overflow-hidden"
            :class="[
                isDragging
                    ? 'border-primary bg-primary/5 scale-[1.01]'
                    : 'border-muted-foreground/25 hover:border-primary/50 hover:bg-muted/50',
                disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer',
            ]"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleDrop"
            @click="triggerUpload"
        >
            <input
                ref="fileInput"
                type="file"
                multiple
                accept="image/*"
                class="hidden"
                :disabled="disabled"
                @change="handleFileChange"
            />

            <div class="flex flex-col items-center justify-center p-8 gap-3">
                <div
                    class="w-14 h-14 rounded-2xl flex items-center justify-center transition-all duration-300"
                    :class="isDragging ? 'bg-primary/10 text-primary scale-110' : 'bg-muted text-muted-foreground'"
                >
                    <Upload v-if="isDragging" class="w-7 h-7 animate-bounce" />
                    <Images v-else class="w-7 h-7" />
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

                <!-- Counter badge -->
                <div v-if="previews.length > 0" class="flex items-center gap-1.5 px-3 py-1 bg-primary/10 text-primary rounded-full text-xs font-medium">
                    <Images class="w-3.5 h-3.5" />
                    {{ previews.length }}/{{ maxFiles }} file
                </div>
            </div>
        </div>

        <!-- Max reached badge -->
        <div v-else class="flex items-center justify-center gap-2 p-3 bg-muted/50 rounded-xl border border-muted-foreground/10">
            <Images class="w-4 h-4 text-muted-foreground" />
            <span class="text-sm text-muted-foreground">Maksimal {{ maxFiles }} gambar tercapai</span>
        </div>

        <!-- Previews Grid -->
        <div v-if="previews.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
            <div
                v-for="(preview, index) in previews"
                :key="index"
                class="relative group aspect-square rounded-xl overflow-hidden border border-border bg-muted/30 shadow-sm hover:shadow-md transition-shadow duration-200"
            >
                <img
                    :src="preview.url"
                    class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                    alt=""
                />

                <!-- Hover overlay -->
                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-all duration-300 flex items-center justify-center">
                    <button
                        @click.stop="removeFile(index)"
                        type="button"
                        class="p-2 bg-destructive/90 text-white rounded-full opacity-0 group-hover:opacity-100 transition-all duration-300 hover:bg-destructive hover:scale-110 shadow-lg transform translate-y-2 group-hover:translate-y-0"
                        title="Hapus gambar"
                    >
                        <X class="w-4 h-4" />
                    </button>
                </div>

                <!-- Index badge -->
                <div class="absolute top-2 left-2 w-6 h-6 rounded-full bg-black/50 text-white text-xs flex items-center justify-center font-medium backdrop-blur-sm">
                    {{ index + 1 }}
                </div>
            </div>

            <!-- Add more button -->
            <button
                v-if="canAddMore"
                type="button"
                @click="triggerUpload"
                class="aspect-square rounded-xl border-2 border-dashed border-muted-foreground/25 flex flex-col items-center justify-center gap-2 text-muted-foreground hover:border-primary/50 hover:text-primary hover:bg-primary/5 transition-all duration-200"
            >
                <Plus class="w-6 h-6" />
                <span class="text-xs font-medium">Tambah</span>
            </button>
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
