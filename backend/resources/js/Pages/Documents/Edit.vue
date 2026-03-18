<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                <Button variant="ghost" size="icon" as-child class="mr-4">
                    <Link :href="route('documents.index')">
                        <ArrowLeft class="w-6 h-6" />
                    </Link>
                </Button>
                <h2 class="font-semibold text-xl text-foreground leading-tight">
                    Edit Dokumen
                </h2>
            </div>
        </template>

        <div class="py-6 sm:py-8">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <Card>
                    <CardContent class="p-6">
                        <form @submit.prevent="submit">
                            <!-- Name -->
                            <div class="mb-6">
                                <Label for="name">
                                    Nama Dokumen <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="mt-1"
                                    :class="{ 'border-destructive': form.errors.name }"
                                    required
                                />
                                <p v-if="form.errors.name" class="mt-1 text-sm text-destructive">{{ form.errors.name }}</p>
                            </div>

                            <!-- Category -->
                            <div class="mb-6">
                                <Label for="category">Kategori</Label>
                                <Input
                                    id="category"
                                    v-model="form.category"
                                    type="text"
                                    list="categories"
                                    class="mt-1"
                                    placeholder="Masukkan atau pilih kategori"
                                />
                                <datalist id="categories">
                                    <option v-for="cat in categories" :key="cat" :value="cat" />
                                </datalist>
                                <p class="mt-1 text-sm text-muted-foreground">Contoh: Surat Keputusan, Laporan, Proposal, dll.</p>
                                <p v-if="form.errors.category" class="mt-1 text-sm text-destructive">{{ form.errors.category }}</p>
                            </div>

                            <!-- Description -->
                            <div class="mb-6">
                                <Label for="description">Deskripsi</Label>
                                <Textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="4"
                                    class="mt-1"
                                    placeholder="Deskripsi singkat tentang dokumen ini..."
                                />
                                <p v-if="form.errors.description" class="mt-1 text-sm text-destructive">{{ form.errors.description }}</p>
                            </div>

                            <!-- Current File Info -->
                            <div class="mb-6">
                                <Label>File Saat Ini</Label>
                                <div class="flex items-center p-4 bg-muted rounded-md mt-1">
                                    <FileText class="w-8 h-8 text-primary mr-3" />
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-foreground">{{ document.name }}.{{ document.file_type }}</p>
                                        <p class="text-xs text-muted-foreground">{{ formatFileSize(document.file_size) }}</p>
                                    </div>
                                    <a
                                        :href="route('documents.download', document.id)"
                                        class="ml-4 text-primary hover:text-primary/80 text-sm font-medium flex items-center"
                                    >
                                        <Download class="w-4 h-4 mr-1" />
                                        Download
                                    </a>
                                </div>
                            </div>

                            <!-- File Upload (Optional) -->
                            <div class="mb-6">
                                <Label>Ganti File (Opsional)</Label>
                                <div
                                    @dragover.prevent="isDragging = true"
                                    @dragleave.prevent="isDragging = false"
                                    @drop.prevent="handleDrop($event)"
                                    @click="fileInputRef?.click()"
                                    class="mt-1 relative border-2 border-dashed rounded-xl p-6 text-center cursor-pointer transition-all duration-200"
                                    :class="isDragging ? 'border-primary bg-primary/5 scale-[1.01]' : 'border-border hover:border-primary/50 hover:bg-muted/30'"
                                >
                                    <input ref="fileInputRef" type="file" class="hidden" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.rar" @change="handleFileSelect($event)" />
                                    <template v-if="!filePreview">
                                        <Upload class="mx-auto h-10 w-10 text-muted-foreground mb-2" />
                                        <p class="text-sm font-medium text-primary">Unggah file baru</p>
                                        <p class="text-xs text-muted-foreground mt-1">Drag & drop atau klik untuk memilih</p>
                                        <p class="text-xs text-muted-foreground mt-0.5">PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, ZIP, RAR hingga 10MB</p>
                                    </template>
                                    <template v-else>
                                        <div class="flex items-center justify-center gap-3">
                                            <FileText class="h-8 w-8 text-primary shrink-0" />
                                            <div class="text-left">
                                                <p class="text-sm font-medium text-foreground">{{ filePreview.name }}</p>
                                                <p class="text-xs text-muted-foreground">{{ formatFileSize(filePreview.size) }}</p>
                                            </div>
                                            <button type="button" @click.stop="clearFile" class="ml-2 w-6 h-6 bg-destructive text-white rounded-full flex items-center justify-center hover:bg-destructive/80">
                                                <X class="w-3 h-3" />
                                            </button>
                                        </div>
                                        <p class="text-xs text-primary mt-2 font-medium">Klik untuk ganti file</p>
                                    </template>
                                </div>
                                <p v-if="form.errors.file" class="mt-1 text-sm text-destructive">{{ form.errors.file }}</p>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-end space-x-3">
                                <Button variant="outline" as-child>
                                    <Link :href="route('documents.index')">Batal</Link>
                                </Button>
                                <Button type="submit" :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
                                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent } from '@/components/ui/card';
import { Textarea } from '@/components/ui/textarea';
import { ArrowLeft, Upload, FileText, Download, X } from 'lucide-vue-next';

const props = defineProps({
    document: Object,
    categories: Array,
});

const form = useForm({
    name: props.document.name,
    category: props.document.category || '',
    description: props.document.description || '',
    file: null,
});

const filePreview = ref(null);
const fileInputRef = ref(null);
const isDragging = ref(false);

const handleFileSelect = (e) => {
    const file = e.target.files[0];
    if (file) setFile(file);
};

const handleDrop = (e) => {
    isDragging.value = false;
    const file = e.dataTransfer.files[0];
    if (file) setFile(file);
};

const setFile = (file) => {
    form.file = file;
    filePreview.value = { name: file.name, size: file.size };
};

const clearFile = () => {
    form.file = null;
    filePreview.value = null;
    if (fileInputRef.value) fileInputRef.value.value = '';
};

const submit = () => {
    form.post(route('documents.update', props.document.id), {
        _method: 'PUT',
        onSuccess: () => {
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
