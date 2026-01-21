<script setup>
import { ref, watch } from 'vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import SearchBar from '@/Components/SearchBar.vue';
import FilterDropdown from '@/Components/FilterDropdown.vue';

const props = defineProps({
    finances: Object,
    wallets: Array,
    filters: Object,
    stats: Object,
});

const showModal = ref(false);
const showDetailModal = ref(false);
const selectedFinance = ref(null);

// Filter states
const search = ref(props.filters.search || '');
const wallet_id = ref(props.filters.wallet_id || '');
const type = ref(props.filters.type || '');
const date_from = ref(props.filters.date_from || '');
const date_to = ref(props.filters.date_to || '');

watch([search, wallet_id, type, date_from, date_to], () => {
    router.get(route('finances.index'), {
        search: search.value,
        wallet_id: wallet_id.value,
        type: type.value,
        date_from: date_from.value,
        date_to: date_to.value,
    }, {
        preserveState: true,
        replace: true,
    });
});

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
        router.delete(route('finances.destroy', finance.id), {
            preserveScroll: true,
        });
    }
};

const openDetail = (finance) => {
    selectedFinance.value = finance;
    showDetailModal.value = true;
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(amount);
};

const getStatusClass = (type) => {
    return type === 'in' ? 'text-green-600 bg-green-50' : 'text-red-600 bg-red-50';
};

const walletOptions = props.wallets.map(w => ({ value: w.id, label: w.name }));
const typeOptions = [
    { value: 'in', label: 'Pemasukan' },
    { value: 'out', label: 'Pengeluaran' },
    { value: 'out', label: 'Pengeluaran' },
];

const categories = {
    in: [
        'Iuran Anggota',
        'Donasi / Sumbangan',
        'Sponsorship',
        'Dana Hibah',
        'Penjualan / Fundraising',
        'Bunga Bank',
        'Pengembalian Dana',
        'Lainnya'
    ],
    out: [
        'Konsumsi & Logistik',
        'Transportasi & Perjalanan',
        'Perlengkapan & Peralatan',
        'Sewa Tempat / Gedung',
        'Listrik, Air & Internet',
        'Cetak, Fotokopi & ATK',
        'Honorarium / Jasa',
        'Pemeliharaan & Perbaikan',
        'Acara / Kegiatan',
        'Bantuan Sosial',
        'Promosi & Publikasi',
        'Lainnya'
    ]
};

watch(() => form.type, () => {
    form.category = '';
});
</script>

<template>
    <Head title="Transaksi Keuangan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Transaksi Keuangan
                </h2>
                <PrimaryButton @click="showModal = true" v-if="hasPermission('manage_finance')">
                    Catat Transaksi
                </PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Summary Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <!-- Total Pemasukan -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden group hover:shadow-md transition-all">
                        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                            <svg class="w-16 h-16 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a8 8 0 1016 0V6a2 2 0 00-2-2H4zm2 3a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm2 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="relative z-10">
                            <p class="text-[10px] font-black uppercase tracking-widest text-green-400 mb-1">Total Pemasukan (Global)</p>
                            <h3 class="text-3xl font-black text-green-700 leading-tight">
                                {{ formatCurrency(stats.total_income) }}
                            </h3>
                            <div class="mt-4 flex items-center text-xs text-green-600 font-bold">
                                <span>Kumulatif dana masuk organisasi</span>
                            </div>
                        </div>
                    </div>

                    <!-- Total Pengeluaran -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden group hover:shadow-md transition-all">
                        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                            <svg class="w-16 h-16 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 100-2 1 1 0 000 2zm7-1a1 1 0 11-2 0 1 1 0 012 0zm-7.535 5.035a1 1 0 101.415-1.414 3 3 0 014.242 0 1 1 0 001.415 1.414 5 5 0 00-7.072 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="relative z-10">
                            <p class="text-[10px] font-black uppercase tracking-widest text-red-400 mb-1">Total Pengeluaran (Global)</p>
                            <h3 class="text-3xl font-black text-red-700 leading-tight">
                                {{ formatCurrency(stats.total_expense) }}
                            </h3>
                            <div class="mt-4 flex items-center text-xs text-red-600 font-bold">
                                <span>Kumulatif dana keluar organisasi</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Filters Bar -->
                <div class="bg-white p-6 rounded-xl shadow-sm mb-6 border border-gray-100">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                        <div class="lg:col-span-1">
                            <InputLabel value="Cari" class="text-[10px] font-black uppercase text-gray-400 mb-1" />
                            <SearchBar v-model="search" placeholder="Kategori atau Ket..." />
                        </div>
                        <div>
                            <InputLabel value="Dompet" class="text-[10px] font-black uppercase text-gray-400 mb-1" />
                            <FilterDropdown v-model="wallet_id" :options="walletOptions" label="Semua Dompet" />
                        </div>
                        <div>
                            <InputLabel value="Jenis" class="text-[10px] font-black uppercase text-gray-400 mb-1" />
                            <FilterDropdown v-model="type" :options="typeOptions" label="Semua Jenis" />
                        </div>
                        <div>
                            <InputLabel value="Dari Tanggal" class="text-[10px] font-black uppercase text-gray-400 mb-1" />
                            <TextInput type="date" v-model="date_from" class="w-full text-xs" />
                        </div>
                        <div>
                            <InputLabel value="Sampai Tanggal" class="text-[10px] font-black uppercase text-gray-400 mb-1" />
                            <TextInput type="date" v-model="date_to" class="w-full text-xs" />
                        </div>
                    </div>
                </div>

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
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 rounded-full text-sm font-black" :class="getStatusClass(finance.type)">
                                            {{ finance.type === 'in' ? '+' : '-' }} {{ formatCurrency(finance.amount) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            <button @click="openDetail(finance)" class="text-indigo-400 hover:text-indigo-600 transition p-2" title="Detail Transaksi">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </button>
                                            <button @click="deleteFinance(finance)" v-if="hasPermission('manage_finance')" class="text-red-300 hover:text-red-500 transition p-2" title="Hapus">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="finances.data.length === 0">
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500 italic">Belum ada transaksi ditemukan.</td>
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
            <div class="bg-white rounded-2xl overflow-hidden">
                <div class="bg-gray-50/50 p-6 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-black text-gray-900 uppercase tracking-tight">Catat Transaksi Baru</h2>
                        <p class="text-xs text-gray-500 mt-1">Masukkan detail pemasukan atau pengeluaran.</p>
                    </div>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 transition p-2 rounded-full hover:bg-white">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-8">
                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <!-- Main Type Selector (Segmented Control Style) -->
                        <div class="grid grid-cols-2 gap-4 p-1 bg-gray-100/80 rounded-2xl">
                            <button type="button" @click="form.type = 'in'" 
                                class="relative py-3 rounded-xl font-black uppercase tracking-wider text-xs transition-all duration-200 flex items-center justify-center gap-2 overflow-hidden"
                                :class="form.type === 'in' ? 'bg-white shadow-sm text-green-600 ring-2 ring-green-500/20' : 'text-gray-400 hover:text-gray-600'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" /></svg>
                                Pemasukan
                            </button>
                            <button type="button" @click="form.type = 'out'" 
                                class="relative py-3 rounded-xl font-black uppercase tracking-wider text-xs transition-all duration-200 flex items-center justify-center gap-2 overflow-hidden"
                                :class="form.type === 'out' ? 'bg-white shadow-sm text-red-600 ring-2 ring-red-500/20' : 'text-gray-400 hover:text-gray-600'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6" /></svg>
                                Pengeluaran
                            </button>
                        </div>

                        <!-- Amount Input (Large & Centered) -->
                        <div class="relative">
                            <label class="block text-center text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2">
                                Nominal (Rp)
                            </label>
                            <div class="relative max-w-xs mx-auto">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold">Rp</span>
                                <input type="number" v-model="form.amount" required step="0.01" placeholder="0"
                                    class="w-full pl-12 pr-4 py-4 text-3xl font-black text-center border-2 border-gray-100 rounded-2xl focus:border-indigo-500 focus:ring-0 transition placeholder-gray-200" 
                                    :class="form.type === 'in' ? 'text-green-600' : 'text-red-600'" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Wallet Selection with Info -->
                            <div class="space-y-2">
                                <InputLabel value="Dompet / Sumber Dana" class="text-[10px] font-black uppercase text-gray-400" />
                                <div class="relative">
                                    <select v-model="form.wallet_id" class="w-full pl-10 pr-4 py-3 border-gray-200 rounded-xl text-sm font-bold text-gray-700 focus:ring-indigo-500 focus:border-indigo-500 appearance-none bg-white">
                                        <option value="">-- Pilih Dompet --</option>
                                        <option v-for="wallet in wallets" :key="wallet.id" :value="wallet.id">
                                            {{ wallet.name }}
                                        </option>
                                    </select>
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                                    </div>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </div>
                                </div>
                                <!-- Informative Balance -->
                                <div v-if="form.wallet_id" class="flex items-center justify-between px-3 py-2 bg-indigo-50 rounded-lg border border-indigo-100">
                                    <span class="text-[10px] uppercase font-bold text-indigo-400">Saldo Saat Ini</span>
                                    <span class="text-xs font-black text-indigo-700 font-mono">
                                        {{ formatCurrency(wallets.find(w => w.id === form.wallet_id)?.balance || 0) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Date Input -->
                            <div class="space-y-2">
                                <InputLabel value="Tanggal Transaksi" class="text-[10px] font-black uppercase text-gray-400" />
                                <div class="relative">
                                    <input type="date" v-model="form.transaction_date" required class="w-full pl-10 pr-4 py-3 border-gray-200 rounded-xl text-sm font-bold text-gray-700 focus:ring-indigo-500 focus:border-indigo-500" />
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Category -->
                        <div class="space-y-2">
                            <InputLabel value="Kategori" class="text-[10px] font-black uppercase text-gray-400" />
                            <div class="relative">
                                <select v-model="form.category" required class="w-full pl-10 pr-10 py-3 border-gray-200 rounded-xl text-sm font-bold text-gray-700 focus:ring-indigo-500 focus:border-indigo-500 appearance-none bg-white">
                                    <option value="" disabled selected>-- Pilih Kategori --</option>
                                    <option v-for="cat in categories[form.type]" :key="cat" :value="cat">
                                        {{ cat }}
                                    </option>
                                </select>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
                                </div>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="space-y-2">
                             <InputLabel value="Keterangan (Opsional)" class="text-[10px] font-black uppercase text-gray-400" />
                            <textarea v-model="form.description" rows="2" placeholder="Tulis rincian tambahan..." class="w-full p-4 border-gray-200 rounded-xl text-sm font-medium text-gray-700 focus:ring-indigo-500 focus:border-indigo-500 placeholder-gray-400 resize-none bg-gray-50/30"></textarea>
                        </div>

                         <!-- File Upload -->
                        <div class="group relative border-2 border-dashed border-gray-200 hover:border-indigo-400 rounded-2xl p-6 text-center transition-colors cursor-pointer" @click="$refs.fileInput.click()">
                            <input ref="fileInput" type="file" @input="form.receipt = $event.target.files[0]" accept="image/*" class="hidden" />
                            <div class="space-y-1">
                                <svg class="mx-auto h-8 w-8 text-gray-400 group-hover:text-indigo-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                <div class="text-xs text-gray-500">
                                    <span v-if="!form.receipt" class="font-bold text-indigo-600 group-hover:text-indigo-700">Upload Bukti Foto</span>
                                    <span v-else class="font-bold text-green-600 truncate max-w-[200px] block mx-auto">{{ form.receipt.name }}</span>
                                </div>
                                <p v-if="!form.receipt" class="text-[10px] text-gray-400">PNG, JPG, max 2MB</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 pt-4 border-t border-gray-100 mt-6">
                            <button type="button" @click="showModal = false" class="flex-1 py-3.5 rounded-xl text-xs font-black uppercase tracking-widest text-gray-500 hover:bg-gray-100 transition">
                                Batal
                            </button>
                            <button type="submit" 
                                class="flex-[2] py-3.5 rounded-xl text-xs font-black uppercase tracking-widest text-white shadow-lg shadow-indigo-200 transition transform active:scale-95 flex items-center justify-center gap-2"
                                :class="form.processing ? 'bg-indigo-400 cursor-wait' : 'bg-indigo-600 hover:bg-indigo-700 hover:shadow-indigo-300'">
                                <svg v-if="!form.processing" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                                <span v-if="form.processing">Menyimpan...</span>
                                <span v-else>Simpan Transaksi</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Modal>

        <!-- Detail Modal -->
        <Modal :show="showDetailModal" @close="showDetailModal = false">
            <div class="p-8" v-if="selectedFinance">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-xl font-black text-gray-900 uppercase tracking-tight">Detail Transaksi</h2>
                    <button @click="showDetailModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="bg-gray-50 rounded-2xl p-6 mb-6">
                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <div>
                            <p class="text-[10px] font-black uppercase text-gray-400 mb-1">Status</p>
                            <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-tighter" :class="getStatusClass(selectedFinance.type)">
                                {{ selectedFinance.type === 'in' ? 'Pemasukan' : 'Pengeluaran' }}
                            </span>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase text-gray-400 mb-1">Tanggal</p>
                            <p class="text-sm font-black text-gray-700">{{ new Date(selectedFinance.transaction_date).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase text-gray-400 mb-1">Dompet</p>
                            <p class="text-sm font-black text-indigo-600">{{ selectedFinance.wallet.name }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase text-gray-400 mb-1">Kategori</p>
                            <p class="text-sm font-black text-gray-700">{{ selectedFinance.category }}</p>
                        </div>
                    </div>
                    <div class="border-t border-gray-200 pt-6">
                        <p class="text-[10px] font-black uppercase text-gray-400 mb-2">Jumlah</p>
                        <p class="text-3xl font-black" :class="selectedFinance.type === 'in' ? 'text-green-600' : 'text-red-600'">
                            {{ formatCurrency(selectedFinance.amount) }}
                        </p>
                    </div>
                </div>

                <div class="mb-6">
                    <p class="text-[10px] font-black uppercase text-gray-400 mb-2">Keterangan / Rincian</p>
                    <p class="text-sm text-gray-600 bg-white border border-gray-100 rounded-xl p-4 italic">
                        "{{ selectedFinance.description }}"
                    </p>
                </div>

                <div v-if="selectedFinance.receipt_path" class="mb-6">
                    <p class="text-[10px] font-black uppercase text-gray-400 mb-2">Bukti Transaksi</p>
                    <div class="rounded-2xl overflow-hidden border border-gray-200 shadow-sm">
                        <img :src="'/storage/' + selectedFinance.receipt_path" class="w-full h-auto object-cover" />
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-100 text-right">
                    <SecondaryButton @click="showDetailModal = false" class="px-8 rounded-xl font-black uppercase tracking-widest text-[10px]">Tutup</SecondaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
