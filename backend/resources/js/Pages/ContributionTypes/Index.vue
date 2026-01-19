<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    types: Array,
    wallets: Array,
});

const showModal = ref(false);
const editingType = ref(null);

const form = useForm({
    name: '',
    wallet_id: '',
    amount: '',
    period: 'monthly',
    description: '',
    is_active: true,
});

const openCreateModal = () => {
    editingType.value = null;
    form.reset();
    showModal.value = true;
};

const openEditModal = (type) => {
    editingType.value = type;
    form.name = type.name;
    form.wallet_id = type.wallet_id;
    form.amount = type.amount;
    form.period = type.period;
    form.description = type.description;
    form.is_active = !!type.is_active;
    showModal.value = true;
};

const submit = () => {
    if (editingType.value) {
        form.put(route('contribution-types.update', editingType.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('contribution-types.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
    editingType.value = null;
};

const deleteType = (type) => {
    if (confirm(`Apakah Anda yakin ingin menghapus jenis iuran ${type.name}?`)) {
        form.delete(route('contribution-types.destroy', type.id));
    }
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(amount);
};

const periods = {
    once: 'Sekali',
    daily: 'Harian',
    weekly: 'Mingguan',
    monthly: 'Bulanan',
    yearly: 'Tahunan',
};
</script>

<template>
    <Head title="Jenis Iuran" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Pengaturan Jenis Iuran
                </h2>
                <PrimaryButton @click="openCreateModal">
                    Tambah Jenis Iuran
                </PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm rounded-xl">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 uppercase tracking-widest text-[10px] font-bold text-gray-400">
                                <tr>
                                    <th class="px-6 py-4 text-left">Nama Iuran</th>
                                    <th class="px-6 py-4 text-left">Dompet Tujuan</th>
                                    <th class="px-6 py-4 text-left">Besaran</th>
                                    <th class="px-6 py-4 text-left">Periode</th>
                                    <th class="px-6 py-4 text-left">Status</th>
                                    <th class="px-6 py-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 italic">
                                <tr v-for="type in types" :key="type.id" class="hover:bg-gray-50/50 transition font-medium">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-gray-900">{{ type.name }}</div>
                                        <div class="text-xs text-gray-400">{{ type.description || '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span v-if="type.wallet" class="px-2 py-1 bg-indigo-50 text-indigo-700 rounded text-xs font-bold">
                                            {{ type.wallet.name }}
                                        </span>
                                        <span v-else class="text-xs text-red-500 italic">Belum diatur</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-black text-indigo-600">
                                        {{ formatCurrency(type.amount) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded text-[11px] font-bold uppercase">
                                            {{ periods[type.period] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 rounded text-[11px] font-bold uppercase" :class="type.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'">
                                            {{ type.is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button @click="openEditModal(type)" class="text-indigo-600 hover:text-indigo-900 font-bold text-sm mr-4 transition">Edit</button>
                                        <button @click="deleteType(type)" class="text-red-400 hover:text-red-600 transition p-2">
                                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="types.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500 italic font-medium">Belum ada jenis iuran yang dikonfigurasi.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Create/Edit -->
        <Modal :show="showModal" @close="closeModal">
            <div class="p-8">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-xl font-black text-gray-900 uppercase tracking-tight">
                        {{ editingType ? 'Edit Jenis Iuran' : 'Tambah Jenis Iuran Baru' }}
                    </h2>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <InputLabel value="Nama Iuran" class="text-[10px] font-bold uppercase text-gray-400 mb-1" />
                        <TextInput type="text" class="w-full border-gray-100 rounded-xl font-bold" v-model="form.name" required placeholder="Contoh: Iuran Bulanan 2026" />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div>
                        <InputLabel value="Dompet Tujuan" class="text-[10px] font-bold uppercase text-gray-400 mb-1" />
                        <select v-model="form.wallet_id" class="w-full border-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl font-bold text-gray-700 shadow-sm" required>
                            <option value="" disabled>-- Pilih Dompet --</option>
                            <option v-for="wallet in wallets" :key="wallet.id" :value="wallet.id">
                                {{ wallet.name }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.wallet_id" />
                        <p class="mt-1 text-xs text-gray-500 italic">Iuran yang dibayar akan masuk ke dompet ini secara otomatis.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <InputLabel value="Besaran (Rp)" class="text-[10px] font-bold uppercase text-gray-400 mb-1" />
                            <TextInput type="number" class="w-full border-gray-100 rounded-xl font-black text-indigo-600" v-model="form.amount" required placeholder="0" />
                            <InputError class="mt-2" :message="form.errors.amount" />
                        </div>
                        <div>
                            <InputLabel value="Periode Pembayaran" class="text-[10px] font-bold uppercase text-gray-400 mb-1" />
                            <select v-model="form.period" class="w-full border-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl font-bold text-gray-700 shadow-sm" required>
                                <option v-for="(label, value) in periods" :key="value" :value="value">{{ label }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.period" />
                        </div>
                    </div>

                    <div>
                        <InputLabel value="Deskripsi (Opsional)" class="text-[10px] font-bold uppercase text-gray-400 mb-1" />
                        <textarea v-model="form.description" rows="3" class="w-full border-gray-100 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm font-medium" placeholder="Rincian mengenai iuran ini..."></textarea>
                    </div>

                    <div class="flex items-center bg-gray-50 p-4 rounded-xl border border-dashed border-gray-200">
                        <input type="checkbox" id="is_active_type" v-model="form.is_active" class="h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                        <div class="ml-3">
                            <label for="is_active_type" class="text-sm font-bold text-gray-700 uppercase tracking-tight">Status Aktif</label>
                            <p class="text-xs text-gray-500">Iuran nonaktif tidak bisa dipilih saat mencatat pembayaran baru.</p>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-3 font-bold">
                        <SecondaryButton @click="closeModal" class="px-8 py-3 rounded-xl"> Batal </SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing" class="px-10 py-3 rounded-xl shadow-lg shadow-indigo-100 uppercase tracking-widest text-xs">
                            {{ editingType ? 'Simpan Perubahan' : 'Buat Jenis Iuran' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
