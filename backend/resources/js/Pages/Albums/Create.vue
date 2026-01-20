<script setup>
import { ref } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

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

const coverImagePreview = ref(null);

const handleCoverImageChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.cover_image = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            coverImagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const removeCoverImage = () => {
    form.cover_image = null;
    coverImagePreview.value = null;
    document.getElementById("cover_image").value = "";
};

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
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Buat Album Baru
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="max-w-3xl mx-auto overflow-hidden bg-white shadow-sm sm:rounded-lg"
                >
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <!-- Nama Album -->
                        <div>
                            <InputLabel for="name" value="Nama Album" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                autofocus
                            />
                            <InputError
                                class="mt-2"
                                :message="form.errors.name"
                            />
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
                            <InputError
                                class="mt-2"
                                :message="form.errors.description"
                            />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Kategori -->
                            <div>
                                <InputLabel for="category" value="Kategori" />
                                <select
                                    id="category"
                                    v-model="form.category"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    required
                                >
                                    <option value="event">Event</option>
                                    <option value="routine">
                                        Kegiatan Rutin
                                    </option>
                                    <option value="official">
                                        Dokumentasi Resmi
                                    </option>
                                    <option value="other">Lainnya</option>
                                </select>
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.category"
                                />
                            </div>

                            <!-- Status -->
                            <div>
                                <InputLabel for="status" value="Status" />
                                <select
                                    id="status"
                                    v-model="form.status"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    required
                                >
                                    <option value="public">Publik</option>
                                    <option value="private">Privat</option>
                                </select>
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.status"
                                />
                            </div>
                        </div>

                        <!-- Event (Optional) -->
                        <div>
                            <InputLabel
                                for="event_id"
                                value="Hubungkan dengan Event (Opsional)"
                            />
                            <select
                                id="event_id"
                                v-model="form.event_id"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            >
                                <option value="">Tidak ada</option>
                                <option
                                    v-for="event in events"
                                    :key="event.id"
                                    :value="event.id"
                                >
                                    {{ event.name }}
                                </option>
                            </select>
                            <InputError
                                class="mt-2"
                                :message="form.errors.event_id"
                            />
                        </div>

                        <!-- Cover Image -->
                        <div>
                            <InputLabel
                                for="cover_image"
                                value="Gambar Cover (Opsional)"
                            />
                            <div class="mt-1">
                                <input
                                    id="cover_image"
                                    type="file"
                                    accept="image/jpeg,image/jpg,image/png"
                                    @change="handleCoverImageChange"
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                />
                                <p class="mt-1 text-xs text-gray-500">
                                    Format: JPG, JPEG, PNG. Maksimal 5MB.
                                </p>
                            </div>
                            <InputError
                                class="mt-2"
                                :message="form.errors.cover_image"
                            />

                            <!-- Image Preview -->
                            <div
                                v-if="coverImagePreview"
                                class="mt-4 relative inline-block"
                            >
                                <img
                                    :src="coverImagePreview"
                                    alt="Cover preview"
                                    class="h-32 w-auto rounded-lg border border-gray-300"
                                />
                                <button
                                    type="button"
                                    @click="removeCoverImage"
                                    class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
                                >
                                    <svg
                                        class="w-4 h-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-4">
                            <Link
                                :href="route('albums.index')"
                                class="text-sm text-gray-600 hover:text-gray-900"
                            >
                                Batal
                            </Link>
                            <PrimaryButton
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                Simpan Album
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
