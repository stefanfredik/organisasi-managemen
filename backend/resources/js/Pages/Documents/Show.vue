<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="route('documents.index')" class="text-muted-foreground hover:text-foreground">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-foreground">Detail Dokumen</h2>
                </div>
                <div class="flex gap-2">
                    <Link
                        v-if="$page.props.auth.can.updateDocument"
                        :href="route('documents.edit', document.id)"
                        class="inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary/90 active:bg-primary/80 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                    </Link>
                    <button
                        v-if="$page.props.auth.can.deleteDocument"
                        @click="confirmDelete"
                        class="inline-flex items-center px-4 py-2 bg-destructive border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-destructive/90 active:bg-danger-900 focus:outline-none focus:ring-2 focus:ring-danger-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                    <Link
                        :href="route('documents.download', document.id)"
                        class="inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary/90 active:bg-primary/80 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6 sm:py-8">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <Card>
                    <CardContent class="p-6">
                        <!-- Document Preview -->
                        <div class="flex items-start mb-6 pb-6 border-b">
                            <div class="flex-shrink-0 h-20 w-20">
                                <div class="h-20 w-20 rounded-lg bg-primary/10 flex items-center justify-center">
                                    <FileText class="h-12 w-12 text-primary" />
                                </div>
                            </div>
                            <div class="ml-6 flex-1">
                                <h3 class="text-2xl font-bold text-foreground mb-2">{{ document.name }}</h3>
                                <div class="flex flex-wrap gap-2 mb-3">
                                    <Badge v-if="document.category" variant="secondary">
                                        {{ document.category }}
                                    </Badge>
                                    <Badge variant="outline">
                                        {{ document.file_type?.toUpperCase() }}
                                    </Badge>
                                </div>
                                <p v-if="document.description" class="text-foreground leading-relaxed">
                                    {{ document.description }}
                                </p>
                            </div>
                        </div>

                        <!-- Document Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="text-sm font-medium text-muted-foreground mb-2">Informasi File</h4>
                                <dl class="space-y-3">
                                    <div>
                                        <dt class="text-sm font-medium text-muted-foreground">Nama File</dt>
                                        <dd class="mt-1 text-sm text-foreground">{{ document.name }}.{{ document.file_type }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-muted-foreground">Ukuran File</dt>
                                        <dd class="mt-1 text-sm text-foreground">{{ formatFileSize(document.file_size) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-muted-foreground">Tipe File</dt>
                                        <dd class="mt-1 text-sm text-foreground">{{ document.file_type?.toUpperCase() }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-muted-foreground mb-2">Informasi Upload</h4>
                                <dl class="space-y-3">
                                    <div>
                                        <dt class="text-sm font-medium text-muted-foreground">Diunggah Oleh</dt>
                                        <dd class="mt-1 text-sm text-foreground">{{ document.uploader?.name || '-' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-muted-foreground">Tanggal Upload</dt>
                                        <dd class="mt-1 text-sm text-foreground">{{ formatDate(document.created_at) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-muted-foreground">Terakhir Diperbarui</dt>
                                        <dd class="mt-1 text-sm text-foreground">{{ formatDate(document.updated_at) }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <DeleteConfirmDialog
            :open="showDeleteModal"
            title="Hapus Dokumen"
            :description="`Apakah Anda yakin ingin menghapus dokumen '${document.name}'? Tindakan ini tidak dapat dibatalkan.`"
            @confirm="deleteDocument"
            @cancel="showDeleteModal = false"
        />
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import DeleteConfirmDialog from '@/Components/DeleteConfirmDialog.vue';
import { Download, Pencil, Trash2, FileText } from 'lucide-vue-next';
import { useToast } from '@/composables/useToast';

const toast = useToast();

const props = defineProps({
    document: Object,
});

const showDeleteModal = ref(false);

const confirmDelete = () => {
    showDeleteModal.value = true;
};

const deleteDocument = () => {
    router.delete(route('documents.destroy', props.document.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
        },
        onError: (errors) => toast.error(Object.values(errors).flat().join(', ') || 'Gagal menghapus dokumen.'),
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
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>
