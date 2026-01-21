<script setup>
import { ref, computed } from "vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import QuillEditor from "@/Components/QuillEditor.vue";

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
        onFinish: () => {
            saving.value = false;
        },
    });
};

const files = ref([]);
const uploading = ref(false);
const upload = () => {
    if (!files.value.length) return;
    const fd = new FormData();
    files.value.forEach((f) => fd.append("files[]", f));
    uploading.value = true;
    router.post(route("meeting-minutes.attachments", props.minute.id), fd, {
        forceFormData: true,
        onFinish: () => {
            uploading.value = false;
            files.value = [];
        },
        onSuccess: () => {
            router.reload({ only: ["minute"] });
        },
    });
};

const search = ref("");
const filtered = computed(() => {
    const q = search.value.trim().toLowerCase();
    if (!q) return props.members || [];
    return (props.members || []).filter((m) => (m.full_name || "").toLowerCase().includes(q));
});
</script>

<template>
    <Head title="Edit Notulensi Rapat" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit Notulensi Rapat</h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
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

                        <div class="border-t pt-4">
                            <h3 class="text-sm font-semibold text-gray-700 mb-2">Lampiran</h3>
                            <div class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <input type="file" multiple @change="(e) => (files.value = Array.from(e.target.files))" />
                                    <button @click="upload" :disabled="uploading || !files.length" class="px-3 py-2 bg-indigo-600 text-white rounded-md disabled:opacity-50">Unggah</button>
                                </div>
                                <ul class="text-sm text-gray-600 list-disc pl-5">
                                    <li v-for="att in props.minute.attachments || []" :key="att.id">
                                        {{ att.original_name }}
                                    </li>
                                </ul>
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

<script>
// local script section for computed filtering
</script>
