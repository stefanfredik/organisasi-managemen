<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    donation: Object,
    isEdit: Boolean,
});

const form = useForm({
    program_name: props.donation?.program_name || '',
    description: props.donation?.description || '',
    target_amount: props.donation?.target_amount || '',
    start_date: props.donation?.start_date || '',
    end_date: props.donation?.end_date || '',
    status: props.donation?.status || 'active',
    is_public: props.donation ? !!props.donation.is_public : true,
});

const submit = () => {
    if (props.isEdit) {
        form.put(route('donations.update', props.donation.id));
    } else {
        form.post(route('donations.store'));
    }
};
</script>

<template>
    <Head :title="isEdit ? 'Edit Program Donasi' : 'Buat Program Donasi'" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ isEdit ? 'Edit Program Donasi' : 'Buat Program Donasi' }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit" class="max-w-2xl">
                            <div class="space-y-6">
                                <div>
                                    <InputLabel for="program_name" value="Nama Program Donasi" />
                                    <TextInput
                                        id="program_name"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.program_name"
                                        required
                                        autofocus
                                        placeholder="Contoh: Donasi Anak Yatim 2026"
                                    />
                                    <InputError class="mt-2" :message="form.errors.program_name" />
                                </div>

                                <div>
                                    <InputLabel for="description" value="Deskripsi" />
                                    <textarea
                                        id="description"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        v-model="form.description"
                                        rows="4"
                                        placeholder="Jelaskan tujuan dan detail penggalangan dana..."
                                    ></textarea>
                                    <InputError class="mt-2" :message="form.errors.description" />
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <InputLabel for="target_amount" value="Target Dana (Rp)" />
                                        <TextInput
                                            id="target_amount"
                                            type="number"
                                            class="mt-1 block w-full"
                                            v-model="form.target_amount"
                                            required
                                            min="0"
                                            placeholder="Contoh: 10000000"
                                        />
                                        <InputError class="mt-2" :message="form.errors.target_amount" />
                                    </div>

                                    <div>
                                        <InputLabel for="is_public" value="Status Publikasi" />
                                        <div class="mt-2 flex items-center space-x-4">
                                            <label class="inline-flex items-center">
                                                <input
                                                    type="checkbox"
                                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                                    v-model="form.is_public"
                                                />
                                                <span class="ml-2 text-sm text-gray-600">Terlihat di Halaman Publik</span>
                                            </label>
                                        </div>
                                        <InputError class="mt-2" :message="form.errors.is_public" />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <InputLabel for="start_date" value="Tanggal Mulai" />
                                        <TextInput
                                            id="start_date"
                                            type="date"
                                            class="mt-1 block w-full"
                                            v-model="form.start_date"
                                            required
                                        />
                                        <InputError class="mt-2" :message="form.errors.start_date" />
                                    </div>

                                    <div>
                                        <InputLabel for="end_date" value="Tanggal Selesai (Opsional)" />
                                        <TextInput
                                            id="end_date"
                                            type="date"
                                            class="mt-1 block w-full"
                                            v-model="form.end_date"
                                        />
                                        <InputError class="mt-2" :message="form.errors.end_date" />
                                    </div>
                                </div>

                                <div v-if="isEdit">
                                    <InputLabel for="status" value="Status Program" />
                                    <select
                                        id="status"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        v-model="form.status"
                                        required
                                    >
                                        <option value="active">Aktif</option>
                                        <option value="completed">Selesai</option>
                                        <option value="cancelled">Dibatalkan</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.status" />
                                </div>

                                <div class="flex items-center justify-end mt-8 gap-4">
                                    <Link
                                        :href="isEdit ? route('donations.show', props.donation.id) : route('donations.index')"
                                        class="text-sm text-gray-600 hover:text-gray-900"
                                    >
                                        Batal
                                    </Link>
                                    <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                        {{ isEdit ? 'Simpan Perubahan' : 'Buat Program Donasi' }}
                                    </PrimaryButton>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
