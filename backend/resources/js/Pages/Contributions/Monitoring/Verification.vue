<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { ref } from 'vue';

const props = defineProps({
    pendingTransactions: Object,
});

const formatCurrency = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(val);
const formatDate = (date) => new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });

const showingProofModal = ref(false);
const selectedProofUrl = ref(null);
const processingId = ref(null);

const showProof = (url) => {
    selectedProofUrl.value = url;
    showingProofModal.value = true;
};

const closeProofModal = () => {
    showingProofModal.value = false;
    selectedProofUrl.value = null;
};

const verifyTransaction = (id, action) => {
    if (!confirm(`Apakah Anda yakin ingin ${action === 'approve' ? 'menyewtujui' : 'menolak'} transaksi ini?`)) return;

    processingId.value = id;
    router.post(route('contributions.verify-action', id), {
        action: action
    }, {
        onFinish: () => processingId.value = null,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Verifikasi Iuran" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Verifikasi Pembayaran
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                
                <!-- Navigation Tabs -->
                <div class="flex space-x-2 border-b border-gray-200 pb-2 overflow-x-auto">
                    <Link :href="route('contributions.monitoring.index')" class="px-4 py-2 text-sm font-bold rounded-lg text-gray-600 hover:bg-gray-50">
                        Jenis Iuran Aktif
                    </Link>
                    <Link :href="route('contributions.verification')" class="px-4 py-2 text-sm font-bold rounded-lg bg-indigo-50 text-indigo-700">
                        Verifikasi
                    </Link>
                     <Link :href="route('contributions.index')" class="px-4 py-2 text-sm font-bold rounded-lg text-gray-600 hover:bg-gray-50">
                        Riwayat Transaksi
                    </Link>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-800">Menunggu Verifikasi</h3>
                        <span class="bg-amber-100 text-amber-800 text-xs font-bold px-3 py-1 rounded-full">
                            {{ pendingTransactions.total }} Permintaan
                        </span>
                    </div>

                    <div v-if="pendingTransactions.data.length === 0" class="p-12 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 mb-4">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">Semua Bersih!</h3>
                        <p class="text-gray-500 mt-1">Tidak ada pembayaran yang menunggu verifikasi saat ini.</p>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead class="bg-gray-50 text-xs uppercase font-bold text-gray-400">
                                <tr>
                                    <th class="px-6 py-4">Tanggal</th>
                                    <th class="px-6 py-4">Anggota</th>
                                    <th class="px-6 py-4">Jenis Iuran</th>
                                    <th class="px-6 py-4">Periode</th>
                                    <th class="px-6 py-4">Jumlah</th>
                                    <th class="px-6 py-4">Bukti</th>
                                    <th class="px-6 py-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="trx in pendingTransactions.data" :key="trx.id" class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 bg-white font-medium text-gray-900">{{ formatDate(trx.created_at) }}</td>
                                    <td class="px-6 py-4 bg-white">
                                        <div class="font-bold text-gray-900">{{ trx.member?.full_name || 'Unknown' }}</div>
                                        <div class="text-xs text-gray-400">{{ trx.member?.member_code }}</div>
                                    </td>
                                    <td class="px-6 py-4 bg-white">
                                        {{ trx.type?.name }}
                                    </td>
                                    <td class="px-6 py-4 bg-white">
                                        <span class="text-xs bg-gray-100 px-2 py-0.5 rounded text-gray-600">
                                            {{ trx.payment_period || '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 bg-white font-bold text-gray-900">
                                        {{ formatCurrency(trx.amount) }}
                                    </td>
                                    <td class="px-6 py-4 bg-white">
                                        <button v-if="trx.receipt_path" @click="showProof('/storage/' + trx.receipt_path)" class="text-indigo-600 hover:text-indigo-800 font-bold text-xs underline">
                                            Lihat Bukti
                                        </button>
                                        <span v-else class="text-gray-400 text-xs italic">Tanpa Bukti</span>
                                    </td>
                                    <td class="px-6 py-4 bg-white text-right space-x-2">
                                        <button 
                                            @click="verifyTransaction(trx.id, 'reject')" 
                                            :disabled="processingId === trx.id"
                                            class="px-3 py-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 text-xs font-bold transition-colors disabled:opacity-50">
                                            Tolak
                                        </button>
                                        <button 
                                            @click="verifyTransaction(trx.id, 'approve')" 
                                            :disabled="processingId === trx.id"
                                            class="px-3 py-1.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm text-xs font-bold transition-colors disabled:opacity-50">
                                            Setujui
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Proof Modal -->
        <Modal :show="showingProofModal" @close="closeProofModal">
            <div class="p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Bukti Pembayaran</h3>
                <div class="flex justify-center bg-gray-100 rounded-lg p-4 mb-4">
                    <img :src="selectedProofUrl" alt="Bukti Transfer" class="max-h-[70vh] object-contain rounded shadow-sm" />
                </div>
                <div class="flex justify-end">
                    <SecondaryButton @click="closeProofModal">
                        Tutup
                    </SecondaryButton>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
