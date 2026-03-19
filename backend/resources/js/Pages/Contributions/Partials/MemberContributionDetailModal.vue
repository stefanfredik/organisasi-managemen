<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import {
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Textarea } from '@/components/ui/textarea';
import axios from 'axios';
import {
    Upload, X, AlertTriangle, CheckCircle, Clock, Banknote, CreditCard,
    Coins, Loader2, CheckCheck, ShieldCheck, CircleAlert,
} from 'lucide-vue-next';

const props = defineProps({
    show: Boolean,
    type: Object,
});

const emit = defineEmits(['close', 'success']);

const page = usePage();
const user = computed(() => page.props.auth.user);
const member = computed(() => user.value.role === 'anggota' ? user.value.member : null);

const loading = ref(false);
const statusData = ref(null);
const selectedPeriods = ref([]);
const paymentMethod = ref('transfer');
const receiptFile = ref(null);

const form = useForm({
    member_ids: [],
    contribution_type_id: '',
    amount: 0,
    payment_date: new Date().toISOString().split('T')[0],
    periods: [],
    payment_method: 'transfer',
    notes: '',
    receipt: null,
});

const totalAmount = computed(() => {
    if (!props.type) return 0;
    return selectedPeriods.value.length * props.type.amount;
});

const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
};

const errorMessage = ref(null);

const fetchStatus = async () => {
    if (!props.type) return;
    loading.value = true;
    errorMessage.value = null;
    statusData.value = null;
    try {
        const url = route('contributions.my-status', props.type.id);
        const res = await axios.get(url);
        statusData.value = res.data;
        selectedPeriods.value = [];
    } catch (e) {
        console.error(e);
        errorMessage.value = e.response?.data?.message || 'Gagal memuat status pembayaran. Pastikan Anda terdaftar sebagai anggota aktif.';
    } finally {
        loading.value = false;
    }
};

watch(() => props.show, (val) => {
    if (val) {
        fetchStatus();
        form.reset();
        receiptFile.value = null;
        receiptPreview.value = null;
        paymentMethod.value = 'transfer';
    }
});

const togglePeriod = (periodKey) => {
    if (selectedPeriods.value.includes(periodKey)) {
        selectedPeriods.value = selectedPeriods.value.filter(k => k !== periodKey);
    } else {
        selectedPeriods.value.push(periodKey);
    }
};

const unpaidPeriods = computed(() => {
    if (!statusData.value?.periods) return [];
    return statusData.value.periods.filter(p => p.status === 'unpaid');
});

const selectAllUnpaid = () => {
    selectedPeriods.value = unpaidPeriods.value.map(p => p.period);
};

const deselectAll = () => {
    selectedPeriods.value = [];
};

const showPaymentForm = computed(() => {
    if (!statusData.value) return false;
    if (statusData.value.type === 'once') return statusData.value.status === 'unpaid';
    return selectedPeriods.value.length > 0;
});

const submit = () => {
    if (!member.value) {
        alert("Data anggota tidak ditemukan. Silakan hubungi admin.");
        return;
    }

    form.member_ids = [member.value.id];
    form.contribution_type_id = props.type.id;
    form.amount = Number(props.type.amount);
    form.periods = selectedPeriods.value;
    form.payment_method = paymentMethod.value;

    if (receiptFile.value) {
        form.receipt = receiptFile.value;
    }

    form.post(route('contributions.bulk-store'), {
        onSuccess: () => {
            emit('success');
            emit('close');
        },
    });
};

const receiptInputRef = ref(null);
const isDraggingReceipt = ref(false);
const receiptPreview = ref(null);

const handleReceiptSelect = (e) => {
    const file = e.target.files[0];
    if (file) setReceiptFile(file);
};

const handleReceiptDrop = (e) => {
    isDraggingReceipt.value = false;
    const file = e.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) setReceiptFile(file);
};

const setReceiptFile = (file) => {
    receiptFile.value = file;
    const reader = new FileReader();
    reader.onload = (e) => { receiptPreview.value = e.target.result; };
    reader.readAsDataURL(file);
};

const clearReceipt = () => {
    receiptFile.value = null;
    receiptPreview.value = null;
    if (receiptInputRef.value) receiptInputRef.value.value = '';
};
</script>

<template>
    <Dialog :open="show" @update:open="(val) => { if (!val) $emit('close'); }">
        <DialogContent class="max-w-lg max-h-[90vh] overflow-hidden flex flex-col p-0">
            <!-- Header -->
            <div class="px-5 pt-5 pb-4 border-b">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2.5 text-base">
                        <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                            <Coins class="w-4 h-4 text-primary" />
                        </div>
                        Pembayaran Iuran
                    </DialogTitle>
                    <DialogDescription class="mt-1 text-xs">Detail dan status pembayaran iuran Anda.</DialogDescription>
                </DialogHeader>
            </div>

            <div class="flex-1 overflow-y-auto">
                <!-- Loading -->
                <div v-if="loading" class="py-16 flex flex-col items-center gap-2 text-muted-foreground">
                    <Loader2 class="w-6 h-6 animate-spin" />
                    <p class="text-sm font-medium">Memuat data pembayaran...</p>
                </div>

                <!-- Error -->
                <div v-else-if="errorMessage" class="py-10 px-5 text-center">
                    <div class="w-12 h-12 rounded-full bg-destructive/10 flex items-center justify-center mx-auto mb-3">
                        <AlertTriangle class="w-6 h-6 text-destructive" />
                    </div>
                    <p class="text-sm font-semibold text-foreground mb-1">Terjadi Kesalahan</p>
                    <p class="text-xs text-muted-foreground mb-4">{{ errorMessage }}</p>
                    <Button variant="outline" size="sm" @click="fetchStatus">Coba Lagi</Button>
                </div>

                <!-- Content -->
                <div v-else-if="statusData" class="divide-y divide-border">

                    <!-- Type Info Card -->
                    <div class="px-5 py-4">
                        <div class="flex items-center justify-between gap-3 p-3.5 rounded-lg bg-muted/50 border">
                            <div>
                                <p class="text-[11px] text-muted-foreground font-medium uppercase tracking-wide">Jenis Iuran</p>
                                <p class="text-sm font-semibold text-foreground mt-0.5">{{ type?.name }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[11px] text-muted-foreground font-medium uppercase tracking-wide">Nominal</p>
                                <p class="text-base font-bold text-primary tabular-nums mt-0.5">{{ formatCurrency(type?.amount) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Periodic Section -->
                    <div v-if="statusData.type !== 'once'" class="px-5 py-4">
                        <!-- Progress + Actions -->
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-semibold text-foreground">Daftar Periode</h3>
                            <div class="flex gap-1">
                                <Button
                                    v-if="selectedPeriods.length < unpaidPeriods.length && unpaidPeriods.length > 0"
                                    variant="ghost"
                                    size="sm"
                                    class="h-7 text-xs px-2"
                                    @click="selectAllUnpaid"
                                >
                                    <CheckCheck class="w-3.5 h-3.5 mr-1" />
                                    Semua
                                </Button>
                                <Button
                                    v-if="selectedPeriods.length > 0"
                                    variant="ghost"
                                    size="sm"
                                    class="h-7 text-xs px-2 text-muted-foreground"
                                    @click="deselectAll"
                                >
                                    <X class="w-3.5 h-3.5 mr-1" />
                                    Batal
                                </Button>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div v-if="statusData.summary" class="mb-3">
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="text-muted-foreground">Progress</span>
                                <span class="font-semibold text-foreground">
                                    {{ statusData.summary.paid }} / {{ statusData.summary.total }}
                                    <span class="text-muted-foreground font-normal">({{ statusData.summary.percentage }}%)</span>
                                </span>
                            </div>
                            <div class="w-full bg-muted rounded-full h-2 overflow-hidden">
                                <div
                                    class="h-full rounded-full transition-all duration-500"
                                    :class="statusData.summary.percentage >= 100 ? 'bg-green-500' : 'bg-primary'"
                                    :style="{ width: statusData.summary.percentage + '%' }"
                                />
                            </div>
                        </div>

                        <!-- Period List -->
                        <div class="max-h-52 overflow-y-auto rounded-lg border bg-card">
                            <div class="divide-y divide-border">
                                <label
                                    v-for="p in statusData.periods"
                                    :key="p.period"
                                    class="flex items-center gap-2.5 px-3 py-2.5 transition-colors cursor-pointer"
                                    :class="[
                                        p.status === 'unpaid' && selectedPeriods.includes(p.period) ? 'bg-primary/5' : 'hover:bg-muted/50',
                                        p.status !== 'unpaid' ? 'cursor-default' : ''
                                    ]"
                                >
                                    <!-- Checkbox area -->
                                    <div class="shrink-0">
                                        <div
                                            v-if="p.status === 'unpaid'"
                                            class="w-4.5 h-4.5 rounded border-2 flex items-center justify-center transition-colors"
                                            :class="selectedPeriods.includes(p.period) ? 'border-primary bg-primary text-white' : 'border-muted-foreground/30'"
                                            @click.prevent="togglePeriod(p.period)"
                                        >
                                            <svg v-if="selectedPeriods.includes(p.period)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="w-2.5 h-2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                        </div>
                                        <div v-else-if="p.status === 'paid'" class="w-4.5 h-4.5 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                            <CheckCircle class="w-3 h-3 text-green-600 dark:text-green-400" />
                                        </div>
                                        <div v-else class="w-4.5 h-4.5 rounded-full bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center">
                                            <Clock class="w-3 h-3 text-yellow-600 dark:text-yellow-400" />
                                        </div>
                                    </div>

                                    <!-- Period info -->
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-foreground">{{ p.label }}</p>
                                        <p v-if="p.due_date" class="text-[11px] text-muted-foreground">Jatuh tempo: {{ p.due_date }}</p>
                                    </div>

                                    <!-- Status badge -->
                                    <Badge
                                        variant="secondary"
                                        class="text-[10px] shrink-0 border-0"
                                        :class="[
                                            p.status === 'paid' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' :
                                            p.status === 'pending' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' :
                                            'bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400'
                                        ]"
                                    >
                                        {{ p.status === 'paid' ? 'Lunas' : p.status === 'pending' ? 'Menunggu' : 'Belum Bayar' }}
                                    </Badge>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- One Time Section -->
                    <div v-else class="px-5 py-4">
                        <div v-if="statusData.status === 'unpaid'" class="p-3.5 bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800/50 rounded-lg flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center shrink-0">
                                <CircleAlert class="w-4.5 h-4.5 text-red-600 dark:text-red-400" />
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-red-700 dark:text-red-400">Belum Dibayar</p>
                                <p class="text-xs text-red-600/70 dark:text-red-400/70">Silakan lakukan pembayaran di bawah ini.</p>
                            </div>
                        </div>
                        <div v-else-if="statusData.status === 'pending'" class="p-3.5 bg-yellow-50 dark:bg-yellow-900/10 border border-yellow-200 dark:border-yellow-800/50 rounded-lg flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center shrink-0">
                                <Clock class="w-4.5 h-4.5 text-yellow-600 dark:text-yellow-400" />
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-yellow-700 dark:text-yellow-400">Menunggu Verifikasi</p>
                                <p class="text-xs text-yellow-600/70 dark:text-yellow-400/70">Pembayaran Anda sedang diproses oleh admin.</p>
                            </div>
                        </div>
                        <div v-else class="p-3.5 bg-green-50 dark:bg-green-900/10 border border-green-200 dark:border-green-800/50 rounded-lg flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center shrink-0">
                                <ShieldCheck class="w-4.5 h-4.5 text-green-600 dark:text-green-400" />
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-green-700 dark:text-green-400">Lunas</p>
                                <p class="text-xs text-green-600/70 dark:text-green-400/70">Iuran ini sudah terbayar lunas.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Form -->
                    <div v-if="showPaymentForm" class="px-5 py-4 space-y-4">
                        <h3 class="text-sm font-semibold text-foreground flex items-center gap-2">
                            <CreditCard class="w-4 h-4 text-primary" />
                            Konfirmasi Pembayaran
                        </h3>

                        <!-- Total Summary -->
                        <div class="p-3.5 rounded-lg bg-muted/50 border">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs text-muted-foreground">{{ statusData.type === 'once' ? 'Pembayaran' : `${selectedPeriods.length} Periode` }}</p>
                                    <p class="text-xs text-muted-foreground mt-0.5">@ {{ formatCurrency(type?.amount) }}</p>
                                </div>
                                <p class="text-xl font-bold text-primary tabular-nums">
                                    {{ formatCurrency(statusData.type === 'once' ? type.amount : totalAmount) }}
                                </p>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div>
                            <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Metode Pembayaran</Label>
                            <div class="grid grid-cols-2 gap-2 mt-1.5">
                                <label class="cursor-pointer">
                                    <input type="radio" v-model="paymentMethod" value="transfer" class="peer sr-only" />
                                    <div class="rounded-lg border-2 p-2.5 peer-checked:border-primary peer-checked:bg-primary/5 text-muted-foreground peer-checked:text-primary transition-all flex items-center gap-2.5 hover:bg-muted/50">
                                        <div class="w-8 h-8 rounded-lg bg-muted flex items-center justify-center shrink-0">
                                            <CreditCard class="w-4 h-4" />
                                        </div>
                                        <div>
                                            <span class="text-sm font-semibold block">Transfer</span>
                                            <span class="text-[11px] text-muted-foreground">Bank / e-wallet</span>
                                        </div>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" v-model="paymentMethod" value="cash" class="peer sr-only" />
                                    <div class="rounded-lg border-2 p-2.5 peer-checked:border-primary peer-checked:bg-primary/5 text-muted-foreground peer-checked:text-primary transition-all flex items-center gap-2.5 hover:bg-muted/50">
                                        <div class="w-8 h-8 rounded-lg bg-muted flex items-center justify-center shrink-0">
                                            <Banknote class="w-4 h-4" />
                                        </div>
                                        <div>
                                            <span class="text-sm font-semibold block">Tunai</span>
                                            <span class="text-[11px] text-muted-foreground">Bayar langsung</span>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Receipt Upload -->
                        <div v-if="paymentMethod === 'transfer'">
                            <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Bukti Transfer</Label>
                            <div
                                @dragover.prevent="isDraggingReceipt = true"
                                @dragleave.prevent="isDraggingReceipt = false"
                                @drop.prevent="handleReceiptDrop($event)"
                                @click="receiptInputRef?.click()"
                                class="mt-1.5 relative border-2 border-dashed rounded-lg p-4 text-center cursor-pointer transition-all duration-200"
                                :class="isDraggingReceipt ? 'border-primary bg-primary/5' : 'border-border hover:border-primary/40 hover:bg-muted/30'"
                            >
                                <input ref="receiptInputRef" type="file" accept="image/*" class="hidden" @change="handleReceiptSelect($event)" />
                                <template v-if="!receiptPreview">
                                    <div class="w-9 h-9 rounded-full bg-muted flex items-center justify-center mx-auto mb-1.5">
                                        <Upload class="h-4 w-4 text-muted-foreground" />
                                    </div>
                                    <p class="text-xs font-medium text-foreground">Upload Bukti Transfer</p>
                                    <p class="text-[11px] text-muted-foreground mt-0.5">Drag & drop atau klik. PNG, JPG max 2MB</p>
                                </template>
                                <template v-else>
                                    <div class="relative inline-block">
                                        <img :src="receiptPreview" class="max-h-28 rounded-lg mx-auto shadow-sm" />
                                        <button type="button" @click.stop="clearReceipt" class="absolute -top-2 -right-2 w-5 h-5 bg-destructive text-white rounded-full flex items-center justify-center hover:bg-destructive/80 shadow-sm">
                                            <X class="w-3 h-3" />
                                        </button>
                                    </div>
                                    <p class="text-[11px] text-muted-foreground mt-1.5">{{ receiptFile?.name }}</p>
                                </template>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Catatan <span class="normal-case font-normal">(Opsional)</span></Label>
                            <Textarea v-model="form.notes" rows="2" class="mt-1.5" placeholder="Keterangan tambahan..." />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div v-if="statusData && showPaymentForm" class="px-5 py-3 border-t bg-muted/30 flex items-center justify-end gap-2">
                <Button variant="outline" size="sm" type="button" @click="$emit('close')">Batal</Button>
                <Button
                    size="sm"
                    :disabled="form.processing || (paymentMethod === 'transfer' && !receiptFile)"
                    @click="submit"
                >
                    <Loader2 v-if="form.processing" class="w-4 h-4 mr-1.5 animate-spin" />
                    {{ form.processing ? 'Memproses...' : 'Kirim Pembayaran' }}
                </Button>
            </div>
            <div v-else-if="statusData && !showPaymentForm" class="px-5 py-3 border-t bg-muted/30 flex items-center justify-end">
                <Button variant="outline" size="sm" @click="$emit('close')">Tutup</Button>
            </div>
        </DialogContent>
    </Dialog>
</template>
