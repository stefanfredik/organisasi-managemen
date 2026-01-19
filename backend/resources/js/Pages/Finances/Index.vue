<script setup>
import { ref } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    finances: Object,
    wallets: Array,
});

const showModal = ref(false);

const form = useForm({
    wallet_id: '',
    type: 'in',
    amount: '',
    category: '',
    description: '',
    transaction_date: new Date().toISOString().split('T')[0],
    receipt: null,
});

const submit = () => {
    form.post(route('finances.store'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        },
    });
};

const deleteFinance = (finance) => {
    if (confirm('Apakah Anda yakin ingin menghapus transaksi ini? Saldo dompet akan disesuaikan kembali.')) {
        form.delete(route('finances.destroy', finance.id));
    }
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(amount);
};

const getStatusClass = (type) => {
    return type === 'in' ? 'text-green-600 bg-green-50' : 'text-red-600 bg-red-50';
};
</script>

<template>
    <Head title="Transaksi Keuangan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Transaksi Keuangan
                </h2>
                <PrimaryButton @click="showModal = true">
                    Catat Transaksi
                </PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm rounded-xl">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <h3 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Riwayat Transaksi</h3>
                        <div class="flex gap-2 text-xs">
                            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-green-500"></span> Pemasukan</span>
                            <span class="flex items-center gap-1 ml-2"><span class="w-2 h-2 rounded-full bg-red-500"></span> Pengeluaran</span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Dompet</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Kategori</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Keterangan</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Jumlah</th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-400 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr v-for="finance in finances.data" :key="finance.id" class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-medium">
                                        {{ new Date(finance.transaction_date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded">{{ finance.wallet.name }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold italic">
                                        {{ finance.category }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                        {{ finance.description }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 rounded-full text-sm font-black" :class="getStatusClass(finance.type)">
                                            {{ finance.type === 'in' ? '+' : '-' }} {{ formatCurrency(finance.amount) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button @click="deleteFinance(finance)" class="text-red-400 hover:text-red-600 transition p-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="finances.data.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500 italic">Belum ada transaksi tercatat.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="finances.links.length > 3" class="px-6 py-4 border-t border-gray-100 flex justify-center">
                        <div class="flex flex-wrap gap-1">
                            <Link v-for="(link, index) in finances.links" :key="index" :href="link.url" 
                                class="px-4 py-2 text-xs font-bold rounded-lg transition"
                                :class="[link.active ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200', !link.url ? 'opacity-30 cursor-not-allowed' : '']"
                                v-html="link.label" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Catat Transaksi -->
        <Modal :show="showModal" @close="showModal = false">
            <div class="p-8">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-xl font-black text-gray-900 uppercase tracking-tight">Catat Transaksi Baru</h2>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <InputLabel value="Jenis Transaksi" class="text-xs font-black uppercase text-gray-400 mb-2" />
                            <div class="flex gap-2">
                                <button type="button" @click="form.type = 'in'" 
                                    class="flex-1 py-3 px-4 rounded-xl border-2 font-bold transition flex items-center justify-center gap-2"
                                    :class="form.type === 'in' ? 'border-green-500 bg-green-50 text-green-700' : 'border-gray-100 text-gray-400 hover:bg-gray-50'">
                                    <span class="w-2 h-2 rounded-full bg-green-500"></span> Pemasukan
                                </button>
                                <button type="button" @click="form.type = 'out'" 
                                    class="flex-1 py-3 px-4 rounded-xl border-2 font-bold transition flex items-center justify-center gap-2"
                                    :class="form.type === 'out' ? 'border-red-500 bg-red-50 text-red-700' : 'border-gray-100 text-gray-400 hover:bg-gray-50'">
                                    <span class="w-2 h-2 rounded-full bg-red-500"></span> Pengeluaran
                                </button>
                            </div>
                        </div>
                        <div>
                            <InputLabel value="Tanggal" class="text-xs font-black uppercase text-gray-400 mb-2" />
                            <TextInput type="date" class="w-full border-gray-100 rounded-xl font-bold" v-model="form.transaction_date" required />
                        </div>
                    </div>

                    <div>
                        <InputLabel value="Pilih Dompet (Kas)" class="text-xs font-black uppercase text-gray-400 mb-2" />
                        <select v-model="form.wallet_id" class="w-full border-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm font-bold text-gray-700" required>
                            <option value="">-- Pilih Dompet --</option>
                            <option v-for="wallet in wallets" :key="wallet.id" :value="wallet.id">
                                {{ wallet.name }} (Saldo: {{ formatCurrency(wallet.balance) }})
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.wallet_id" />
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <InputLabel value="Kategori" class="text-xs font-black uppercase text-gray-400 mb-2" />
                            <TextInput type="text" class="w-full border-gray-100 rounded-xl font-bold" v-model="form.category" required placeholder="Misal: Konsumsi, Sewa, Donasi" />
                            <InputError class="mt-2" :message="form.errors.category" />
                        </div>
                        <div>
                            <InputLabel value="Jumlah (Rp)" class="text-xs font-black uppercase text-gray-400 mb-2" />
                            <TextInput type="number" step="0.01" class="w-full border-gray-100 rounded-xl font-black text-indigo-600" v-model="form.amount" required placeholder="0" />
                            <InputError class="mt-2" :message="form.errors.amount" />
                        </div>
                    </div>

                    <div>
                        <InputLabel value="Keterangan" class="text-xs font-black uppercase text-gray-400 mb-2" />
                        <textarea v-model="form.description" rows="3" class="w-full border-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm font-medium" placeholder="Tulis rincian transaksi di sini..." required></textarea>
                        <InputError class="mt-2" :message="form.errors.description" />
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <SecondaryButton @click="showModal = false" class="px-8 py-3 rounded-xl"> Batal </SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing" class="px-10 py-3 rounded-xl shadow-lg shadow-indigo-100 font-black uppercase tracking-widest">
                            Simpan Transaksi
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
