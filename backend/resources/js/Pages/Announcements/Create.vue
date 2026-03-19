<script setup>
import { ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import QuillEditor from "@/Components/QuillEditor.vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Checkbox } from "@/components/ui/checkbox";
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from "@/components/ui/select";
import { ArrowLeft, Loader2, Megaphone } from "lucide-vue-next";

const props = defineProps({
    roleOptions: Array,
});

const form = ref({
    title: "",
    content: "",
    status: "draft",
    publish_date: "",
    target_audience: "all",
    target_roles: [],
});

const saving = ref(false);

const save = () => {
    saving.value = true;
    router.post(route("announcements.store"), form.value, {
        onFinish: () => { saving.value = false; },
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
    <Head title="Tambah Pengumuman" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2.5">
                <Button variant="ghost" size="icon" class="h-8 w-8 shrink-0" as-child>
                    <Link :href="route('announcements.index')">
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                </Button>
                <h2 class="text-lg font-semibold leading-tight text-foreground">Tambah Pengumuman</h2>
            </div>
        </template>

        <div class="py-3 sm:py-6">
            <div class="max-w-3xl mx-auto px-3 sm:px-6 lg:px-8">
                <div class="bg-card rounded-xl border overflow-hidden">
                    <div class="h-1 w-full bg-primary" />

                    <form @submit.prevent="save" class="p-4 sm:p-6 space-y-4 sm:space-y-5">
                        <!-- Title -->
                        <div>
                            <Label for="title" class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Judul</Label>
                            <Input id="title" v-model="form.title" type="text" class="mt-1.5" placeholder="Judul pengumuman..." />
                        </div>

                        <!-- Content -->
                        <div>
                            <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Konten</Label>
                            <div class="mt-1.5">
                                <QuillEditor v-model="form.content" placeholder="Tulis isi pengumuman..." />
                            </div>
                        </div>

                        <div class="border-t" />

                        <!-- Settings -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                            <div>
                                <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Status</Label>
                                <Select v-model="form.status">
                                    <SelectTrigger class="mt-1.5 w-full">
                                        <SelectValue placeholder="Pilih status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="draft">Draft</SelectItem>
                                        <SelectItem value="published">Dipublikasikan</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div>
                                <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Tanggal Publish</Label>
                                <Input v-model="form.publish_date" type="date" class="mt-1.5" />
                            </div>
                            <div>
                                <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Audiens</Label>
                                <Select v-model="form.target_audience">
                                    <SelectTrigger class="mt-1.5 w-full">
                                        <SelectValue placeholder="Pilih audiens" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">Semua</SelectItem>
                                        <SelectItem value="roles">Per Role</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <!-- Role Selection -->
                        <div v-if="form.target_audience === 'roles'" class="bg-muted/50 rounded-lg p-3 border">
                            <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Pilih Role</Label>
                            <div class="mt-2 grid grid-cols-2 sm:grid-cols-3 gap-2">
                                <label v-for="r in roleOptions" :key="r" class="inline-flex items-center gap-2 cursor-pointer">
                                    <Checkbox
                                        :checked="form.target_roles.includes(r)"
                                        @update:checked="toggleRole(r, $event)"
                                    />
                                    <span class="text-sm capitalize">{{ r }}</span>
                                </label>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="flex items-center justify-end gap-2 pt-2 border-t">
                            <Button variant="outline" size="sm" as-child>
                                <Link :href="route('announcements.index')">Batal</Link>
                            </Button>
                            <Button size="sm" type="submit" :disabled="saving || !form.title">
                                <Loader2 v-if="saving" class="w-4 h-4 mr-1 animate-spin" />
                                {{ saving ? 'Menyimpan...' : 'Simpan' }}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
