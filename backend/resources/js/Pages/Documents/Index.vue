<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Manajemen Dokumen
                </h2>
                <Link
                    v-if="$page.props.auth.can.createDocument"
                    :href="route('documents.create')"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Unggah Dokumen
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Success Message -->
                <div v-if="$page.props.flash.success" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ $page.props.flash.success }}
                </div>

                <!-- Search and Filter -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Cari Dokumen</label>
                                <input
                                    v-model="searchForm.search"
                                    type="text"
                                    placeholder="Cari berdasarkan nama, deskripsi, atau kategori..."
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    @input="debounceSearch"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                                <select
                                    v-model="searchForm.category"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    @change="applyFilters"
                                >
                                    <option value="">Semua Kategori</option>
                                    <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Documents List -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="documents.data.length === 0" class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada dokumen</h3>
                            <p class="mt-1 text-sm text-gray-500">Mulai dengan mengunggah dokumen baru.</p>
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Dokumen</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ukuran</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Diunggah Oleh</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="document in documents.data" :key="document.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ document.name }}</div>
                                                    <div class="text-sm text-gray-500">{{ document.file_type?.toUpperCase() }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span v-if="document.category" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                {{ document.category }}
                                            </span>
                                            <span v-else class="text-sm text-gray-500">-</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatFileSize(document.file_size) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ document.uploader?.name || '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatDate(document.created_at) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end space-x-2">
                                                <a
                                                    :href="route('documents.download', document.id)"
                                                    class="text-blue-600 hover:text-blue-900"
                                                    title="Download"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                    </svg>
                                                </a>
                                                <Link
                                                    v-if="$page.props.auth.can.updateDocument"
                                                    :href="route('documents.edit', document.id)"
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                    title="Edit"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </Link>
                                                <button
                                                    v-if="$page.props.auth.can.deleteDocument"
                                                    @click="confirmDelete(document)"
                                                    class="text-red-600 hover:text-red-900"
                                                    title="Hapus"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="documents.data.length > 0" class="mt-6">
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-700">
                                    Menampilkan {{ documents.from }} sampai {{ documents.to }} dari {{ documents.total }} dokumen
                                </div>
                                <div class="flex space-x-2">
                                    <Link
                                        v-for="link in documents.links"
                                        :key="link.label"
                                        :href="link.url"
                                        :class="[
                                            'px-3 py-2 text-sm rounded-md',
                                            link.active
                                                ? 'bg-blue-600 text-white'
                                                : link.url
                                                ? 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
                                                : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                        ]"
                                        v-html="link.label"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Konfirmasi Hapus</h3>
                <p class="text-sm text-gray-600 mb-6">
                    Apakah Anda yakin ingin menghapus dokumen "{{ documentToDelete?.name }}"? Tindakan ini tidak dapat dibatalkan.
                </p>
                <div class="flex justify-end space-x-3">
                    <SecondaryButton @click="showDeleteModal = false">Batal</SecondaryButton>
                    <DangerButton @click="deleteDocument">Hapus</DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    documents: Object,
    filters: Object,
    categories: Array,
});

const searchForm = reactive({
    search: props.filters.search || '',
    category: props.filters.category || '',
});

const showDeleteModal = ref(false);
const documentToDelete = ref(null);

let searchTimeout = null;

const debounceSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
};

const applyFilters = () => {
    router.get(route('documents.index'), searchForm, {
        preserveState: true,
        preserveScroll: true,
    });
};

const confirmDelete = (document) => {
    documentToDelete.value = document;
    showDeleteModal.value = true;
};

const deleteDocument = () => {
    router.delete(route('documents.destroy', documentToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            documentToDelete.value = null;
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

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>
