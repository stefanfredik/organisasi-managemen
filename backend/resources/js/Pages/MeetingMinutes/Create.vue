<script setup>
import { ref, computed } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import QuillEditor from "@/Components/QuillEditor.vue";

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
        onFinish: () => {
            saving.value = false;
        },
    });
};

const handleFileChange = (event) => {
    const files = Array.from(event.target.files);
    selectedFiles.value = files;
    form.files = files;
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
</script>

<template>
    <Head title="Tambah Notulensi Rapat" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Tambah Notulensi Rapat</h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tanggal Rapat</label>
                                <input v-model="form.meeting_date" type="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Agenda</label>
                                <input v-model="form.agenda" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
                                <div v-if="form.errors.agenda" class="text-xs text-red-600 mt-1">{{ form.errors.agenda }}</div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Peserta</label>
                            <div class="mt-2 space-y-2">
                                <input v-model="search" type="text" placeholder="Cari anggota..." class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 max-h-48 overflow-auto border rounded-md p-2">
                                    <label v-for="m in filtered" :key="m.id" class="inline-flex items-center text-sm">
                                        <input type="checkbox" :value="m.id" v-model="form.participants" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                                        <span class="ml-2">{{ m.full_name }}</span>
                                    </label>
                                </div>
                                <p class="text-xs text-gray-400">Pilih satu atau lebih anggota sebagai peserta rapat.</p>
                                <div v-if="form.errors.participants" class="text-xs text-red-600 mt-1">{{ form.errors.participants }}</div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Hasil Rapat</label>
                            <div class="mt-1">
                                <QuillEditor v-model="form.notes" placeholder="Tulis hasil rapat..." />
                            </div>
                        </div>
                        
                        <!-- Lampiran Section -->
                        <div class="border-t pt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Lampiran (Opsional)</label>
                            <div class="space-y-3">
                                <div class="flex items-center gap-2">
                                    <input 
                                        type="file" 
                                        multiple 
                                        @change="handleFileChange"
                                        accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif"
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                    />
                                </div>
                                <p class="text-xs text-gray-500">
                                    Format yang didukung: PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG, GIF (Max 10MB per file)
                                </p>
                                
                                <!-- File Preview List -->
                                <div v-if="selectedFiles.length > 0" class="mt-3">
                                    <p class="text-sm font-medium text-gray-700 mb-2">File yang akan diunggah:</p>
                                    <ul class="space-y-2">
                                        <li 
                                            v-for="(file, index) in selectedFiles" 
                                            :key="index"
                                            class="flex items-center justify-between p-2 bg-gray-50 rounded-md"
                                        >
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">{{ file.name }}</p>
                                                    <p class="text-xs text-gray-500">{{ formatFileSize(file.size) }}</p>
                                                </div>
                                            </div>
                                            <button
                                                type="button"
                                                @click="removeFile(index)"
                                                class="text-red-600 hover:text-red-800"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                                
                                <div v-if="form.errors.files" class="text-xs text-red-600 mt-1">{{ form.errors.files }}</div>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-end gap-2">
                            <Link :href="route('meeting-minutes.index')" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md">Batal</Link>
                            <button @click="save" :disabled="saving || !form.agenda || !form.meeting_date" class="px-4 py-2 bg-indigo-600 text-white rounded-md disabled:opacity-50">Simpan</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
