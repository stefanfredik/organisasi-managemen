<script setup>
import { ref, reactive } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ImageUpload from "@/Components/ImageUpload.vue";
import InputError from "@/Components/InputError.vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from "@/components/ui/select";
import { ArrowLeft, Save, ImageIcon } from "lucide-vue-next";

const props = defineProps({
    events: Array,
});

const form = useForm({
    name: "",
    description: "",
    category: "other",
    event_id: "",
    status: "public",
    cover_image: null,
});

const clientErrors = reactive({
    name: "",
    category: "",
    status: "",
});

const touched = reactive({
    name: false,
});

const validateName = () => {
    if (!form.name.trim()) {
        clientErrors.name = "Nama album harus diisi.";
    } else if (form.name.trim().length > 255) {
        clientErrors.name = "Nama album maksimal 255 karakter.";
    } else {
        clientErrors.name = "";
    }
};

const validateAll = () => {
    touched.name = true;
    validateName();

    if (!form.category) {
        clientErrors.category = "Kategori harus dipilih.";
    } else {
        clientErrors.category = "";
    }

    if (!form.status) {
        clientErrors.status = "Status harus dipilih.";
    } else {
        clientErrors.status = "";
    }

    return !clientErrors.name && !clientErrors.category && !clientErrors.status;
};

const submit = () => {
    if (!validateAll()) return;

    form.post(route("albums.store"), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Buat Album" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('albums.index')" class="shrink-0 p-1.5 rounded-lg text-muted-foreground hover:text-foreground hover:bg-muted transition-colors">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <h2 class="text-lg font-semibold leading-tight text-foreground">Buat Album Baru</h2>
            </div>
        </template>

        <div class="py-4 sm:py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-2xl mx-auto">
                    <form @submit.prevent="submit" class="bg-card border rounded-xl overflow-hidden">
                        <div class="p-4 sm:p-6 space-y-5">
                            <!-- Nama Album -->
                            <div>
                                <Label for="name">
                                    Nama Album
                                    <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="mt-1.5 block w-full"
                                    placeholder="Masukkan nama album"
                                    autofocus
                                    @blur="touched.name = true; validateName()"
                                    @input="touched.name && validateName()"
                                />
                                <InputError class="mt-1.5" :message="clientErrors.name || form.errors.name" />
                            </div>

                            <!-- Deskripsi -->
                            <div>
                                <Label for="description">Deskripsi</Label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="3"
                                    placeholder="Deskripsi singkat album (opsional)"
                                    class="mt-1.5 block w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-ring"
                                ></textarea>
                                <InputError class="mt-1.5" :message="form.errors.description" />
                            </div>

                            <!-- Kategori & Status -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <Label for="category">
                                        Kategori
                                        <span class="text-destructive">*</span>
                                    </Label>
                                    <Select v-model="form.category" @update:modelValue="clientErrors.category = ''">
                                        <SelectTrigger class="mt-1.5 w-full">
                                            <SelectValue placeholder="Pilih kategori" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="event">Event</SelectItem>
                                            <SelectItem value="routine">Kegiatan Rutin</SelectItem>
                                            <SelectItem value="official">Dokumentasi Resmi</SelectItem>
                                            <SelectItem value="other">Lainnya</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError class="mt-1.5" :message="clientErrors.category || form.errors.category" />
                                </div>

                                <div>
                                    <Label for="status">
                                        Status
                                        <span class="text-destructive">*</span>
                                    </Label>
                                    <Select v-model="form.status" @update:modelValue="clientErrors.status = ''">
                                        <SelectTrigger class="mt-1.5 w-full">
                                            <SelectValue placeholder="Pilih status" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="public">Publik</SelectItem>
                                            <SelectItem value="private">Privat</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError class="mt-1.5" :message="clientErrors.status || form.errors.status" />
                                </div>
                            </div>

                            <!-- Event (Optional) -->
                            <div>
                                <Label for="event_id">Hubungkan dengan Event</Label>
                                <p class="text-xs text-muted-foreground mt-0.5">Opsional — pilih event yang terkait dengan album ini.</p>
                                <Select v-model="form.event_id">
                                    <SelectTrigger class="mt-1.5 w-full">
                                        <SelectValue placeholder="Pilih event" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">Tidak ada</SelectItem>
                                        <SelectItem v-for="event in events" :key="event.id" :value="event.id.toString()">
                                            {{ event.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError class="mt-1.5" :message="form.errors.event_id" />
                            </div>

                            <!-- Cover Image -->
                            <div>
                                <Label>
                                    <span class="flex items-center gap-1.5">
                                        <ImageIcon class="w-3.5 h-3.5" />
                                        Gambar Cover
                                    </span>
                                </Label>
                                <p class="text-xs text-muted-foreground mt-0.5">Opsional — format JPG, JPEG, PNG. Maksimal 5MB.</p>
                                <div class="mt-1.5">
                                    <ImageUpload
                                        v-model="form.cover_image"
                                        label="Upload Cover Album"
                                        hint="Seret atau klik untuk upload"
                                        :max-size="5120"
                                        :error="form.errors.cover_image"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="flex items-center justify-end gap-3 px-4 sm:px-6 py-4 border-t bg-muted/30">
                            <Button variant="outline" as-child>
                                <Link :href="route('albums.index')">Batal</Link>
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                <Save class="w-3.5 h-3.5 mr-1.5" />
                                {{ form.processing ? 'Menyimpan...' : 'Simpan Album' }}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
