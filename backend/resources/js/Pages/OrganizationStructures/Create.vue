<script setup>
import { useForm, Head, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

const props = defineProps({
    members: Array,
    parents: Array,
});

const form = useForm({
    member_id: "",
    position_name: "",
    level: 0,
    parent_id: "",
    sort_order: 0,
    period_start: new Date().getFullYear(),
    period_end: "",
    is_active: true,
    photo: null,
});

const onPhotoChange = (e) => {
    form.photo = e.target.files[0];
};

const submit = () => {
    form.post(route("organization-structures.store"));
};
</script>

<template>
    <Head title="Tambah Struktur Organisasi" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Tambah Struktur Organisasi
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit">
                            <!-- Posisi & Anggota -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <InputLabel for="position_name" value="Nama Posisi" />
                                    <TextInput id="position_name" type="text" class="mt-1 block w-full" v-model="form.position_name" required />
                                    <InputError class="mt-2" :message="form.errors.position_name" />
                                </div>
                                <div>
                                    <InputLabel for="member_id" value="Anggota (Opsional)" />
                                    <select
                                        id="member_id"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        v-model="form.member_id"
                                    >
                                        <option value="">-</option>
                                        <option v-for="m in members" :key="m.id" :value="m.id">
                                            {{ m.full_name }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.member_id" />
                                </div>
                            </div>

                            <!-- Hierarchy -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <InputLabel for="level" value="Level" />
                                    <TextInput id="level" type="number" class="mt-1 block w-full" v-model="form.level" min="0" required />
                                    <InputError class="mt-2" :message="form.errors.level" />
                                </div>
                                <div>
                                    <InputLabel for="parent_id" value="Induk (Opsional)" />
                                    <select
                                        id="parent_id"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        v-model="form.parent_id"
                                    >
                                        <option value="">-</option>
                                        <option v-for="p in parents" :key="p.id" :value="p.id">
                                            {{ p.position_name }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.parent_id" />
                                </div>
                                <div>
                                    <InputLabel for="sort_order" value="Urutan" />
                                    <TextInput id="sort_order" type="number" class="mt-1 block w-full" v-model="form.sort_order" min="0" required />
                                    <InputError class="mt-2" :message="form.errors.sort_order" />
                                </div>
                            </div>

                            <!-- Period -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <InputLabel for="period_start" value="Tahun Mulai" />
                                    <TextInput id="period_start" type="number" class="mt-1 block w-full" v-model="form.period_start" required />
                                    <InputError class="mt-2" :message="form.errors.period_start" />
                                </div>
                                <div>
                                    <InputLabel for="period_end" value="Tahun Selesai (Opsional)" />
                                    <TextInput id="period_end" type="number" class="mt-1 block w-full" v-model="form.period_end" />
                                    <InputError class="mt-2" :message="form.errors.period_end" />
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="mb-4">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" v-model="form.is_active" />
                                    <span class="ml-2 text-sm text-gray-700">Aktif</span>
                                </label>
                                <InputError class="mt-2" :message="form.errors.is_active" />
                            </div>

                            <!-- Photo -->
                            <div class="mb-4">
                                <InputLabel for="photo" value="Foto (Opsional)" />
                                <input id="photo" type="file" accept="image/*" class="mt-1 block w-full" @change="onPhotoChange" />
                                <InputError class="mt-2" :message="form.errors.photo" />
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <Link :href="route('organization-structures.index')" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Batal</Link>
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Simpan</PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

