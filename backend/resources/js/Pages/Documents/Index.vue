<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-foreground leading-tight">
                    Manajemen Dokumen
                </h2>
                <Button v-if="$page.props.auth.can.createDocument" as-child>
                    <Link :href="route('documents.create')">
                        <Plus class="w-4 h-4 mr-2" />
                        Unggah Dokumen
                    </Link>
                </Button>
            </div>
        </template>

        <div class="py-6 sm:py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Search and Filter -->
                <Card class="mb-6">
                    <CardContent class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">Cari Dokumen</label>
                                <Input
                                    v-model="searchForm.search"
                                    type="text"
                                    placeholder="Cari berdasarkan nama, deskripsi, atau kategori..."
                                    @input="debounceSearch"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-foreground mb-2">Kategori</label>
                                <Select v-model="searchForm.category" @update:model-value="applyFilters">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Semua Kategori" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">Semua Kategori</SelectItem>
                                        <SelectItem v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Documents List -->
                <Card>
                    <CardContent class="p-6">
                        <div v-if="documents.data.length === 0" class="text-center py-12">
                            <FileText class="mx-auto h-12 w-12 text-muted-foreground" />
                            <h3 class="mt-2 text-sm font-medium text-foreground">Tidak ada dokumen</h3>
                            <p class="mt-1 text-sm text-muted-foreground">Mulai dengan mengunggah dokumen baru.</p>
                        </div>

                        <div v-else class="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Nama Dokumen</TableHead>
                                        <TableHead>Kategori</TableHead>
                                        <TableHead>Ukuran</TableHead>
                                        <TableHead>Diunggah Oleh</TableHead>
                                        <TableHead>Tanggal</TableHead>
                                        <TableHead class="text-right">Aksi</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <tr v-for="document in documents.data" :key="document.id" class="hover:bg-muted">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-lg bg-primary/10 flex items-center justify-center">
                                                        <FileText class="h-6 w-6 text-primary" />
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-foreground">{{ document.name }}</div>
                                                    <div class="text-sm text-muted-foreground">{{ document.file_type?.toUpperCase() }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <Badge v-if="document.category" variant="secondary">
                                                {{ document.category }}
                                            </Badge>
                                            <span v-else class="text-sm text-muted-foreground">-</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                                            {{ formatFileSize(document.file_size) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                                            {{ document.uploader?.name || '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                                            {{ formatDate(document.created_at) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end space-x-2">
                                                <Button variant="ghost" size="icon" as-child>
                                                    <a :href="route('documents.download', document.id)" title="Download">
                                                        <Download class="w-4 h-4" />
                                                    </a>
                                                </Button>
                                                <Button v-if="$page.props.auth.can.updateDocument" variant="ghost" size="icon" as-child>
                                                    <Link :href="route('documents.edit', document.id)" title="Edit">
                                                        <Pencil class="w-4 h-4" />
                                                    </Link>
                                                </Button>
                                                <Button
                                                    v-if="$page.props.auth.can.deleteDocument"
                                                    variant="ghost"
                                                    size="icon"
                                                    class="text-destructive hover:text-destructive"
                                                    @click="confirmDelete(document)"
                                                >
                                                    <Trash2 class="w-4 h-4" />
                                                </Button>
                                            </div>
                                        </td>
                                    </tr>
                                </TableBody>
                            </Table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="documents.data.length > 0" class="mt-6">
                            <Pagination :links="documents.links" />
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <DeleteConfirmDialog
            :open="showDeleteModal"
            title="Hapus Dokumen"
            :description="`Apakah Anda yakin ingin menghapus dokumen '${documentToDelete?.name}'? Tindakan ini tidak dapat dibatalkan.`"
            @confirm="deleteDocument"
            @cancel="showDeleteModal = false; documentToDelete = null"
        />
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/components/ui/button';
import DeleteConfirmDialog from '@/Components/DeleteConfirmDialog.vue';
import { Input } from '@/components/ui/input';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import Pagination from '@/Components/Pagination.vue';
import { Plus, Download, Pencil, Trash2, FileText } from 'lucide-vue-next';
import { useToast } from '@/composables/useToast';

const toast = useToast();

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
        month: 'short',
        day: 'numeric',
    });
};
</script>
