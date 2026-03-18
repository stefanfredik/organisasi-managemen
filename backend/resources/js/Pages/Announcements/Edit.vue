<script setup>
import { ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import QuillEditor from "@/Components/QuillEditor.vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card, CardContent } from "@/components/ui/card";
import { Checkbox } from "@/components/ui/checkbox";
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from "@/components/ui/select";

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

const toggleRole = (role, checked) => {
    if (checked) {
        form.value.target_roles.push(role);
    } else {
        form.value.target_roles = form.value.target_roles.filter(r => r !== role);
    }
};
</script>

<template>
    <Head title="Edit Pengumuman" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-foreground">Edit Pengumuman</h2>
        </template>

        <div class="py-6 sm:py-8">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <Card>
                    <CardContent class="p-6 space-y-4">
                        <div>
                            <Label for="title">Judul</Label>
                            <Input id="title" v-model="form.title" type="text" class="mt-1" />
                        </div>
                        <div>
                            <Label>Konten</Label>
                            <div class="mt-1">
                                <QuillEditor v-model="form.content" placeholder="Tulis isi pengumuman..." />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <Label>Status</Label>
                                <Select v-model="form.status">
                                    <SelectTrigger class="mt-1 w-full">
                                        <SelectValue placeholder="Pilih status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="draft">Draft</SelectItem>
                                        <SelectItem value="published">Dipublikasikan</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div>
                                <Label>Tanggal Publish</Label>
                                <Input v-model="form.publish_date" type="date" class="mt-1" />
                            </div>
                            <div>
                                <Label>Audiens</Label>
                                <Select v-model="form.target_audience">
                                    <SelectTrigger class="mt-1 w-full">
                                        <SelectValue placeholder="Pilih audiens" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">Semua</SelectItem>
                                        <SelectItem value="roles">Per Role</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                        <div v-if="form.target_audience === 'roles'">
                            <Label>Pilih Role</Label>
                            <div class="mt-2 grid grid-cols-2 md:grid-cols-3 gap-2">
                                <label v-for="r in roleOptions" :key="r" class="inline-flex items-center gap-2">
                                    <Checkbox
                                        :checked="form.target_roles.includes(r)"
                                        @update:checked="toggleRole(r, $event)"
                                    />
                                    <span class="text-sm capitalize">{{ r }}</span>
                                </label>
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3">
                            <Button variant="outline" as-child>
                                <Link :href="route('announcements.index')">Batal</Link>
                            </Button>
                            <Button @click="save" :disabled="saving || !form.title">Simpan</Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
