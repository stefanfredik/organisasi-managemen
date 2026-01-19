<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    member: Object,
});

const activeTab = ref('profile');
const isPhotoZoomed = ref(false);

const tabs = [
    { id: 'profile', name: 'Profil' },
    { id: 'contributions', name: 'Riwayat Iuran' },
    { id: 'events', name: 'Riwayat Kegiatan' },
];

const deleteMember = () => {
    if (confirm(`Apakah Anda yakin ingin menghapus anggota ${props.member.full_name}?`)) {
        router.delete(`/members/${props.member.id}`);
    }
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};
</script>

<template>
    <Head :title="`Detail Anggota - ${member.full_name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Detail Anggota
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
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Member Header Card -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-start gap-6">
                            <!-- Photo -->
                            <div class="flex-shrink-0">
                                <img
                                    v-if="member.photo_url"
                                    :src="member.photo_url"
                                    :alt="member.full_name"
                                    class="h-32 w-32 rounded-lg object-cover cursor-pointer hover:opacity-90 transition-opacity"
                                    @click="isPhotoZoomed = true"
                                />
                                <div
                                    v-else
                                    class="h-32 w-32 rounded-lg bg-gray-200 flex items-center justify-center"
                                >
                                    <span class="text-gray-500 font-medium text-4xl">
                                        {{ member.full_name.charAt(0).toUpperCase() }}
                                    </span>
                                </div>
                            </div>

                            <!-- Info -->
                            <div class="flex-1">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h3 class="text-2xl font-bold text-gray-900">
                                            {{ member.full_name }}
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-500">
                                            {{ member.member_code }}
                                        </p>
                                        <div class="mt-2">
                                            <span
                                                class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full"
                                                :class="member.status === 'active'
                                                    ? 'bg-green-100 text-green-800'
                                                    : 'bg-gray-100 text-gray-800'"
                                            >
                                                {{ member.status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="flex gap-2">
                                        <Link
                                            :href="`/members/${member.id}/edit`"
                                            class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                        >
                                            Edit
                                        </Link>
                                        <button
                                            type="button"
                                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                            @click="deleteMember"
                                        >
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <!-- Tab Navigation -->
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                            <button
                                v-for="tab in tabs"
                                :key="tab.id"
                                type="button"
                                :class="[
                                    activeTab === tab.id
                                        ? 'border-indigo-500 text-indigo-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
                                ]"
                                @click="activeTab = tab.id"
                            >
                                {{ tab.name }}
                            </button>
                        </nav>
                    </div>

                    <!-- Tab Content -->
                    <div class="p-6">
                        <!-- Profile Tab -->
                        <div v-if="activeTab === 'profile'" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Email</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ member.email || '-' }}</p>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Nomor Telepon</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ member.phone || '-' }}</p>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Tanggal Lahir</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ formatDate(member.date_of_birth) }}</p>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Tanggal Bergabung</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ formatDate(member.join_date) }}</p>
                                </div>

                                <div class="md:col-span-2">
                                    <h4 class="text-sm font-medium text-gray-500">Alamat</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ member.address || '-' }}</p>
                                </div>

                                <div class="md:col-span-2">
                                    <h4 class="text-sm font-medium text-gray-500">Catatan</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ member.notes || '-' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Contributions Tab -->
                        <div v-if="activeTab === 'contributions'">
                            <div class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada riwayat iuran</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Fitur manajemen iuran akan tersedia di Phase 4
                                </p>
                            </div>
                        </div>

                        <!-- Events Tab -->
                        <div v-if="activeTab === 'events'">
                            <div class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada riwayat kegiatan</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Fitur manajemen kegiatan akan tersedia di Phase 3
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Photo Zoom Modal -->
        <Modal :show="isPhotoZoomed" @close="isPhotoZoomed = false" maxWidth="2xl">
            <div class="p-4 flex flex-col items-center">
                <div class="w-full flex justify-end mb-2">
                    <button @click="isPhotoZoomed = false" class="text-gray-500 hover:text-gray-700">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <img
                    :src="member.photo_url"
                    :alt="member.full_name"
                    class="max-w-full max-h-[80vh] rounded-lg shadow-lg"
                />
                <div class="mt-4 text-center">
                    <h3 class="text-lg font-medium text-gray-900">{{ member.full_name }}</h3>
                    <p class="text-sm text-gray-500">{{ member.member_code }}</p>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
