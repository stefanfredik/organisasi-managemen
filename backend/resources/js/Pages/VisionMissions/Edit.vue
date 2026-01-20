<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    visionMission: Object,
});

const form = useForm({
    vision: props.visionMission.vision,
    missions: props.visionMission.missions || [''],
    period_start: props.visionMission.period_start,
    period_end: props.visionMission.period_end,
    status: props.visionMission.status,
});

const addMission = () => {
    form.missions.push('');
};

const removeMission = (index) => {
    form.missions.splice(index, 1);
};

const submit = () => {
    form.put(route('vision-missions.update', props.visionMission.id));
};
</script>

<template>
    <Head title="Edit Visi & Misi" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Visi & Misi
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit">
                            <!-- Period -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <InputLabel for="period_start" value="Tahun Mulai" />
                                    <TextInput
                                        id="period_start"
                                        type="number"
                                        class="mt-1 block w-full"
                                        v-model="form.period_start"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.period_start" />
                                </div>
                                <div>
                                    <InputLabel for="period_end" value="Tahun Selesai (Opsional)" />
                                    <TextInput
                                        id="period_end"
                                        type="number"
                                        class="mt-1 block w-full"
                                        v-model="form.period_end"
                                    />
                                    <InputError class="mt-2" :message="form.errors.period_end" />
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="mb-4">
                                <InputLabel for="status" value="Status" />
                                <select
                                    id="status"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    v-model="form.status"
                                    required
                                >
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Tidak Aktif</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.status" />
                            </div>

                            <!-- Vision -->
                            <div class="mb-4">
                                <InputLabel for="vision" value="Visi" />
                                <textarea
                                    id="vision"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    v-model="form.vision"
                                    rows="3"
                                    required
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.vision" />
                            </div>

                            <!-- Missions -->
                            <div class="mb-4">
                                <InputLabel value="Misi" />
                                <div v-for="(mission, index) in form.missions" :key="index" class="flex items-center mt-2 space-x-2">
                                    <TextInput
                                        type="text"
                                        class="block w-full"
                                        v-model="form.missions[index]"
                                        placeholder="Masukkan butir misi..."
                                        required
                                    />
                                    <button
                                        type="button"
                                        @click="removeMission(index)"
                                        class="text-red-600 hover:text-red-800"
                                        v-if="form.missions.length > 1"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                                <InputError class="mt-2" :message="form.errors.missions" />
                                <button
                                    type="button"
                                    @click="addMission"
                                    class="mt-2 text-sm text-indigo-600 hover:text-indigo-900 flex items-center"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Tambah Misi
                                </button>
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <Link
                                    :href="route('vision-missions.index')"
                                    class="text-sm text-gray-600 hover:text-gray-900 mr-4"
                                >
                                    Batal
                                </Link>
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Simpan Perubahan
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
