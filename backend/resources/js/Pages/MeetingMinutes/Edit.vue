<script setup>
import { ref, computed } from "vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import QuillEditor from "@/Components/QuillEditor.vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Checkbox } from "@/components/ui/checkbox";
import InputError from "@/Components/InputError.vue";
import { ArrowLeft, Loader2, Upload, FileText, X } from "lucide-vue-next";

const props = defineProps({
    minute: Object,
    members: Array,
});

const form = useForm({
    meeting_date: props.minute.meeting_date ? props.minute.meeting_date : "",
    agenda: props.minute.agenda || "",
    participants: props.minute.participants || [],
    notes: props.minute.notes || "",
});

const saving = ref(false);

const save = () => {
    saving.value = true;
    form.put(route("meeting-minutes.update", props.minute.id), {
        onFinish: () => { saving.value = false; },
    });
};

const files = ref([]);
const uploading = ref(false);
const fileInputRef = ref(null);
const isDragging = ref(false);

const handleFileSelect = (e) => {
    files.value = [...files.value, ...Array.from(e.target.files)];
    if (fileInputRef.value) fileInputRef.value.value = '';
};

const handleDrop = (e) => {
    isDragging.value = false;
    files.value = [...files.value, ...Array.from(e.dataTransfer.files)];
};

const upload = () => {
    if (!files.value.length) return;
    const fd = new FormData();
    files.value.forEach((f) => fd.append("files[]", f));
    uploading.value = true;
    router.post(route("meeting-minutes.attachments", props.minute.id), fd, {
        forceFormData: true,
        onFinish: () => { uploading.value = false; files.value = []; },
        onSuccess: () => { router.reload({ only: ["minute"] }); },
    });
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
    <Head title="Edit Notulensi Rapat" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2.5">
                <Button variant="ghost" size="icon" class="h-8 w-8 shrink-0" as-child>
                    <Link :href="route('meeting-minutes.index')">
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                </Button>
                <h2 class="text-lg font-semibold leading-tight text-foreground">Edit Notulensi Rapat</h2>
            </div>
        </template>

        <div class="py-3 sm:py-6">
            <div class="max-w-3xl mx-auto px-3 sm:px-6 lg:px-8">
                <div class="bg-card rounded-xl border overflow-hidden">
                    <div class="h-1 w-full bg-primary" />

                    <form @submit.prevent="save" class="p-4 sm:p-6 space-y-4 sm:space-y-5">
                        <!-- Date & Agenda -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                            <div>
                                <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Tanggal Rapat</Label>
                                <Input v-model="form.meeting_date" type="date" class="mt-1.5" />
                                <InputError class="mt-1" :message="form.errors.meeting_date" />
                            </div>
                            <div>
                                <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Agenda</Label>
                                <Input v-model="form.agenda" type="text" class="mt-1.5" placeholder="Agenda rapat..." />
                                <InputError class="mt-1" :message="form.errors.agenda" />
                            </div>
                        </div>

                        <!-- Participants -->
                        <div>
                            <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Peserta</Label>
                            <div class="mt-1.5 space-y-2">
                                <Input v-model="search" type="text" placeholder="Cari anggota..." />
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-48 overflow-auto border rounded-lg p-2.5">
                                    <label v-for="m in filtered" :key="m.id" class="inline-flex items-center gap-2 text-sm cursor-pointer">
                                        <Checkbox
                                            :checked="form.participants.includes(m.id)"
                                            @update:checked="toggleParticipant(m.id, $event)"
                                        />
                                        <span>{{ m.full_name }}</span>
                                    </label>
                                </div>
                                <p class="text-[10px] sm:text-xs text-muted-foreground">Pilih satu atau lebih anggota sebagai peserta rapat.</p>
                                <InputError :message="form.errors.participants" />
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Hasil Rapat</Label>
                            <div class="mt-1.5">
                                <QuillEditor v-model="form.notes" placeholder="Tulis hasil rapat..." />
                            </div>
                        </div>

                        <div class="border-t" />

                        <!-- Attachments -->
                        <div>
                            <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Lampiran</Label>
                            <div class="mt-1.5 space-y-3">
                                <!-- Existing attachments -->
                                <div v-if="(props.minute.attachments || []).length > 0" class="space-y-1.5">
                                    <p class="text-[10px] sm:text-xs font-medium text-muted-foreground uppercase">File yang sudah ada:</p>
                                    <div v-for="att in props.minute.attachments" :key="att.id" class="flex items-center gap-2.5 p-2 sm:p-2.5 bg-muted/50 rounded-lg">
                                        <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                                            <FileText class="w-4 h-4 text-primary" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs sm:text-sm font-medium text-foreground truncate">{{ att.original_name }}</p>
                                            <p v-if="att.size" class="text-[10px] sm:text-xs text-muted-foreground">{{ formatFileSize(att.size) }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Upload new files -->
                                <div
                                    @dragover.prevent="isDragging = true"
                                    @dragleave.prevent="isDragging = false"
                                    @drop.prevent="handleDrop($event)"
                                    @click="fileInputRef?.click()"
                                    class="relative border-2 border-dashed rounded-xl p-4 sm:p-5 text-center cursor-pointer transition-all duration-200"
                                    :class="isDragging ? 'border-primary bg-primary/5 scale-[1.01]' : 'border-border hover:border-primary/50 hover:bg-muted/30'"
                                >
                                    <input ref="fileInputRef" type="file" multiple class="hidden" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif" @change="handleFileSelect($event)" />
                                    <Upload class="mx-auto h-7 w-7 sm:h-8 sm:w-8 text-muted-foreground mb-1.5" />
                                    <p class="text-xs sm:text-sm font-medium text-primary">Tambah Lampiran Baru</p>
                                    <p class="text-[10px] sm:text-xs text-muted-foreground mt-0.5">Drag & drop atau klik. PDF, DOC, XLS, JPG, PNG</p>
                                </div>

                                <div v-if="files.length > 0" class="space-y-1.5">
                                    <div class="flex items-center justify-between">
                                        <p class="text-xs sm:text-sm font-medium text-foreground">{{ files.length }} file baru</p>
                                        <button type="button" @click="files = []" class="text-[10px] sm:text-xs text-destructive hover:underline">Hapus semua</button>
                                    </div>
                                    <div v-for="(file, index) in files" :key="index" class="flex items-center gap-2.5 p-2 sm:p-2.5 bg-primary/5 rounded-lg">
                                        <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                                            <FileText class="w-4 h-4 text-primary" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs sm:text-sm font-medium text-foreground truncate">{{ file.name }}</p>
                                            <p class="text-[10px] sm:text-xs text-muted-foreground">{{ formatFileSize(file.size) }}</p>
                                        </div>
                                        <button type="button" @click="files.splice(index, 1)" class="shrink-0 w-6 h-6 bg-destructive/10 text-destructive rounded-full flex items-center justify-center hover:bg-destructive/20">
                                            <X class="w-3 h-3" />
                                        </button>
                                    </div>
                                    <Button @click="upload" :disabled="uploading || !files.length" size="sm" class="w-full">
                                        <Loader2 v-if="uploading" class="w-4 h-4 mr-1 animate-spin" />
                                        {{ uploading ? 'Mengunggah...' : 'Unggah Lampiran' }}
                                    </Button>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="flex items-center justify-end gap-2 pt-2 border-t">
                            <Button variant="outline" size="sm" as-child>
                                <Link :href="route('meeting-minutes.index')">Batal</Link>
                            </Button>
                            <Button size="sm" type="submit" :disabled="saving || !form.agenda || !form.meeting_date">
                                <Loader2 v-if="saving" class="w-4 h-4 mr-1 animate-spin" />
                                {{ saving ? 'Menyimpan...' : 'Simpan Perubahan' }}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
