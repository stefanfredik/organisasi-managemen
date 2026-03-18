<script setup>
import { ref } from "vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import MultipleImageUpload from "@/Components/MultipleImageUpload.vue";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent } from "@/components/ui/card";
import {
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter,
} from "@/components/ui/dialog";
import {
    AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle,
} from "@/components/ui/alert-dialog";

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

const handlePhotosChange = (files) => {
    photoFiles.value = files;
    // Sync descriptions array length
    while (photoPreviews.value.length < files.length) {
        photoPreviews.value.push({ description: "" });
    }
    photoPreviews.value = photoPreviews.value.slice(0, files.length);
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
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="route('albums.index')" class="text-muted-foreground hover:text-foreground">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-foreground">{{ album.name }}</h2>
                </div>
                <div class="flex gap-2">
                    <Link
                        v-if="hasPermission('manage_albums')"
                        :href="route('albums.edit', album)"
                        class="inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary/90 active:bg-primary/80 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                    </Link>
                    <button
                        v-if="hasPermission('manage_albums')"
                        @click="confirmDelete"
                        class="inline-flex items-center px-4 py-2 bg-destructive border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-destructive/90 active:bg-danger-900 focus:outline-none focus:ring-2 focus:ring-danger-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1 1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                </div>
            </div>
        </template>

        <div class="py-6 sm:py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Album Info -->
                <div class="bg-card shadow-sm sm:rounded-lg p-6">
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
                                        class="text-sm font-medium text-muted-foreground"
                                    >
                                        Deskripsi
                                    </dt>
                                    <dd class="mt-1 text-sm text-foreground">
                                        {{ album.description }}
                                    </dd>
                                </div>
                                <div v-if="album.event">
                                    <dt
                                        class="text-sm font-medium text-muted-foreground"
                                    >
                                        Event Terkait
                                    </dt>
                                    <dd class="mt-1 text-sm text-foreground">
                                        <Link
                                            :href="
                                                route(
                                                    'events.show',
                                                    album.event,
                                                )
                                            "
                                            class="text-primary hover:text-primary/80"
                                        >
                                            {{ album.event.name }}
                                        </Link>
                                    </dd>
                                </div>
                                <div>
                                    <dt
                                        class="text-sm font-medium text-muted-foreground"
                                    >
                                        Jumlah Foto
                                    </dt>
                                    <dd class="mt-1 text-sm text-foreground">
                                        {{ album.photos.length }} foto
                                    </dd>
                                </div>
                                <div>
                                    <dt
                                        class="text-sm font-medium text-muted-foreground"
                                    >
                                        Dibuat oleh
                                    </dt>
                                    <dd class="mt-1 text-sm text-foreground">
                                        {{ album.creator.name }} pada
                                        {{ formatDate(album.created_at) }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Photos Section -->
                <div class="bg-card shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-foreground">
                            Foto Album
                        </h3>
                        <button
                            v-if="hasPermission('manage_albums')"
                            @click="openUploadModal"
                            class="inline-flex items-center justify-center rounded-xl border border-transparent bg-primary px-4 py-3 text-xs font-bold uppercase tracking-widest text-white transition duration-200 ease-in-out hover:bg-primary/90 active:bg-primary/80 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 shadow-md shadow-sm"
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
                        class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4"
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
                                        class="p-2 bg-card rounded-full hover:bg-muted"
                                        title="Download"
                                    >
                                        <svg
                                            class="w-5 h-5 text-foreground"
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
                                        class="p-2 bg-destructive/100 rounded-full hover:bg-destructive"
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
                            class="mx-auto h-12 w-12 text-muted-foreground"
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
                        <h3 class="mt-2 text-sm font-medium text-foreground">
                            Belum ada foto
                        </h3>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Mulai dengan mengupload foto pertama.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-foreground">Hapus Album</h2>
                <p class="mt-1 text-sm text-muted-foreground">
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
                <h2 class="text-lg font-medium text-foreground mb-4">
                    Upload Foto
                </h2>

                <div class="space-y-4">
                    <MultipleImageUpload
                        :model-value="photoFiles"
                        @update:model-value="handlePhotosChange"
                        label="Upload Foto Album"
                        hint="Format: JPG, JPEG, PNG. Maksimal 5MB per file."
                        :max-size="5120"
                        :max-files="20"
                    />

                    <!-- Photo Descriptions -->
                    <div
                        v-if="photoPreviews.length > 0"
                        class="space-y-3 max-h-48 overflow-y-auto"
                    >
                        <div
                            v-for="(preview, index) in photoPreviews"
                            :key="index"
                            class="flex items-center gap-3"
                        >
                            <span class="text-xs text-muted-foreground w-16 shrink-0">Foto {{ index + 1 }}</span>
                            <input
                                v-model="preview.description"
                                type="text"
                                placeholder="Deskripsi foto (opsional)"
                                class="flex-1 text-sm border border-input rounded-md px-3 py-1.5"
                            />
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
                    <p class="text-sm text-muted-foreground">
                        {{ selectedPhoto.description }}
                    </p>
                </div>
                <div
                    class="mt-4 flex justify-between items-center text-sm text-muted-foreground"
                >
                    <span>Diupload oleh {{ selectedPhoto.uploader.name }}</span>
                    <span>{{ formatDate(selectedPhoto.created_at) }}</span>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
