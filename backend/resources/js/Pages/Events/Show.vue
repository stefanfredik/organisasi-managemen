<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    event: Object,
    members: Array,
});

const showParticipantModal = ref(false);
const showDocModal = ref(false);

const participantForm = useForm({
    member_id: '',
    notes: '',
});

const docForm = useForm({
    files: [],
    caption: '',
});

const getStatusBadge = (status) => {
    const badges = {
        draft: 'bg-gray-100 text-gray-800',
        published: 'bg-blue-100 text-blue-800',
        ongoing: 'bg-yellow-100 text-yellow-800',
        completed: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800',
    };
    return badges[status] || 'bg-gray-100 text-gray-800';
};

const formatDate = (date) => {
    return new Date(date).toLocaleString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const deleteEvent = () => {
    if (confirm('Apakah Anda yakin ingin menghapus kegiatan ini?')) {
        router.delete(route('events.destroy', props.event));
    }
};

const addParticipant = () => {
    participantForm.post(route('events.participants.add', props.event), {
        onSuccess: () => {
            showParticipantModal.value = false;
            participantForm.reset();
        },
    });
};

const removeParticipant = (member) => {
    if (confirm(`Hapus ${member.full_name} dari daftar peserta?`)) {
        router.delete(route('events.participants.remove', [props.event, member]));
    }
};

const updateAttendance = (member, status) => {
    router.patch(route('events.participants.update-status', [props.event, member]), {
        attendance_status: status,
    });
};

const uploadDoc = () => {
    docForm.post(route('events.documentations.upload', props.event), {
        onSuccess: () => {
            showDocModal.value = false;
            docForm.reset();
        },
    });
};

const deleteDoc = (doc) => {
    if (confirm('Hapus dokumentasi ini?')) {
        router.delete(route('events.documentations.destroy', [props.event, doc]));
    }
};

const handleFileSelect = (e) => {
    docForm.files = Array.from(e.target.files);
};
</script>

<template>
    <Head :title="`Detail Kegiatan - ${event.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Detail Kegiatan
                </h2>
                <div class="flex gap-2">
                    <Link
                        v-if="$page.props.auth.user.role !== 'member'"
                        :href="route('events.edit', event)"
                        class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        Edit
                    </Link>
                    <button
                        v-if="$page.props.auth.user.role === 'admin' || $page.props.auth.user.role === 'ketua'"
                        @click="deleteEvent"
                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        Hapus
                    </button>
                    <Link
                        :href="route('events.index')"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        Kembali
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Info Utama -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900">
                                    {{ event.name }}
                                </h3>
                                <span
                                    :class="getStatusBadge(event.status)"
                                    class="inline-block mt-2 px-3 py-1 text-xs rounded-full font-semibold"
                                >
                                    {{ event.status.charAt(0).toUpperCase() + event.status.slice(1) }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Waktu Pelaksanaan
                                    </h4>
                                    <p class="mt-1 text-gray-900">
                                        {{ formatDate(event.start_date) }}
                                        <span v-if="event.end_date">
                                            - {{ formatDate(event.end_date) }}
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Lokasi
                                    </h4>
                                    <p class="mt-1 text-gray-900">
                                        {{ event.location || '-' }}
                                    </p>
                                </div>
                                <div>
                                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Penanggung Jawab (PIC)
                                    </h4>
                                    <p class="mt-1 text-gray-900">
                                        {{ event.pic ? event.pic.full_name : '-' }}
                                    </p>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Maksimal Peserta
                                    </h4>
                                    <p class="mt-1 text-gray-900">
                                        {{ event.max_participants || 'Tanpa Batas' }}
                                    </p>
                                </div>
                                <div>
                                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Visibilitas Publik
                                    </h4>
                                    <p class="mt-1 text-gray-900">
                                        {{ event.is_public ? 'Ya (Tampil di halaman depan)' : 'Tidak' }}
                                    </p>
                                </div>
                                <div>
                                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Dibuat Oleh
                                    </h4>
                                    <p class="mt-1 text-gray-900 text-sm">
                                        {{ event.creator.name }} pada {{ formatDate(event.created_at) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8">
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Deskripsi Kegiatan
                            </h4>
                            <div class="mt-2 text-gray-700 whitespace-pre-wrap">
                                {{ event.description || 'Tidak ada deskripsi.' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dokumentasi -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg pb-6">
                    <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Dokumentasi ({{ event.documentations.length }})
                        </h3>
                        <button
                            v-if="$page.props.auth.user.role !== 'member'"
                            @click="showDocModal = true"
                            class="inline-flex items-center px-3 py-1 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition"
                        >
                            Upload Foto
                        </button>
                    </div>
                    <div v-if="event.documentations.length > 0" class="p-6 grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        <div v-for="doc in event.documentations" :key="doc.id" class="relative group">
                            <img
                                :src="doc.url"
                                :alt="doc.caption"
                                class="w-full h-32 object-cover rounded shadow"
                            />
                            <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity rounded">
                                <button
                                    v-if="$page.props.auth.user.role !== 'member'"
                                    @click="deleteDoc(doc)"
                                    class="text-white hover:text-red-400"
                                >
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M19 4h-3.5l-1-1h-5l-1 1H5v2h14V4zM6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12z"/>
                                    </svg>
                                </button>
                            </div>
                            <p v-if="doc.caption" class="mt-1 text-xs text-gray-500 truncate">{{ doc.caption }}</p>
                        </div>
                    </div>
                    <div v-else class="p-12 text-center text-gray-500">
                        Belum ada dokumentasi.
                    </div>
                </div>

                <!-- Daftar Peserta -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Daftar Peserta ({{ event.participants.length }} / {{ event.max_participants || '∞' }})
                        </h3>
                        <button
                            v-if="$page.props.auth.user.role !== 'member'"
                            @click="showParticipantModal = true"
                            class="inline-flex items-center px-3 py-1 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition"
                        >
                            Tambah Peserta
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nama Anggota
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tanggal Daftar
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status Kehadiran
                                    </th>
                                    <th v-if="$page.props.auth.user.role !== 'member'" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="participant in event.participants" :key="participant.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ participant.full_name }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ participant.member_code }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ formatDate(participant.pivot.registration_date) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <span class="px-2 py-1 text-xs rounded-full font-semibold"
                                                :class="{
                                                    'bg-blue-100 text-blue-800': participant.pivot.attendance_status === 'registered',
                                                    'bg-green-100 text-green-800': participant.pivot.attendance_status === 'attended',
                                                    'bg-red-100 text-red-800': participant.pivot.attendance_status === 'absent',
                                                }"
                                            >
                                                {{ participant.pivot.attendance_status.charAt(0).toUpperCase() + participant.pivot.attendance_status.slice(1) }}
                                            </span>
                                            
                                            <select
                                                v-if="$page.props.auth.user.role !== 'member'"
                                                :value="participant.pivot.attendance_status"
                                                @change="updateAttendance(participant, $event.target.value)"
                                                class="text-xs rounded border-gray-300 py-0"
                                            >
                                                <option value="registered">Registered</option>
                                                <option value="attended">Attended</option>
                                                <option value="absent">Absent</option>
                                            </select>
                                        </div>
                                    </td>
                                    <td v-if="$page.props.auth.user.role !== 'member'" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button
                                            @click="removeParticipant(participant)"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="event.participants.length === 0">
                                    <td
                                        colspan="4"
                                        class="px-6 py-12 text-center text-gray-500"
                                    >
                                        Belum ada peserta terdaftar.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Participant Modal -->
        <Modal :show="showParticipantModal" @close="showParticipantModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Tambah Peserta Kegiatan
                </h2>

                <div class="mt-4 space-y-4">
                    <div>
                        <InputLabel for="member_id" value="Pilih Anggota" />
                        <select
                            id="member_id"
                            v-model="participantForm.member_id"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        >
                            <option value="">Pilih Member...</option>
                            <option v-for="member in members" :key="member.id" :value="member.id">
                                {{ member.full_name }} ({{ member.member_code }})
                            </option>
                        </select>
                        <InputError class="mt-2" :message="participantForm.errors.member_id" />
                    </div>

                    <div>
                        <InputLabel for="notes" value="Catatan (Opsional)" />
                        <textarea
                            id="notes"
                            v-model="participantForm.notes"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            rows="2"
                        ></textarea>
                        <InputError class="mt-2" :message="participantForm.errors.notes" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="showParticipantModal = false"> Batal </SecondaryButton>

                    <PrimaryButton
                        class="ml-3"
                        :class="{ 'opacity-25': participantForm.processing }"
                        :disabled="participantForm.processing"
                        @click="addParticipant"
                    >
                        Tambah Peserta
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Documentation Modal -->
        <Modal :show="showDocModal" @close="showDocModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Upload Dokumentasi (Foto)
                </h2>

                <div class="mt-4 space-y-4">
                    <div>
                        <InputLabel for="files" value="Pilih Foto" />
                        <input
                            type="file"
                            id="files"
                            multiple
                            accept="image/*"
                            @change="handleFileSelect"
                            class="mt-1 block w-full border text-sm"
                        />
                        <InputError class="mt-2" :message="docForm.errors['files.0']" />
                    </div>

                    <div>
                        <InputLabel for="caption" value="Keterangan (Opsional)" />
                        <TextInput
                            id="caption"
                            v-model="docForm.caption"
                            type="text"
                            class="mt-1 block w-full"
                        />
                        <InputError class="mt-2" :message="docForm.errors.caption" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="showDocModal = false"> Batal </SecondaryButton>

                    <PrimaryButton
                        class="ml-3"
                        :class="{ 'opacity-25': docForm.processing }"
                        :disabled="docForm.processing"
                        @click="uploadDoc"
                    >
                        Simpan Foto
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
