<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import {
    Sheet, SheetContent, SheetHeader, SheetTitle, SheetDescription, SheetFooter,
} from '@/components/ui/sheet';
import {
    DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import FilterDropdown from '@/Components/FilterDropdown.vue';
import {
    Plus, TrendingUp, TrendingDown, Wallet, Calendar, Camera, X,
    Search, SlidersHorizontal, MoreVertical, Eye, Trash2,
} from 'lucide-vue-next';

const props = defineProps({
    finances: Object,
    wallets: Array,
    filters: Object,
    stats: Object,
});

const showModal = ref(false);
const showDetailModal = ref(false);
const selectedFinance = ref(null);
const showFiltersSheet = ref(false);

// Filter states
const search = ref(props.filters.search || '');
const wallet_id = ref(props.filters.wallet_id || '');
const type = ref(props.filters.type || '');
const date_from = ref(props.filters.date_from || '');
const date_to = ref(props.filters.date_to || '');

const walletOptions = props.wallets.map(w => ({ value: String(w.id), label: w.name }));
const typeOptions = [
    { value: 'in', label: 'Pemasukan' },
    { value: 'out', label: 'Pengeluaran' },
];

// Active filters (Members-style chips)
const activeFilters = computed(() => {
    const items = [];
    const optionLabel = (opts, val) => {
        const f = opts.find(o => String(o.value) === String(val));
        return f ? f.label : val;
    };
    if (wallet_id.value) items.push({ key: 'wallet_id', label: `Dompet: ${optionLabel(walletOptions, wallet_id.value)}` });
    if (type.value) items.push({ key: 'type', label: `Jenis: ${optionLabel(typeOptions, type.value)}` });
    if (date_from.value) items.push({ key: 'date_from', label: `Dari: ${date_from.value}` });
    if (date_to.value) items.push({ key: 'date_to', label: `Sampai: ${date_to.value}` });
    return items;
});

const activeFilterCount = computed(() => activeFilters.value.length);

const clearFilter = (key) => {
    const map = { wallet_id, type, date_from, date_to };
    if (map[key]) map[key].value = '';
};

const resetFilters = () => {
    wallet_id.value = '';
    type.value = '';
    date_from.value = '';
    date_to.value = '';
    applyFilters();
};

const applyFilters = () => {
    router.get(route('finances.index'), {
        search: search.value,
        wallet_id: wallet_id.value,
        type: type.value,
        date_from: date_from.value,
        date_to: date_to.value,
    }, { preserveState: true, replace: true });
};

// Debounced search for mobile
let searchTimeout = null;
const onMobileSearch = (val) => {
    search.value = val;
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 300);
};

// Desktop: watch filters for auto-apply via DataTable
watch([search, wallet_id, type, date_from, date_to], () => {
    applyFilters();
});

// Receipt upload
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

// Amount formatting with thousand separator
const amountDisplay = ref('');

const formatThousand = (val) => {
    if (!val) return '';
    return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
};

const onAmountInput = (e) => {
    const raw = e.target.value.replace(/\./g, '');
    const num = raw.replace(/[^\d]/g, '');
    form.amount = num;
    amountDisplay.value = formatThousand(num);
    e.target.value = amountDisplay.value;
};

const formErrors = ref({});

const clearError = (field) => {
    if (formErrors.value[field]) {
        const { [field]: _, ...rest } = formErrors.value;
        formErrors.value = rest;
    }
};

const validate = () => {
    const errors = {};
    if (!form.amount || Number(form.amount) <= 0) errors.amount = 'Nominal harus diisi.';
    if (!form.wallet_id) errors.wallet_id = 'Silakan pilih dompet.';
    if (!form.transaction_date) errors.transaction_date = 'Tanggal harus diisi.';
    if (!form.category) errors.category = 'Kategori harus dipilih.';
    formErrors.value = errors;
    return Object.keys(errors).length === 0;
};

const submit = () => {
    if (!validate()) return;
    form.post(route('finances.store'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
            formErrors.value = {};
            amountDisplay.value = '';
            clearReceipt();
        },
    });
};

const deleteFinance = (finance) => {
    if (confirm('Apakah Anda yakin ingin menghapus transaksi ini? Saldo dompet akan disesuaikan kembali.')) {
        router.delete(route('finances.destroy', finance.id), { preserveScroll: true });
    }
};

const openDetail = (finance) => {
    selectedFinance.value = finance;
    showDetailModal.value = true;
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(amount);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};

const formatDateLong = (date) => {
    return new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
};

const columns = [
    {
        key: 'transaction_date',
        label: 'Tanggal',
        sortable: true,
        format: (value) => formatDate(value),
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
    if (action === 'detail') openDetail(row);
    else if (action === 'delete') deleteFinance(row);
};

const handleDropdownAction = (action, row) => {
    setTimeout(() => {
        if (action === 'detail') openDetail(row);
        else if (action === 'delete') deleteFinance(row);
    }, 100);
};

const categories = {
    in: [
        'Iuran Anggota', 'Donasi / Sumbangan', 'Sponsorship', 'Dana Hibah',
        'Penjualan / Fundraising', 'Bunga Bank', 'Pengembalian Dana', 'Lainnya'
    ],
    out: [
        'Konsumsi & Logistik', 'Transportasi & Perjalanan', 'Perlengkapan & Peralatan',
        'Sewa Tempat / Gedung', 'Listrik, Air & Internet', 'Cetak, Fotokopi & ATK',
        'Honorarium / Jasa', 'Pemeliharaan & Perbaikan', 'Acara / Kegiatan',
        'Bantuan Sosial', 'Promosi & Publikasi', 'Lainnya'
    ]
};

watch(() => form.type, () => { form.category = ''; });
</script>

<template>
    <Head title="Transaksi Keuangan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold leading-tight text-foreground">Transaksi Keuangan</h2>
                <Button v-if="hasPermission('manage_finance')" size="sm" @click="showModal = true">
                    <Plus class="w-4 h-4 mr-1" />
                    <span class="hidden sm:inline">Catat Transaksi</span>
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

                <!-- Mobile: Card-based list -->
                <div class="md:hidden">
                    <!-- Mobile Search & Filter Bar -->
                    <div class="flex items-center gap-2 mb-3">
                        <div class="relative flex-1">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                            <input
                                type="text"
                                :value="search"
                                placeholder="Cari kategori / keterangan..."
                                class="w-full pl-9 pr-3 py-2.5 bg-card border rounded-xl text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary"
                                @input="onMobileSearch($event.target.value)"
                            />
                        </div>
                        <Button variant="outline" size="icon" class="shrink-0 h-10 w-10 relative" @click="showFiltersSheet = true">
                            <SlidersHorizontal class="w-4 h-4" />
                            <Badge v-if="activeFilterCount" variant="default" class="absolute -top-1 -right-1 h-4 w-4 p-0 justify-center text-[10px]">{{ activeFilterCount }}</Badge>
                        </Button>
                    </div>

                    <!-- Active filter chips mobile -->
                    <div v-if="activeFilters.length" class="flex flex-wrap gap-1.5 mb-3">
                        <button
                            v-for="f in activeFilters" :key="f.key"
                            class="inline-flex items-center gap-1 rounded-full bg-primary/10 text-primary px-2.5 py-0.5 text-xs font-medium"
                            @click="clearFilter(f.key)"
                        >
                            {{ f.label }}
                            <X class="w-3 h-3" />
                        </button>
                        <button class="text-xs text-muted-foreground px-2" @click="resetFilters">Reset</button>
                    </div>

                    <!-- Transaction List -->
                    <div class="bg-card border rounded-xl overflow-hidden divide-y divide-border">
                        <div
                            v-for="finance in finances.data"
                            :key="finance.id"
                            class="flex items-center gap-3 px-4 py-3 active:bg-muted/50 transition-colors"
                            @click="openDetail(finance)"
                        >
                            <!-- Type icon -->
                            <div class="shrink-0">
                                <div
                                    class="h-11 w-11 rounded-full flex items-center justify-center"
                                    :class="finance.type === 'in' ? 'bg-green-500/10' : 'bg-destructive/10'"
                                >
                                    <TrendingUp v-if="finance.type === 'in'" class="w-5 h-5 text-green-600" />
                                    <TrendingDown v-else class="w-5 h-5 text-destructive" />
                                </div>
                            </div>

                            <!-- Category & Info -->
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-foreground truncate">{{ finance.category }}</p>
                                <p class="text-xs text-muted-foreground mt-0.5 flex items-center gap-1.5">
                                    <Wallet class="w-3 h-3" />
                                    {{ finance.wallet?.name || '-' }}
                                    <span class="text-muted-foreground/40">·</span>
                                    {{ formatDate(finance.transaction_date) }}
                                </p>
                            </div>

                            <!-- Amount -->
                            <div class="shrink-0 text-right">
                                <p class="text-sm font-bold" :class="finance.type === 'in' ? 'text-green-600' : 'text-destructive'">
                                    {{ finance.type === 'in' ? '+' : '-' }} {{ formatCurrency(finance.amount) }}
                                </p>
                            </div>

                            <!-- Hamburger menu -->
                            <DropdownMenu v-if="hasPermission('manage_finance')">
                                <DropdownMenuTrigger as-child>
                                    <button
                                        class="shrink-0 w-8 h-8 flex items-center justify-center rounded-full hover:bg-muted transition-colors"
                                        @click.stop
                                    >
                                        <MoreVertical class="w-4 h-4 text-muted-foreground" />
                                    </button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuItem @click="handleDropdownAction('detail', finance)">
                                        <Eye class="h-4 w-4 mr-2" />
                                        Lihat Detail
                                    </DropdownMenuItem>
                                    <DropdownMenuItem @click="handleDropdownAction('delete', finance)" class="text-destructive">
                                        <Trash2 class="h-4 w-4 mr-2" />
                                        Hapus
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>

                        <!-- Empty state -->
                        <div v-if="finances.data.length === 0" class="px-4 py-12 text-center">
                            <Wallet class="mx-auto h-10 w-10 text-muted-foreground/40 mb-3" />
                            <p class="text-muted-foreground text-sm">Tidak ada transaksi ditemukan.</p>
                        </div>
                    </div>

                    <!-- Mobile Pagination -->
                    <div v-if="finances.links?.length > 3" class="flex justify-center gap-1 mt-4">
                        <Link
                            v-for="link in finances.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            class="min-w-[40px] min-h-[40px] px-3 py-2 rounded-lg text-xs font-medium flex items-center justify-center transition-colors"
                            :class="link.active
                                ? 'bg-primary text-primary-foreground'
                                : link.url
                                    ? 'bg-card border text-muted-foreground hover:bg-muted'
                                    : 'text-muted-foreground/40 pointer-events-none'"
                            v-html="link.label"
                        />
                    </div>
                </div>

                <!-- Desktop: DataTable -->
                <div class="hidden md:block bg-card border rounded-xl overflow-hidden">
                    <!-- Active filter chips -->
                    <div v-if="activeFilters.length" class="px-4 py-2 border-b flex flex-wrap gap-1.5">
                        <button
                            v-for="f in activeFilters" :key="f.key"
                            class="inline-flex items-center gap-1 rounded-full bg-primary/10 text-primary px-2.5 py-0.5 text-xs font-medium hover:bg-primary/20"
                            @click="clearFilter(f.key)"
                        >
                            {{ f.label }}
                            <X class="w-3 h-3" />
                        </button>
                        <button class="text-xs text-muted-foreground hover:text-foreground px-2" @click="resetFilters">
                            Reset
                        </button>
                    </div>

                    <!-- Table -->
                    <DataTable
                        :data="finances"
                        :columns="columns"
                        :filters="filters"
                        search-route="finances.index"
                        :sortable="true"
                        :striped="true"
                        @action="handleAction"
                    >
                        <template #header-actions>
                            <div class="flex items-center gap-2">
                                <Button variant="outline" size="sm" @click="showFiltersSheet = true" class="gap-1.5">
                                    <SlidersHorizontal class="w-4 h-4" />
                                    Filter
                                    <Badge v-if="activeFilterCount" variant="default" class="ml-1 h-5 w-5 p-0 justify-center text-[10px]">{{ activeFilterCount }}</Badge>
                                </Button>
                            </div>
                        </template>
                        <template #cell-transaction_date="{ row }">
                            <span class="text-sm">{{ formatDate(row.transaction_date) }}</span>
                        </template>
                        <template #cell-wallet="{ row }">
                            <span class="text-sm">{{ row.wallet?.name || '-' }}</span>
                        </template>
                        <template #cell-category="{ row }">
                            <span class="text-sm font-medium">{{ row.category }}</span>
                            <span v-if="row.description" class="block text-xs text-muted-foreground truncate max-w-[200px]">{{ row.description }}</span>
                        </template>
                        <template #cell-type="{ row }">
                            <Badge :variant="row.type === 'in' ? 'success' : 'destructive'" class="font-bold px-3 py-1">
                                {{ row.type === 'in' ? '+' : '-' }} {{ formatCurrency(row.amount) }}
                            </Badge>
                        </template>
                        <template #actions="{ row }">
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="ghost" size="icon">
                                        <MoreVertical class="h-4 w-4" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuItem @click="handleDropdownAction('detail', row)">
                                        <Eye class="h-4 w-4 mr-2" />
                                        Lihat Detail
                                    </DropdownMenuItem>
                                    <DropdownMenuItem v-if="hasPermission('manage_finance')" @click="handleDropdownAction('delete', row)" class="text-destructive">
                                        <Trash2 class="h-4 w-4 mr-2" />
                                        Hapus
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </template>
                    </DataTable>
                </div>
            </div>
        </div>

        <!-- Filter Sheet (Members-style) -->
        <Sheet :open="showFiltersSheet" @update:open="showFiltersSheet = $event">
            <SheetContent side="right" class="w-80">
                <SheetHeader>
                    <SheetTitle>Filter Transaksi</SheetTitle>
                    <SheetDescription>Atur filter untuk menyaring data.</SheetDescription>
                </SheetHeader>
                <div class="flex-1 overflow-y-auto space-y-4 py-4">
                    <div class="space-y-1.5">
                        <Label class="text-xs text-muted-foreground">Dompet</Label>
                        <FilterDropdown v-model="wallet_id" :options="walletOptions" label="Semua" />
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs text-muted-foreground">Jenis Transaksi</Label>
                        <FilterDropdown v-model="type" :options="typeOptions" label="Semua" />
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs text-muted-foreground">Periode Transaksi</Label>
                        <div class="flex gap-2">
                            <Input v-model="date_from" type="date" class="text-xs" />
                            <Input v-model="date_to" type="date" class="text-xs" />
                        </div>
                    </div>
                </div>
                <SheetFooter class="gap-2 pt-4 border-t">
                    <Button variant="outline" size="sm" class="flex-1" @click="resetFilters">Reset</Button>
                    <Button size="sm" class="flex-1" @click="applyFilters(); showFiltersSheet = false">Terapkan</Button>
                </SheetFooter>
            </SheetContent>
        </Sheet>

        <!-- Sheet Catat Transaksi -->
        <Sheet :open="showModal" @update:open="(val) => { if (!val) showModal = false; }">
            <SheetContent side="bottom" class="sm:max-w-lg sm:mx-auto sm:rounded-t-2xl max-h-[92vh] flex flex-col">
                <!-- Header -->
                <div class="px-5 pt-5 pb-3 border-b shrink-0">
                    <h3 class="text-lg font-bold text-foreground">Catat Transaksi</h3>
                    <p class="text-xs text-muted-foreground mt-0.5">Masukkan detail pemasukan atau pengeluaran</p>
                </div>

                <!-- Scrollable form -->
                <form @submit.prevent="submit" class="flex-1 overflow-y-auto">
                    <div class="p-5 space-y-5">
                        <!-- Type Selector -->
                        <div class="grid grid-cols-2 gap-3 p-1.5 bg-muted/60 rounded-2xl">
                            <button type="button" @click="form.type = 'in'"
                                class="relative py-3.5 rounded-xl font-bold uppercase tracking-wider text-xs transition-all duration-200 flex items-center justify-center gap-2"
                                :class="form.type === 'in' ? 'bg-card shadow-md text-green-600 ring-2 ring-green-500/30' : 'text-muted-foreground'">
                                <div v-if="form.type === 'in'" class="absolute inset-0 rounded-xl bg-green-500/5"></div>
                                <TrendingUp class="w-4 h-4 relative z-10" />
                                <span class="relative z-10">Pemasukan</span>
                            </button>
                            <button type="button" @click="form.type = 'out'"
                                class="relative py-3.5 rounded-xl font-bold uppercase tracking-wider text-xs transition-all duration-200 flex items-center justify-center gap-2"
                                :class="form.type === 'out' ? 'bg-card shadow-md text-destructive ring-2 ring-red-500/30' : 'text-muted-foreground'">
                                <div v-if="form.type === 'out'" class="absolute inset-0 rounded-xl bg-red-500/5"></div>
                                <TrendingDown class="w-4 h-4 relative z-10" />
                                <span class="relative z-10">Pengeluaran</span>
                            </button>
                        </div>

                        <!-- Amount Input - Hero style -->
                        <div class="rounded-2xl p-5 text-center" :class="[
                            form.type === 'in' ? 'bg-green-500/5 border border-green-500/10' : 'bg-red-500/5 border border-red-500/10',
                            formErrors.amount ? 'ring-2 ring-destructive/30' : ''
                        ]">
                            <label class="block text-[10px] font-bold uppercase tracking-widest text-muted-foreground mb-3">
                                Nominal
                            </label>
                            <div class="relative max-w-[280px] mx-auto">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-lg font-bold" :class="form.type === 'in' ? 'text-green-600/50' : 'text-destructive/50'">Rp</span>
                                <input
                                    type="text"
                                    inputmode="numeric"
                                    :value="amountDisplay"
                                    @input="onAmountInput($event); clearError('amount')"
                                    placeholder="0"
                                    class="w-full pl-10 pr-4 py-3 text-2xl sm:text-3xl font-black text-center bg-transparent border-0 border-b-2 focus:ring-0 transition placeholder:text-muted-foreground/30"
                                    :class="form.type === 'in' ? 'text-green-600 border-green-500/30 focus:border-green-500' : 'text-destructive border-red-500/30 focus:border-red-500'"
                                />
                            </div>
                            <p v-if="formErrors.amount" class="mt-2 text-xs text-destructive">{{ formErrors.amount }}</p>
                        </div>

                        <!-- Wallet as card selector -->
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-muted-foreground mb-2 px-1">Dompet</label>
                            <div class="grid gap-2" :class="wallets.length <= 3 ? `grid-cols-${wallets.length}` : 'grid-cols-2'">
                                <button
                                    v-for="w in wallets"
                                    :key="w.id"
                                    type="button"
                                    @click="form.wallet_id = w.id.toString(); clearError('wallet_id')"
                                    class="relative p-3 rounded-xl border-2 text-left transition-all duration-150"
                                    :class="[
                                        form.wallet_id === w.id.toString()
                                            ? 'border-primary bg-primary/5 shadow-sm'
                                            : formErrors.wallet_id
                                                ? 'border-destructive/40 hover:border-destructive/60'
                                                : 'border-border hover:border-primary/30 hover:bg-muted/30'
                                    ]"
                                >
                                    <div class="flex items-center gap-2 mb-1">
                                        <Wallet class="w-4 h-4" :class="form.wallet_id === w.id.toString() ? 'text-primary' : 'text-muted-foreground'" />
                                        <span class="text-sm font-semibold truncate" :class="form.wallet_id === w.id.toString() ? 'text-primary' : 'text-foreground'">{{ w.name }}</span>
                                    </div>
                                    <p class="text-[10px] font-mono font-bold" :class="form.wallet_id === w.id.toString() ? 'text-primary/70' : 'text-muted-foreground'">
                                        {{ formatCurrency(w.balance) }}
                                    </p>
                                    <div v-if="form.wallet_id === w.id.toString()" class="absolute top-2 right-2 w-5 h-5 bg-primary rounded-full flex items-center justify-center">
                                        <svg class="w-3 h-3 text-primary-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                    </div>
                                </button>
                            </div>
                            <p v-if="formErrors.wallet_id" class="mt-2 text-sm text-destructive flex items-center gap-1.5 px-1">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                {{ formErrors.wallet_id }}
                            </p>
                        </div>

                        <!-- Date & Category row -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-muted-foreground mb-2 px-1">Tanggal</label>
                                <Input type="date" v-model="form.transaction_date" @change="clearError('transaction_date')"
                                    class="h-11" :class="formErrors.transaction_date ? 'border-destructive focus:ring-destructive' : ''" />
                                <p v-if="formErrors.transaction_date" class="mt-1.5 text-sm text-destructive px-1">{{ formErrors.transaction_date }}</p>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-muted-foreground mb-2 px-1">Kategori</label>
                                <select
                                    v-model="form.category"
                                    @change="clearError('category')"
                                    class="w-full h-11 rounded-md border bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-offset-2"
                                    :class="formErrors.category ? 'border-destructive focus:ring-destructive' : 'border-input focus:ring-ring'"
                                >
                                    <option value="" disabled>Pilih Kategori</option>
                                    <option v-for="cat in categories[form.type]" :key="cat" :value="cat">{{ cat }}</option>
                                </select>
                                <p v-if="formErrors.category" class="mt-1.5 text-sm text-destructive px-1">{{ formErrors.category }}</p>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-muted-foreground mb-2 px-1">Keterangan (Opsional)</label>
                            <Textarea v-model="form.description" rows="2" placeholder="Tulis rincian tambahan..." class="resize-none" />
                        </div>

                        <!-- Receipt Upload - compact -->
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-muted-foreground mb-2 px-1">Bukti (Opsional)</label>
                            <div
                                @dragover.prevent="isDraggingReceipt = true"
                                @dragleave.prevent="isDraggingReceipt = false"
                                @drop.prevent="handleReceiptDrop($event)"
                                @click="receiptInputRef?.click()"
                                class="relative border-2 border-dashed rounded-xl text-center cursor-pointer transition-all duration-200"
                                :class="[
                                    isDraggingReceipt ? 'border-primary bg-primary/5' : 'border-border hover:border-primary/40',
                                    receiptPreview ? 'p-3' : 'p-4'
                                ]"
                            >
                                <input ref="receiptInputRef" type="file" accept="image/*" class="hidden" @change="handleReceiptSelect($event)" />
                                <template v-if="!receiptPreview">
                                    <div class="flex items-center justify-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-muted flex items-center justify-center shrink-0">
                                            <Camera class="w-5 h-5 text-muted-foreground" />
                                        </div>
                                        <div class="text-left">
                                            <p class="text-sm font-medium text-foreground"><span class="text-primary">Pilih foto</span> atau seret ke sini</p>
                                            <p class="text-[11px] text-muted-foreground">PNG, JPG max 2MB</p>
                                        </div>
                                    </div>
                                </template>
                                <template v-else>
                                    <div class="flex items-center gap-3">
                                        <img :src="receiptPreview" class="w-16 h-16 rounded-lg object-cover shrink-0" />
                                        <div class="flex-1 text-left min-w-0">
                                            <p class="text-sm font-medium text-foreground truncate">{{ form.receipt?.name }}</p>
                                            <p class="text-xs text-muted-foreground">{{ formatReceiptSize(form.receipt?.size) }}</p>
                                        </div>
                                        <button type="button" @click.stop="clearReceipt" class="w-8 h-8 bg-destructive/10 text-destructive rounded-full flex items-center justify-center shrink-0 hover:bg-destructive/20 transition-colors">
                                            <X class="w-4 h-4" />
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Sticky footer -->
                    <div class="sticky bottom-0 bg-background border-t p-4 flex gap-3 shrink-0">
                        <Button variant="outline" type="button" @click="showModal = false" class="flex-1 h-12">
                            Batal
                        </Button>
                        <Button type="submit" :disabled="form.processing" class="flex-[2] h-12 text-base font-bold"
                            :class="form.type === 'in' ? 'bg-green-600 hover:bg-green-700' : ''">
                            <span v-if="form.processing">Menyimpan...</span>
                            <span v-else>Simpan Transaksi</span>
                        </Button>
                    </div>
                </form>
            </SheetContent>
        </Sheet>

        <!-- Detail Sheet -->
        <Sheet :open="showDetailModal" @update:open="(val) => { if (!val) showDetailModal = false; }">
            <SheetContent v-if="selectedFinance" side="bottom" class="sm:max-w-lg sm:mx-auto sm:rounded-t-2xl max-h-[88vh] flex flex-col p-0">
                <!-- Header with type indicator -->
                <div class="shrink-0 px-5 pt-5 pb-4 border-b">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center"
                            :class="selectedFinance.type === 'in' ? 'bg-green-500/10' : 'bg-destructive/10'">
                            <TrendingUp v-if="selectedFinance.type === 'in'" class="w-5 h-5 text-green-600" />
                            <TrendingDown v-else class="w-5 h-5 text-destructive" />
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-foreground">Detail Transaksi</h3>
                            <Badge :variant="selectedFinance.type === 'in' ? 'success' : 'destructive'" class="mt-0.5 text-[10px]">
                                {{ selectedFinance.type === 'in' ? 'Pemasukan' : 'Pengeluaran' }}
                            </Badge>
                        </div>
                    </div>
                </div>

                <!-- Scrollable content -->
                <div class="flex-1 overflow-y-auto">
                    <!-- Amount hero -->
                    <div class="px-5 py-5 text-center" :class="selectedFinance.type === 'in' ? 'bg-green-500/5' : 'bg-red-500/5'">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground mb-1">Jumlah</p>
                        <p class="text-3xl sm:text-4xl font-black" :class="selectedFinance.type === 'in' ? 'text-green-600' : 'text-destructive'">
                            {{ selectedFinance.type === 'in' ? '+' : '-' }} {{ formatCurrency(selectedFinance.amount) }}
                        </p>
                    </div>

                    <!-- Info rows -->
                    <div class="divide-y divide-border">
                        <div class="flex items-center justify-between px-5 py-3.5">
                            <span class="text-sm text-muted-foreground">Tanggal</span>
                            <span class="text-sm font-semibold text-foreground">{{ formatDateLong(selectedFinance.transaction_date) }}</span>
                        </div>
                        <div class="flex items-center justify-between px-5 py-3.5">
                            <span class="text-sm text-muted-foreground">Dompet</span>
                            <span class="text-sm font-semibold text-primary">{{ selectedFinance.wallet.name }}</span>
                        </div>
                        <div class="flex items-center justify-between px-5 py-3.5">
                            <span class="text-sm text-muted-foreground">Kategori</span>
                            <span class="text-sm font-semibold text-foreground">{{ selectedFinance.category }}</span>
                        </div>
                    </div>

                    <!-- Description -->
                    <div v-if="selectedFinance.description" class="px-5 py-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground mb-2">Keterangan</p>
                        <p class="text-sm text-foreground bg-muted/50 rounded-xl px-4 py-3 leading-relaxed">
                            {{ selectedFinance.description }}
                        </p>
                    </div>

                    <!-- Receipt image -->
                    <div v-if="selectedFinance.receipt_path" class="px-5 py-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground mb-2">Bukti Transaksi</p>
                        <div class="rounded-xl overflow-hidden border bg-muted/30">
                            <img :src="'/storage/' + selectedFinance.receipt_path" class="w-full max-h-60 sm:max-h-80 object-contain" />
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="shrink-0 border-t p-4">
                    <Button variant="outline" @click="showDetailModal = false" class="w-full h-11">Tutup</Button>
                </div>
            </SheetContent>
        </Sheet>
    </AuthenticatedLayout>
</template>
