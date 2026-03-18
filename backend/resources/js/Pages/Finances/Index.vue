<script setup>
import { ref, watch } from 'vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import {
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogDescription,
} from '@/components/ui/dialog';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import SearchBar from '@/Components/SearchBar.vue';
import FilterDropdown from '@/Components/FilterDropdown.vue';
import { Plus, TrendingUp, TrendingDown, Wallet, Calendar, Camera, X } from 'lucide-vue-next';

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

const receiptInputRef = ref(null);
const isDraggingReceipt = ref(false);
const receiptPreview = ref(null);

const handleReceiptSelect = (e) => {
    const file = e.target.files[0];
    if (file) setReceipt(file);
};

const handleReceiptDrop = (e) => {
    isDraggingReceipt.value = false;
    const file = e.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) setReceipt(file);
};

const setReceipt = (file) => {
    form.receipt = file;
    const reader = new FileReader();
    reader.onload = (e) => { receiptPreview.value = e.target.result; };
    reader.readAsDataURL(file);
};

const clearReceipt = () => {
    form.receipt = null;
    receiptPreview.value = null;
    if (receiptInputRef.value) receiptInputRef.value.value = '';
};

const formatReceiptSize = (bytes) => {
    if (!bytes) return '';
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(1) + ' MB';
};

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
            clearReceipt();
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
    return type === 'in' ? 'text-success-600 bg-success/10' : 'text-destructive bg-destructive/10';
};

const walletOptions = props.wallets.map(w => ({ value: w.id, label: w.name }));
const typeOptions = [
    { value: 'in', label: 'Pemasukan' },
    { value: 'out', label: 'Pengeluaran' },
];

const columns = [
    {
        key: 'transaction_date',
        label: 'Tanggal',
        sortable: true,
        format: (value) => new Date(value).toLocaleDateString('id-ID', { 
            day: '2-digit', 
            month: 'short', 
            year: 'numeric' 
        }),
    },
    {
        key: 'wallet',
        label: 'Dompet',
        sortable: true,
        format: (value) => value?.name || '-',
    },
    {
        key: 'category',
        label: 'Kategori',
        sortable: false,
    },
    {
        key: 'type',
        label: 'Jumlah',
        sortable: true,
        format: (value, row) => {
            const amount = formatCurrency(row.amount);
            const sign = value === 'in' ? '+' : '-';
            return {
                type: 'badge',
                value: `${sign} ${amount}`,
                props: {
                    variant: value === 'in' ? 'success' : 'destructive',
                    class: 'font-black px-3 py-1'
                }
            };
        },
    },
];

const handleAction = ({ action, row }) => {
    if (action === 'detail') {
        openDetail(row);
    } else if (action === 'delete') {
        deleteFinance(row);
    }
};

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
                <h2 class="text-xl font-semibold leading-tight text-foreground">
                    Transaksi Keuangan
                </h2>
                <Button type="submit" @click="showModal = true" v-if="hasPermission('manage_finance')">
                    Catat Transaksi
                </Button>
            </div>
        </template>

        <div class="py-4 sm:py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Compact Summary -->
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="bg-card p-3 sm:p-4 rounded-xl border">
                        <p class="text-[10px] font-bold uppercase text-muted-foreground">Pemasukan</p>
                        <p class="text-lg sm:text-xl font-bold text-success-700">{{ formatCurrency(stats.total_income) }}</p>
                    </div>
                    <div class="bg-card p-3 sm:p-4 rounded-xl border">
                        <p class="text-[10px] font-bold uppercase text-muted-foreground">Pengeluaran</p>
                        <p class="text-lg sm:text-xl font-bold text-destructive">{{ formatCurrency(stats.total_expense) }}</p>
                    </div>
                </div>

                <div class="bg-card border rounded-xl overflow-hidden">
                    <!-- Compact filter bar -->
                    <div class="p-3 sm:p-4 border-b">
                        <div class="flex flex-col sm:flex-row gap-2">
                            <div class="flex-1">
                                <SearchBar v-model="search" placeholder="Cari kategori / keterangan..." />
                            </div>
                            <div class="flex gap-2">
                                <div class="flex-1 sm:w-32"><FilterDropdown v-model="wallet_id" :options="walletOptions" label="Dompet" /></div>
                                <div class="flex-1 sm:w-28"><FilterDropdown v-model="type" :options="typeOptions" label="Jenis" /></div>
                            </div>
                        </div>
                        <div class="flex gap-2 mt-2">
                            <Input type="date" v-model="date_from" class="flex-1 text-xs h-8" placeholder="Dari" />
                            <Input type="date" v-model="date_to" class="flex-1 text-xs h-8" placeholder="Sampai" />
                        </div>
                    </div>

                    <!-- Finances Table using DataTable Component -->
                    <DataTable
                        :data="finances"
                        :columns="columns"
                        :filters="filters"
                        search-route="finances.index"
                        :sortable="true"
                        :show-column-filter="true"
                        :striped="true"
                        @action="handleAction"
                    >
                        <template #actions="{ row }">
                            <div class="flex justify-end gap-2">
                                <Button 
                                    variant="ghost" 
                                    size="sm"
                                    @click="openDetail(row)"
                                    title="Detail Transaksi"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </Button>
                                <Button 
                                    v-if="hasPermission('manage_finance')"
                                    variant="ghost" 
                                    size="sm"
                                    class="text-destructive"
                                    @click="deleteFinance(row)"
                                    title="Hapus"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </Button>
                            </div>
                        </template>
                    </DataTable>
                </div>
            </div>
        </div>

        <!-- Dialog Catat Transaksi -->
        <Dialog :open="showModal" @update:open="(val) => { if (!val) showModal = false; }">
            <DialogContent class="max-w-2xl">
                <DialogHeader>
                    <DialogTitle>Catat Transaksi Baru</DialogTitle>
                    <DialogDescription>Masukkan detail pemasukan atau pengeluaran.</DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submit" class="space-y-6">

                    <!-- Main Type Selector -->
                    <div class="grid grid-cols-2 gap-4 p-1 bg-muted/80 rounded-2xl">
                        <button type="button" @click="form.type = 'in'"
                            class="relative py-3 rounded-xl font-black uppercase tracking-wider text-xs transition-all duration-200 flex items-center justify-center gap-2 overflow-hidden"
                            :class="form.type === 'in' ? 'bg-card shadow-sm text-green-600 ring-2 ring-green-500/20' : 'text-muted-foreground hover:text-muted-foreground'">
                            <TrendingUp class="w-4 h-4" />
                            Pemasukan
                        </button>
                        <button type="button" @click="form.type = 'out'"
                            class="relative py-3 rounded-xl font-black uppercase tracking-wider text-xs transition-all duration-200 flex items-center justify-center gap-2 overflow-hidden"
                            :class="form.type === 'out' ? 'bg-card shadow-sm text-destructive ring-2 ring-red-500/20' : 'text-muted-foreground hover:text-muted-foreground'">
                            <TrendingDown class="w-4 h-4" />
                            Pengeluaran
                        </button>
                    </div>

                    <!-- Amount Input -->
                    <div class="relative">
                        <label class="block text-center text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-2">
                            Nominal (Rp)
                        </label>
                        <div class="relative max-w-xs mx-auto">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-muted-foreground font-bold">Rp</span>
                            <Input type="number" v-model="form.amount" required step="0.01" placeholder="0"
                                class="w-full pl-12 pr-4 py-4 text-3xl font-black text-center border-2 focus:border-ring focus:ring-0 transition placeholder:text-muted-foreground"
                                :class="form.type === 'in' ? 'text-green-600' : 'text-destructive'" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Wallet Selection -->
                        <div class="space-y-2">
                            <Label class="text-[10px] font-black uppercase text-muted-foreground">Dompet / Sumber Dana</Label>
                            <Select v-model="form.wallet_id" required>
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Pilih Dompet" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="wallet in wallets" :key="wallet.id" :value="wallet.id.toString()">
                                        {{ wallet.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <div v-if="form.wallet_id" class="flex items-center justify-between px-3 py-2 bg-primary/10 rounded-lg border border-primary/20">
                                <span class="text-[10px] uppercase font-bold text-primary/60">Saldo Saat Ini</span>
                                <span class="text-xs font-black text-primary font-mono">
                                    {{ formatCurrency(wallets.find(w => w.id === form.wallet_id)?.balance || 0) }}
                                </span>
                            </div>
                        </div>

                        <!-- Date Input -->
                        <div class="space-y-2">
                            <Label class="text-[10px] font-black uppercase text-muted-foreground">Tanggal Transaksi</Label>
                            <Input type="date" v-model="form.transaction_date" required />
                        </div>
                    </div>

                    <!-- Category -->
                    <div class="space-y-2">
                        <Label class="text-[10px] font-black uppercase text-muted-foreground">Kategori</Label>
                        <Select v-model="form.category" required>
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Pilih Kategori" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="" disabled>-- Pilih Kategori --</SelectItem>
                                <SelectItem v-for="cat in categories[form.type]" :key="cat" :value="cat">
                                    {{ cat }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Description -->
                    <div class="space-y-2">
                         <Label class="text-[10px] font-black uppercase text-muted-foreground">Keterangan (Opsional)</Label>
                        <Textarea v-model="form.description" rows="2" placeholder="Tulis rincian tambahan..." />
                    </div>

                    <!-- File Upload -->
                    <div>
                        <div
                            @dragover.prevent="isDraggingReceipt = true"
                            @dragleave.prevent="isDraggingReceipt = false"
                            @drop.prevent="handleReceiptDrop($event)"
                            @click="receiptInputRef?.click()"
                            class="relative border-2 border-dashed rounded-xl p-5 text-center cursor-pointer transition-all duration-200"
                            :class="isDraggingReceipt ? 'border-primary bg-primary/5 scale-[1.01]' : 'border-border hover:border-primary/50 hover:bg-muted/30'"
                        >
                            <input ref="receiptInputRef" type="file" accept="image/*" class="hidden" @change="handleReceiptSelect($event)" />
                            <template v-if="!receiptPreview">
                                <Camera class="mx-auto h-8 w-8 text-muted-foreground mb-2" />
                                <p class="text-sm font-medium text-primary">Upload Bukti Foto</p>
                                <p class="text-xs text-muted-foreground mt-1">Drag & drop atau klik untuk memilih. PNG, JPG max 2MB</p>
                            </template>
                            <template v-else>
                                <div class="relative inline-block">
                                    <img :src="receiptPreview" class="max-h-32 rounded-lg mx-auto" />
                                    <button type="button" @click.stop="clearReceipt" class="absolute -top-2 -right-2 w-6 h-6 bg-destructive text-white rounded-full flex items-center justify-center hover:bg-destructive/80">
                                        <X class="w-3 h-3" />
                                    </button>
                                </div>
                                <p class="text-xs text-muted-foreground mt-2">{{ form.receipt?.name }} ({{ formatReceiptSize(form.receipt?.size) }})</p>
                            </template>
                        </div>
                    </div>

                    <DialogFooter class="gap-2">
                        <Button variant="outline" type="button" @click="showModal = false" class="flex-1">
                            Batal
                        </Button>
                        <Button type="submit" :disabled="form.processing" class="flex-[2]">
                            <span v-if="form.processing">Menyimpan...</span>
                            <span v-else>Simpan Transaksi</span>
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Detail Dialog -->
        <Dialog :open="showDetailModal" @update:open="(val) => { if (!val) showDetailModal = false; }">
            <DialogContent v-if="selectedFinance">
                <DialogHeader>
                    <DialogTitle>Detail Transaksi</DialogTitle>
                </DialogHeader>

                <div class="bg-muted rounded-2xl p-6 mb-6">
                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <div>
                            <p class="text-[10px] font-black uppercase text-muted-foreground mb-1">Status</p>
                            <Badge :variant="selectedFinance.type === 'in' ? 'success' : 'destructive'">
                                {{ selectedFinance.type === 'in' ? 'Pemasukan' : 'Pengeluaran' }}
                            </Badge>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase text-muted-foreground mb-1">Tanggal</p>
                            <p class="text-sm font-black text-foreground">{{ new Date(selectedFinance.transaction_date).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase text-muted-foreground mb-1">Dompet</p>
                            <p class="text-sm font-black text-primary">{{ selectedFinance.wallet.name }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase text-muted-foreground mb-1">Kategori</p>
                            <p class="text-sm font-black text-foreground">{{ selectedFinance.category }}</p>
                        </div>
                    </div>
                    <div class="border-t border pt-6">
                        <p class="text-[10px] font-black uppercase text-muted-foreground mb-2">Jumlah</p>
                        <p class="text-2xl sm:text-3xl font-black" :class="selectedFinance.type === 'in' ? 'text-green-600' : 'text-destructive'">
                            {{ formatCurrency(selectedFinance.amount) }}
                        </p>
                    </div>
                </div>

                <div class="mb-6">
                    <p class="text-[10px] font-black uppercase text-muted-foreground mb-2">Keterangan / Rincian</p>
                    <p class="text-sm text-muted-foreground bg-card border border rounded-xl p-4 italic">
                        "{{ selectedFinance.description }}"
                    </p>
                </div>

                <div v-if="selectedFinance.receipt_path" class="mb-6">
                    <p class="text-[10px] font-black uppercase text-muted-foreground mb-2">Bukti Transaksi</p>
                    <div class="rounded-2xl overflow-hidden border border shadow-sm">
                        <img :src="'/storage/' + selectedFinance.receipt_path" class="w-full h-auto object-cover" />
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="showDetailModal = false" class="px-8">Tutup</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AuthenticatedLayout>
</template>
