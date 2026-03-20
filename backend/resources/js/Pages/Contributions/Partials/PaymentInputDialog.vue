<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import {
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import CurrencyInput from '@/Components/CurrencyInput.vue';
import axios from 'axios';
import {
    Upload, X, CircleDollarSign, CheckCircle, Banknote, CreditCard,
    Coins, CalendarDays, Loader2, CheckCheck,
} from 'lucide-vue-next';

const props = defineProps({
    open: Boolean,
    types: Array,
    wallets: Array,
    members: Array,
    initialTypeId: [String, Number],
    formatCurrency: Function,
    buildPeriodOptions: Function,
});

const emit = defineEmits(['update:open', 'success']);

const page = usePage();
const userRole = computed(() => page.props.auth.user.role);
const userPosition = computed(() => page.props.auth.user.position);

const manualForm = useForm({
    member_id: '',
    contribution_type_id: '',
    amount: 0,
    payment_date: new Date().toISOString().split('T')[0],
    payment_period: '',
    payment_periods: [],
    payment_method: 'cash',
    wallet_id: '',
    notes: '',
    receipt: null,
});

const selectedType = computed(() => {
    return props.types?.find((t) => String(t.id) === String(manualForm.contribution_type_id)) || null;
});

const selectedMember = computed(() => {
    return props.members?.find((m) => m.id == manualForm.member_id) || null;
});

const filteredMembers = ref([]);
const paidPeriods = ref([]);
const statusLoading = ref(false);

const loadUnpaidMembers = async () => {
    filteredMembers.value = [];
    if (!selectedType.value) return;
    try {
        const res = await axios.get(route('contributions.unpaid-members'), {
            params: {
                contribution_type_id: selectedType.value.id,
                payment_period: manualForm.payment_period || null,
            },
        });
        filteredMembers.value = res.data || [];
    } catch (e) {
        filteredMembers.value = props.members || [];
    }
};

const fetchMemberStatus = async () => {
    paidPeriods.value = [];
    const isMember = userPosition.value === 'anggota';
    if ((!manualForm.member_id && !isMember) || !selectedType.value) return;
    statusLoading.value = true;
    try {
        const url = route('contributions.my-status', selectedType.value.id);
        const params = manualForm.member_id ? { member_id: manualForm.member_id } : {};
        const res = await axios.get(url, { params });
        const data = res.data;

        if (data.type === 'once') {
            if (data.status === 'paid') {
                paidPeriods.value = ['once'];
                manualForm.payment_periods = [];
            }
        } else if (data?.periods && Array.isArray(data.periods)) {
            paidPeriods.value = data.periods.filter(p => p.status === 'paid').map(p => p.period);
            manualForm.payment_periods = manualForm.payment_periods.filter(pp => !paidPeriods.value.includes(pp));
        }
    } finally {
        statusLoading.value = false;
    }
};

watch(
    () => manualForm.contribution_type_id,
    () => {
        if (selectedType.value) {
            manualForm.amount = Number(selectedType.value.amount || 0);
            if (selectedType.value.wallet_id) {
                manualForm.wallet_id = selectedType.value.wallet_id;
            }
            manualForm.payment_period = '';
            manualForm.payment_periods = [];
            if (userPosition.value === 'anggota') {
                manualForm.payment_method = 'transfer';
            }
            if (userPosition.value !== 'anggota') {
                loadUnpaidMembers();
            }
            fetchMemberStatus();
        } else {
            filteredMembers.value = props.members || [];
        }
    },
);

watch(
    () => manualForm.member_id,
    () => {
        manualForm.payment_periods = [];
        fetchMemberStatus();
    },
);

const memberOptions = computed(() => {
    const base = filteredMembers.value.length ? filteredMembers.value : (props.members || []);
    return base.map(m => ({
        value: m.id,
        label: m.full_name,
        subLabel: m.member_code,
    }));
});

watch(
    () => manualForm.payment_periods,
    () => {
        if (!selectedType.value) return;
        if (selectedType.value.period !== 'once') {
            manualForm.amount = Number(selectedType.value.amount || 0) * (manualForm.payment_periods?.length || 0);
        } else {
            manualForm.amount = Number(selectedType.value.amount || 0);
        }
    },
    { deep: true },
);

const manualPeriodOptions = computed(() => props.buildPeriodOptions(selectedType.value));

const selectedCount = computed(() => manualForm.payment_periods.length);
const unpaidCount = computed(() => {
    return manualPeriodOptions.value.filter(o => !paidPeriods.value.includes(o.value)).length;
});

const selectAllUnpaid = () => {
    const unpaid = manualPeriodOptions.value
        .filter(o => !paidPeriods.value.includes(o.value))
        .map(o => o.value);
    manualForm.payment_periods = unpaid;
};

const deselectAll = () => {
    manualForm.payment_periods = [];
};

// Receipt handling
const manualReceiptInputRef = ref(null);
const isDraggingManualReceipt = ref(false);
const manualReceiptPreview = ref(null);

const handleManualReceiptSelect = (e) => {
    const file = e.target.files[0];
    if (file) setManualReceipt(file);
};

const handleManualReceiptDrop = (e) => {
    isDraggingManualReceipt.value = false;
    const file = e.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) setManualReceipt(file);
};

const setManualReceipt = (file) => {
    manualForm.receipt = file;
    const reader = new FileReader();
    reader.onload = (e) => { manualReceiptPreview.value = e.target.result; };
    reader.readAsDataURL(file);
};

const clearManualReceipt = () => {
    manualForm.receipt = null;
    manualReceiptPreview.value = null;
    if (manualReceiptInputRef.value) manualReceiptInputRef.value.value = '';
};

const submitManual = () => {
    const isBulk = selectedType.value && selectedType.value.period !== 'once' && manualForm.payment_periods.length > 0;
    if (!isBulk && !manualForm.payment_period) {
        manualForm.payment_period = manualForm.payment_periods[0] || '';
    }
    if (isBulk && !manualForm.member_id && userPosition.value !== 'anggota') {
        return;
    }

    if (userPosition.value === 'anggota' && !manualForm.receipt) {
        manualForm.setError('receipt', 'Bukti transfer wajib diunggah untuk verifikasi.');
        return;
    }

    if (isBulk) {
        manualForm.transform((data) => ({
            member_ids: data.member_id ? [data.member_id] : [],
            contribution_type_id: data.contribution_type_id,
            amount: data.amount,
            payment_date: data.payment_date,
            periods: data.payment_periods,
            payment_method: data.payment_method,
            wallet_id: data.wallet_id,
            notes: data.notes,
            receipt: data.receipt,
        }));
    } else {
        manualForm.transform((data) => ({
            member_id: data.member_id,
            contribution_type_id: data.contribution_type_id,
            amount: data.amount,
            payment_date: data.payment_date,
            payment_period: data.payment_period || null,
            payment_method: data.payment_method,
            wallet_id: data.wallet_id,
            notes: data.notes,
            receipt: data.receipt,
        }));
    }
    manualForm.post(isBulk ? route('contributions.bulk-store') : route('contributions.store'), {
        preserveScroll: true,
        onSuccess: () => {
            emit('update:open', false);
            emit('success');
            manualForm.reset();
            manualForm.payment_method = 'cash';
            manualForm.payment_date = new Date().toISOString().split('T')[0];
            manualForm.payment_periods = [];
            manualReceiptPreview.value = null;
        },
    });
};

// Set initial type when dialog opens
watch(() => props.open, (val) => {
    if (val && props.initialTypeId) {
        manualForm.contribution_type_id = props.initialTypeId.toString();
    }
    if (!val) {
        manualForm.reset();
        manualForm.payment_method = 'cash';
        manualForm.payment_date = new Date().toISOString().split('T')[0];
        manualForm.payment_periods = [];
        manualReceiptPreview.value = null;
    }
});
</script>

<template>
    <Dialog :open="open" @update:open="(val) => $emit('update:open', val)">
        <DialogContent class="max-w-4xl max-h-[90vh] overflow-hidden flex flex-col p-0">
            <!-- Header -->
            <div class="px-5 pt-5 pb-4 border-b">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2.5 text-base">
                        <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                            <CircleDollarSign class="w-4 h-4 text-primary" />
                        </div>
                        {{ userPosition === 'anggota' ? 'Upload Bukti Pembayaran' : 'Input Pembayaran' }}
                    </DialogTitle>
                    <DialogDescription class="mt-1 text-xs">
                        {{ userPosition === 'anggota' ? 'Unggah bukti transfer untuk pembayaran iuran Anda' : 'Catat transaksi pembayaran iuran anggota' }}
                    </DialogDescription>
                </DialogHeader>
            </div>

            <form @submit.prevent="submitManual" class="flex flex-col flex-1 overflow-hidden">
                <div class="flex-1 overflow-y-auto">
                    <div class="grid grid-cols-1 lg:grid-cols-12 divide-y lg:divide-y-0 lg:divide-x divide-border">

                        <!-- Left: Form Input -->
                        <div class="lg:col-span-7 p-5 space-y-4">
                            <!-- Type Selection -->
                            <div>
                                <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Jenis Iuran</Label>
                                <Select v-model="manualForm.contribution_type_id">
                                    <SelectTrigger class="mt-1.5 w-full">
                                        <SelectValue placeholder="Pilih jenis iuran..." />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="t in types" :key="t.id" :value="t.id.toString()">{{ t.name }}</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="manualForm.errors.contribution_type_id" class="mt-1 text-xs text-destructive">{{ manualForm.errors.contribution_type_id }}</p>
                            </div>

                            <!-- Type info card -->
                            <div v-if="selectedType" class="flex items-center gap-3 p-3 rounded-lg bg-muted/50 border">
                                <Coins class="w-5 h-5 text-primary shrink-0" />
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-foreground truncate">{{ selectedType.name }}</p>
                                    <p class="text-xs text-muted-foreground">{{ selectedType.period === 'once' ? 'Sekali Bayar' : selectedType.period === 'monthly' ? 'Bulanan' : selectedType.period === 'weekly' ? 'Mingguan' : 'Tahunan' }}</p>
                                </div>
                                <p class="text-sm font-bold text-primary tabular-nums shrink-0">{{ formatCurrency(selectedType.amount) }}</p>
                            </div>

                            <!-- Member Selection (admin only) -->
                            <div v-if="userPosition !== 'anggota'">
                                <SearchableSelect
                                    label="Anggota"
                                    v-model="manualForm.member_id"
                                    :options="memberOptions"
                                    placeholder="Pilih anggota..."
                                    search-placeholder="Cari nama atau kode..."
                                    :error="manualForm.errors.member_id"
                                />
                            </div>

                            <div class="border-t" />

                            <!-- Nominal -->
                            <div>
                                <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Nominal</Label>
                                <div class="relative mt-1.5">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-base text-muted-foreground font-semibold">Rp</span>
                                    <CurrencyInput
                                        v-model="manualForm.amount"
                                        class="block w-full rounded-lg border pl-10 pr-4 py-2.5 text-xl font-bold text-foreground focus:ring-primary focus:border-primary tabular-nums"
                                    />
                                </div>
                                <p v-if="manualForm.errors.amount" class="mt-1 text-xs text-destructive">{{ manualForm.errors.amount }}</p>
                            </div>

                            <!-- Date -->
                            <div>
                                <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Tanggal Bayar</Label>
                                <Input
                                    type="date"
                                    v-model="manualForm.payment_date"
                                    class="mt-1.5 block w-full"
                                />
                                <p v-if="manualForm.errors.payment_date" class="mt-1 text-xs text-destructive">{{ manualForm.errors.payment_date }}</p>
                            </div>

                            <!-- Payment Method -->
                            <div>
                                <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Metode Pembayaran</Label>
                                <div v-if="userPosition !== 'anggota'" class="grid grid-cols-2 gap-2 mt-1.5">
                                    <label class="cursor-pointer">
                                        <input type="radio" v-model="manualForm.payment_method" value="cash" class="peer sr-only" />
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
                                        <input type="radio" v-model="manualForm.payment_method" value="transfer" class="peer sr-only" />
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
                                <div v-else class="mt-1.5 flex items-center gap-2.5 p-3 rounded-lg bg-muted/50 border">
                                    <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary shrink-0">
                                        <CreditCard class="w-4 h-4" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-primary">Transfer Bank</p>
                                        <p class="text-[11px] text-muted-foreground">Metode pembayaran otomatis</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Wallet (admin only, when type has no wallet) -->
                            <div v-if="!selectedType?.wallet_id && userPosition !== 'anggota'">
                                <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Dompet Tujuan</Label>
                                <Select v-model="manualForm.wallet_id">
                                    <SelectTrigger class="mt-1.5 w-full">
                                        <SelectValue placeholder="Pilih dompet..." />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="w in wallets" :key="w.id" :value="w.id.toString()">{{ w.name }}</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="manualForm.errors.wallet_id" class="mt-1 text-xs text-destructive">{{ manualForm.errors.wallet_id }}</p>
                            </div>

                            <!-- Receipt Upload -->
                            <div>
                                <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">
                                    {{ userPosition === 'anggota' ? 'Bukti Transfer' : 'Bukti Transfer (Opsional)' }}
                                </Label>
                                <div
                                    @dragover.prevent="isDraggingManualReceipt = true"
                                    @dragleave.prevent="isDraggingManualReceipt = false"
                                    @drop.prevent="handleManualReceiptDrop($event)"
                                    @click="manualReceiptInputRef?.click()"
                                    class="mt-1.5 relative border-2 border-dashed rounded-lg p-4 text-center cursor-pointer transition-all duration-200"
                                    :class="isDraggingManualReceipt ? 'border-primary bg-primary/5' : 'border-border hover:border-primary/40 hover:bg-muted/30'"
                                >
                                    <input ref="manualReceiptInputRef" type="file" accept="image/*" class="hidden" @change="handleManualReceiptSelect($event)" />
                                    <template v-if="!manualReceiptPreview">
                                        <div class="w-9 h-9 rounded-full bg-muted flex items-center justify-center mx-auto mb-1.5">
                                            <Upload class="h-4 w-4 text-muted-foreground" />
                                        </div>
                                        <p class="text-xs font-medium text-foreground">Upload Bukti Transfer</p>
                                        <p class="text-[11px] text-muted-foreground mt-0.5">Drag & drop atau klik. PNG, JPG max 2MB</p>
                                    </template>
                                    <template v-else>
                                        <div class="relative inline-block">
                                            <img :src="manualReceiptPreview" class="max-h-28 rounded-lg mx-auto shadow-sm" />
                                            <button type="button" @click.stop="clearManualReceipt" class="absolute -top-2 -right-2 w-5 h-5 bg-destructive text-white rounded-full flex items-center justify-center hover:bg-destructive/80 shadow-sm">
                                                <X class="w-3 h-3" />
                                            </button>
                                        </div>
                                        <p class="text-[11px] text-muted-foreground mt-1.5">{{ manualForm.receipt?.name }}</p>
                                    </template>
                                </div>
                                <p v-if="manualForm.errors.receipt" class="mt-1 text-xs text-destructive">{{ manualForm.errors.receipt }}</p>
                            </div>

                            <!-- Notes -->
                            <div>
                                <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Catatan <span class="normal-case font-normal">(Opsional)</span></Label>
                                <Textarea
                                    v-model="manualForm.notes"
                                    rows="2"
                                    class="mt-1.5"
                                    placeholder="Keterangan tambahan..."
                                />
                            </div>
                        </div>

                        <!-- Right: Period Selection -->
                        <div class="lg:col-span-5 flex flex-col bg-muted/10">
                            <!-- Period Header -->
                            <div class="px-4 py-3 border-b">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-sm font-semibold text-foreground flex items-center gap-1.5">
                                            <CalendarDays class="w-4 h-4 text-primary" />
                                            Pilih Periode
                                        </h3>
                                        <p v-if="selectedType && manualPeriodOptions.length" class="text-xs text-muted-foreground mt-0.5">
                                            {{ selectedCount }} dari {{ unpaidCount }} dipilih
                                        </p>
                                    </div>
                                    <div v-if="manualPeriodOptions.length" class="flex gap-1">
                                        <Button
                                            v-if="selectedCount < unpaidCount"
                                            variant="ghost"
                                            size="sm"
                                            class="h-7 text-xs px-2"
                                            type="button"
                                            @click="selectAllUnpaid"
                                        >
                                            <CheckCheck class="w-3.5 h-3.5 mr-1" />
                                            Semua
                                        </Button>
                                        <Button
                                            v-if="selectedCount > 0"
                                            variant="ghost"
                                            size="sm"
                                            class="h-7 text-xs px-2 text-muted-foreground"
                                            type="button"
                                            @click="deselectAll"
                                        >
                                            <X class="w-3.5 h-3.5 mr-1" />
                                            Batal
                                        </Button>
                                    </div>
                                </div>
                            </div>

                            <!-- Period List -->
                            <div class="flex-1 overflow-y-auto p-3 min-h-[200px] max-h-[400px]">
                                <div v-if="statusLoading" class="flex flex-col items-center justify-center h-full py-8">
                                    <Loader2 class="w-5 h-5 animate-spin text-muted-foreground mb-2" />
                                    <p class="text-xs text-muted-foreground">Memuat status...</p>
                                </div>
                                <div v-else-if="manualPeriodOptions.length" class="space-y-1">
                                    <label
                                        v-for="opt in manualPeriodOptions"
                                        :key="opt.value"
                                        class="flex items-center justify-between px-3 py-2 rounded-lg border bg-card cursor-pointer transition-all"
                                        :class="[
                                            paidPeriods.includes(opt.value)
                                                ? 'opacity-50 cursor-not-allowed border-transparent bg-muted/60'
                                                : manualForm.payment_periods.includes(opt.value)
                                                    ? 'border-primary bg-primary/5 shadow-sm'
                                                    : 'hover:border-primary/30'
                                        ]"
                                    >
                                        <div class="flex items-center gap-2.5">
                                            <div
                                                class="w-4.5 h-4.5 rounded border-2 flex items-center justify-center shrink-0 transition-colors"
                                                :class="[
                                                    paidPeriods.includes(opt.value)
                                                        ? 'border-green-400 bg-green-100 dark:bg-green-900/30'
                                                        : manualForm.payment_periods.includes(opt.value)
                                                            ? 'border-primary bg-primary text-white'
                                                            : 'border-muted-foreground/30'
                                                ]"
                                            >
                                                <CheckCircle v-if="paidPeriods.includes(opt.value)" class="w-3 h-3 text-green-600 dark:text-green-400" />
                                                <svg v-else-if="manualForm.payment_periods.includes(opt.value)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="w-2.5 h-2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                            </div>
                                            <input
                                                v-if="!paidPeriods.includes(opt.value)"
                                                type="checkbox"
                                                class="sr-only"
                                                :value="opt.value"
                                                v-model="manualForm.payment_periods"
                                            />
                                            <span class="text-sm font-medium text-foreground">{{ opt.label }}</span>
                                        </div>
                                        <Badge v-if="paidPeriods.includes(opt.value)" variant="secondary" class="text-[10px] bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 border-0">
                                            Lunas
                                        </Badge>
                                    </label>
                                </div>
                                <div v-else class="flex flex-col items-center justify-center h-full py-12 text-center px-4">
                                    <div class="w-10 h-10 rounded-full bg-muted flex items-center justify-center mb-2">
                                        <CalendarDays class="w-5 h-5 text-muted-foreground" />
                                    </div>
                                    <p class="text-sm font-medium text-muted-foreground">Belum ada periode</p>
                                    <p class="text-xs text-muted-foreground mt-1">Pilih jenis iuran terlebih dahulu.</p>
                                </div>
                            </div>
                            <p v-if="manualForm.errors.payment_period" class="px-4 pb-2 text-xs text-destructive">{{ manualForm.errors.payment_period }}</p>
                            <p v-if="manualForm.errors.periods" class="px-4 pb-2 text-xs text-destructive">{{ manualForm.errors.periods }}</p>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-5 py-3 border-t bg-muted/30 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] text-muted-foreground uppercase tracking-wide font-medium">Total Pembayaran</p>
                        <div class="flex items-baseline gap-2">
                            <p class="text-xl font-bold text-foreground tabular-nums">{{ formatCurrency(manualForm.amount) }}</p>
                            <p v-if="selectedCount > 0" class="text-xs text-muted-foreground">
                                ({{ selectedCount }} periode)
                            </p>
                        </div>
                        <p v-if="selectedMember" class="text-xs text-muted-foreground">
                            {{ selectedMember?.full_name }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <div v-if="selectedType?.period === 'once' && paidPeriods.includes('once')" class="flex items-center gap-1 text-xs text-green-600 font-semibold mr-2 bg-green-50 dark:bg-green-900/20 px-2.5 py-1 rounded-full">
                            <CheckCircle class="w-3 h-3" />
                            Sudah Lunas
                        </div>
                        <Button variant="outline" size="sm" type="button" @click="$emit('update:open', false)">Batal</Button>
                        <Button size="sm" type="submit" :disabled="manualForm.processing || (selectedType?.period === 'once' && paidPeriods.includes('once'))">
                            <Loader2 v-if="manualForm.processing" class="w-4 h-4 mr-1.5 animate-spin" />
                            {{ manualForm.processing ? 'Memproses...' : 'Simpan Pembayaran' }}
                        </Button>
                    </div>
                </div>
            </form>
        </DialogContent>
    </Dialog>
</template>
