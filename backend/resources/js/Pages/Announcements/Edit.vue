<script setup>
import { ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import QuillEditor from "@/Components/QuillEditor.vue";

const props = defineProps({
    announcement: Object,
    roleOptions: Array,
});

const normalizeDate = (val) => {
    if (!val) return "";
    if (typeof val === "string") {
        if (val.includes("T")) return val.split("T")[0];
        return val;
    }
    const d = new Date(val);
    if (!isNaN(d)) {
        const yyyy = d.getFullYear();
        const mm = String(d.getMonth() + 1).padStart(2, "0");
        const dd = String(d.getDate()).padStart(2, "0");
        return `${yyyy}-${mm}-${dd}`;
    }
    return "";
};

const form = ref({
    title: props.announcement?.title || "",
    content: props.announcement?.content || "",
    status: props.announcement?.status || "draft",
    publish_date: normalizeDate(props.announcement?.publish_date),
    target_audience: props.announcement?.target_audience || "all",
    target_roles: props.announcement?.target_roles || [],
});

const saving = ref(false);

const save = () => {
    saving.value = true;
    router.put(route("announcements.update", props.announcement.id), form.value, {
        onFinish: () => {
            saving.value = false;
        },
    });
};
</script>

<template>
    <Head title="Edit Pengumuman" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit Pengumuman</h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Judul</label>
                            <input v-model="form.title" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Konten</label>
                            <div class="mt-1">
                                <QuillEditor v-model="form.content" placeholder="Tulis isi pengumuman..." />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <select v-model="form.status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="draft">Draft</option>
                                    <option value="published">Dipublikasikan</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tanggal Publish</label>
                                <input v-model="form.publish_date" type="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Audiens</label>
                                <select v-model="form.target_audience" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="all">Semua</option>
                                    <option value="roles">Per Role</option>
                                </select>
                            </div>
                        </div>
                        <div v-if="form.target_audience === 'roles'">
                            <label class="block text-sm font-medium text-gray-700">Pilih Role</label>
                            <div class="mt-2 grid grid-cols-2 md:grid-cols-3 gap-2">
                                <label v-for="r in roleOptions" :key="r" class="inline-flex items-center">
                                    <input type="checkbox" :value="r" v-model="form.target_roles" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                                    <span class="ml-2 capitalize">{{ r }}</span>
                                </label>
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-2">
                            <Link :href="route('announcements.index')" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md">Batal</Link>
                            <button @click="save" :disabled="saving || !form.title" class="px-4 py-2 bg-indigo-600 text-white rounded-md disabled:opacity-50">Simpan</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
