<script setup>
import { ref, computed, watch, nextTick } from 'vue';
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
const member = computed(() => user.value.position === 'anggota' ? user.value.member : null);

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

const paymentFormRef = ref(null);

const togglePeriod = (periodKey) => {
    if (selectedPeriods.value.includes(periodKey)) {
        selectedPeriods.value = selectedPeriods.value.filter(k => k !== periodKey);
    } else {
        selectedPeriods.value.push(periodKey);
        // Scroll to payment form when first period selected
        if (selectedPeriods.value.length === 1) {
            nextTick(() => {
                paymentFormRef.value?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            });
        }
    }
};

const unpaidPeriods = computed(() => {
    if (!statusData.value?.periods) return [];
    return statusData.value.periods.filter(p => p.status === 'unpaid');
});

const selectAllUnpaid = () => {
    selectedPeriods.value = unpaidPeriods.value.map(p => p.period);
    nextTick(() => {
        paymentFormRef.value?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });
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
        <DialogContent class="max-w-lg max-h-[90vh] sm:max-h-[85vh] overflow-hidden flex flex-col p-0">
            <!-- Header -->
            <div class="px-4 sm:px-5 pt-4 sm:pt-5 pb-3 sm:pb-4 border-b">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2 sm:gap-2.5 text-sm sm:text-base">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                            <Coins class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-primary" />
                        </div>
                        Pembayaran Iuran
                    </DialogTitle>
                    <DialogDescription class="mt-0.5 sm:mt-1 text-[11px] sm:text-xs">{{ type?.name }} &mdash; {{ formatCurrency(type?.amount) }} / periode</DialogDescription>
                </DialogHeader>
            </div>

            <div class="flex-1 overflow-y-auto overscroll-contain">
                <!-- Loading -->
                <div v-if="loading" class="py-16 flex flex-col items-center gap-2 text-muted-foreground">
                    <Loader2 class="w-6 h-6 animate-spin" />
                    <p class="text-sm font-medium">Memuat data pembayaran...</p>
                </div>

                <!-- Error -->
                <div v-else-if="errorMessage" class="py-10 px-4 sm:px-5 text-center">
                    <div class="w-12 h-12 rounded-full bg-destructive/10 flex items-center justify-center mx-auto mb-3">
                        <AlertTriangle class="w-6 h-6 text-destructive" />
                    </div>
                    <p class="text-sm font-semibold text-foreground mb-1">Terjadi Kesalahan</p>
                    <p class="text-xs text-muted-foreground mb-4">{{ errorMessage }}</p>
                    <Button variant="outline" size="sm" @click="fetchStatus">Coba Lagi</Button>
                </div>

                <!-- Content -->
                <div v-else-if="statusData">

                    <!-- Periodic Section -->
                    <div v-if="statusData.type !== 'once'" class="px-4 sm:px-5 py-3 sm:py-4">
                        <!-- Progress Bar -->
                        <div v-if="statusData.summary" class="mb-3">
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="text-muted-foreground">Progress Pembayaran</span>
                                <span class="font-semibold text-foreground">
                                    {{ statusData.summary.paid }} / {{ statusData.summary.total }}
                                    <span class="text-muted-foreground font-normal">({{ statusData.summary.percentage }}%)</span>
                                </span>
                            </div>
                            <div class="w-full bg-muted rounded-full h-1.5 sm:h-2 overflow-hidden">
                                <div
                                    class="h-full rounded-full transition-all duration-500"
                                    :class="statusData.summary.percentage >= 100 ? 'bg-green-500' : 'bg-primary'"
                                    :style="{ width: statusData.summary.percentage + '%' }"
                                />
                            </div>
                        </div>

                        <!-- Select actions -->
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs font-medium text-muted-foreground">Pilih periode yang ingin dibayar</p>
                            <div class="flex gap-1">
                                <Button
                                    v-if="selectedPeriods.length < unpaidPeriods.length && unpaidPeriods.length > 0"
                                    variant="outline"
                                    size="sm"
                                    class="h-6 text-[11px] px-2 gap-1"
                                    @click="selectAllUnpaid"
                                >
                                    <CheckCheck class="w-3 h-3" />
                                    Pilih Semua
                                </Button>
                                <Button
                                    v-if="selectedPeriods.length > 0"
                                    variant="ghost"
                                    size="sm"
                                    class="h-6 text-[11px] px-2 gap-1 text-muted-foreground"
                                    @click="deselectAll"
                                >
                                    <X class="w-3 h-3" />
                                    Batal
                                </Button>
                            </div>
                        </div>

                        <!-- Period List -->
                        <div class="max-h-40 sm:max-h-52 overflow-y-auto rounded-lg border bg-card">
                            <div class="divide-y divide-border">
                                <div
                                    v-for="p in statusData.periods"
                                    :key="p.period"
                                    @click="p.status === 'unpaid' && togglePeriod(p.period)"
                                    class="flex items-center gap-2.5 px-3 py-2 sm:py-2.5 transition-colors"
                                    :class="[
                                        p.status === 'unpaid' ? 'cursor-pointer active:bg-muted/70' : 'cursor-default opacity-75',
                                        p.status === 'unpaid' && selectedPeriods.includes(p.period) ? 'bg-primary/5' : 'hover:bg-muted/50',
                                    ]"
                                >
                                    <!-- Checkbox / Status icon -->
                                    <div class="shrink-0">
                                        <div
                                            v-if="p.status === 'unpaid'"
                                            class="w-5 h-5 rounded border-2 flex items-center justify-center transition-colors"
                                            :class="selectedPeriods.includes(p.period) ? 'border-primary bg-primary text-white' : 'border-muted-foreground/30'"
                                        >
                                            <svg v-if="selectedPeriods.includes(p.period)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3"><polyline points="20 6 9 17 4 12"/></svg>
                                        </div>
                                        <div v-else-if="p.status === 'paid'" class="w-5 h-5 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                            <CheckCircle class="w-3 h-3 text-green-600 dark:text-green-400" />
                                        </div>
                                        <div v-else class="w-5 h-5 rounded-full bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center">
                                            <Clock class="w-3 h-3 text-yellow-600 dark:text-yellow-400" />
                                        </div>
                                    </div>

                                    <!-- Period info -->
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs sm:text-sm font-medium text-foreground">{{ p.label }}</p>
                                    </div>

                                    <!-- Status badge -->
                                    <Badge
                                        variant="secondary"
                                        class="text-[9px] sm:text-[10px] shrink-0 border-0"
                                        :class="[
                                            p.status === 'paid' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' :
                                            p.status === 'pending' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' :
                                            'bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400'
                                        ]"
                                    >
                                        {{ p.status === 'paid' ? 'Lunas' : p.status === 'pending' ? 'Menunggu' : 'Belum' }}
                                    </Badge>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- One Time Section -->
                    <div v-else class="px-4 sm:px-5 py-3 sm:py-4">
                        <div v-if="statusData.status === 'unpaid'" class="p-3 sm:p-3.5 bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800/50 rounded-lg flex items-center gap-3">
                            <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center shrink-0">
                                <CircleAlert class="w-4 h-4 text-red-600 dark:text-red-400" />
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-red-700 dark:text-red-400">Belum Dibayar</p>
                                <p class="text-xs text-red-600/70 dark:text-red-400/70">Silakan lakukan pembayaran di bawah ini.</p>
                            </div>
                        </div>
                        <div v-else-if="statusData.status === 'pending'" class="p-3 sm:p-3.5 bg-yellow-50 dark:bg-yellow-900/10 border border-yellow-200 dark:border-yellow-800/50 rounded-lg flex items-center gap-3">
                            <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center shrink-0">
                                <Clock class="w-4 h-4 text-yellow-600 dark:text-yellow-400" />
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-yellow-700 dark:text-yellow-400">Menunggu Verifikasi</p>
                                <p class="text-xs text-yellow-600/70 dark:text-yellow-400/70">Pembayaran Anda sedang diproses oleh admin.</p>
                            </div>
                        </div>
                        <div v-else class="p-3 sm:p-3.5 bg-green-50 dark:bg-green-900/10 border border-green-200 dark:border-green-800/50 rounded-lg flex items-center gap-3">
                            <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center shrink-0">
                                <ShieldCheck class="w-4 h-4 text-green-600 dark:text-green-400" />
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-green-700 dark:text-green-400">Lunas</p>
                                <p class="text-xs text-green-600/70 dark:text-green-400/70">Iuran ini sudah terbayar lunas.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Form -->
                    <div v-if="showPaymentForm" ref="paymentFormRef" class="px-4 sm:px-5 py-3 sm:py-4 border-t space-y-3 sm:space-y-4 bg-muted/20">
                        <!-- Total Summary -->
                        <div class="p-3 sm:p-3.5 rounded-lg bg-card border">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium text-foreground flex items-center gap-1.5">
                                        <CreditCard class="w-3.5 h-3.5 text-primary" />
                                        {{ statusData.type === 'once' ? 'Total Bayar' : `${selectedPeriods.length} Periode` }}
                                    </p>
                                    <p class="text-[11px] text-muted-foreground mt-0.5">@ {{ formatCurrency(type?.amount) }}</p>
                                </div>
                                <p class="text-lg sm:text-xl font-bold text-primary tabular-nums">
                                    {{ formatCurrency(statusData.type === 'once' ? type.amount : totalAmount) }}
                                </p>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div>
                            <Label class="text-[11px] sm:text-xs font-medium text-muted-foreground uppercase tracking-wide">Metode Pembayaran</Label>
                            <div class="grid grid-cols-2 gap-2 mt-1.5">
                                <label class="cursor-pointer">
                                    <input type="radio" v-model="paymentMethod" value="transfer" class="peer sr-only" />
                                    <div class="rounded-lg border-2 p-2 sm:p-2.5 peer-checked:border-primary peer-checked:bg-primary/5 text-muted-foreground peer-checked:text-primary transition-all flex items-center gap-2 hover:bg-muted/50">
                                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-muted flex items-center justify-center shrink-0">
                                            <CreditCard class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                                        </div>
                                        <div>
                                            <span class="text-xs sm:text-sm font-semibold block">Transfer</span>
                                            <span class="text-[10px] sm:text-[11px] text-muted-foreground">Bank / e-wallet</span>
                                        </div>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" v-model="paymentMethod" value="cash" class="peer sr-only" />
                                    <div class="rounded-lg border-2 p-2 sm:p-2.5 peer-checked:border-primary peer-checked:bg-primary/5 text-muted-foreground peer-checked:text-primary transition-all flex items-center gap-2 hover:bg-muted/50">
                                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-muted flex items-center justify-center shrink-0">
                                            <Banknote class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                                        </div>
                                        <div>
                                            <span class="text-xs sm:text-sm font-semibold block">Tunai</span>
                                            <span class="text-[10px] sm:text-[11px] text-muted-foreground">Bayar langsung</span>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Receipt Upload -->
                        <div v-if="paymentMethod === 'transfer'">
                            <Label class="text-[11px] sm:text-xs font-medium text-muted-foreground uppercase tracking-wide">Bukti Transfer</Label>
                            <div
                                @dragover.prevent="isDraggingReceipt = true"
                                @dragleave.prevent="isDraggingReceipt = false"
                                @drop.prevent="handleReceiptDrop($event)"
                                @click="receiptInputRef?.click()"
                                class="mt-1.5 relative border-2 border-dashed rounded-lg p-3 sm:p-4 text-center cursor-pointer transition-all duration-200"
                                :class="isDraggingReceipt ? 'border-primary bg-primary/5' : 'border-border hover:border-primary/40 hover:bg-muted/30'"
                            >
                                <input ref="receiptInputRef" type="file" accept="image/*" class="hidden" @change="handleReceiptSelect($event)" />
                                <template v-if="!receiptPreview">
                                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-muted flex items-center justify-center mx-auto mb-1">
                                        <Upload class="h-3.5 w-3.5 sm:h-4 sm:w-4 text-muted-foreground" />
                                    </div>
                                    <p class="text-xs font-medium text-foreground">Upload Bukti Transfer</p>
                                    <p class="text-[10px] sm:text-[11px] text-muted-foreground mt-0.5">Tap untuk pilih foto. PNG, JPG max 2MB</p>
                                </template>
                                <template v-else>
                                    <div class="relative inline-block">
                                        <img :src="receiptPreview" class="max-h-24 sm:max-h-28 rounded-lg mx-auto shadow-sm" />
                                        <button type="button" @click.stop="clearReceipt" class="absolute -top-2 -right-2 w-5 h-5 bg-destructive text-white rounded-full flex items-center justify-center hover:bg-destructive/80 shadow-sm">
                                            <X class="w-3 h-3" />
                                        </button>
                                    </div>
                                    <p class="text-[10px] text-muted-foreground mt-1">{{ receiptFile?.name }}</p>
                                </template>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <Label class="text-[11px] sm:text-xs font-medium text-muted-foreground uppercase tracking-wide">Catatan <span class="normal-case font-normal">(Opsional)</span></Label>
                            <Textarea v-model="form.notes" rows="2" class="mt-1.5 text-sm" placeholder="Keterangan tambahan..." />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div v-if="statusData && showPaymentForm" class="px-4 sm:px-5 py-2.5 sm:py-3 border-t bg-muted/30 flex items-center justify-end gap-2 shrink-0">
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
            <div v-else-if="statusData && !showPaymentForm && unpaidPeriods.length > 0" class="px-4 sm:px-5 py-2.5 sm:py-3 border-t bg-muted/30 flex items-center justify-between gap-2 shrink-0">
                <p class="text-[11px] text-muted-foreground">Pilih periode di atas untuk membayar</p>
                <Button variant="outline" size="sm" @click="$emit('close')">Tutup</Button>
            </div>
            <div v-else-if="statusData" class="px-4 sm:px-5 py-2.5 sm:py-3 border-t bg-muted/30 flex items-center justify-end shrink-0">
                <Button variant="outline" size="sm" @click="$emit('close')">Tutup</Button>
            </div>
        </DialogContent>
    </Dialog>
</template>
