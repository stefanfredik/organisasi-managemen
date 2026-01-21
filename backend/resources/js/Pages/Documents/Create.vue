<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                <Link :href="route('documents.index')" class="mr-4 text-gray-600 hover:text-gray-900">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Unggah Dokumen Baru
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6">
                        <!-- Name -->
                        <div class="mb-6">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nama Dokumen <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                :class="{ 'border-red-500': form.errors.name }"
                                required
                            />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>

                        <!-- Category -->
                        <div class="mb-6">
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                                Kategori
                            </label>
                            <input
                                id="category"
                                v-model="form.category"
                                type="text"
                                list="categories"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Masukkan atau pilih kategori"
                            />
                            <datalist id="categories">
                                <option v-for="cat in categories" :key="cat" :value="cat" />
                            </datalist>
                            <p class="mt-1 text-sm text-gray-500">Contoh: Surat Keputusan, Laporan, Proposal, dll.</p>
                            <p v-if="form.errors.category" class="mt-1 text-sm text-red-600">{{ form.errors.category }}</p>
                        </div>

                        <!-- Description -->
                        <div class="mb-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Deskripsi
                            </label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="4"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Deskripsi singkat tentang dokumen ini..."
                            ></textarea>
                            <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
                        </div>

                        <!-- File Upload -->
                        <div class="mb-6">
                            <label for="file" class="block text-sm font-medium text-gray-700 mb-2">
                                File Dokumen <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-blue-400 transition-colors">
                                <div class="space-y-1 text-center">
                                    <svg
                                        v-if="!filePreview"
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
                                    <div v-else class="text-sm text-gray-600">
                                        <p class="font-medium">{{ filePreview.name }}</p>
                                        <p class="text-xs text-gray-500">{{ formatFileSize(filePreview.size) }}</p>
                                    </div>
                                    <div class="flex text-sm text-gray-600">
                                        <label
                                            for="file-upload"
                                            class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500"
                                        >
                                            <span>{{ filePreview ? 'Ganti file' : 'Unggah file' }}</span>
                                            <input
                                                id="file-upload"
                                                type="file"
                                                class="sr-only"
                                                accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.rar"
                                                @change="handleFileChange"
                                                required
                                            />
                                        </label>
                                        <p class="pl-1">atau drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, ZIP, RAR hingga 10MB
                                    </p>
                                </div>
                            </div>
                            <p v-if="form.errors.file" class="mt-1 text-sm text-red-600">{{ form.errors.file }}</p>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end space-x-3">
                            <Link
                                :href="route('documents.index')"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                            >
                                Batal
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                :class="{ 'opacity-25': form.processing }"
                            >
                                <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ form.processing ? 'Mengunggah...' : 'Unggah Dokumen' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    categories: Array,
});

const form = useForm({
    name: '',
    category: '',
    description: '',
    file: null,
});

const filePreview = ref(null);

const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.file = file;
        filePreview.value = {
            name: file.name,
            size: file.size,
        };
    }
};

const submit = () => {
    form.post(route('documents.store'), {
        onSuccess: () => {
            form.reset();
            filePreview.value = null;
        },
    });
};

const formatFileSize = (bytes) => {
    if (!bytes) return 'N/A';
    const units = ['B', 'KB', 'MB', 'GB'];
    let size = bytes;
    let unitIndex = 0;
    while (size >= 1024 && unitIndex < units.length - 1) {
        size /= 1024;
        unitIndex++;
    }
    return `${size.toFixed(2)} ${units[unitIndex]}`;
};
</script>
