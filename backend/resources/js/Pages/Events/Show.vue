<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
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
const zoomedIndex = ref(null);

const zoomedDoc = computed(() => {
    if (zoomedIndex.value === null) return null;
    return props.event.documentations[zoomedIndex.value];
});

const prevDoc = () => {
    if (zoomedIndex.value > 0) {
        zoomedIndex.value--;
    } else {
        zoomedIndex.value = props.event.documentations.length - 1;
    }
};

const nextDoc = () => {
    if (zoomedIndex.value < props.event.documentations.length - 1) {
        zoomedIndex.value++;
    } else {
        zoomedIndex.value = 0;
    }
};

const handleKeyDown = (e) => {
    if (zoomedIndex.value === null) return;
    if (e.key === 'ArrowLeft') prevDoc();
    if (e.key === 'ArrowRight') nextDoc();
    if (e.key === 'Escape') zoomedIndex.value = null;
};

onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
});

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
                        v-if="hasPermission('manage_events')"
                        :href="route('events.edit', event)"
                        class="inline-flex items-center justify-center rounded-xl border border-transparent bg-orange-600 px-4 py-3 text-xs font-bold uppercase tracking-widest text-white transition duration-200 ease-in-out hover:bg-orange-700 active:bg-orange-800 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 shadow-md shadow-orange-100"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                    </Link>
                    <button
                        v-if="hasPermission('manage_events')"
                        @click="deleteEvent"
                        class="inline-flex items-center justify-center rounded-xl border border-transparent bg-red-600 px-4 py-3 text-xs font-bold uppercase tracking-widest text-white transition duration-200 ease-in-out hover:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 shadow-md shadow-red-100"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1 1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                    <Link
                        :href="route('events.index')"
                        class="inline-flex items-center justify-center rounded-xl border border-transparent bg-gray-600 px-4 py-3 text-xs font-bold uppercase tracking-widest text-white transition duration-200 ease-in-out hover:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 shadow-md shadow-gray-100"
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
                            v-if="hasPermission('manage_events')"
                            @click="showDocModal = true"
                            class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2 text-xs font-bold uppercase tracking-widest text-white hover:bg-indigo-700 transition shadow-sm"
                        >
                            Upload Foto
                        </button>
                    </div>
                    <div v-if="event.documentations.length > 0" class="p-6 grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        <div v-for="(doc, index) in event.documentations" :key="doc.id" class="relative group">
                            <img
                                :src="doc.url"
                                :alt="doc.caption"
                                class="w-full h-32 object-cover rounded shadow"
                            />
                            <div 
                                class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity rounded cursor-pointer"
                                @click="zoomedIndex = index"
                            >
                                <button
                                    v-if="hasPermission('manage_events')"
                                    @click.stop="deleteDoc(doc)"
                                    class="text-white hover:text-red-400 p-2"
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
                            v-if="hasPermission('manage_events')"
                            @click="showParticipantModal = true"
                            class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2 text-xs font-bold uppercase tracking-widest text-white hover:bg-indigo-700 transition shadow-sm"
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
                                    <th v-if="hasPermission('manage_events')" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
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
                                                v-if="hasPermission('manage_events')"
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
                                    <td v-if="hasPermission('manage_events')" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button
                                            @click="removeParticipant(participant)"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1 1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
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

        <!-- Documentation Zoom Modal -->
        <Modal :show="zoomedIndex !== null" @close="zoomedIndex = null" maxWidth="4xl">
            <div class="p-4 flex flex-col items-center relative group">
                <div class="w-full flex justify-end mb-2">
                    <button @click="zoomedIndex = null" class="text-gray-500 hover:text-gray-700">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Navigation Buttons -->
                <button
                    v-if="event.documentations.length > 1"
                    @click="prevDoc"
                    class="absolute left-6 top-1/2 -translate-y-1/2 bg-black/50 text-white p-2 rounded-full hover:bg-black/70 transition opacity-0 group-hover:opacity-100 focus:opacity-100"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <button
                    v-if="event.documentations.length > 1"
                    @click="nextDoc"
                    class="absolute right-6 top-1/2 -translate-y-1/2 bg-black/50 text-white p-2 rounded-full hover:bg-black/70 transition opacity-0 group-hover:opacity-100 focus:opacity-100"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                <img
                    v-if="zoomedDoc"
                    :src="zoomedDoc.url"
                    :alt="zoomedDoc.caption"
                    class="max-w-full max-h-[85vh] rounded shadow-xl"
                />
                
                <div v-if="zoomedDoc" class="mt-4 text-center">
                    <p v-if="zoomedDoc.caption" class="text-gray-700 font-medium">
                        {{ zoomedDoc.caption }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                        {{ zoomedIndex + 1 }} / {{ event.documentations.length }}
                    </p>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
