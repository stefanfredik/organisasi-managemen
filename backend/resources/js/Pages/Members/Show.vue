<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    member: Object,
    contributions: Array,
    events: Array,
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

const formatCurrency = (amount) => {
    if (!amount) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(amount);
};

const getStatusBadgeClass = (status) => {
    const classes = {
        'paid': 'bg-green-100 text-green-800',
        'pending': 'bg-yellow-100 text-yellow-800',
        'cancelled': 'bg-red-100 text-red-800',
        'present': 'bg-green-100 text-green-800',
        'absent': 'bg-red-100 text-red-800',
        'excused': 'bg-yellow-100 text-yellow-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const getStatusLabel = (status) => {
    const labels = {
        'paid': 'Lunas',
        'pending': 'Menunggu',
        'cancelled': 'Dibatalkan',
        'present': 'Hadir',
        'absent': 'Tidak Hadir',
        'excused': 'Izin',
    };
    return labels[status] || status;
};
</script>

<template>
    <Head :title="`Detail Anggota - ${member.full_name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center space-x-3">
                <Link href="/members" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <h2 class="text-xl font-semibold text-gray-800">Detail Anggota</h2>
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
                                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                        </Link>
                                        <button
                                            type="button"
                                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                            @click="deleteMember"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
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
                                    <h4 class="text-sm font-medium text-gray-500">NIK</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ member.nik || '-' }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Nama Panggilan</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ member.nickname || '-' }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Jenis Kelamin</h4>
                                    <p class="mt-1 text-sm text-gray-900">
                                        {{ member.gender === 'male' ? 'Laki-laki' : member.gender === 'female' ? 'Perempuan' : '-' }}
                                    </p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Agama</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ member.religion || '-' }}</p>
                                </div>
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
                                    <h4 class="text-sm font-medium text-gray-500">Alamat Domisili</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ member.domicile_address || '-' }}</p>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Status Tempat Tinggal</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ member.living_status || '-' }}</p>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Status Pernikahan</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ member.marital_status || '-' }}</p>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Pekerjaan</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ member.occupation || '-' }}</p>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Waktu Tiba di Bali</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ formatDate(member.arrival_date_bali) }}</p>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Kampung Asal</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ member.origin_hamlet || '-' }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Desa</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ member.origin_village || '-' }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Kecamatan</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ member.origin_subdistrict || '-' }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Kabupaten</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ member.origin_regency || '-' }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Provinsi</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ member.origin_province || '-' }}</p>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">BPJS Kesehatan</h4>
                                    <p class="mt-1 text-sm text-gray-900">
                                        {{ member.bpjs_health_active ? 'Aktif' : 'Tidak Aktif' }}
                                    </p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">BPJS Tenaga Kerja</h4>
                                    <p class="mt-1 text-sm text-gray-900">
                                        {{ member.bpjs_employment_active ? 'Aktif' : 'Tidak Aktif' }}
                                    </p>
                                </div>

                                <div class="md:col-span-2">
                                    <h4 class="text-sm font-medium text-gray-500">Catatan</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ member.notes || '-' }}</p>
                                </div>
                            </div>

                            <div class="mt-6">
                                <h4 class="text-sm font-medium text-gray-500">Foto KTP</h4>
                                <div class="mt-2">
                                    <img
                                        v-if="member.ktp_photo_url"
                                        :src="member.ktp_photo_url"
                                        alt="Foto KTP"
                                        class="h-48 w-auto rounded-lg object-cover"
                                    />
                                    <p v-else class="text-sm text-gray-900">-</p>
                                </div>
                            </div>
                        </div>

                        <!-- Contributions Tab -->
                        <div v-if="activeTab === 'contributions'">
                            <div v-if="contributions && contributions.length > 0" class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Jenis Iuran
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Jumlah
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Tanggal Bayar
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Periode
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Metode
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Status
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="contribution in contributions" :key="contribution.id">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ contribution.type_name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ formatCurrency(contribution.amount) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ formatDate(contribution.payment_date) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ contribution.payment_period || '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 capitalize">
                                                {{ contribution.payment_method || '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                    :class="getStatusBadgeClass(contribution.status)"
                                                >
                                                    {{ getStatusLabel(contribution.status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div v-else class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada riwayat iuran</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Anggota ini belum memiliki catatan pembayaran iuran
                                </p>
                            </div>
                        </div>

                        <!-- Events Tab -->
                        <div v-if="activeTab === 'events'">
                            <div v-if="events && events.length > 0" class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Nama Kegiatan
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Tanggal Kegiatan
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Lokasi
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Tanggal Daftar
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Status Kehadiran
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="event in events" :key="event.id">
                                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                                {{ event.title }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ formatDate(event.event_date) }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                {{ event.location || '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ formatDate(event.registration_date) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                    :class="getStatusBadgeClass(event.attendance_status)"
                                                >
                                                    {{ getStatusLabel(event.attendance_status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div v-else class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada riwayat kegiatan</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Anggota ini belum terdaftar dalam kegiatan apapun
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
