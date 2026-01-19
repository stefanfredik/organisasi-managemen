<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    donation: Object,
});

const showingModal = ref(false);

const form = useForm({
    donor_name: '',
    donor_email: '',
    donor_phone: '',
    amount: '',
    donation_date: new Date().toISOString().substr(0, 10),
    is_anonymous: false,
    notes: '',
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(amount);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};

const calculateProgress = (collected, target) => {
    if (!target || target <= 0) return 0;
    return Math.min(Math.round((collected / target) * 100), 100);
};

const openModal = () => {
    form.reset();
    showingModal.value = true;
};

const closeModal = () => {
    showingModal.value = false;
};

const submitTransaction = () => {
    form.post(route('donations.transactions.store', props.donation.id), {
        onSuccess: () => {
            closeModal();
            form.reset();
        },
    });
};

const getStatusBadge = (status) => {
    const badges = {
        active: 'bg-green-100 text-green-800',
        completed: 'bg-blue-100 text-blue-800',
        cancelled: 'bg-red-100 text-red-800',
    };
    return badges[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head :title="donation.program_name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li>
                            <Link :href="route('donations.index')" class="text-sm font-medium text-gray-700 hover:text-indigo-600">
                                Donasi
                            </Link>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                                <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Detail</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <div class="flex gap-2">
                    <Link
                        v-if="$page.props.auth.user.role !== 'anggota'"
                        :href="route('donations.edit', donation.id)"
                        class="px-4 py-2 bg-orange-500 text-white rounded-md text-sm font-semibold hover:bg-orange-600 transition"
                    >
                        Edit Program
                    </Link>
                    <button
                        v-if="$page.props.auth.user.role === 'admin' || $page.props.auth.user.role === 'bendahara'"
                        @click="openModal"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-semibold hover:bg-indigo-700 transition"
                    >
                        Catat Donasi
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left Column: Program Info -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Main Card -->
                        <div class="bg-white p-6 shadow sm:rounded-lg">
                            <div class="flex justify-between items-start mb-4">
                                <h2 class="text-2xl font-bold text-gray-900">{{ donation.program_name }}</h2>
                                <span :class="getStatusBadge(donation.status)" class="px-3 py-1 rounded-full text-xs font-bold">
                                    {{ donation.status.toUpperCase() }}
                                </span>
                            </div>
                            
                            <div class="prose max-w-none text-gray-600 mb-8 whitespace-pre-wrap">
                                {{ donation.description || 'Tidak ada deskripsi.' }}
                            </div>

                            <div class="mb-8">
                                <div class="flex justify-between items-end mb-2">
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Dana Terkumpul</p>
                                        <p class="text-3xl font-bold text-indigo-600">{{ formatCurrency(donation.collected_amount) }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-gray-500">Target: {{ formatCurrency(donation.target_amount) }}</p>
                                        <p class="text-sm font-bold text-gray-700">{{ calculateProgress(donation.collected_amount, donation.target_amount) }}% Tercapai</p>
                                    </div>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-4">
                                    <div 
                                        class="bg-indigo-600 h-4 rounded-full transition-all duration-700 ease-out" 
                                        :style="{ width: calculateProgress(donation.collected_amount, donation.target_amount) + '%' }"
                                    ></div>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 py-6 border-t">
                                <div>
                                    <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Tanggal Mulai</p>
                                    <p class="text-sm font-medium text-gray-900">{{ formatDate(donation.start_date) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Tanggal Selesai</p>
                                    <p class="text-sm font-medium text-gray-900">{{ donation.end_date ? formatDate(donation.end_date) : 'Berlangsung' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Status Publik</p>
                                    <p class="text-sm font-medium text-gray-900">{{ donation.is_public ? 'Terlihat di Portal' : 'Draft/Internal' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Transactions Table -->
                        <div class="bg-white shadow sm:rounded-lg overflow-hidden">
                            <div class="p-6 border-b">
                                <h3 class="text-lg font-bold text-gray-900">Daftar Donatur</h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase font-bold tracking-wider">
                                        <tr>
                                            <th class="px-6 py-3 text-left">Tanggal</th>
                                            <th class="px-6 py-3 text-left">Nama Donatur</th>
                                            <th class="px-6 py-3 text-right">Jumlah</th>
                                            <th class="px-6 py-3 text-left">Catatan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        <tr v-for="tx in donation.transactions" :key="tx.id" class="text-sm text-gray-700">
                                            <td class="px-6 py-4 whitespace-nowrap">{{ formatDate(tx.donation_date) }}</td>
                                            <td class="px-6 py-4">
                                                <div class="font-medium text-gray-900">
                                                    {{ tx.is_anonymous ? 'Hamba Allah' : (tx.donor_name || 'Anonim') }}
                                                </div>
                                                <div v-if="!tx.is_anonymous && tx.donor_email" class="text-xs text-gray-500">{{ tx.donor_email }}</div>
                                            </td>
                                            <td class="px-6 py-4 text-right font-bold text-green-600">{{ formatCurrency(tx.amount) }}</td>
                                            <td class="px-6 py-4 italic text-gray-500 max-w-xs truncate">{{ tx.notes || '-' }}</td>
                                        </tr>
                                        <tr v-if="donation.transactions.length === 0">
                                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">Belum ada transaksi donasi.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Sidebar / Stats -->
                    <div class="space-y-6">
                        <div class="bg-white p-6 shadow sm:rounded-lg">
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-widest mb-4">Informasi Tambahan</h3>
                            <div class="space-y-4">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-indigo-50 rounded-full">
                                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Dibuat Oleh</p>
                                        <p class="text-sm font-medium">{{ donation.creator.name }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-indigo-50 rounded-full">
                                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Total Donatur</p>
                                        <p class="text-sm font-medium">{{ donation.transactions.length }} Orang</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-indigo-50 rounded-full">
                                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500">Terdaftar Pada</p>
                                        <p class="text-sm font-medium">{{ formatDate(donation.created_at) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Transaction Modal -->
        <Modal :show="showingModal" @close="closeModal">
            <div class="p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Catat Transaksi Donasi</h3>
                <form @submit.prevent="submitTransaction" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="donor_name" value="Nama Donatur (Opsional)" />
                            <TextInput id="donor_name" type="text" v-model="form.donor_name" class="mt-1 block w-full" :disabled="form.is_anonymous" />
                            <InputError class="mt-2" :message="form.errors.donor_name" />
                        </div>
                        <div class="flex items-center pt-8">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" v-model="form.is_anonymous" class="rounded border-gray-300 text-indigo-600 shadow-sm transition" />
                                <span class="ml-2 text-sm text-gray-600">Sembunyikan Nama (Hamba Allah)</span>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="donor_email" value="Email" />
                            <TextInput id="donor_email" type="email" v-model="form.donor_email" class="mt-1 block w-full" />
                            <InputError class="mt-2" :message="form.errors.donor_email" />
                        </div>
                        <div>
                            <InputLabel for="donor_phone" value="Telepon" />
                            <TextInput id="donor_phone" type="text" v-model="form.donor_phone" class="mt-1 block w-full" />
                            <InputError class="mt-2" :message="form.errors.donor_phone" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="amount" value="Jumlah Donasi (Rp)" />
                            <TextInput id="amount" type="number" v-model="form.amount" class="mt-1 block w-full font-bold text-green-600" required min="1" />
                            <InputError class="mt-2" :message="form.errors.amount" />
                        </div>
                        <div>
                            <InputLabel for="donation_date" value="Tanggal Donasi" />
                            <TextInput id="donation_date" type="date" v-model="form.donation_date" class="mt-1 block w-full" required />
                            <InputError class="mt-2" :message="form.errors.donation_date" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="notes" value="Catatan" />
                        <textarea id="notes" v-model="form.notes" class="mt-1 block w-full border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" rows="2"></textarea>
                        <InputError class="mt-2" :message="form.errors.notes" />
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <SecondaryButton @click="closeModal" type="button">Batal</SecondaryButton>
                        <PrimaryButton :disabled="form.processing">Simpan Transaksi</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
