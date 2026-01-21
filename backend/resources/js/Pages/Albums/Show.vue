<script setup>
import { ref } from "vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import DangerButton from "@/Components/DangerButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import Modal from "@/Components/Modal.vue";

const props = defineProps({
    album: Object,
});

const deleteForm = useForm({});
const uploadForm = useForm({
    photos: [],
    descriptions: [],
});

const showDeleteModal = ref(false);
const showUploadModal = ref(false);
const showPhotoModal = ref(false);
const selectedPhoto = ref(null);
const photoFiles = ref([]);
const photoPreviews = ref([]);

const confirmDelete = () => {
    showDeleteModal.value = true;
};

const deleteAlbum = () => {
    deleteForm.delete(route("albums.destroy", props.album), {
        onSuccess: () => {
            showDeleteModal.value = false;
        },
    });
};

const openUploadModal = () => {
    showUploadModal.value = true;
    photoFiles.value = [];
    photoPreviews.value = [];
};

const handlePhotoSelect = (event) => {
    const files = Array.from(event.target.files);
    photoFiles.value = files;

    // Create previews
    photoPreviews.value = [];
    files.forEach((file) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            photoPreviews.value.push({
                url: e.target.result,
                name: file.name,
                description: "",
            });
        };
        reader.readAsDataURL(file);
    });
};

const removePreview = (index) => {
    photoPreviews.value.splice(index, 1);
    const dt = new DataTransfer();
    photoFiles.value.forEach((file, i) => {
        if (i !== index) dt.items.add(file);
    });
    photoFiles.value = Array.from(dt.files);
    document.getElementById("photos").files = dt.files;
};

const uploadPhotos = () => {
    uploadForm.photos = photoFiles.value;
    uploadForm.descriptions = photoPreviews.value.map((p) => p.description);

    uploadForm.post(route("albums.photos.upload", props.album), {
        onSuccess: () => {
            showUploadModal.value = false;
            photoFiles.value = [];
            photoPreviews.value = [];
        },
    });
};

const viewPhoto = (photo) => {
    selectedPhoto.value = photo;
    showPhotoModal.value = true;
};

const closePhotoModal = () => {
    showPhotoModal.value = false;
    selectedPhoto.value = null;
};

const deletePhoto = (photo) => {
    if (confirm("Apakah Anda yakin ingin menghapus foto ini?")) {
        router.delete(route("albums.photos.destroy", [props.album, photo]), {
            preserveScroll: true,
        });
    }
};

const downloadPhoto = (photo) => {
    window.open(route("photos.download", photo), "_blank");
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("id-ID", {
        day: "numeric",
        month: "long",
        year: "numeric",
    });
};

const getCategoryLabel = (category) => {
    const labels = {
        event: "Event",
        routine: "Kegiatan Rutin",
        official: "Dokumentasi Resmi",
        other: "Lainnya",
    };
    return labels[category] || category;
};
</script>

<template>
    <Head :title="album.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2
                        class="text-xl font-semibold leading-tight text-gray-800"
                    >
                        {{ album.name }}
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">
                        {{ getCategoryLabel(album.category) }} •
                        {{ album.status === "public" ? "Publik" : "Privat" }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link
                        v-if="hasPermission('manage_albums')"
                        :href="route('albums.edit', album)"
                        class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-3 text-xs font-bold uppercase tracking-widest text-gray-700 shadow-sm transition duration-200 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25"
                    >
                        Edit Album
                    </Link>
                    <button
                        v-if="hasPermission('manage_albums')"
                        @click="confirmDelete"
                        class="inline-flex items-center justify-center rounded-xl border border-transparent bg-red-600 px-4 py-3 text-xs font-bold uppercase tracking-widest text-white transition duration-200 ease-in-out hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 active:bg-red-800 shadow-md shadow-red-100"
                    >
                        Hapus Album
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Album Info -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Cover Image -->
                        <div v-if="album.cover_image" class="md:col-span-1">
                            <img
                                :src="`/storage/${album.cover_image}`"
                                :alt="album.name"
                                class="w-full h-64 object-cover rounded-lg"
                            />
                        </div>

                        <!-- Details -->
                        <div
                            :class="
                                album.cover_image
                                    ? 'md:col-span-2'
                                    : 'md:col-span-3'
                            "
                        >
                            <dl class="space-y-4">
                                <div v-if="album.description">
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Deskripsi
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ album.description }}
                                    </dd>
                                </div>
                                <div v-if="album.event">
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Event Terkait
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <Link
                                            :href="
                                                route(
                                                    'events.show',
                                                    album.event,
                                                )
                                            "
                                            class="text-indigo-600 hover:text-indigo-900"
                                        >
                                            {{ album.event.name }}
                                        </Link>
                                    </dd>
                                </div>
                                <div>
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Jumlah Foto
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ album.photos.length }} foto
                                    </dd>
                                </div>
                                <div>
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Dibuat oleh
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ album.creator.name }} pada
                                        {{ formatDate(album.created_at) }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Photos Section -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Foto Album
                        </h3>
                        <button
                            v-if="hasPermission('manage_albums')"
                            @click="openUploadModal"
                            class="inline-flex items-center justify-center rounded-xl border border-transparent bg-indigo-600 px-4 py-3 text-xs font-bold uppercase tracking-widest text-white transition duration-200 ease-in-out hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 shadow-md shadow-indigo-100"
                        >
                            <svg
                                class="w-4 h-4 mr-1"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 4v16m8-8H4"
                                />
                            </svg>
                            Upload Foto
                        </button>
                    </div>

                    <!-- Photo Grid -->
                    <div
                        v-if="album.photos.length > 0"
                        class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4"
                    >
                        <div
                            v-for="photo in album.photos"
                            :key="photo.id"
                            class="relative group cursor-pointer"
                            @click="viewPhoto(photo)"
                        >
                            <img
                                :src="`/storage/${photo.file_path}`"
                                :alt="photo.description || 'Photo'"
                                class="w-full h-48 object-cover rounded-lg"
                            />
                            <div
                                class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-opacity rounded-lg flex items-center justify-center"
                            >
                                <div
                                    class="opacity-0 group-hover:opacity-100 flex gap-2"
                                >
                                    <button
                                        @click.stop="downloadPhoto(photo)"
                                        class="p-2 bg-white rounded-full hover:bg-gray-100"
                                        title="Download"
                                    >
                                        <svg
                                            class="w-5 h-5 text-gray-700"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                                            />
                                        </svg>
                                    </button>
                                    <button
                                        v-if="hasPermission('manage_albums')"
                                        @click.stop="deletePhoto(photo)"
                                        class="p-2 bg-red-500 rounded-full hover:bg-red-600"
                                        title="Hapus"
                                    >
                                        <svg
                                            class="w-5 h-5 text-white"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="text-center py-12">
                        <svg
                            class="mx-auto h-12 w-12 text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                            />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">
                            Belum ada foto
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Mulai dengan mengupload foto pertama.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Hapus Album</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Apakah Anda yakin ingin menghapus album "{{ album.name }}"?
                    Semua foto dalam album ini juga akan dihapus. Tindakan ini
                    tidak dapat dibatalkan.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showDeleteModal = false">
                        Batal
                    </SecondaryButton>
                    <DangerButton
                        @click="deleteAlbum"
                        :class="{ 'opacity-25': deleteForm.processing }"
                        :disabled="deleteForm.processing"
                    >
                        Hapus Album
                    </DangerButton>
                </div>
            </div>
        </Modal>

        <!-- Upload Photos Modal -->
        <Modal
            :show="showUploadModal"
            @close="showUploadModal = false"
            max-width="3xl"
        >
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    Upload Foto
                </h2>

                <div class="space-y-4">
                    <div>
                        <input
                            id="photos"
                            type="file"
                            accept="image/jpeg,image/jpg,image/png"
                            multiple
                            @change="handlePhotoSelect"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                        />
                        <p class="mt-1 text-xs text-gray-500">
                            Format: JPG, JPEG, PNG. Maksimal 5MB per file. Pilih
                            multiple files.
                        </p>
                    </div>

                    <!-- Photo Previews -->
                    <div
                        v-if="photoPreviews.length > 0"
                        class="space-y-4 max-h-96 overflow-y-auto"
                    >
                        <div
                            v-for="(preview, index) in photoPreviews"
                            :key="index"
                            class="flex gap-4 p-3 border rounded-lg"
                        >
                            <img
                                :src="preview.url"
                                :alt="preview.name"
                                class="w-24 h-24 object-cover rounded"
                            />
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">
                                    {{ preview.name }}
                                </p>
                                <textarea
                                    v-model="preview.description"
                                    placeholder="Deskripsi foto (opsional)"
                                    rows="2"
                                    class="mt-2 w-full text-sm border-gray-300 rounded-md"
                                ></textarea>
                            </div>
                            <button
                                @click="removePreview(index)"
                                class="text-red-600 hover:text-red-800"
                            >
                                <svg
                                    class="w-5 h-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showUploadModal = false">
                        Batal
                    </SecondaryButton>
                    <PrimaryButton
                        @click="uploadPhotos"
                        :class="{
                            'opacity-25':
                                uploadForm.processing ||
                                photoPreviews.length === 0,
                        }"
                        :disabled="
                            uploadForm.processing || photoPreviews.length === 0
                        "
                    >
                        Upload {{ photoPreviews.length }} Foto
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- View Photo Modal -->
        <Modal :show="showPhotoModal" @close="closePhotoModal" max-width="4xl">
            <div v-if="selectedPhoto" class="p-6">
                <img
                    :src="`/storage/${selectedPhoto.file_path}`"
                    :alt="selectedPhoto.description || 'Photo'"
                    class="w-full h-auto max-h-[70vh] object-contain rounded-lg"
                />
                <div v-if="selectedPhoto.description" class="mt-4">
                    <p class="text-sm text-gray-600">
                        {{ selectedPhoto.description }}
                    </p>
                </div>
                <div
                    class="mt-4 flex justify-between items-center text-sm text-gray-500"
                >
                    <span>Diupload oleh {{ selectedPhoto.uploader.name }}</span>
                    <span>{{ formatDate(selectedPhoto.created_at) }}</span>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
