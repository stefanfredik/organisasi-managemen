<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    contributions: Object,
    types: Array,
    wallets: Array,
    members: Array,
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const isAdminOrTreasurer = computed(() => ['admin', 'treasurer'].includes(user.value.role));

const showCreateModal = ref(false);
const showVerifyModal = ref(false);
const selectedContribution = ref(null);

const form = useForm({
    member_id: user.value.role === 'member' ? user.value.member?.id : '',
    contribution_type_id: '',
    wallet_id: '',
    amount: '',
    payment_date: new Date().toISOString().split('T')[0],
    notes: '',
    receipt: null,
});

const verifyForm = useForm({
    status: 'paid',
});

const openVerifyModal = (contribution) => {
    selectedContribution.value = contribution;
    showVerifyModal.value = true;
};

const submitVerify = () => {
    verifyForm.post(route('contributions.verify', selectedContribution.value.id), {
        onSuccess: () => {
            showVerifyModal.value = false;
            selectedContribution.value = null;
        },
    });
};

const submitCreate = () => {
    form.post(route('contributions.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            form.reset();
        },
    });
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(amount);
};

const getStatusBadge = (status) => {
    const map = {
        pending: 'bg-yellow-100 text-yellow-700',
        paid: 'bg-green-100 text-green-700',
        rejected: 'bg-red-100 text-red-700',
    };
    return map[status] || 'bg-gray-100 text-gray-700';
};

const statusLabels = {
    pending: 'Menunggu Verifikasi',
    paid: 'Sudah Dibayar',
    rejected: 'Ditolak',
};
</script>

<template>
    <Head title="Iuran Anggota" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    {{ isAdminOrTreasurer ? 'Manajemen Iuran Anggota' : 'Riwayat Iuran Saya' }}
                </h2>
                <PrimaryButton @click="showCreateModal = true">
                    Bayar Iuran
                </PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Data Table -->
                <div class="bg-white overflow-hidden shadow-sm rounded-xl">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <h3 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Daftar Pembayaran</h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50/50 uppercase tracking-widest text-[10px] font-bold text-gray-400">
                                <tr>
                                    <th v-if="isAdminOrTreasurer" class="px-6 py-4 text-left">Anggota</th>
                                    <th class="px-6 py-4 text-left">Jenis Iuran</th>
                                    <th class="px-6 py-4 text-left">Tanggal Bayar</th>
                                    <th class="px-6 py-4 text-left">Jumlah</th>
                                    <th class="px-6 py-4 text-left">Status</th>
                                    <th class="px-6 py-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 italic">
                                <tr v-for="contribution in contributions.data" :key="contribution.id" class="hover:bg-gray-50/50 transition font-medium">
                                    <td v-if="isAdminOrTreasurer" class="px-6 py-4">
                                        <div class="text-sm font-bold text-gray-900">{{ contribution.member.full_name }}</div>
                                        <div class="text-[10px] text-gray-400">{{ contribution.member.member_code }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-gray-700">{{ contribution.type.name }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 font-bold">
                                        {{ new Date(contribution.payment_date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-black text-indigo-600">
                                        {{ formatCurrency(contribution.amount) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tight" :class="getStatusBadge(contribution.status)">
                                            {{ statusLabels[contribution.status] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <a v-if="contribution.receipt_path" :href="'/storage/' + contribution.receipt_path" target="_blank" class="p-2 text-indigo-400 hover:bg-indigo-50 rounded-lg transition" title="Lihat Bukti Bayar">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                            <button v-if="isAdminOrTreasurer && contribution.status === 'pending'" @click="openVerifyModal(contribution)" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-md shadow-indigo-100 hover:bg-indigo-700 transition">
                                                Verifikasi
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="contributions.data.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500 italic font-medium">Belum ada riwayat pembayaran iuran.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="contributions.links.length > 3" class="px-6 py-4 border-t border-gray-100 flex justify-center">
                        <div class="flex flex-wrap gap-1">
                            <Link v-for="(link, index) in contributions.links" :key="index" :href="link.url" 
                                class="px-4 py-2 text-[10px] font-black rounded-lg transition uppercase tracking-tighter"
                                :class="[link.active ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-gray-500 hover:bg-gray-50 border border-gray-100', !link.url ? 'opacity-30 cursor-not-allowed' : '']"
                                v-html="link.label" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Bayar Iuran -->
        <Modal :show="showCreateModal" @close="showCreateModal = false">
            <div class="p-8">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-xl font-black text-gray-900 uppercase tracking-tight">Bayar Iuran Baru</h2>
                    <button @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitCreate" class="space-y-6">
                    <div v-if="isAdminOrTreasurer">
                        <InputLabel value="Pilih Anggota" class="text-[10px] font-bold uppercase text-gray-400 mb-2" />
                        <select v-model="form.member_id" class="w-full border-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl font-bold text-gray-700 shadow-sm" required>
                            <option value="">-- Cari Nama/Kode Anggota --</option>
                            <option v-for="member in members" :key="member.id" :value="member.id">
                                {{ member.full_name }} ({{ member.member_code }})
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.member_id" />
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <InputLabel value="Jenis Iuran" class="text-[10px] font-bold uppercase text-gray-400 mb-2" />
                            <select v-model="form.contribution_type_id" class="w-full border-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl font-bold text-gray-700 shadow-sm" required @change="(e) => {
                                const type = types.find(t => t.id == e.target.value);
                                if (type) form.amount = type.amount;
                            }">
                                <option value="">-- Pilih Jenis --</option>
                                <option v-for="type in types" :key="type.id" :value="type.id">{{ type.name }} ({{ formatCurrency(type.amount) }})</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.contribution_type_id" />
                        </div>
                        <div>
                            <InputLabel value="Tanggal Bayar" class="text-[10px] font-bold uppercase text-gray-400 mb-2" />
                            <TextInput type="date" class="w-full border-gray-100 rounded-xl font-bold" v-model="form.payment_date" required />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <InputLabel value="Nominal (Rp)" class="text-[10px] font-bold uppercase text-gray-400 mb-2" />
                            <TextInput type="number" class="w-full border-gray-100 rounded-xl font-black text-indigo-600" v-model="form.amount" required />
                            <InputError class="mt-2" :message="form.errors.amount" />
                        </div>
                        <div>
                            <InputLabel value="Pilih Rekening Tujuan" class="text-[10px] font-bold uppercase text-gray-400 mb-2" />
                            <select v-model="form.wallet_id" class="w-full border-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl font-bold text-gray-700 shadow-sm" required>
                                <option value="">-- Rekening Kas --</option>
                                <option v-for="wallet in wallets" :key="wallet.id" :value="wallet.id">{{ wallet.name }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.wallet_id" />
                        </div>
                    </div>

                    <div>
                        <InputLabel value="Catatan Tambahan (Opsional)" class="text-[10px] font-bold uppercase text-gray-400 mb-2" />
                        <textarea v-model="form.notes" rows="2" class="w-full border-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm font-medium" placeholder="Misal: Pembayaran lewat transfer BCA..."></textarea>
                    </div>

                    <div class="bg-gray-50 p-6 rounded-2xl border-2 border-dashed border-gray-100">
                        <InputLabel value="Lampirkan Bukti Foto/Struk" class="text-[10px] font-black uppercase text-indigo-400 mb-3" />
                        <input type="file" @input="form.receipt = $event.target.files[0]" accept="image/*" class="block w-full text-xs text-gray-400 file:mr-4 file:py-2.5 file:px-6 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:uppercase file:tracking-widest file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 transition file:cursor-pointer" />
                        <p class="mt-2 text-[10px] text-gray-400 italic font-medium">* Maksimal ukuran file 2MB (format gambar).</p>
                        <InputError class="mt-2" :message="form.errors.receipt" />
                    </div>

                    <div class="mt-8 flex justify-end gap-3 font-bold">
                        <SecondaryButton @click="showCreateModal = false" class="px-8 py-3 rounded-xl"> Batal </SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing" class="px-10 py-3 rounded-xl shadow-xl shadow-indigo-100 uppercase tracking-widest text-xs font-black">
                            Konfirmasi & Kirim
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Modal Verifikasi -->
        <Modal :show="showVerifyModal" @close="showVerifyModal = false">
            <div class="p-8">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-xl font-black text-gray-900 uppercase tracking-tight">Verifikasi Pembayaran</h2>
                    <button @click="showVerifyModal = false" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div v-if="selectedContribution" class="mb-8 p-6 bg-indigo-50 rounded-2xl border border-indigo-100">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-[10px] uppercase font-black text-indigo-300 mb-1">Penyetor</p>
                            <p class="text-sm font-black text-indigo-900">{{ selectedContribution.member.full_name }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-black text-indigo-300 mb-1">Nominal</p>
                            <p class="text-sm font-black text-indigo-900">{{ formatCurrency(selectedContribution.amount) }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-[10px] uppercase font-black text-indigo-300 mb-1">Peruntukan</p>
                            <p class="text-sm font-black text-indigo-900">{{ selectedContribution.type.name }}</p>
                        </div>
                    </div>
                    <div v-if="selectedContribution.notes" class="mt-4 pt-4 border-t border-indigo-100">
                        <p class="text-[10px] uppercase font-black text-indigo-300 mb-1">Catatan</p>
                        <p class="text-xs italic text-indigo-600">"{{ selectedContribution.notes }}"</p>
                    </div>
                </div>

                <form @submit.prevent="submitVerify" class="space-y-8 text-center">
                    <div>
                        <InputLabel value="Tentukan Status Pembayaran" class="text-[10px] font-black uppercase text-gray-400 mb-6" />
                        <div class="flex gap-4">
                            <button type="button" @click="verifyForm.status = 'paid'" class="flex-1 py-5 rounded-2xl border-4 font-black transition text-center text-xs tracking-widest" :class="verifyForm.status === 'paid' ? 'border-green-500 bg-green-50 text-green-700 shadow-xl shadow-green-100' : 'border-gray-50 text-gray-300 hover:bg-gray-50'">
                                TERIMA & VALIDASI
                            </button>
                            <button type="button" @click="verifyForm.status = 'rejected'" class="flex-1 py-5 rounded-2xl border-4 font-black transition text-center text-xs tracking-widest" :class="verifyForm.status === 'rejected' ? 'border-red-500 bg-red-50 text-red-700 shadow-xl shadow-red-100' : 'border-gray-50 text-gray-300 hover:bg-gray-50'">
                                TOLAK PEMBAYARAN
                            </button>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 font-bold pt-4 border-t border-gray-50">
                        <SecondaryButton @click="showVerifyModal = false" class="px-8 py-3 rounded-xl border-0"> Batal </SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': verifyForm.processing }" :disabled="verifyForm.processing" class="px-10 py-4 rounded-2xl shadow-xl shadow-indigo-100 uppercase tracking-widest text-[10px] font-black">
                            Simpan Perubahan
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
