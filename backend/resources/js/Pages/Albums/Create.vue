<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ImageUpload from "@/Components/ImageUpload.vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card, CardContent } from "@/components/ui/card";
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from "@/components/ui/select";

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

const submit = () => {
    form.post(route("albums.store"), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Buat Album" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-foreground">
                Buat Album Baru
            </h2>
        </template>

        <div class="py-6 sm:py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <Card>
                    <CardContent class="p-6 space-y-6">
                        <!-- Nama Album -->
                        <div>
                            <Label for="name">Nama Album</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                autofocus
                            />
                            <p v-if="form.errors.name" class="mt-2 text-sm text-destructive">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Deskripsi -->
                        <div>
                            <Label for="description">Deskripsi</Label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="4"
                                class="mt-1 block w-full border-input focus:border-ring focus:ring-ring rounded-md shadow-sm"
                            ></textarea>
                            <p v-if="form.errors.description" class="mt-2 text-sm text-destructive">
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:gap-6 md:grid-cols-2">
                            <!-- Kategori -->
                            <div>
                                <Label for="category">Kategori</Label>
                                <Select v-model="form.category" required>
                                    <SelectTrigger class="mt-1 w-full">
                                        <SelectValue placeholder="Pilih kategori" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="event">Event</SelectItem>
                                        <SelectItem value="routine">Kegiatan Rutin</SelectItem>
                                        <SelectItem value="official">Dokumentasi Resmi</SelectItem>
                                        <SelectItem value="other">Lainnya</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.category" class="mt-2 text-sm text-destructive">
                                    {{ form.errors.category }}
                                </p>
                            </div>

                            <!-- Status -->
                            <div>
                                <Label for="status">Status</Label>
                                <Select v-model="form.status" required>
                                    <SelectTrigger class="mt-1 w-full">
                                        <SelectValue placeholder="Pilih status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="public">Publik</SelectItem>
                                        <SelectItem value="private">Privat</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.status" class="mt-2 text-sm text-destructive">
                                    {{ form.errors.status }}
                                </p>
                            </div>
                        </div>

                        <!-- Event (Optional) -->
                        <div>
                            <Label for="event_id">Hubungkan dengan Event (Opsional)</Label>
                            <Select v-model="form.event_id">
                                <SelectTrigger class="mt-1 w-full">
                                    <SelectValue placeholder="Pilih event" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Tidak ada</SelectItem>
                                    <SelectItem v-for="event in events" :key="event.id" :value="event.id.toString()">
                                        {{ event.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.event_id" class="mt-2 text-sm text-destructive">
                                {{ form.errors.event_id }}
                            </p>
                        </div>

                        <!-- Cover Image -->
                        <div>
                            <Label>Gambar Cover (Opsional)</Label>
                            <div class="mt-1">
                                <ImageUpload
                                    v-model="form.cover_image"
                                    label="Upload Cover Album"
                                    hint="Format: JPG, JPEG, PNG. Maksimal 5MB."
                                    :max-size="5120"
                                    :error="form.errors.cover_image"
                                />
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-4">
                            <Button variant="ghost" as-child>
                                <Link :href="route('albums.index')">Batal</Link>
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                Simpan Album
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
