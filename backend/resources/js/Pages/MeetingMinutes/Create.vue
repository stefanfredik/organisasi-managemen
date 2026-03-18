<script setup>
import { ref, computed } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import QuillEditor from "@/Components/QuillEditor.vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card, CardContent } from "@/components/ui/card";
import { Checkbox } from "@/components/ui/checkbox";
import InputError from "@/Components/InputError.vue";
import { FileText, X, Upload } from "lucide-vue-next";

const props = defineProps({
    members: Array,
});

const form = useForm({
    meeting_date: "",
    agenda: "",
    participants: [],
    notes: "",
    files: [],
});

const saving = ref(false);
const selectedFiles = ref([]);

const save = () => {
    saving.value = true;
    form.post(route("meeting-minutes.store"), {
        onFinish: () => { saving.value = false; },
    });
};

const fileInputRef = ref(null);
const isDragging = ref(false);

const handleFileSelect = (e) => {
    addFiles(Array.from(e.target.files));
    if (fileInputRef.value) fileInputRef.value.value = '';
};

const handleDrop = (e) => {
    isDragging.value = false;
    addFiles(Array.from(e.dataTransfer.files));
};

const addFiles = (newFiles) => {
    selectedFiles.value = [...selectedFiles.value, ...newFiles];
    form.files = selectedFiles.value;
};

const clearAllFiles = () => {
    selectedFiles.value = [];
    form.files = [];
    if (fileInputRef.value) fileInputRef.value.value = '';
};

const removeFile = (index) => {
    selectedFiles.value.splice(index, 1);
    form.files = selectedFiles.value;
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const search = ref("");
const filtered = computed(() => {
    const q = search.value.trim().toLowerCase();
    if (!q) return props.members || [];
    return (props.members || []).filter((m) => (m.full_name || "").toLowerCase().includes(q));
});

const toggleParticipant = (id, checked) => {
    if (checked) {
        form.participants.push(id);
    } else {
        form.participants = form.participants.filter(p => p !== id);
    }
};
</script>

<template>
    <Head title="Tambah Notulensi Rapat" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-foreground">Tambah Notulensi Rapat</h2>
        </template>

        <div class="py-6 sm:py-8">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <Card>
                    <CardContent class="p-6 space-y-4">
                        <div class="grid grid-cols-1 gap-4 sm:gap-6 md:grid-cols-2">
                            <div>
                                <Label>Tanggal Rapat</Label>
                                <Input v-model="form.meeting_date" type="date" class="mt-1" />
                            </div>
                            <div>
                                <Label>Agenda</Label>
                                <Input v-model="form.agenda" type="text" class="mt-1" />
                                <InputError class="mt-1" :message="form.errors.agenda" />
                            </div>
                        </div>
                        <div>
                            <Label>Peserta</Label>
                            <div class="mt-2 space-y-2">
                                <Input v-model="search" type="text" placeholder="Cari anggota..." />
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 max-h-48 overflow-auto border rounded-md p-2">
                                    <label v-for="m in filtered" :key="m.id" class="inline-flex items-center gap-2 text-sm">
                                        <Checkbox
                                            :checked="form.participants.includes(m.id)"
                                            @update:checked="toggleParticipant(m.id, $event)"
                                        />
                                        <span>{{ m.full_name }}</span>
                                    </label>
                                </div>
                                <p class="text-xs text-muted-foreground">Pilih satu atau lebih anggota sebagai peserta rapat.</p>
                                <InputError :message="form.errors.participants" />
                            </div>
                        </div>
                        <div>
                            <Label>Hasil Rapat</Label>
                            <div class="mt-1">
                                <QuillEditor v-model="form.notes" placeholder="Tulis hasil rapat..." />
                            </div>
                        </div>

                        <!-- Lampiran Section -->
                        <div class="border-t pt-4">
                            <Label class="mb-2">Lampiran (Opsional)</Label>
                            <div class="space-y-3">
                                <div
                                    @dragover.prevent="isDragging = true"
                                    @dragleave.prevent="isDragging = false"
                                    @drop.prevent="handleDrop($event)"
                                    @click="fileInputRef?.click()"
                                    class="relative border-2 border-dashed rounded-xl p-5 text-center cursor-pointer transition-all duration-200"
                                    :class="isDragging ? 'border-primary bg-primary/5 scale-[1.01]' : 'border-border hover:border-primary/50 hover:bg-muted/30'"
                                >
                                    <input ref="fileInputRef" type="file" multiple class="hidden" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif" @change="handleFileSelect($event)" />
                                    <Upload class="mx-auto h-8 w-8 text-muted-foreground mb-2" />
                                    <p class="text-sm font-medium text-primary">Tambah Lampiran</p>
                                    <p class="text-xs text-muted-foreground mt-1">Drag & drop atau klik. PDF, DOC, XLS, JPG, PNG (Max 10MB/file)</p>
                                </div>

                                <div v-if="selectedFiles.length > 0" class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-medium text-foreground">{{ selectedFiles.length }} file dipilih</p>
                                        <button type="button" @click="clearAllFiles" class="text-xs text-destructive hover:underline">Hapus semua</button>
                                    </div>
                                    <div
                                        v-for="(file, index) in selectedFiles"
                                        :key="index"
                                        class="flex items-center gap-3 p-2.5 bg-muted rounded-lg"
                                    >
                                        <FileText class="w-5 h-5 text-muted-foreground shrink-0" />
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-foreground truncate">{{ file.name }}</p>
                                            <p class="text-xs text-muted-foreground">{{ formatFileSize(file.size) }}</p>
                                        </div>
                                        <button type="button" @click="removeFile(index)" class="shrink-0 w-6 h-6 bg-destructive/10 text-destructive rounded-full flex items-center justify-center hover:bg-destructive/20">
                                            <X class="w-3 h-3" />
                                        </button>
                                    </div>
                                </div>

                                <InputError :message="form.errors.files" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <Button variant="outline" as-child>
                                <Link :href="route('meeting-minutes.index')">Batal</Link>
                            </Button>
                            <Button @click="save" :disabled="saving || !form.agenda || !form.meeting_date">Simpan</Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
