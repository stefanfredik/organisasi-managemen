<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogDescription,
} from '@/components/ui/dialog';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import CurrencyInput from '@/Components/CurrencyInput.vue';
import {
    ArrowLeft, Pencil, Trash2, Heart, CalendarDays, Globe, Users, UserCircle, Plus,
    CheckCircle2, Upload, X, Search, Eye, ShieldCheck, XCircle, Clock, MoreVertical,
    Loader2, FileImage, Inbox, CircleDollarSign, Banknote, CreditCard, User, Mail, Phone,
} from 'lucide-vue-next';
import {
    Sheet, SheetContent, SheetHeader, SheetTitle, SheetDescription,
} from '@/components/ui/sheet';
import DeleteConfirmDialog from '@/Components/DeleteConfirmDialog.vue';
import { useToast } from '@/composables/useToast';

const toast = useToast();

const props = defineProps({
    donation: Object,
    members: Array,
});

const page = usePage();
const isAdmin = computed(() => ['admin', 'bendahara'].includes(page.props.auth.user.role));
const isMember = computed(() => page.props.auth.user.role === 'anggota');

const showingModal = ref(false);
const showingPaymentModal = ref(false);
const showingVerifyModal = ref(false);
const selectedTransaction = ref(null);
const searchQuery = ref('');

const showingDetailSheet = ref(false);
const detailTransaction = ref(null);
const openDetailSheet = (tx) => { detailTransaction.value = tx; showingDetailSheet.value = true; };
const closeDetailSheet = () => { showingDetailSheet.value = false; detailTransaction.value = null; };

const filteredTransactions = computed(() => {
    if (!searchQuery.value) return props.donation.transactions;
    const q = searchQuery.value.toLowerCase();
    return props.donation.transactions.filter(tx => {
        return [tx.donor_name, tx.donor_email, tx.notes]
            .filter(Boolean).some(v => v.toLowerCase().includes(q));
    });
});

const form = useForm({
    member_id: '',
    donor_name: '',
    donor_email: '',
    donor_phone: '',
    amount: 0,
    donation_date: new Date().toISOString().split('T')[0],
    is_anonymous: false,
    payment_method: 'cash',
    notes: '',
    receipt: null,
});

const memberOptions = computed(() => {
    return (props.members || []).map(m => ({
        value: m.id,
        label: m.full_name,
        subLabel: m.member_code,
    }));
});

const selectedMember = computed(() => {
    return props.members?.find(m => m.id == form.member_id) || null;
});

watch(() => form.member_id, (newId) => {
    if (newId) {
        const member = props.members.find(m => m.id.toString() === newId.toString());
        if (member) {
            form.donor_name = member.full_name;
            form.donor_email = member.email || '';
            form.donor_phone = member.phone || '';
        }
    }
});

// Receipt handling for admin form
const adminReceiptInputRef = ref(null);
const isDraggingAdminReceipt = ref(false);
const adminReceiptPreview = ref(null);

const handleAdminReceiptSelect = (e) => {
    const file = e.target.files[0];
    if (file) setAdminReceipt(file);
};
const handleAdminReceiptDrop = (e) => {
    isDraggingAdminReceipt.value = false;
    const file = e.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) setAdminReceipt(file);
};
const setAdminReceipt = (file) => {
    form.receipt = file;
    const reader = new FileReader();
    reader.onload = (e) => { adminReceiptPreview.value = e.target.result; };
    reader.readAsDataURL(file);
};
const clearAdminReceipt = () => {
    form.receipt = null;
    adminReceiptPreview.value = null;
    if (adminReceiptInputRef.value) adminReceiptInputRef.value.value = '';
};

const paymentForm = useForm({
    amount: '',
    donation_date: new Date().toISOString().split('T')[0],
    receipt: null,
    is_anonymous: false,
    notes: '',
    donor_name: page.props.auth.user.name,
});

const verifyForm = useForm({ action: '', notes: '' });

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};

const calculateProgress = (collected, target) => {
    if (!target || target <= 0) return 0;
    return Math.min(Math.round((collected / target) * 100), 100);
};

const progress = computed(() => calculateProgress(props.donation.collected_amount, props.donation.target_amount));

const getStatusConfig = (status) => {
    const map = {
        active: { label: 'Aktif', class: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' },
        completed: { label: 'Selesai', class: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' },
        cancelled: { label: 'Dibatalkan', class: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' },
    };
    return map[status] || { label: status, class: 'bg-muted text-foreground' };
};

const getTxStatusConfig = (status) => {
    const map = {
        pending: { label: 'Menunggu', class: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' },
        paid: { label: 'Terverifikasi', class: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' },
        rejected: { label: 'Ditolak', class: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' },
    };
    return map[status] || { label: status, class: 'bg-muted text-foreground' };
};

const openModal = () => {
    form.reset();
    form.payment_method = 'cash';
    form.payment_date = new Date().toISOString().split('T')[0];
    adminReceiptPreview.value = null;
    showingModal.value = true;
};
const closeModal = () => {
    showingModal.value = false;
    adminReceiptPreview.value = null;
};

const openPaymentModal = () => {
    paymentForm.reset();
    paymentForm.donor_name = page.props.auth.user.name;
    showingPaymentModal.value = true;
};

const payReceiptInputRef = ref(null);
const isDraggingPayReceipt = ref(false);
const payReceiptPreview = ref(null);

const handlePayReceiptSelect = (e) => { const f = e.target.files[0]; if (f) setPayReceipt(f); };
const handlePayReceiptDrop = (e) => {
    isDraggingPayReceipt.value = false;
    const f = e.dataTransfer.files[0];
    if (f && f.type.startsWith('image/')) setPayReceipt(f);
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
const closePaymentModal = () => { showingPaymentModal.value = false; clearPayReceipt(); };

const openVerifyModal = (tx) => { selectedTransaction.value = tx; verifyForm.reset(); showingVerifyModal.value = true; };
const closeVerifyModal = () => { showingVerifyModal.value = false; selectedTransaction.value = null; };

const submitTransaction = () => {
    form.post(route('donations.transactions.store', props.donation.id), {
        onSuccess: () => {
            closeModal();
            form.reset();
            form.payment_method = 'cash';
            form.payment_date = new Date().toISOString().split('T')[0];
            adminReceiptPreview.value = null;
        },
    });
};

const submitPayment = () => {
    paymentForm.post(route('donations.pay', props.donation.id), {
        onSuccess: () => { closePaymentModal(); paymentForm.reset(); },
    });
};

const submitVerification = (action) => {
    verifyForm.action = action;
    verifyForm.post(route('donations.transactions.verify', selectedTransaction.value.id), {
        onSuccess: () => { closeVerifyModal(); verifyForm.reset(); },
    });
};

const showDeleteDonationModal = ref(false);
const deleteDonation = () => {
    showDeleteDonationModal.value = true;
};
const confirmDeleteDonation = () => {
    router.delete(route('donations.destroy', props.donation.id), {
        onError: (errors) => toast.error(Object.values(errors).flat().join(', ') || 'Gagal menghapus program donasi.'),
        onFinish: () => (showDeleteDonationModal.value = false),
    });
};

// Edit/Delete Transaction
const showingEditModal = ref(false);
const editingTransaction = ref(null);
const editForm = useForm({
    donor_name: '',
    donor_email: '',
    donor_phone: '',
    amount: 0,
    donation_date: '',
    is_anonymous: false,
    payment_method: 'cash',
    notes: '',
});

const openEditModal = (tx) => {
    editingTransaction.value = tx;
    editForm.donor_name = tx.donor_name || '';
    editForm.donor_email = tx.donor_email || '';
    editForm.donor_phone = tx.donor_phone || '';
    editForm.amount = parseFloat(tx.amount);
    editForm.donation_date = tx.donation_date ? tx.donation_date.split('T')[0] : '';
    editForm.is_anonymous = tx.is_anonymous || false;
    editForm.payment_method = tx.payment_method || 'cash';
    editForm.notes = tx.notes || '';
    showingEditModal.value = true;
};
const closeEditModal = () => { showingEditModal.value = false; editingTransaction.value = null; };

const submitEditTransaction = () => {
    editForm.put(route('donations.transactions.update', editingTransaction.value.id), {
        onSuccess: () => closeEditModal(),
    });
};

const deleteTransactionTarget = ref(null);
const deleteTransaction = (tx) => {
    deleteTransactionTarget.value = tx;
};
const confirmDeleteTransaction = () => {
    if (deleteTransactionTarget.value) {
        router.delete(route('donations.transactions.destroy', deleteTransactionTarget.value.id), {
            onError: (errors) => toast.error(Object.values(errors).flat().join(', ') || 'Gagal menghapus transaksi.'),
            onFinish: () => (deleteTransactionTarget.value = null),
        });
    }
};
</script>

<template>
    <Head :title="donation.program_name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <Button variant="ghost" size="icon" class="h-8 w-8 shrink-0" as-child>
                        <Link :href="route('donations.index')">
                            <ArrowLeft class="w-4 h-4" />
                        </Link>
                    </Button>
                    <h2 class="text-lg font-semibold leading-tight text-foreground truncate">{{ donation.program_name }}</h2>
                </div>
                <div class="flex gap-1.5">
                    <Button v-if="isAdmin" variant="outline" size="sm" as-child>
                        <Link :href="route('donations.edit', donation.id)">
                            <Pencil class="w-4 h-4 mr-1" />
                            <span class="hidden sm:inline">Edit</span>
                        </Link>
                    </Button>
                    <Button v-if="isAdmin" variant="destructive" size="sm" @click="deleteDonation">
                        <Trash2 class="w-4 h-4 mr-1" />
                        <span class="hidden sm:inline">Hapus</span>
                    </Button>
                </div>
            </div>
        </template>

        <div class="py-3 sm:py-6">
            <div class="mx-auto max-w-7xl px-3 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-6">

                    <!-- Left Column -->
                    <div class="lg:col-span-2 space-y-3 sm:space-y-4">

                        <!-- Main Info Card -->
                        <div class="bg-card rounded-xl border overflow-hidden">
                            <div
                                class="h-1 w-full"
                                :class="donation.status === 'active' ? 'bg-green-500' : donation.status === 'completed' ? 'bg-blue-500' : 'bg-muted-foreground/30'"
                            />
                            <div class="p-3 sm:p-5">
                                <div class="flex items-start justify-between gap-2 mb-2 sm:mb-3">
                                    <h2 class="text-sm sm:text-lg font-bold text-foreground">{{ donation.program_name }}</h2>
                                    <span :class="['px-1.5 sm:px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-semibold shrink-0', getStatusConfig(donation.status).class]">
                                        {{ getStatusConfig(donation.status).label }}
                                    </span>
                                </div>

                                <p class="text-xs sm:text-sm text-muted-foreground whitespace-pre-wrap mb-2.5 sm:mb-4 line-clamp-3 sm:line-clamp-none">
                                    {{ donation.description || 'Tidak ada deskripsi.' }}
                                </p>

                                <!-- Progress -->
                                <div class="bg-muted/50 rounded-lg p-2.5 sm:p-4 border space-y-1.5 sm:space-y-2.5">
                                    <div class="flex justify-between items-baseline">
                                        <div>
                                            <p class="text-[9px] sm:text-[10px] text-muted-foreground uppercase font-medium">Terkumpul</p>
                                            <p class="text-sm sm:text-lg font-bold text-primary tabular-nums">{{ formatCurrency(donation.collected_amount) }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-[9px] sm:text-[10px] text-muted-foreground uppercase font-medium">Target</p>
                                            <p class="text-xs sm:text-sm font-semibold text-foreground tabular-nums">{{ formatCurrency(donation.target_amount) }}</p>
                                        </div>
                                    </div>
                                    <div class="w-full bg-muted rounded-full h-1.5 sm:h-2 overflow-hidden">
                                        <div
                                            class="h-full rounded-full transition-all duration-700 ease-out"
                                            :class="progress >= 100 ? 'bg-green-500' : progress >= 50 ? 'bg-primary' : 'bg-amber-500'"
                                            :style="{ width: progress + '%' }"
                                        />
                                    </div>
                                    <p class="text-right text-[9px] sm:text-[10px] font-bold text-primary">{{ progress }}% Tercapai</p>
                                </div>

                                <!-- Meta -->
                                <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2.5 pt-2.5 sm:mt-4 sm:pt-4 border-t text-[11px] sm:text-sm">
                                    <div class="flex items-center gap-1 text-muted-foreground sm:flex-col sm:items-start sm:gap-0">
                                        <span class="sm:text-[10px] sm:uppercase sm:font-medium sm:tracking-wide">
                                            <CalendarDays class="w-3 h-3 inline sm:hidden" />
                                            <span class="hidden sm:inline">Tanggal Mulai</span>
                                        </span>
                                        <span class="font-medium text-foreground sm:mt-0.5">{{ formatDate(donation.start_date) }}</span>
                                    </div>
                                    <div class="flex items-center gap-1 text-muted-foreground sm:flex-col sm:items-start sm:gap-0">
                                        <span class="sm:hidden">-</span>
                                        <span class="hidden sm:inline sm:text-[10px] sm:uppercase sm:font-medium sm:tracking-wide">Tanggal Selesai</span>
                                        <span class="font-medium text-foreground sm:mt-0.5">{{ donation.end_date ? formatDate(donation.end_date) : 'Berlangsung' }}</span>
                                    </div>
                                    <div class="flex items-center gap-1 text-muted-foreground sm:flex-col sm:items-start sm:gap-0">
                                        <Globe class="w-3 h-3 sm:hidden" />
                                        <span class="hidden sm:inline sm:text-[10px] sm:uppercase sm:font-medium sm:tracking-wide">Visibilitas</span>
                                        <span class="font-medium text-foreground sm:mt-0.5 flex items-center gap-1">
                                            <Globe class="w-3.5 h-3.5 hidden sm:inline" />
                                            {{ donation.is_public ? 'Publik' : 'Internal' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Transactions -->
                        <div class="bg-card rounded-xl border overflow-hidden">
                            <div class="px-3 sm:px-5 py-2.5 sm:py-4 border-b flex flex-col sm:flex-row sm:items-center justify-between gap-2 sm:gap-3">
                                <h3 class="text-xs sm:text-sm font-semibold text-foreground">Daftar Donatur</h3>
                                <div class="flex gap-2">
                                    <div class="relative flex-1 sm:w-56">
                                        <Search class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-muted-foreground" />
                                        <Input v-model="searchQuery" placeholder="Cari donatur..." class="pl-8 h-7 sm:h-8 text-xs sm:text-sm" />
                                    </div>
                                    <Button v-if="isAdmin" size="sm" class="h-7 sm:h-8 text-xs shrink-0" @click="openModal">
                                        <Plus class="w-3.5 h-3.5 sm:hidden" />
                                        <span class="hidden sm:inline">Catat Donasi</span>
                                    </Button>
                                </div>
                            </div>

                            <!-- Mobile: Cards -->
                            <div class="sm:hidden divide-y">
                                <div v-for="tx in filteredTransactions" :key="tx.id" class="px-3 py-2 flex items-center gap-2.5" @click="openDetailSheet(tx)">
                                    <!-- Left: status dot -->
                                    <div
                                        class="w-1.5 h-1.5 rounded-full shrink-0"
                                        :class="tx.status === 'paid' ? 'bg-green-500' : tx.status === 'pending' ? 'bg-yellow-500' : 'bg-red-500'"
                                    />
                                    <!-- Middle: info -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-1">
                                            <p class="text-xs font-semibold text-foreground truncate">
                                                {{ tx.is_anonymous ? 'Hamba Allah' : (tx.donor_name || 'Anonim') }}
                                            </p>
                                            <span class="text-xs font-bold text-primary tabular-nums shrink-0">{{ formatCurrency(tx.amount) }}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-[10px] text-muted-foreground mt-0.5">
                                            <span>{{ formatDate(tx.donation_date) }}</span>
                                            <span class="text-border">|</span>
                                            <CreditCard v-if="tx.payment_method === 'transfer'" class="w-2.5 h-2.5" />
                                            <Banknote v-else class="w-2.5 h-2.5" />
                                            <span>{{ tx.payment_method === 'transfer' ? 'TF' : 'Cash' }}</span>
                                        </div>
                                    </div>
                                    <!-- Right: hamburger -->
                                    <Button variant="ghost" size="icon" class="h-7 w-7 shrink-0" @click.stop="openDetailSheet(tx)">
                                        <MoreVertical class="w-4 h-4 text-muted-foreground" />
                                    </Button>
                                </div>
                                <div v-if="filteredTransactions.length === 0" class="py-8 text-center">
                                    <Inbox class="w-6 h-6 text-muted-foreground mx-auto mb-1.5" />
                                    <p class="text-xs text-muted-foreground">Belum ada transaksi.</p>
                                </div>
                            </div>

                            <!-- Desktop: Table -->
                            <div class="hidden sm:block overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b bg-muted/50 text-[11px] text-muted-foreground uppercase tracking-wide">
                                            <th class="px-4 py-2.5 text-left font-medium">Tanggal</th>
                                            <th class="px-4 py-2.5 text-left font-medium">Donatur</th>
                                            <th class="px-4 py-2.5 text-right font-medium">Jumlah</th>
                                            <th class="px-4 py-2.5 text-center font-medium">Metode</th>
                                            <th class="px-4 py-2.5 text-center font-medium">Status</th>
                                            <th class="px-4 py-2.5 text-center font-medium">Bukti</th>
                                            <th class="px-4 py-2.5 text-left font-medium">Catatan</th>
                                            <th v-if="isAdmin" class="px-4 py-2.5 text-center font-medium">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y">
                                        <tr v-for="tx in filteredTransactions" :key="tx.id" class="hover:bg-muted/30 transition-colors">
                                            <td class="px-4 py-3 whitespace-nowrap text-muted-foreground">{{ formatDate(tx.donation_date) }}</td>
                                            <td class="px-4 py-3">
                                                <p class="font-medium text-foreground">{{ tx.is_anonymous ? 'Hamba Allah' : (tx.donor_name || 'Anonim') }}</p>
                                                <p v-if="!tx.is_anonymous && tx.donor_email" class="text-[11px] text-muted-foreground">{{ tx.donor_email }}</p>
                                            </td>
                                            <td class="px-4 py-3 text-right font-bold text-primary tabular-nums">{{ formatCurrency(tx.amount) }}</td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="inline-flex items-center gap-1 text-xs text-muted-foreground">
                                                    <CreditCard v-if="tx.payment_method === 'transfer'" class="w-3.5 h-3.5" />
                                                    <Banknote v-else class="w-3.5 h-3.5" />
                                                    {{ tx.payment_method === 'transfer' ? 'Transfer' : 'Tunai' }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <span :class="['px-1.5 py-0.5 rounded-full text-[10px] font-semibold', getTxStatusConfig(tx.status).class]">
                                                    {{ getTxStatusConfig(tx.status).label }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <a v-if="tx.receipt_path" :href="'/storage/' + tx.receipt_path" target="_blank" class="text-primary hover:text-primary/80 text-xs underline">Lihat</a>
                                                <span v-else class="text-muted-foreground text-xs">-</span>
                                            </td>
                                            <td class="px-4 py-3 text-muted-foreground italic max-w-[12rem] truncate">{{ tx.notes || '-' }}</td>
                                            <td v-if="isAdmin" class="px-4 py-3 text-center">
                                                <div class="flex items-center justify-center gap-1">
                                                    <Button v-if="tx.status === 'pending'" variant="outline" size="sm" class="h-7 text-xs" @click="openVerifyModal(tx)">
                                                        <ShieldCheck class="w-3.5 h-3.5 mr-1" />
                                                        Verifikasi
                                                    </Button>
                                                    <Button variant="ghost" size="icon" class="h-7 w-7" @click="openEditModal(tx)">
                                                        <Pencil class="w-3.5 h-3.5 text-muted-foreground" />
                                                    </Button>
                                                    <Button variant="ghost" size="icon" class="h-7 w-7" @click="deleteTransaction(tx)">
                                                        <Trash2 class="w-3.5 h-3.5 text-destructive" />
                                                    </Button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr v-if="filteredTransactions.length === 0">
                                            <td :colspan="isAdmin ? 8 : 7" class="py-12 text-center text-muted-foreground">
                                                <Inbox class="w-7 h-7 mx-auto mb-2 text-muted-foreground" />
                                                Belum ada transaksi donasi.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Sidebar -->
                    <div class="space-y-3 sm:space-y-4">
                        <!-- CTA for Member -->
                        <div v-if="isMember && donation.status === 'active'" class="bg-card rounded-xl border overflow-hidden">
                            <div class="h-1 w-full bg-primary" />
                            <div class="p-3 sm:p-5 flex sm:flex-col items-center sm:text-center gap-3 sm:space-y-3">
                                <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-full bg-primary/10 flex items-center justify-center shrink-0 sm:mx-auto">
                                    <Heart class="w-4 h-4 sm:w-5 sm:h-5 text-primary" />
                                </div>
                                <div class="flex-1 min-w-0 sm:flex-none">
                                    <h3 class="text-xs sm:text-sm font-bold text-foreground">Ingin Berdonasi?</h3>
                                    <p class="text-[10px] sm:text-xs text-muted-foreground mt-0.5 sm:mt-1">Sisihkan sebagian rezeki untuk membantu sesama.</p>
                                </div>
                                <Button size="sm" class="shrink-0 sm:w-full" @click="openPaymentModal">
                                    <Heart class="w-3.5 h-3.5 mr-1" />
                                    Donasi
                                </Button>
                            </div>
                        </div>

                        <!-- Info Sidebar -->
                        <div class="bg-card rounded-xl border p-3 sm:p-5">
                            <!-- Mobile: inline compact -->
                            <div class="sm:hidden flex items-center gap-4 text-[11px]">
                                <div class="flex items-center gap-1.5">
                                    <UserCircle class="w-3.5 h-3.5 text-primary shrink-0" />
                                    <span class="text-muted-foreground">{{ donation.creator.name }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <Users class="w-3.5 h-3.5 text-primary shrink-0" />
                                    <span class="text-muted-foreground">{{ donation.transactions.length }} Donatur</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <CalendarDays class="w-3.5 h-3.5 text-primary shrink-0" />
                                    <span class="text-muted-foreground">{{ formatDate(donation.created_at) }}</span>
                                </div>
                            </div>
                            <!-- Desktop: full sidebar -->
                            <div class="hidden sm:block space-y-4">
                                <h3 class="text-xs font-semibold text-muted-foreground uppercase tracking-wide">Informasi</h3>
                                <div class="space-y-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                                            <UserCircle class="w-4 h-4 text-primary" />
                                        </div>
                                        <div>
                                            <p class="text-[11px] text-muted-foreground">Dibuat Oleh</p>
                                            <p class="text-sm font-medium text-foreground">{{ donation.creator.name }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                                            <Users class="w-4 h-4 text-primary" />
                                        </div>
                                        <div>
                                            <p class="text-[11px] text-muted-foreground">Total Donatur</p>
                                            <p class="text-sm font-medium text-foreground">{{ donation.transactions.length }} Orang</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                                            <CalendarDays class="w-4 h-4 text-primary" />
                                        </div>
                                        <div>
                                            <p class="text-[11px] text-muted-foreground">Terdaftar Pada</p>
                                            <p class="text-sm font-medium text-foreground">{{ formatDate(donation.created_at) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Transaction Dialog (Admin) -->
        <Dialog :open="showingModal" @update:open="(val) => { if (!val) closeModal(); }">
            <DialogContent class="max-w-4xl max-h-[90vh] overflow-hidden flex flex-col p-0">
                <!-- Header -->
                <div class="px-5 pt-5 pb-4 border-b">
                    <DialogHeader>
                        <DialogTitle class="flex items-center gap-2.5 text-base">
                            <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                                <CircleDollarSign class="w-4 h-4 text-primary" />
                            </div>
                            Catat Transaksi Donasi
                        </DialogTitle>
                        <DialogDescription class="mt-1 text-xs">Catat transaksi donasi dari donatur.</DialogDescription>
                    </DialogHeader>
                </div>

                <form @submit.prevent="submitTransaction" class="flex flex-col flex-1 overflow-hidden">
                    <div class="flex-1 overflow-y-auto">
                        <div class="grid grid-cols-1 lg:grid-cols-12 divide-y lg:divide-y-0 lg:divide-x divide-border">

                            <!-- Left: Form Input -->
                            <div class="lg:col-span-7 p-5 space-y-4">
                                <!-- Member Selection -->
                                <div v-if="isAdmin">
                                    <SearchableSelect
                                        label="Anggota (Opsional)"
                                        v-model="form.member_id"
                                        :options="memberOptions"
                                        placeholder="Manual / Bukan Anggota"
                                        search-placeholder="Cari nama atau kode..."
                                        :error="form.errors.member_id"
                                    />
                                </div>

                                <!-- Donor Name + Anonymous -->
                                <div>
                                    <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Nama Donatur</Label>
                                    <div class="flex items-center gap-3 mt-1.5">
                                        <Input v-model="form.donor_name" class="flex-1" :disabled="form.is_anonymous" placeholder="Nama lengkap donatur" />
                                        <label class="flex items-center gap-1.5 shrink-0 cursor-pointer">
                                            <Checkbox :checked="form.is_anonymous" @update:checked="(v) => form.is_anonymous = v" />
                                            <span class="text-xs text-muted-foreground whitespace-nowrap">Hamba Allah</span>
                                        </label>
                                    </div>
                                    <p v-if="form.errors.donor_name" class="mt-1 text-xs text-destructive">{{ form.errors.donor_name }}</p>
                                </div>

                                <!-- Email + Phone -->
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Email</Label>
                                        <Input v-model="form.donor_email" type="email" class="mt-1.5" placeholder="email@contoh.com" />
                                        <p v-if="form.errors.donor_email" class="mt-1 text-xs text-destructive">{{ form.errors.donor_email }}</p>
                                    </div>
                                    <div>
                                        <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Telepon</Label>
                                        <Input v-model="form.donor_phone" class="mt-1.5" placeholder="08xxxxxxxxxx" />
                                        <p v-if="form.errors.donor_phone" class="mt-1 text-xs text-destructive">{{ form.errors.donor_phone }}</p>
                                    </div>
                                </div>

                                <div class="border-t" />

                                <!-- Nominal -->
                                <div>
                                    <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Nominal Donasi</Label>
                                    <div class="relative mt-1.5">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-base text-muted-foreground font-semibold">Rp</span>
                                        <CurrencyInput
                                            v-model="form.amount"
                                            class="block w-full rounded-lg border pl-10 pr-4 py-2.5 text-xl font-bold text-foreground focus:ring-primary focus:border-primary tabular-nums"
                                        />
                                    </div>
                                    <p v-if="form.errors.amount" class="mt-1 text-xs text-destructive">{{ form.errors.amount }}</p>
                                </div>

                                <!-- Date -->
                                <div>
                                    <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Tanggal Donasi</Label>
                                    <Input type="date" v-model="form.donation_date" class="mt-1.5 block w-full" />
                                    <p v-if="form.errors.donation_date" class="mt-1 text-xs text-destructive">{{ form.errors.donation_date }}</p>
                                </div>

                                <!-- Payment Method -->
                                <div>
                                    <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Metode Pembayaran</Label>
                                    <div class="grid grid-cols-2 gap-2 mt-1.5">
                                        <label class="cursor-pointer">
                                            <input type="radio" v-model="form.payment_method" value="cash" class="peer sr-only" />
                                            <div class="rounded-lg border-2 p-3 peer-checked:border-primary peer-checked:bg-primary/5 text-muted-foreground peer-checked:text-primary transition-all flex items-center gap-2.5 hover:bg-muted/50">
                                                <div class="w-8 h-8 rounded-lg bg-muted flex items-center justify-center shrink-0">
                                                    <Banknote class="w-4 h-4" />
                                                </div>
                                                <div>
                                                    <span class="text-sm font-semibold block">Tunai</span>
                                                    <span class="text-[11px] text-muted-foreground">Pembayaran langsung</span>
                                                </div>
                                            </div>
                                        </label>
                                        <label class="cursor-pointer">
                                            <input type="radio" v-model="form.payment_method" value="transfer" class="peer sr-only" />
                                            <div class="rounded-lg border-2 p-3 peer-checked:border-primary peer-checked:bg-primary/5 text-muted-foreground peer-checked:text-primary transition-all flex items-center gap-2.5 hover:bg-muted/50">
                                                <div class="w-8 h-8 rounded-lg bg-muted flex items-center justify-center shrink-0">
                                                    <CreditCard class="w-4 h-4" />
                                                </div>
                                                <div>
                                                    <span class="text-sm font-semibold block">Transfer</span>
                                                    <span class="text-[11px] text-muted-foreground">Via bank / e-wallet</span>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <!-- Receipt Upload -->
                                <div>
                                    <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Bukti Transfer <span class="normal-case font-normal">(Opsional)</span></Label>
                                    <div
                                        @dragover.prevent="isDraggingAdminReceipt = true"
                                        @dragleave.prevent="isDraggingAdminReceipt = false"
                                        @drop.prevent="handleAdminReceiptDrop($event)"
                                        @click="adminReceiptInputRef?.click()"
                                        class="mt-1.5 relative border-2 border-dashed rounded-lg p-4 text-center cursor-pointer transition-all duration-200"
                                        :class="isDraggingAdminReceipt ? 'border-primary bg-primary/5' : 'border-border hover:border-primary/40 hover:bg-muted/30'"
                                    >
                                        <input ref="adminReceiptInputRef" type="file" accept="image/*" class="hidden" @change="handleAdminReceiptSelect($event)" />
                                        <template v-if="!adminReceiptPreview">
                                            <div class="w-9 h-9 rounded-full bg-muted flex items-center justify-center mx-auto mb-1.5">
                                                <Upload class="h-4 w-4 text-muted-foreground" />
                                            </div>
                                            <p class="text-xs font-medium text-foreground">Upload Bukti Transfer</p>
                                            <p class="text-[11px] text-muted-foreground mt-0.5">Drag & drop atau klik. PNG, JPG max 2MB</p>
                                        </template>
                                        <template v-else>
                                            <div class="relative inline-block">
                                                <img :src="adminReceiptPreview" class="max-h-28 rounded-lg mx-auto shadow-sm" />
                                                <button type="button" @click.stop="clearAdminReceipt" class="absolute -top-2 -right-2 w-5 h-5 bg-destructive text-white rounded-full flex items-center justify-center hover:bg-destructive/80 shadow-sm">
                                                    <X class="w-3 h-3" />
                                                </button>
                                            </div>
                                            <p class="text-[11px] text-muted-foreground mt-1.5">{{ form.receipt?.name }}</p>
                                        </template>
                                    </div>
                                    <p v-if="form.errors.receipt" class="mt-1 text-xs text-destructive">{{ form.errors.receipt }}</p>
                                </div>

                                <!-- Notes -->
                                <div>
                                    <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Catatan <span class="normal-case font-normal">(Opsional)</span></Label>
                                    <Textarea v-model="form.notes" rows="2" class="mt-1.5" placeholder="Keterangan tambahan..." />
                                    <p v-if="form.errors.notes" class="mt-1 text-xs text-destructive">{{ form.errors.notes }}</p>
                                </div>
                            </div>

                            <!-- Right: Donation Info & Summary -->
                            <div class="lg:col-span-5 flex flex-col bg-muted/10">
                                <!-- Program Info Header -->
                                <div class="px-4 py-3 border-b">
                                    <h3 class="text-sm font-semibold text-foreground flex items-center gap-1.5">
                                        <Heart class="w-4 h-4 text-primary" />
                                        Info Program
                                    </h3>
                                    <p class="text-xs text-muted-foreground mt-0.5">{{ donation.program_name }}</p>
                                </div>

                                <!-- Program Details -->
                                <div class="flex-1 overflow-y-auto p-4 space-y-4">
                                    <!-- Progress -->
                                    <div class="bg-card rounded-lg border p-3.5 space-y-2">
                                        <div class="flex justify-between items-baseline">
                                            <div>
                                                <p class="text-[10px] text-muted-foreground uppercase font-medium">Terkumpul</p>
                                                <p class="text-sm font-bold text-primary tabular-nums">{{ formatCurrency(donation.collected_amount) }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-[10px] text-muted-foreground uppercase font-medium">Target</p>
                                                <p class="text-xs font-semibold text-foreground tabular-nums">{{ formatCurrency(donation.target_amount) }}</p>
                                            </div>
                                        </div>
                                        <div class="w-full bg-muted rounded-full h-1.5 overflow-hidden">
                                            <div
                                                class="h-full rounded-full transition-all duration-500"
                                                :class="progress >= 100 ? 'bg-green-500' : progress >= 50 ? 'bg-primary' : 'bg-amber-500'"
                                                :style="{ width: progress + '%' }"
                                            />
                                        </div>
                                        <p class="text-right text-[10px] font-bold text-primary">{{ progress }}% Tercapai</p>
                                    </div>

                                    <!-- Donor Preview Card -->
                                    <div v-if="selectedMember || form.donor_name" class="bg-card rounded-lg border p-3.5">
                                        <p class="text-[10px] text-muted-foreground uppercase font-medium tracking-wide mb-2">Data Donatur</p>
                                        <div class="space-y-1.5">
                                            <div class="flex items-center gap-2 text-sm">
                                                <User class="w-3.5 h-3.5 text-muted-foreground shrink-0" />
                                                <span class="font-medium text-foreground truncate">{{ form.is_anonymous ? 'Hamba Allah' : (form.donor_name || '-') }}</span>
                                            </div>
                                            <div v-if="form.donor_email && !form.is_anonymous" class="flex items-center gap-2 text-xs text-muted-foreground">
                                                <Mail class="w-3.5 h-3.5 shrink-0" />
                                                <span class="truncate">{{ form.donor_email }}</span>
                                            </div>
                                            <div v-if="form.donor_phone && !form.is_anonymous" class="flex items-center gap-2 text-xs text-muted-foreground">
                                                <Phone class="w-3.5 h-3.5 shrink-0" />
                                                <span>{{ form.donor_phone }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Recent Donors -->
                                    <div v-if="donation.transactions.length > 0">
                                        <p class="text-[10px] text-muted-foreground uppercase font-medium tracking-wide mb-2">Donatur Terakhir</p>
                                        <div class="space-y-1">
                                            <div
                                                v-for="tx in donation.transactions.slice(0, 5)"
                                                :key="tx.id"
                                                class="flex items-center justify-between px-3 py-2 rounded-lg border bg-card"
                                            >
                                                <div class="flex items-center gap-2 min-w-0">
                                                    <div class="w-6 h-6 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                                                        <User class="w-3 h-3 text-primary" />
                                                    </div>
                                                    <span class="text-xs font-medium text-foreground truncate">
                                                        {{ tx.is_anonymous ? 'Hamba Allah' : (tx.donor_name || 'Anonim') }}
                                                    </span>
                                                </div>
                                                <span class="text-xs font-bold text-primary tabular-nums shrink-0 ml-2">{{ formatCurrency(tx.amount) }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Empty state -->
                                    <div v-else class="flex flex-col items-center justify-center py-8 text-center">
                                        <div class="w-10 h-10 rounded-full bg-muted flex items-center justify-center mb-2">
                                            <Heart class="w-5 h-5 text-muted-foreground" />
                                        </div>
                                        <p class="text-sm font-medium text-muted-foreground">Belum ada donatur</p>
                                        <p class="text-xs text-muted-foreground mt-1">Transaksi pertama akan tercatat di sini.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-5 py-3 border-t bg-muted/30 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <div>
                            <p class="text-[11px] text-muted-foreground uppercase tracking-wide font-medium">Total Donasi</p>
                            <div class="flex items-baseline gap-2">
                                <p class="text-xl font-bold text-foreground tabular-nums">{{ formatCurrency(form.amount) }}</p>
                            </div>
                            <p v-if="selectedMember" class="text-xs text-muted-foreground">{{ selectedMember.full_name }}</p>
                            <p v-else-if="form.donor_name && !form.is_anonymous" class="text-xs text-muted-foreground">{{ form.donor_name }}</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <Button variant="outline" size="sm" type="button" @click="closeModal">Batal</Button>
                            <Button size="sm" type="submit" :disabled="form.processing">
                                <Loader2 v-if="form.processing" class="w-4 h-4 mr-1.5 animate-spin" />
                                {{ form.processing ? 'Memproses...' : 'Simpan Transaksi' }}
                            </Button>
                        </div>
                    </div>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Member Payment Dialog -->
        <Dialog :open="showingPaymentModal" @update:open="(val) => { if (!val) closePaymentModal(); }">
            <DialogContent class="max-w-lg p-0 overflow-hidden">
                <div class="px-5 pt-5 pb-4 border-b">
                    <DialogHeader>
                        <DialogTitle class="flex items-center gap-2 text-base">
                            <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                                <Heart class="w-4 h-4 text-primary" />
                            </div>
                            Sumbang Donasi
                        </DialogTitle>
                        <DialogDescription class="text-xs mt-1">Isi detail donasi Anda di bawah.</DialogDescription>
                    </DialogHeader>
                </div>
                <form @submit.prevent="submitPayment" class="divide-y">
                    <div class="px-5 py-4 space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <Label class="text-xs">Nama Donatur</Label>
                                <Input v-model="paymentForm.donor_name" class="mt-1" :disabled="paymentForm.is_anonymous" />
                                <p v-if="paymentForm.errors.donor_name" class="mt-1 text-xs text-destructive">{{ paymentForm.errors.donor_name }}</p>
                            </div>
                            <div class="flex items-end pb-2">
                                <div class="flex items-center gap-2">
                                    <Checkbox :checked="paymentForm.is_anonymous" @update:checked="(v) => paymentForm.is_anonymous = v" />
                                    <Label class="text-xs text-muted-foreground">Sembunyikan nama</Label>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <Label class="text-xs">Jumlah (Rp)</Label>
                                <Input v-model="paymentForm.amount" type="number" min="1" required class="mt-1" />
                                <p v-if="paymentForm.errors.amount" class="mt-1 text-xs text-destructive">{{ paymentForm.errors.amount }}</p>
                            </div>
                            <div>
                                <Label class="text-xs">Tanggal Transfer</Label>
                                <Input v-model="paymentForm.donation_date" type="date" required class="mt-1" />
                                <p v-if="paymentForm.errors.donation_date" class="mt-1 text-xs text-destructive">{{ paymentForm.errors.donation_date }}</p>
                            </div>
                        </div>

                        <!-- Receipt Upload -->
                        <div>
                            <Label class="text-xs">Bukti Transfer</Label>
                            <div
                                @dragover.prevent="isDraggingPayReceipt = true"
                                @dragleave.prevent="isDraggingPayReceipt = false"
                                @drop.prevent="handlePayReceiptDrop($event)"
                                @click="payReceiptInputRef?.click()"
                                class="mt-1.5 border-2 border-dashed rounded-lg p-4 text-center cursor-pointer transition-all duration-200"
                                :class="isDraggingPayReceipt ? 'border-primary bg-primary/5' : 'border-border hover:border-primary/40 hover:bg-muted/30'"
                            >
                                <input ref="payReceiptInputRef" type="file" accept="image/*" class="hidden" @change="handlePayReceiptSelect($event)" />
                                <template v-if="!payReceiptPreview">
                                    <div class="w-9 h-9 rounded-full bg-muted flex items-center justify-center mx-auto mb-1.5">
                                        <Upload class="w-4 h-4 text-muted-foreground" />
                                    </div>
                                    <p class="text-xs font-medium text-foreground">Upload Bukti Transfer</p>
                                    <p class="text-[11px] text-muted-foreground mt-0.5">Drag & drop atau klik. PNG, JPG max 2MB</p>
                                </template>
                                <template v-else>
                                    <div class="relative inline-block">
                                        <img :src="payReceiptPreview" class="max-h-28 rounded-lg mx-auto shadow-sm" />
                                        <button type="button" @click.stop="clearPayReceipt" class="absolute -top-2 -right-2 w-5 h-5 bg-destructive text-white rounded-full flex items-center justify-center hover:bg-destructive/80 shadow-sm">
                                            <X class="w-3 h-3" />
                                        </button>
                                    </div>
                                    <p class="text-[11px] text-muted-foreground mt-1.5">{{ paymentForm.receipt?.name }}</p>
                                </template>
                            </div>
                            <p v-if="paymentForm.errors.receipt" class="mt-1 text-xs text-destructive">{{ paymentForm.errors.receipt }}</p>
                        </div>

                        <div>
                            <Label class="text-xs">Catatan / Doa</Label>
                            <Textarea v-model="paymentForm.notes" rows="2" class="mt-1" placeholder="Semoga berkah..." />
                            <p v-if="paymentForm.errors.notes" class="mt-1 text-xs text-destructive">{{ paymentForm.errors.notes }}</p>
                        </div>
                    </div>

                    <div class="px-5 py-3 flex items-center justify-end gap-2 bg-muted/30">
                        <Button variant="outline" size="sm" type="button" @click="closePaymentModal">Batal</Button>
                        <Button size="sm" type="submit" :disabled="paymentForm.processing">
                            <Loader2 v-if="paymentForm.processing" class="w-4 h-4 mr-1 animate-spin" />
                            {{ paymentForm.processing ? 'Mengirim...' : 'Kirim Donasi' }}
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Verification Dialog -->
        <Dialog :open="showingVerifyModal" @update:open="(val) => { if (!val) closeVerifyModal(); }">
            <DialogContent class="max-w-lg p-0 overflow-hidden">
                <div class="px-5 pt-5 pb-4 border-b">
                    <DialogHeader>
                        <DialogTitle class="text-base">Verifikasi Donasi</DialogTitle>
                        <DialogDescription class="text-xs">Periksa detail donasi sebelum memverifikasi.</DialogDescription>
                    </DialogHeader>
                </div>

                <div v-if="selectedTransaction" class="px-5 py-4 space-y-4">
                    <div class="grid grid-cols-2 gap-2.5">
                        <div class="bg-muted/50 rounded-lg p-3">
                            <p class="text-[11px] font-medium uppercase text-muted-foreground tracking-wide mb-1">Donatur</p>
                            <p class="text-sm font-semibold text-foreground">{{ selectedTransaction.donor_name || 'Anonim' }}</p>
                        </div>
                        <div class="bg-muted/50 rounded-lg p-3">
                            <p class="text-[11px] font-medium uppercase text-muted-foreground tracking-wide mb-1">Jumlah</p>
                            <p class="text-sm font-bold text-primary tabular-nums">{{ formatCurrency(selectedTransaction.amount) }}</p>
                        </div>
                        <div class="bg-muted/50 rounded-lg p-3">
                            <p class="text-[11px] font-medium uppercase text-muted-foreground tracking-wide mb-1">Tanggal</p>
                            <p class="text-sm font-medium text-foreground">{{ formatDate(selectedTransaction.donation_date) }}</p>
                        </div>
                        <div class="bg-muted/50 rounded-lg p-3">
                            <p class="text-[11px] font-medium uppercase text-muted-foreground tracking-wide mb-1">Bukti</p>
                            <a v-if="selectedTransaction.receipt_path" :href="'/storage/' + selectedTransaction.receipt_path" target="_blank" class="text-sm text-primary underline hover:text-primary/80">Lihat Bukti</a>
                            <p v-else class="text-sm text-muted-foreground">Tidak ada</p>
                        </div>
                    </div>

                    <div>
                        <Label class="text-xs text-muted-foreground">Catatan Verifikasi</Label>
                        <Textarea v-model="verifyForm.notes" rows="2" class="mt-1" placeholder="Alasan terima/tolak..." />
                        <p v-if="verifyForm.errors.notes" class="mt-1 text-xs text-destructive">{{ verifyForm.errors.notes }}</p>
                    </div>
                </div>

                <div class="px-5 py-3 border-t bg-muted/30 flex items-center justify-end gap-2">
                    <Button variant="outline" size="sm" type="button" @click="closeVerifyModal">Batal</Button>
                    <Button variant="outline" size="sm" type="button" @click="submitVerification('reject')" :disabled="verifyForm.processing">
                        <XCircle class="w-4 h-4 mr-1" />
                        Tolak
                    </Button>
                    <Button size="sm" type="button" @click="submitVerification('approve')" :disabled="verifyForm.processing">
                        <CheckCircle2 class="w-4 h-4 mr-1" />
                        Setujui
                    </Button>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Edit Transaction Dialog -->
        <Dialog :open="showingEditModal" @update:open="(val) => { if (!val) closeEditModal(); }">
            <DialogContent class="max-w-lg">
                <DialogHeader>
                    <DialogTitle class="text-base">Edit Transaksi Donasi</DialogTitle>
                    <DialogDescription>Ubah detail transaksi donasi.</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitEditTransaction" class="space-y-4 px-5 py-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="col-span-2">
                            <Label class="text-xs text-muted-foreground">Nama Donatur</Label>
                            <Input v-model="editForm.donor_name" class="mt-1" />
                        </div>
                        <div>
                            <Label class="text-xs text-muted-foreground">Email</Label>
                            <Input v-model="editForm.donor_email" type="email" class="mt-1" />
                        </div>
                        <div>
                            <Label class="text-xs text-muted-foreground">Telepon</Label>
                            <Input v-model="editForm.donor_phone" class="mt-1" />
                        </div>
                        <div>
                            <Label class="text-xs text-muted-foreground">Jumlah</Label>
                            <CurrencyInput v-model="editForm.amount" class="mt-1" />
                        </div>
                        <div>
                            <Label class="text-xs text-muted-foreground">Tanggal</Label>
                            <Input v-model="editForm.donation_date" type="date" class="mt-1" />
                        </div>
                        <div>
                            <Label class="text-xs text-muted-foreground">Metode Pembayaran</Label>
                            <Select v-model="editForm.payment_method">
                                <SelectTrigger class="mt-1"><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="cash">Tunai</SelectItem>
                                    <SelectItem value="transfer">Transfer</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="flex items-end pb-1">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <Checkbox :checked="editForm.is_anonymous" @update:checked="(v) => editForm.is_anonymous = v" />
                                <span class="text-xs text-muted-foreground">Anonim</span>
                            </label>
                        </div>
                        <div class="col-span-2">
                            <Label class="text-xs text-muted-foreground">Catatan</Label>
                            <Textarea v-model="editForm.notes" rows="2" class="mt-1" />
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <Button variant="outline" size="sm" type="button" @click="closeEditModal">Batal</Button>
                        <Button size="sm" type="submit" :disabled="editForm.processing">
                            <Loader2 v-if="editForm.processing" class="w-4 h-4 mr-1 animate-spin" />
                            Simpan
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Mobile: Transaction Detail Sheet -->
        <Sheet :open="showingDetailSheet" @update:open="val => { if (!val) closeDetailSheet(); }">
            <SheetContent side="bottom" class="rounded-t-2xl max-h-[85vh] overflow-y-auto px-4 pb-6 pt-3">
                <SheetHeader class="text-left pb-3 border-b mb-3">
                    <SheetTitle class="text-sm">Detail Transaksi</SheetTitle>
                    <SheetDescription class="sr-only">Detail transaksi donasi</SheetDescription>
                </SheetHeader>

                <template v-if="detailTransaction">
                    <!-- Donor & Amount -->
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                                <User class="w-4 h-4 text-primary" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-foreground truncate">
                                    {{ detailTransaction.is_anonymous ? 'Hamba Allah' : (detailTransaction.donor_name || 'Anonim') }}
                                </p>
                                <p v-if="!detailTransaction.is_anonymous && detailTransaction.donor_email" class="text-[11px] text-muted-foreground truncate">
                                    {{ detailTransaction.donor_email }}
                                </p>
                            </div>
                        </div>
                        <span :class="['px-1.5 py-0.5 rounded-full text-[10px] font-semibold shrink-0', getTxStatusConfig(detailTransaction.status).class]">
                            {{ getTxStatusConfig(detailTransaction.status).label }}
                        </span>
                    </div>

                    <!-- Amount highlight -->
                    <div class="bg-primary/5 border border-primary/20 rounded-lg p-3 text-center mb-3">
                        <p class="text-[10px] text-muted-foreground uppercase font-medium">Jumlah Donasi</p>
                        <p class="text-lg font-bold text-primary tabular-nums">{{ formatCurrency(detailTransaction.amount) }}</p>
                    </div>

                    <!-- Detail grid -->
                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <div class="bg-muted/50 rounded-lg p-2.5">
                            <p class="text-[10px] text-muted-foreground uppercase font-medium">Tanggal</p>
                            <p class="text-xs font-medium text-foreground mt-0.5">{{ formatDate(detailTransaction.donation_date) }}</p>
                        </div>
                        <div class="bg-muted/50 rounded-lg p-2.5">
                            <p class="text-[10px] text-muted-foreground uppercase font-medium">Metode</p>
                            <p class="text-xs font-medium text-foreground mt-0.5 flex items-center gap-1">
                                <CreditCard v-if="detailTransaction.payment_method === 'transfer'" class="w-3 h-3" />
                                <Banknote v-else class="w-3 h-3" />
                                {{ detailTransaction.payment_method === 'transfer' ? 'Transfer' : 'Tunai' }}
                            </p>
                        </div>
                        <div v-if="!detailTransaction.is_anonymous && detailTransaction.donor_phone" class="bg-muted/50 rounded-lg p-2.5">
                            <p class="text-[10px] text-muted-foreground uppercase font-medium">Telepon</p>
                            <p class="text-xs font-medium text-foreground mt-0.5">{{ detailTransaction.donor_phone }}</p>
                        </div>
                        <div v-if="detailTransaction.receipt_path" class="bg-muted/50 rounded-lg p-2.5">
                            <p class="text-[10px] text-muted-foreground uppercase font-medium">Bukti</p>
                            <a :href="'/storage/' + detailTransaction.receipt_path" target="_blank" class="text-xs text-primary underline font-medium mt-0.5 inline-block">Lihat Bukti</a>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div v-if="detailTransaction.notes" class="bg-muted/50 rounded-lg p-2.5 mb-4">
                        <p class="text-[10px] text-muted-foreground uppercase font-medium">Catatan</p>
                        <p class="text-xs text-foreground mt-0.5 whitespace-pre-wrap">{{ detailTransaction.notes }}</p>
                    </div>

                    <!-- Actions -->
                    <div v-if="isAdmin" class="space-y-2 border-t pt-3">
                        <Button v-if="detailTransaction.status === 'pending'" variant="outline" size="sm" class="w-full justify-start" @click="closeDetailSheet(); openVerifyModal(detailTransaction);">
                            <ShieldCheck class="w-4 h-4 mr-2 text-primary" />
                            Verifikasi Transaksi
                        </Button>
                        <Button variant="outline" size="sm" class="w-full justify-start" @click="closeDetailSheet(); openEditModal(detailTransaction);">
                            <Pencil class="w-4 h-4 mr-2" />
                            Edit Transaksi
                        </Button>
                        <Button variant="outline" size="sm" class="w-full justify-start text-destructive hover:text-destructive" @click="closeDetailSheet(); deleteTransaction(detailTransaction);">
                            <Trash2 class="w-4 h-4 mr-2" />
                            Hapus Transaksi
                        </Button>
                    </div>
                </template>
            </SheetContent>
        </Sheet>

        <DeleteConfirmDialog
            :open="showDeleteDonationModal"
            title="Hapus Program Donasi"
            description="Apakah Anda yakin ingin menghapus program donasi ini? Semua transaksi terkait juga akan dihapus. Tindakan ini tidak dapat dibatalkan."
            @confirm="confirmDeleteDonation"
            @cancel="showDeleteDonationModal = false"
        />

        <DeleteConfirmDialog
            :open="!!deleteTransactionTarget"
            title="Hapus Transaksi Donasi"
            description="Apakah Anda yakin ingin menghapus transaksi donasi ini? Tindakan ini tidak dapat dibatalkan."
            @confirm="confirmDeleteTransaction"
            @cancel="deleteTransactionTarget = null"
        />
    </AuthenticatedLayout>
</template>
