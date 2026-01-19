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
});

const showModal = ref(false);
const editingWallet = ref(null);

const form = useForm({
    name: '',
    description: '',
    balance: 0,
    is_active: true,
});

const openCreateModal = () => {
    editingWallet.value = null;
    form.reset();
    showModal.value = true;
};

const openEditModal = (wallet) => {
    editingWallet.value = wallet;
    form.name = wallet.name;
    form.description = wallet.description;
    form.balance = wallet.balance;
    form.is_active = !!wallet.is_active;
    showModal.value = true;
};

const submit = () => {
    if (editingWallet.value) {
        form.put(route('wallets.update', editingWallet.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('wallets.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
    editingWallet.value = null;
};

const deleteWallet = (wallet) => {
    if (confirm(`Apakah Anda yakin ingin menghapus dompet ${wallet.name}?`)) {
        form.delete(route('wallets.destroy', wallet.id));
    }
};

const formatNumber = (number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(number);
};
</script>

<template>
    <Head title="Manajemen Dompet" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Manajemen Dompet (Kas)
                </h2>
                <PrimaryButton @click="openCreateModal">
                    Tambah Dompet
                </PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div v-if="wallets.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="wallet in wallets" :key="wallet.id" class="bg-white overflow-hidden shadow-sm rounded-xl border-t-4 transition hover:shadow-md" :class="wallet.is_active ? 'border-indigo-500' : 'border-gray-300'">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex-1">
                                    <h3 class="text-lg font-bold text-gray-900 truncate" :title="wallet.name">{{ wallet.name }}</h3>
                                    <p class="text-sm text-gray-500 line-clamp-2 mt-1 min-h-[40px]">{{ wallet.description || 'Tidak ada deskripsi' }}</p>
                                </div>
                                <span class="ml-2 px-2 py-1 text-[10px] font-bold uppercase rounded-full" :class="wallet.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'">
                                    {{ wallet.is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>
                            
                            <div class="mb-6 bg-gray-50 p-4 rounded-lg">
                                <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold mb-1">Saldo Saat Ini</p>
                                <p class="text-2xl font-black text-indigo-600">{{ formatNumber(wallet.balance) }}</p>
                            </div>

                            <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                                <div class="text-[11px] text-gray-400 font-medium">
                                    {{ wallet.finances_count }} Transaksi | {{ wallet.contributions_count }} Iuran
                                </div>
                                <div class="flex gap-3">
                                    <button @click="openEditModal(wallet)" class="text-indigo-600 hover:text-indigo-900 text-sm font-bold transition">Edit</button>
                                    <button @click="deleteWallet(wallet)" class="text-red-500 hover:text-red-700 text-sm font-bold transition">Hapus</button>
                                </div>
                            </div>
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
                    <PrimaryButton @click="openCreateModal">
                        Buat Dompet Pertama
                    </PrimaryButton>
                </div>
            </div>
        </div>

        <!-- Modal Create/Edit -->
        <Modal :show="showModal" @close="closeModal">
            <div class="p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-black text-gray-900">
                        {{ editingWallet ? 'Edit Data Dompet' : 'Buat Dompet Baru' }}
                    </h2>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
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

                    <div v-if="!editingWallet">
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
                        <SecondaryButton @click="closeModal" class="px-6"> Batal </SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing" class="px-8 shadow-lg shadow-indigo-100">
                            {{ editingWallet ? 'Simpan Perubahan' : 'Konfirmasi & Buat' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
