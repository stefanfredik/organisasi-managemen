<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ImageUpload from '@/Components/ImageUpload.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    member: Object,
});

const form = useForm({
    full_name: props.member.full_name,
    email: props.member.email,
    phone: props.member.phone,
    address: props.member.address,
    date_of_birth: props.member.date_of_birth,
    join_date: props.member.join_date,
    status: props.member.status,
    photo: null,
    notes: props.member.notes,
});

const submit = () => {
    form.post(`/members/${props.member.id}`, {
        _method: 'put',
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Edit Anggota" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Edit Anggota: {{ member.full_name }}
                </h2>
                <Link
                    href="/members"
                    class="text-sm text-gray-600 hover:text-gray-900"
                >
                    Kembali ke Daftar
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <!-- Photo Upload -->
                        <div>
                            <InputLabel for="photo" value="Foto Profil" />
                            <div class="mt-2">
                                <ImageUpload
                                    v-model="form.photo"
                                    :current-image="member.photo_url"
                                />
                            </div>
                            <InputError class="mt-2" :message="form.errors.photo" />
                        </div>

                        <!-- Member Code (Read-only) -->
                        <div>
                            <InputLabel for="member_code" value="Kode Anggota" />
                            <TextInput
                                id="member_code"
                                :model-value="member.member_code"
                                type="text"
                                class="mt-1 block w-full bg-gray-50"
                                disabled
                            />
                            <p class="mt-1 text-sm text-gray-500">Kode anggota tidak dapat diubah</p>
                        </div>

                        <!-- Full Name -->
                        <div>
                            <InputLabel for="full_name" value="Nama Lengkap *" />
                            <TextInput
                                id="full_name"
                                v-model="form.full_name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                autofocus
                            />
                            <InputError class="mt-2" :message="form.errors.full_name" />
                        </div>

                        <!-- Email and Phone -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="email" value="Email" />
                                <TextInput
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>

                            <div>
                                <InputLabel for="phone" value="Nomor Telepon" />
                                <TextInput
                                    id="phone"
                                    v-model="form.phone"
                                    type="text"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.phone" />
                            </div>
                        </div>

                        <!-- Address -->
                        <div>
                            <InputLabel for="address" value="Alamat" />
                            <textarea
                                id="address"
                                v-model="form.address"
                                rows="3"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            ></textarea>
                            <InputError class="mt-2" :message="form.errors.address" />
                        </div>

                        <!-- Date of Birth and Join Date -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="date_of_birth" value="Tanggal Lahir" />
                                <TextInput
                                    id="date_of_birth"
                                    v-model="form.date_of_birth"
                                    type="date"
                                    class="mt-1 block w-full"
                                />
                                <InputError class="mt-2" :message="form.errors.date_of_birth" />
                            </div>

                            <div>
                                <InputLabel for="join_date" value="Tanggal Bergabung *" />
                                <TextInput
                                    id="join_date"
                                    v-model="form.join_date"
                                    type="date"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.join_date" />
                            </div>
                        </div>

                        <!-- Status -->
                        <div>
                            <InputLabel for="status" value="Status *" />
                            <select
                                id="status"
                                v-model="form.status"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required
                            >
                                <option value="active">Aktif</option>
                                <option value="inactive">Tidak Aktif</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.status" />
                        </div>

                        <!-- Notes -->
                        <div>
                            <InputLabel for="notes" value="Catatan" />
                            <textarea
                                id="notes"
                                v-model="form.notes"
                                rows="3"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            ></textarea>
                            <InputError class="mt-2" :message="form.errors.notes" />
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end gap-4">
                            <Link
                                href="/members"
                                class="text-sm text-gray-600 hover:text-gray-900"
                            >
                                Batal
                            </Link>
                            <PrimaryButton :disabled="form.processing">
                                Simpan Perubahan
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
