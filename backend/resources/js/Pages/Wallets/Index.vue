<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    wallets: Array,
    stats: Object,
});

const showModal = ref(false);
const isEditing = ref(false);

const form = useForm({
    id: null,
    name: '',
    description: '',
    balance: 0,
    is_active: true,
});

const openModal = (wallet = null) => {
    if (wallet) {
        isEditing.value = true;
        form.id = wallet.id;
        form.name = wallet.name;
        form.description = wallet.description;
        form.balance = wallet.balance;
        form.is_active = !!wallet.is_active;
    } else {
        isEditing.value = false;
        form.reset();
        form.balance = 0;
        form.is_active = true;
    }
    showModal.value = true;
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('wallets.update', form.id), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    } else {
        form.post(route('wallets.store'), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    }
};

const deleteWallet = (wallet) => {
    if (confirm(`Apakah Anda yakin ingin menghapus dompet ${wallet.name}?`)) {
        form.delete(route('wallets.destroy', wallet.id));
    }
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(amount);
};
</script>

<template>
    <Head title="Kas & Dompet" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Kas & Dompet Organisasi
                </h2>
                <PrimaryButton @click="openModal()" v-if="$page.props.auth.user.role === 'admin' || $page.props.auth.user.role === 'bendahara'">
                    Tambah Dompet
                </PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Summary Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Total Saldo -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden group hover:shadow-md transition-all">
                        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                            <svg class="w-16 h-16 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
                                <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="relative z-10">
                            <p class="text-[10px] font-black uppercase tracking-widest text-indigo-400 mb-1">Total Saldo (Kas)</p>
                            <h3 class="text-3xl font-black text-gray-900 leading-tight">
                                {{ formatCurrency(stats.total_balance) }}
                            </h3>
                            <div class="mt-4 flex items-center text-xs text-indigo-600 font-bold">
                                <span>Dana siap pakai saat ini</span>
                            </div>
                        </div>
                    </div>

                    <!-- Total Pemasukan -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden group hover:shadow-md transition-all">
                        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                            <svg class="w-16 h-16 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a8 8 0 1016 0V6a2 2 0 00-2-2H4zm2 3a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm2 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="relative z-10">
                            <p class="text-[10px] font-black uppercase tracking-widest text-green-400 mb-1">Total Pemasukan</p>
                            <h3 class="text-3xl font-black text-green-700 leading-tight">
                                {{ formatCurrency(stats.total_income) }}
                            </h3>
                            <div class="mt-4 flex items-center text-xs text-green-600 font-bold">
                                <span>Kumulatif dana masuk</span>
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
                            <p class="text-[10px] font-black uppercase tracking-widest text-red-400 mb-1">Total Pengeluaran</p>
                            <h3 class="text-3xl font-black text-red-700 leading-tight">
                                {{ formatCurrency(stats.total_expense) }}
                            </h3>
                            <div class="mt-4 flex items-center text-xs text-red-600 font-bold">
                                <span>Kumulatif dana keluar</span>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-6">

                <div v-if="wallets.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="(wallet, index) in wallets" :key="wallet.id" class="group relative perspective-1000">
                        <!-- Credit Card Container -->
                        <Link :href="route('wallets.show', wallet.id)" class="block relative w-full aspect-[1.586/1] rounded-2xl shadow-xl transition-all duration-500 transform group-hover:scale-[1.02] overflow-hidden text-white cursor-pointer"
                             :class="[
                                wallet.is_active 
                                    ? 'bg-gradient-to-br from-cyan-500 via-blue-600 to-blue-800' 
                                    : 'bg-gradient-to-br from-gray-600 via-gray-700 to-gray-800 grayscale'
                             ]">
                            
                            <!-- Detailed background patterns for realism -->
                            <div class="absolute inset-0 opacity-10" 
                                 style="background-image: radial-gradient(circle at 50% -20%, rgba(255,255,255,0.8), transparent 50%);">
                            </div>
                            <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-white opacity-5 blur-3xl"></div>
                            <div class="absolute bottom-0 left-0 -ml-16 -mb-16 w-64 h-64 rounded-full bg-black opacity-10 blur-3xl"></div>

                            <div class="absolute inset-0 p-6 flex flex-col justify-between z-10">
                                <!-- Top Row: Name and Contactless Icon -->
                                <div class="flex justify-between items-start">
                                    <h3 class="font-bold text-lg tracking-wider uppercase truncate max-w-[70%] font-mono" :title="wallet.name">
                                        {{ wallet.name }}
                                    </h3>
                                    <!-- Contactless Icon -->
                                    <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                                    </svg>
                                </div>

                                <!-- Middle Row: Chip -->
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-9 bg-yellow-200 rounded-md relative overflow-hidden shadow-sm flex items-center justify-center opacity-90">
                                        <div class="absolute inset-0 border-[0.5px] border-yellow-600 opacity-40 rounded-md"></div>
                                        <div class="w-full h-[1px] bg-yellow-600 opacity-40 absolute top-1/3"></div>
                                        <div class="w-full h-[1px] bg-yellow-600 opacity-40 absolute bottom-1/3"></div>
                                        <div class="h-full w-[1px] bg-yellow-600 opacity-40 absolute left-1/3"></div>
                                        <div class="h-full w-[1px] bg-yellow-600 opacity-40 absolute right-1/3"></div>
                                    </div>
                                    <!-- Signal/WiFi Icon (Access Indicator) -->
                                    <svg v-if="wallet.is_active" class="w-5 h-5 opacity-60 rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>

                                <!-- Center/Bottom: Balance as Card Number -->
                                <div class="mt-2">
                                    <p class="text-xs text-blue-100 uppercase tracking-widest mb-1 opacity-80">Saldo Tersedia</p>
                                    <p class="text-2xl font-mono font-bold tracking-widest text-shadow-sm truncate">
                                        {{ formatCurrency(wallet.balance) }}
                                    </p>
                                </div>

                                <!-- Bottom Row: Details and Logo -->
                                <div class="flex justify-between items-end">
                                    <div class="flex flex-col mr-4">
                                        <span class="text-[8px] uppercase text-blue-100 opacity-70">DIBUAT</span>
                                        <span class="text-xs font-mono font-bold">{{ new Date(wallet.created_at).toLocaleDateString('id-ID', { month: '2-digit', year: '2-digit' }) }}</span>
                                        <span class="text-[10px] mt-1 opacity-80 uppercase truncate max-w-[100px]" :title="wallet.description">{{ wallet.description || 'Main Wallet' }}</span>
                                    </div>
                                    <div class="flex gap-4">
                                        <div class="flex flex-col">
                                            <span class="text-[8px] uppercase text-green-300 opacity-90 font-bold tracking-wider">MASUK</span>
                                            <span class="text-xs font-mono font-bold text-green-50">+ {{ formatCurrency(wallet.total_income || 0) }}</span>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-[8px] uppercase text-red-300 opacity-90 font-bold tracking-wider">KELUAR</span>
                                            <span class="text-xs font-mono font-bold text-red-50">- {{ formatCurrency(wallet.total_expense || 0) }}</span>
                                        </div>
                                    </div>
                                    <h4 class="text-xl font-bold italic tracking-tighter opacity-90">VISA</h4>
                                </div>
                            </div>
                        </Link>

                        <!-- Action Buttons (Slide out or appear below) -->
                        <div class="absolute -bottom-4 left-0 right-0 flex justify-center opacity-0 group-hover:opacity-100 group-hover:bottom-[-2.5rem] transition-all duration-300 z-20" v-if="$page.props.auth.user.role === 'admin' || $page.props.auth.user.role === 'bendahara'">
                            <div class="bg-white rounded-full shadow-lg p-1.5 flex gap-2 border border-gray-100 transform scale-90 group-hover:scale-100">
                                <button @click="openModal(wallet)" class="p-2 bg-indigo-50 text-indigo-600 rounded-full hover:bg-indigo-600 hover:text-white transition" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                </button>
                                <button @click="deleteWallet(wallet)" class="p-2 bg-red-50 text-red-600 rounded-full hover:bg-red-600 hover:text-white transition" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Stats below card (Visible always, subtle) -->
                        <div class="mt-4 flex justify-between px-2 text-xs text-gray-400 font-medium opacity-60 group-hover:opacity-100 transition-opacity">
                            <span>{{ wallet.finances_count }} Transaksi</span>
                            <span>{{ wallet.contributions_count }} Iuran</span>
                        </div>
                    </div>
                    
                </div>

                <div v-else class="bg-white p-16 text-center rounded-xl shadow-sm border border-gray-100">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Belum Ada Dompet</h3>
                    <p class="text-gray-500 mb-6">Mulai dengan membuat dompet untuk mengelola dana organisasi.</p>
                    
                    <PrimaryButton @click="openModal()">
                        Buat Dompet Pertama
                    </PrimaryButton>
                </div>
            </div>
        </div>

        <!-- Modal Create/Edit -->
        <Modal :show="showModal" @close="showModal = false">
            <div class="p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-black text-gray-900">
                        {{ isEditing ? 'Edit Data Dompet' : 'Buat Dompet Baru' }}
                    </h2>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <InputLabel for="name" value="Nama Dompet" class="text-xs font-bold uppercase text-gray-500" />
                        <TextInput id="name" type="text" class="mt-1 block w-full border-gray-200" v-model="form.name" required placeholder="Contoh: Kas Utama, Dana Sosial" />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div>
                        <InputLabel for="description" value="Deskripsi (Opsional)" class="text-xs font-bold uppercase text-gray-500" />
                        <textarea id="description" rows="3" class="mt-1 block w-full border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm" v-model="form.description" placeholder="Jelaskan kegunaan dompet ini..."></textarea>
                        <InputError class="mt-2" :message="form.errors.description" />
                    </div>

                    <div v-if="!isEditing">
                        <InputLabel for="balance" value="Saldo Awal (Rp)" class="text-xs font-bold uppercase text-gray-500" />
                        <TextInput id="balance" type="number" step="0.01" class="mt-1 block w-full border-gray-200" v-model="form.balance" />
                        <p class="mt-1 text-xs text-gray-400 italic">* Saldo awal hanya bisa diatur saat pembuatan dompet.</p>
                        <InputError class="mt-2" :message="form.errors.balance" />
                    </div>

                    <div class="flex items-center bg-gray-50 p-3 rounded-lg">
                        <input type="checkbox" id="is_active" v-model="form.is_active" class="h-5 w-5 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                        <div class="ml-3">
                            <label for="is_active" class="text-sm font-bold text-gray-700">Status Aktif</label>
                            <p class="text-xs text-gray-500">Dompet nonaktif tidak akan muncul di pilihan transaksi baru.</p>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-3">
                        <SecondaryButton @click="showModal = false" class="px-6"> Batal </SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing" class="px-8 shadow-lg shadow-indigo-100">
                            {{ isEditing ? 'Simpan Perubahan' : 'Konfirmasi & Buat' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
