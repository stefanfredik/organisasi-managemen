<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import MapPicker from '@/Components/MapPicker.vue';

const props = defineProps({
    event: Object,
    members: Array,
});

const form = useForm({
    name: props.event.name,
    description: props.event.description,
    start_date: props.event.start_date.slice(0, 16), // Format for datetime-local
    end_date: props.event.end_date ? props.event.end_date.slice(0, 16) : '',
    location: props.event.location,
    latitude: props.event.latitude,
    longitude: props.event.longitude,
    pic_id: props.event.pic_id,
    max_participants: props.event.max_participants,
    status: props.event.status,
    is_public: props.event.is_public,
});

const submit = () => {
    form.put(route('events.update', props.event), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Edit Kegiatan - ${event.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-foreground">
                Edit Kegiatan: {{ event.name }}
            </h2>
        </template>

        <div class="py-6 sm:py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl mx-auto overflow-hidden bg-card shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <!-- Nama Kegiatan -->
                        <div>
                            <Label for="name">Nama Kegiatan</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                autofocus
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
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
                            <InputError class="mt-2" :message="form.errors.description" />
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:gap-6 md:grid-cols-2">
                            <!-- Waktu Mulai -->
                            <div>
                                <Label for="start_date">Waktu Mulai</Label>
                                <Input
                                    id="start_date"
                                    v-model="form.start_date"
                                    type="datetime-local"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.start_date" />
                            </div>

                            <!-- Waktu Selesai -->
                            <div>
                                <Label for="end_date">Waktu Selesai (Opsional)</Label>
                                <Input
                                    id="end_date"
                                    v-model="form.end_date"
                                    type="datetime-local"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.end_date" />
                            </div>
                        </div>

                        <!-- Lokasi -->
                        <div>
                            <Label for="location">Lokasi</Label>
                            <Input
                                id="location"
                                v-model="form.location"
                                type="text"
                                class="mt-1 block w-full"
                            />
                            <InputError class="mt-2" :message="form.errors.location" />
                        </div>

                        <!-- Map Picker -->
                        <div>
                            <Label>Pilih Lokasi di Peta</Label>
                            <div class="mt-1">
                                <MapPicker
                                    :latitude="form.latitude"
                                    :longitude="form.longitude"
                                    @update:latitude="form.latitude = $event"
                                    @update:longitude="form.longitude = $event"
                                />
                            </div>
                            <div class="grid grid-cols-2 gap-4 mt-2">
                                <div>
                                    <Label for="latitude">Latitude</Label>
                                    <Input
                                        id="latitude"
                                        :value="form.latitude ? parseFloat(form.latitude).toFixed(7) : ''"
                                        type="text"
                                        class="mt-1 block w-full"
                                        readonly
                                    />
                                    <InputError class="mt-2" :message="form.errors.latitude" />
                                </div>
                                <div>
                                    <Label for="longitude">Longitude</Label>
                                    <Input
                                        id="longitude"
                                        :value="form.longitude ? parseFloat(form.longitude).toFixed(7) : ''"
                                        type="text"
                                        class="mt-1 block w-full"
                                        readonly
                                    />
                                    <InputError class="mt-2" :message="form.errors.longitude" />
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:gap-6 md:grid-cols-2">
                            <!-- PIC -->
                            <div>
                                <Label for="pic_id">Penanggung Jawab (PIC)</Label>
                                <select
                                    id="pic_id"
                                    v-model="form.pic_id"
                                    class="mt-1 block w-full border-input focus:border-ring focus:ring-ring rounded-md shadow-sm"
                                >
                                    <option value="">Pilih PIC</option>
                                    <option
                                        v-for="member in members"
                                        :key="member.id"
                                        :value="member.id"
                                    >
                                        {{ member.full_name }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.pic_id" />
                            </div>

                            <!-- Maks Peserta -->
                            <div>
                                <Label for="max_participants">Maksimal Peserta</Label>
                                <Input
                                    id="max_participants"
                                    v-model="form.max_participants"
                                    type="number"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.max_participants" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:gap-6 md:grid-cols-2">
                            <!-- Status -->
                            <div>
                                <Label for="status">Status</Label>
                                <select
                                    id="status"
                                    v-model="form.status"
                                    class="mt-1 block w-full border-input focus:border-ring focus:ring-ring rounded-md shadow-sm"
                                    required
                                >
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="ongoing">Sedang Berlangsung</option>
                                    <option value="completed">Selesai</option>
                                    <option value="cancelled">Dibatalkan</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.status" />
                            </div>

                            <!-- Is Public -->
                            <div class="flex items-center pt-8">
                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        v-model="form.is_public"
                                        class="rounded border-input text-primary shadow-sm focus:ring-ring"
                                    />
                                    <span class="ml-2 text-sm text-muted-foreground">Publikasikan di halaman depan</span>
                                </label>
                                <InputError class="mt-2" :message="form.errors.is_public" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-4">
                            <Link
                                :href="route('events.show', event)"
                                class="text-sm text-muted-foreground hover:text-foreground"
                            >
                                Batal
                            </Link>
                            <Button type="submit"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                Perbarui Kegiatan
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
