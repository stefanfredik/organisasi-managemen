<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import {
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogDescription,
} from '@/components/ui/dialog';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import { User, Mail, Phone, Calendar, Upload, X } from 'lucide-vue-next';



const props = defineProps({
    donation: Object,
    members: Array,
});

const page = usePage();
const showingModal = ref(false);
const showingPaymentModal = ref(false);
const showingVerifyModal = ref(false);
const selectedTransaction = ref(null);
const searchQuery = ref('');

const filteredTransactions = computed(() => {
    if (!searchQuery.value) {
        return props.donation.transactions;
    }
    const query = searchQuery.value.toLowerCase();
    return props.donation.transactions.filter(tx => {
        const name = tx.donor_name ? tx.donor_name.toLowerCase() : '';
        const email = tx.donor_email ? tx.donor_email.toLowerCase() : '';
        const notes = tx.notes ? tx.notes.toLowerCase() : '';
        return name.includes(query) || email.includes(query) || notes.includes(query);
    });
});

const form = useForm({
    member_id: '',
    donor_name: '',
    donor_email: '',
    donor_phone: '',
    amount: '',
    donation_date: new Date().toISOString().substr(0, 10),
    is_anonymous: false,
    notes: '',
});

watch(() => form.member_id, (newId) => {
    if (newId) {
        const member = props.members.find(m => m.id === newId);
        if (member) {
            form.donor_name = member.full_name;
            form.donor_email = member.email || '';
            form.donor_phone = member.phone || '';
        }
    }
});

const paymentForm = useForm({
    amount: '',
    donation_date: new Date().toISOString().substr(0, 10),
    receipt: null,
    is_anonymous: false,
    notes: '',
    donor_name: page.props.auth.user.name,
});

const verifyForm = useForm({
    action: '',
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

const openPaymentModal = () => {
    paymentForm.reset();
    paymentForm.donor_name = page.props.auth.user.name;
    showingPaymentModal.value = true;
};

const payReceiptInputRef = ref(null);
const isDraggingPayReceipt = ref(false);
const payReceiptPreview = ref(null);

const handlePayReceiptSelect = (e) => {
    const file = e.target.files[0];
    if (file) setPayReceipt(file);
};

const handlePayReceiptDrop = (e) => {
    isDraggingPayReceipt.value = false;
    const file = e.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) setPayReceipt(file);
};

const setPayReceipt = (file) => {
    paymentForm.receipt = file;
    const reader = new FileReader();
    reader.onload = (e) => { payReceiptPreview.value = e.target.result; };
    reader.readAsDataURL(file);
};

const clearPayReceipt = () => {
    paymentForm.receipt = null;
    payReceiptPreview.value = null;
    if (payReceiptInputRef.value) payReceiptInputRef.value.value = '';
};

const closePaymentModal = () => {
    showingPaymentModal.value = false;
    clearPayReceipt();
};

const openVerifyModal = (transaction) => {
    selectedTransaction.value = transaction;
    verifyForm.reset();
    showingVerifyModal.value = true;
};

const closeVerifyModal = () => {
    showingVerifyModal.value = false;
    selectedTransaction.value = null;
};

const submitTransaction = () => {
    form.post(route('donations.transactions.store', props.donation.id), {
        onSuccess: () => {
            closeModal();
            form.reset();
        },
    });
};

const submitPayment = () => {
    paymentForm.post(route('donations.pay', props.donation.id), {
        onSuccess: () => {
            closePaymentModal();
            paymentForm.reset();
        },
    });
};

const submitVerification = (action) => {
    verifyForm.action = action;
    verifyForm.post(route('donations.transactions.verify', selectedTransaction.value.id), {
        onSuccess: () => {
            closeVerifyModal();
            verifyForm.reset();
        },
    });
};

const getStatusBadge = (status) => {
    const badges = {
        active: 'bg-success/20 text-success-foreground',
        completed: 'bg-primary/20 text-primary',
        cancelled: 'bg-destructive/20 text-destructive',
    };
    return badges[status] || 'bg-muted text-foreground';
};

const getTransactionStatusBadge = (status) => {
    const badges = {
        pending: 'bg-warning-100 text-warning-800',
        paid: 'bg-success/20 text-success-foreground',
        rejected: 'bg-destructive/20 text-destructive',
    };
    return badges[status] || 'bg-muted text-foreground';
};

const deleteDonation = () => {
    if (confirm('Apakah Anda yakin ingin menghapus program donasi ini?')) {
        router.delete(route('donations.destroy', props.donation.id));
    }
};
</script>

<template>
    <Head :title="donation.program_name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="route('donations.index')" class="text-muted-foreground hover:text-foreground">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-foreground">{{ donation.program_name }}</h2>
                </div>
                <div class="flex gap-2">
                    <Link
                        v-if="$page.props.auth.user.role !== 'anggota'"
                        :href="route('donations.edit', donation.id)"
                        class="inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary/90 active:bg-primary/80 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                    </Link>
                    <button
                        v-if="$page.props.auth.user.role === 'admin' || $page.props.auth.user.role === 'bendahara'"
                        @click="deleteDonation"
                        class="inline-flex items-center px-4 py-2 bg-destructive border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-destructive/90 active:bg-danger-900 focus:outline-none focus:ring-2 focus:ring-danger-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1 1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                </div>
            </div>
        </template>

        <div class="py-6 sm:py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left Column: Program Info -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Main Card -->
                        <div class="bg-card p-6 shadow sm:rounded-lg">
                            <div class="flex justify-between items-start mb-4">
                                <h2 class="text-2xl font-bold text-foreground">{{ donation.program_name }}</h2>
                                <span :class="getStatusBadge(donation.status)" class="px-3 py-1 rounded-full text-xs font-bold">
                                    {{ donation.status.toUpperCase() }}
                                </span>
                            </div>
                            
                            <div class="prose max-w-none text-muted-foreground mb-8 whitespace-pre-wrap">
                                {{ donation.description || 'Tidak ada deskripsi.' }}
                            </div>

                            <div class="mb-8">
                                <div class="flex justify-between items-end mb-2">
                                    <div>
                                        <p class="text-sm font-medium text-muted-foreground">Dana Terkumpul</p>
                                        <p class="text-2xl sm:text-3xl font-bold text-primary">{{ formatCurrency(donation.collected_amount) }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-muted-foreground">Target: {{ formatCurrency(donation.target_amount) }}</p>
                                        <p class="text-sm font-bold text-foreground">{{ calculateProgress(donation.collected_amount, donation.target_amount) }}% Tercapai</p>
                                    </div>
                                </div>
                                <div class="w-full bg-muted rounded-full h-4">
                                    <div 
                                        class="bg-primary h-4 rounded-full transition-all duration-700 ease-out" 
                                        :style="{ width: calculateProgress(donation.collected_amount, donation.target_amount) + '%' }"
                                    ></div>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 py-6 border-t">
                                <div>
                                    <p class="text-xs text-muted-foreground uppercase font-bold tracking-wider">Tanggal Mulai</p>
                                    <p class="text-sm font-medium text-foreground">{{ formatDate(donation.start_date) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-muted-foreground uppercase font-bold tracking-wider">Tanggal Selesai</p>
                                    <p class="text-sm font-medium text-foreground">{{ donation.end_date ? formatDate(donation.end_date) : 'Berlangsung' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-muted-foreground uppercase font-bold tracking-wider">Status Publik</p>
                                    <p class="text-sm font-medium text-foreground">{{ donation.is_public ? 'Terlihat di Portal' : 'Draft/Internal' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Transactions Table -->
                        <div class="bg-card shadow sm:rounded-lg overflow-hidden">
                            <div class="p-6 border-b flex flex-col sm:flex-row justify-between items-center gap-4">
                                <h3 class="text-lg font-bold text-foreground">Daftar Donatur</h3>
                                <div class="w-full sm:w-auto">
                                    <input 
                                        type="text" 
                                        v-model="searchQuery" 
                                        placeholder="Cari donatur..." 
                                        class="w-full rounded-md border-input shadow-sm focus:border-ring focus:ring-ring text-sm"
                                    >
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-border">
                                    <thead class="bg-muted text-xs text-muted-foreground uppercase font-bold tracking-wider">
                                        <tr>
                                            <th class="px-6 py-3 text-left">Tanggal</th>
                                            <th class="px-6 py-3 text-left">Nama Donatur</th>
                                            <th class="px-6 py-3 text-right">Jumlah</th>
                                            <th class="px-6 py-3 text-center">Status</th>
                                            <th class="px-6 py-3 text-center">Bukti</th>
                                            <th class="px-6 py-3 text-left">Catatan</th>
                                            <th v-if="$page.props.auth.user.role === 'admin' || $page.props.auth.user.role === 'bendahara'" class="px-6 py-3 text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-border bg-card">
                                        <tr v-for="tx in filteredTransactions" :key="tx.id" class="text-sm text-foreground">
                                            <td class="px-6 py-4 whitespace-nowrap">{{ formatDate(tx.donation_date) }}</td>
                                            <td class="px-6 py-4">
                                                <div class="font-medium text-foreground">
                                                    {{ tx.is_anonymous ? 'Hamba Allah' : (tx.donor_name || 'Anonim') }}
                                                </div>
                                                <div v-if="!tx.is_anonymous && tx.donor_email" class="text-xs text-muted-foreground">{{ tx.donor_email }}</div>
                                            </td>
                                            <td class="px-6 py-4 text-right font-bold text-success-600">{{ formatCurrency(tx.amount) }}</td>
                                            <td class="px-6 py-4 text-center">
                                                 <span :class="getTransactionStatusBadge(tx.status)" class="px-2 py-1 rounded-full text-xs font-bold capitalize">
                                                    {{ tx.status }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <a v-if="tx.receipt_path" :href="'/storage/' + tx.receipt_path" target="_blank" class="text-primary hover:text-primary/80 underline text-xs">
                                                    Lihat
                                                </a>
                                                <span v-else class="text-muted-foreground text-xs">-</span>
                                            </td>
                                            <td class="px-6 py-4 italic text-muted-foreground max-w-xs truncate">{{ tx.notes || '-' }}</td>
                                            <td v-if="$page.props.auth.user.role === 'admin' || $page.props.auth.user.role === 'bendahara'" class="px-6 py-4 text-center">
                                                <button
                                                    v-if="tx.status === 'pending'"
                                                    @click="openVerifyModal(tx)"
                                                    class="text-primary hover:text-primary/80 font-medium text-xs border border-primary px-2 py-1 rounded hover:bg-primary/10"
                                                >
                                                    Verifikasi
                                                </button>
                                            </td>
                                        </tr>
                                        <tr v-if="donation.transactions.length === 0">
                                            <td colspan="7" class="px-6 py-12 text-center text-muted-foreground">Belum ada transaksi donasi.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Sidebar / Stats -->
                    <div class="space-y-6">
                        <!-- CTA Donate Button for Member -->
                        <div v-if="$page.props.auth.user.role === 'anggota'" class="bg-primary rounded-lg shadow-lg p-6 text-center">
                            <h3 class="text-white text-lg font-bold mb-2">Ingin Berdonasi?</h3>
                            <p class="text-primary-100 text-sm mb-4">Mari sisihkan sebagian rezeki untuk membantu sesama.</p>
                            <button
                                @click="openPaymentModal"
                                class="w-full px-6 py-3 bg-card text-primary rounded-lg font-bold text-lg hover:bg-primary/10 transition transform hover:scale-105"
                            >
                                Sumbang Sekarang
                            </button>
                        </div>

                        <div class="bg-card p-6 shadow sm:rounded-lg">
                            <h3 class="text-sm font-bold text-foreground uppercase tracking-widest mb-4">Informasi Tambahan</h3>
                            <div class="space-y-4">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-primary/10 rounded-full">
                                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                    </div>
                                    <div>
                                        <p class="text-xs text-muted-foreground">Dibuat Oleh</p>
                                        <p class="text-sm font-medium">{{ donation.creator.name }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-primary/10 rounded-full">
                                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                    </div>
                                    <div>
                                        <p class="text-xs text-muted-foreground">Total Donatur</p>
                                        <p class="text-sm font-medium">{{ donation.transactions.length }} Orang</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-primary/10 rounded-full">
                                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </div>
                                    <div>
                                        <p class="text-xs text-muted-foreground">Terdaftar Pada</p>
                                        <p class="text-sm font-medium">{{ formatDate(donation.created_at) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Transaction Dialog -->
        <Dialog :open="showingModal" @update:open="(val) => { if (!val) closeModal(); }">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Catat Transaksi Donasi</DialogTitle>
                </DialogHeader>
                <form @submit.prevent="submitTransaction" class="space-y-4">
                    <div v-if="$page.props.auth.user.role === 'admin' || $page.props.auth.user.role === 'bendahara'" class="mb-4">
                        <Label for="member_id">Pilih Anggota (Opsional)</Label>
                        <Select v-model="form.member_id">
                            <SelectTrigger class="mt-1 w-full">
                                <SelectValue placeholder="-- Manual Input / Bukan Anggota --" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">-- Manual Input / Bukan Anggota --</SelectItem>
                                <SelectItem v-for="member in members" :key="member.id" :value="member.id.toString()">
                                    {{ member.full_name }} - {{ member.member_code }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.member_id" class="mt-2 text-sm text-destructive">{{ form.errors.member_id }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <Label for="donor_name">Nama Donatur (Opsional)</Label>
                            <Input id="donor_name" type="text" v-model="form.donor_name" class="mt-1 block w-full" :disabled="form.is_anonymous" />
                            <p v-if="form.errors.donor_name" class="mt-2 text-sm text-destructive">{{ form.errors.donor_name }}</p>
                        </div>
                        <div class="flex items-center space-x-2 pt-8">
                            <Checkbox id="is_anonymous" :checked="form.is_anonymous" @update:checked="(val) => form.is_anonymous = val" />
                            <Label for="is_anonymous" class="text-sm text-muted-foreground">Sembunyikan Nama (Hamba Allah)</Label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <Label for="donor_email">Email</Label>
                            <Input id="donor_email" type="email" v-model="form.donor_email" class="mt-1 block w-full" />
                            <p v-if="form.errors.donor_email" class="mt-2 text-sm text-destructive">{{ form.errors.donor_email }}</p>
                        </div>
                        <div>
                            <Label for="donor_phone">Telepon</Label>
                            <Input id="donor_phone" type="text" v-model="form.donor_phone" class="mt-1 block w-full" />
                            <p v-if="form.errors.donor_phone" class="mt-2 text-sm text-destructive">{{ form.errors.donor_phone }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <Label for="amount">Jumlah Donasi (Rp)</Label>
                            <Input id="amount" type="number" v-model="form.amount" class="mt-1 block w-full font-bold text-green-600" required min="1" />
                            <p v-if="form.errors.amount" class="mt-2 text-sm text-destructive">{{ form.errors.amount }}</p>
                        </div>
                        <div>
                            <Label for="donation_date">Tanggal Donasi</Label>
                            <Input id="donation_date" type="date" v-model="form.donation_date" class="mt-1 block w-full" required />
                            <p v-if="form.errors.donation_date" class="mt-2 text-sm text-destructive">{{ form.errors.donation_date }}</p>
                        </div>
                    </div>

                    <div>
                        <Label for="notes">Catatan</Label>
                        <Textarea id="notes" v-model="form.notes" rows="2" />
                        <p v-if="form.errors.notes" class="mt-2 text-sm text-destructive">{{ form.errors.notes }}</p>
                    </div>

                    <DialogFooter>
                        <Button variant="outline" type="button" @click="closeModal">Batal</Button>
                        <Button type="submit" :disabled="form.processing">Simpan Transaksi</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Member Payment Dialog -->
        <Dialog :open="showingPaymentModal" @update:open="(val) => { if (!val) closePaymentModal(); }">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Sumbang Donasi</DialogTitle>
                </DialogHeader>
                <form @submit.prevent="submitPayment" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <Label for="pay_donor_name">Nama Donatur (Opsional)</Label>
                            <Input id="pay_donor_name" type="text" v-model="paymentForm.donor_name" class="mt-1 block w-full" :disabled="paymentForm.is_anonymous" />
                            <p v-if="paymentForm.errors.donor_name" class="mt-2 text-sm text-destructive">{{ paymentForm.errors.donor_name }}</p>
                        </div>
                        <div class="flex items-center space-x-2 pt-8">
                            <Checkbox id="pay_is_anonymous" :checked="paymentForm.is_anonymous" @update:checked="(val) => paymentForm.is_anonymous = val" />
                            <Label for="pay_is_anonymous" class="text-sm text-muted-foreground">Sembunyikan Nama</Label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <Label for="pay_amount">Jumlah Donasi (Rp)</Label>
                            <Input id="pay_amount" type="number" v-model="paymentForm.amount" class="mt-1 block w-full font-bold text-green-600" required min="1" />
                            <p v-if="paymentForm.errors.amount" class="mt-2 text-sm text-destructive">{{ paymentForm.errors.amount }}</p>
                        </div>
                        <div>
                            <Label for="pay_donation_date">Tanggal Transfer</Label>
                            <Input id="pay_donation_date" type="date" v-model="paymentForm.donation_date" class="mt-1 block w-full" required />
                            <p v-if="paymentForm.errors.donation_date" class="mt-2 text-sm text-destructive">{{ paymentForm.errors.donation_date }}</p>
                        </div>
                    </div>

                    <div>
                        <Label>Bukti Transfer (Gambar)</Label>
                        <div
                            @dragover.prevent="isDraggingPayReceipt = true"
                            @dragleave.prevent="isDraggingPayReceipt = false"
                            @drop.prevent="handlePayReceiptDrop($event)"
                            @click="payReceiptInputRef?.click()"
                            class="mt-1 relative border-2 border-dashed rounded-xl p-5 text-center cursor-pointer transition-all duration-200"
                            :class="isDraggingPayReceipt ? 'border-primary bg-primary/5 scale-[1.01]' : 'border-border hover:border-primary/50 hover:bg-muted/30'"
                        >
                            <input ref="payReceiptInputRef" type="file" accept="image/*" class="hidden" @change="handlePayReceiptSelect($event)" />
                            <template v-if="!payReceiptPreview">
                                <Upload class="mx-auto h-8 w-8 text-muted-foreground mb-2" />
                                <p class="text-sm font-medium text-primary">Upload Bukti Transfer</p>
                                <p class="text-xs text-muted-foreground mt-1">Drag & drop atau klik. JPG, PNG max 2MB</p>
                            </template>
                            <template v-else>
                                <div class="relative inline-block">
                                    <img :src="payReceiptPreview" class="max-h-32 rounded-lg mx-auto" />
                                    <button type="button" @click.stop="clearPayReceipt" class="absolute -top-2 -right-2 w-6 h-6 bg-destructive text-white rounded-full flex items-center justify-center hover:bg-destructive/80">
                                        <X class="w-3 h-3" />
                                    </button>
                                </div>
                                <p class="text-xs text-muted-foreground mt-2">{{ paymentForm.receipt?.name }}</p>
                            </template>
                        </div>
                        <p v-if="paymentForm.errors.receipt" class="mt-2 text-sm text-destructive">{{ paymentForm.errors.receipt }}</p>
                    </div>

                    <div>
                        <Label for="pay_notes">Catatan / Doa</Label>
                        <Textarea id="pay_notes" v-model="paymentForm.notes" rows="2" />
                        <p v-if="paymentForm.errors.notes" class="mt-2 text-sm text-destructive">{{ paymentForm.errors.notes }}</p>
                    </div>

                    <DialogFooter>
                        <Button variant="outline" type="button" @click="closePaymentModal">Batal</Button>
                        <Button type="submit" :disabled="paymentForm.processing">Kirim Donasi</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Verification Dialog -->
        <Dialog :open="showingVerifyModal" @update:open="(val) => { if (!val) closeVerifyModal(); }">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Verifikasi Donasi</DialogTitle>
                </DialogHeader>

                <div v-if="selectedTransaction" class="mb-6 bg-muted p-4 rounded-lg">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-muted-foreground">Donatur</p>
                            <p class="font-medium">{{ selectedTransaction.donor_name || 'Anonim' }}</p>
                        </div>
                        <div>
                            <p class="text-muted-foreground">Jumlah</p>
                            <p class="font-medium text-green-600">{{ formatCurrency(selectedTransaction.amount) }}</p>
                        </div>
                        <div>
                            <p class="text-muted-foreground">Tanggal</p>
                            <p class="font-medium">{{ formatDate(selectedTransaction.donation_date) }}</p>
                        </div>
                         <div>
                            <p class="text-muted-foreground">Bukti</p>
                            <a v-if="selectedTransaction.receipt_path" :href="'/storage/' + selectedTransaction.receipt_path" target="_blank" class="text-primary underline">Lihat Bukti</a>
                            <span v-else class="text-muted-foreground">Tidak ada</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <Label for="verify_notes">Catatan Verifikasi (Opsional)</Label>
                        <Textarea id="verify_notes" v-model="verifyForm.notes" rows="2" placeholder="Alasan terima/tolak..." />
                        <p v-if="verifyForm.errors.notes" class="mt-2 text-sm text-destructive">{{ verifyForm.errors.notes }}</p>
                    </div>

                    <DialogFooter>
                        <Button variant="outline" type="button" @click="closeVerifyModal">Batal</Button>
                        <Button variant="destructive" type="button" @click="submitVerification('reject')" :disabled="verifyForm.processing">
                            Tolak
                        </Button>
                        <Button type="button" @click="submitVerification('approve')" :disabled="verifyForm.processing" class="bg-green-600 hover:bg-green-700">
                            Terima & Verifikasi
                        </Button>
                    </DialogFooter>
                </div>
            </DialogContent>
        </Dialog>
    </AuthenticatedLayout>
</template>
