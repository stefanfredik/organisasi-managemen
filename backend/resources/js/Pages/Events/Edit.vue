<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

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
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Edit Kegiatan: {{ event.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="max-w-3xl mx-auto overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <!-- Nama Kegiatan -->
                        <div>
                            <InputLabel for="name" value="Nama Kegiatan" />
                            <TextInput
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
                            <InputLabel for="description" value="Deskripsi" />
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="4"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            ></textarea>
                            <InputError class="mt-2" :message="form.errors.description" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Waktu Mulai -->
                            <div>
                                <InputLabel for="start_date" value="Waktu Mulai" />
                                <TextInput
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
                                <InputLabel for="end_date" value="Waktu Selesai (Opsional)" />
                                <TextInput
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
                            <InputLabel for="location" value="Lokasi" />
                            <TextInput
                                id="location"
                                v-model="form.location"
                                type="text"
                                class="mt-1 block w-full"
                            />
                            <InputError class="mt-2" :message="form.errors.location" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- PIC -->
                            <div>
                                <InputLabel for="pic_id" value="Penanggung Jawab (PIC)" />
                                <select
                                    id="pic_id"
                                    v-model="form.pic_id"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
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
                                <InputLabel for="max_participants" value="Maksimal Peserta" />
                                <TextInput
                                    id="max_participants"
                                    v-model="form.max_participants"
                                    type="number"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.max_participants" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Status -->
                            <div>
                                <InputLabel for="status" value="Status" />
                                <select
                                    id="status"
                                    v-model="form.status"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
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
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-600">Publikasikan di halaman depan</span>
                                </label>
                                <InputError class="mt-2" :message="form.errors.is_public" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-4">
                            <Link
                                :href="route('events.show', event)"
                                class="text-sm text-gray-600 hover:text-gray-900"
                            >
                                Batal
                            </Link>
                            <PrimaryButton
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                Perbarui Kegiatan
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
