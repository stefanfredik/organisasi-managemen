<script setup>
import { ref } from "vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import MultipleImageUpload from "@/Components/MultipleImageUpload.vue";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter,
} from "@/components/ui/dialog";
import {
    DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
    ArrowLeft, Pencil, Trash2, Upload, Download, ImageIcon,
    Calendar, User, Tag, Eye, ChevronLeft, ChevronRight, X,
    MoreVertical,
} from "lucide-vue-next";
import DeleteConfirmDialog from '@/Components/DeleteConfirmDialog.vue';
import { useToast } from '@/composables/useToast';

const toast = useToast();

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
const selectedPhotoIndex = ref(0);
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
        onError: (errors) => toast.error(Object.values(errors).flat().join(', ') || 'Gagal menghapus album.'),
    });
};

const openUploadModal = () => {
    showUploadModal.value = true;
    photoFiles.value = [];
    photoPreviews.value = [];
};

const handlePhotosChange = (files) => {
    photoFiles.value = files;
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

const viewPhoto = (photo, index) => {
    selectedPhoto.value = photo;
    selectedPhotoIndex.value = index;
    showPhotoModal.value = true;
};

const closePhotoModal = () => {
    showPhotoModal.value = false;
    selectedPhoto.value = null;
};

const prevPhoto = () => {
    if (selectedPhotoIndex.value > 0) {
        selectedPhotoIndex.value--;
        selectedPhoto.value = props.album.photos[selectedPhotoIndex.value];
    }
};

const nextPhoto = () => {
    if (selectedPhotoIndex.value < props.album.photos.length - 1) {
        selectedPhotoIndex.value++;
        selectedPhoto.value = props.album.photos[selectedPhotoIndex.value];
    }
};

const deletePhotoTarget = ref(null);
const deletePhoto = (photo) => {
    deletePhotoTarget.value = photo;
};
const confirmDeletePhoto = () => {
    if (deletePhotoTarget.value) {
        router.delete(route("albums.photos.destroy", [props.album, deletePhotoTarget.value]), {
            preserveScroll: true,
            onError: (errors) => toast.error(Object.values(errors).flat().join(', ') || 'Gagal menghapus foto.'),
            onFinish: () => (deletePhotoTarget.value = null),
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

const getCategoryVariant = (cat) => {
    const map = { event: 'default', routine: 'success', official: 'info', other: 'secondary' };
    return map[cat] || 'secondary';
};
</script>

<template>
    <Head :title="album.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <Link :href="route('albums.index')" class="shrink-0 p-1.5 rounded-lg text-muted-foreground hover:text-foreground hover:bg-muted transition-colors">
                        <ArrowLeft class="w-5 h-5" />
                    </Link>
                    <h2 class="text-lg font-semibold leading-tight text-foreground truncate">{{ album.name }}</h2>
                </div>
                <DropdownMenu v-if="hasPermission('manage_albums')">
                    <DropdownMenuTrigger as-child>
                        <Button variant="outline" size="sm">
                            <MoreVertical class="w-4 h-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuItem as-child>
                            <Link :href="route('albums.edit', album)" class="flex items-center gap-2">
                                <Pencil class="w-4 h-4" />
                                Edit Album
                            </Link>
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem @click="confirmDelete" class="text-destructive flex items-center gap-2">
                            <Trash2 class="w-4 h-4" />
                            Hapus Album
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </template>

        <div class="py-4 sm:py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">

                <!-- Album Info Card -->
                <div class="bg-card rounded-xl border overflow-hidden">
                    <div class="flex flex-col md:flex-row">
                        <!-- Cover Image -->
                        <div v-if="album.cover_image" class="md:w-80 lg:w-96 shrink-0">
                            <img
                                :src="`/storage/${album.cover_image}`"
                                :alt="album.name"
                                class="w-full h-48 md:h-full object-cover"
                            />
                        </div>

                        <!-- Details -->
                        <div class="flex-1 p-4 sm:p-6">
                            <div class="flex flex-wrap items-center gap-2 mb-3">
                                <Badge :variant="getCategoryVariant(album.category)">
                                    {{ getCategoryLabel(album.category) }}
                                </Badge>
                                <Badge :variant="album.status === 'public' ? 'success' : 'warning'">
                                    {{ album.status === "public" ? "Publik" : "Privat" }}
                                </Badge>
                            </div>

                            <p v-if="album.description" class="text-sm text-muted-foreground mb-4">
                                {{ album.description }}
                            </p>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div v-if="album.event" class="flex items-center gap-2 text-sm">
                                    <Tag class="w-4 h-4 text-muted-foreground shrink-0" />
                                    <span class="text-muted-foreground">Event:</span>
                                    <Link
                                        :href="route('events.show', album.event)"
                                        class="text-primary hover:text-primary/80 font-medium truncate"
                                    >
                                        {{ album.event.name }}
                                    </Link>
                                </div>
                                <div class="flex items-center gap-2 text-sm">
                                    <ImageIcon class="w-4 h-4 text-muted-foreground shrink-0" />
                                    <span class="text-foreground font-medium">{{ album.photos.length }} foto</span>
                                </div>
                                <div class="flex items-center gap-2 text-sm">
                                    <User class="w-4 h-4 text-muted-foreground shrink-0" />
                                    <span class="text-muted-foreground">{{ album.creator.name }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-sm">
                                    <Calendar class="w-4 h-4 text-muted-foreground shrink-0" />
                                    <span class="text-muted-foreground">{{ formatDate(album.created_at) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Photos Section -->
                <div class="bg-card rounded-xl border overflow-hidden">
                    <div class="flex items-center justify-between p-4 sm:p-5 border-b">
                        <h3 class="text-sm font-semibold text-foreground">Foto Album</h3>
                        <Button
                            v-if="hasPermission('manage_albums')"
                            size="sm"
                            @click="openUploadModal"
                        >
                            <Upload class="w-3.5 h-3.5 mr-1.5" />
                            Upload Foto
                        </Button>
                    </div>

                    <!-- Photo Grid -->
                    <div v-if="album.photos.length > 0" class="p-3 sm:p-4">
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-2 sm:gap-3">
                            <div
                                v-for="(photo, index) in album.photos"
                                :key="photo.id"
                                class="group relative aspect-square rounded-lg overflow-hidden cursor-pointer bg-muted"
                                @click="viewPhoto(photo, index)"
                            >
                                <img
                                    :src="`/storage/${photo.file_path}`"
                                    :alt="photo.description || 'Photo'"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                />
                                <!-- Hover overlay -->
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-all duration-200 flex items-center justify-center">
                                    <div class="opacity-0 group-hover:opacity-100 flex items-center gap-2 transition-opacity">
                                        <button
                                            @click.stop="downloadPhoto(photo)"
                                            class="p-2 rounded-full bg-white/90 hover:bg-white text-foreground shadow-sm transition-colors"
                                            title="Download"
                                        >
                                            <Download class="w-4 h-4" />
                                        </button>
                                        <button
                                            v-if="hasPermission('manage_albums')"
                                            @click.stop="deletePhoto(photo)"
                                            class="p-2 rounded-full bg-red-500/90 hover:bg-red-500 text-white shadow-sm transition-colors"
                                            title="Hapus"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                                <!-- Description tooltip -->
                                <div v-if="photo.description" class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <p class="text-[10px] text-white truncate">{{ photo.description }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="flex flex-col items-center justify-center py-16 text-center px-4">
                        <div class="w-16 h-16 rounded-2xl bg-muted flex items-center justify-center mb-4">
                            <ImageIcon class="w-8 h-8 text-muted-foreground" />
                        </div>
                        <p class="text-sm font-medium text-foreground mb-1">Belum ada foto</p>
                        <p class="text-xs text-muted-foreground mb-4">Mulai dengan mengupload foto pertama.</p>
                        <Button v-if="hasPermission('manage_albums')" size="sm" @click="openUploadModal">
                            <Upload class="w-3.5 h-3.5 mr-1.5" />
                            Upload Foto
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Dialog -->
        <DeleteConfirmDialog
            :open="showDeleteModal"
            title="Hapus Album"
            description="Apakah Anda yakin ingin menghapus album ini? Semua foto dalam album juga akan dihapus. Tindakan ini tidak dapat dibatalkan."
            @confirm="deleteAlbum"
            @cancel="showDeleteModal = false"
        />

        <!-- Upload Photos Dialog -->
        <Dialog v-model:open="showUploadModal">
            <DialogContent class="sm:max-w-2xl">
                <DialogHeader>
                    <DialogTitle>Upload Foto</DialogTitle>
                    <DialogDescription>Tambahkan foto ke album {{ album.name }}</DialogDescription>
                </DialogHeader>

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
                    <div v-if="photoPreviews.length > 0" class="space-y-2 max-h-48 overflow-y-auto">
                        <div
                            v-for="(preview, index) in photoPreviews"
                            :key="index"
                            class="flex items-center gap-3"
                        >
                            <span class="text-xs text-muted-foreground w-14 shrink-0">Foto {{ index + 1 }}</span>
                            <input
                                v-model="preview.description"
                                type="text"
                                placeholder="Deskripsi foto (opsional)"
                                class="flex-1 text-sm border border-input bg-background rounded-md px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-ring"
                            />
                        </div>
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="showUploadModal = false">Batal</Button>
                    <Button
                        @click="uploadPhotos"
                        :disabled="uploadForm.processing || photoPreviews.length === 0"
                    >
                        <Upload class="w-3.5 h-3.5 mr-1.5" />
                        Upload {{ photoPreviews.length }} Foto
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Photo Lightbox Dialog -->
        <Dialog v-model:open="showPhotoModal">
            <DialogContent class="sm:max-w-4xl p-0 gap-0 overflow-hidden bg-black/95 border-none">
                <DialogHeader class="sr-only">
                    <DialogTitle>Lihat Foto</DialogTitle>
                    <DialogDescription>Preview foto album</DialogDescription>
                </DialogHeader>

                <div v-if="selectedPhoto" class="relative">
                    <!-- Close button -->
                    <button
                        @click="closePhotoModal"
                        class="absolute top-3 right-3 z-10 p-2 rounded-full bg-black/50 text-white hover:bg-black/70 transition-colors"
                    >
                        <X class="w-5 h-5" />
                    </button>

                    <!-- Navigation prev -->
                    <button
                        v-if="selectedPhotoIndex > 0"
                        @click="prevPhoto"
                        class="absolute left-3 top-1/2 -translate-y-1/2 z-10 p-2 rounded-full bg-black/50 text-white hover:bg-black/70 transition-colors"
                    >
                        <ChevronLeft class="w-5 h-5" />
                    </button>

                    <!-- Navigation next -->
                    <button
                        v-if="selectedPhotoIndex < album.photos.length - 1"
                        @click="nextPhoto"
                        class="absolute right-3 top-1/2 -translate-y-1/2 z-10 p-2 rounded-full bg-black/50 text-white hover:bg-black/70 transition-colors"
                    >
                        <ChevronRight class="w-5 h-5" />
                    </button>

                    <!-- Image -->
                    <img
                        :src="`/storage/${selectedPhoto.file_path}`"
                        :alt="selectedPhoto.description || 'Photo'"
                        class="w-full max-h-[75vh] object-contain"
                    />

                    <!-- Info bar -->
                    <div class="p-4 bg-black/80">
                        <div class="flex items-center justify-between gap-4">
                            <div class="min-w-0">
                                <p v-if="selectedPhoto.description" class="text-sm text-white truncate">
                                    {{ selectedPhoto.description }}
                                </p>
                                <p class="text-xs text-white/60 mt-0.5">
                                    {{ selectedPhoto.uploader?.name }} &middot; {{ formatDate(selectedPhoto.created_at) }}
                                    &middot; {{ selectedPhotoIndex + 1 }}/{{ album.photos.length }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <button
                                    @click="downloadPhoto(selectedPhoto)"
                                    class="p-2 rounded-lg text-white/70 hover:text-white hover:bg-white/10 transition-colors"
                                    title="Download"
                                >
                                    <Download class="w-4 h-4" />
                                </button>
                                <button
                                    v-if="hasPermission('manage_albums')"
                                    @click="deletePhoto(selectedPhoto)"
                                    class="p-2 rounded-lg text-white/70 hover:text-red-400 hover:bg-white/10 transition-colors"
                                    title="Hapus"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
        <DeleteConfirmDialog :open="!!deletePhotoTarget" title="Hapus Foto" description="Apakah Anda yakin ingin menghapus foto ini? Tindakan ini tidak dapat dibatalkan." @confirm="confirmDeletePhoto" @cancel="deletePhotoTarget = null" />
    </AuthenticatedLayout>
</template>
