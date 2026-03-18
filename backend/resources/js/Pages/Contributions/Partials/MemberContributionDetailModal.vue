<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import {
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogDescription,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import axios from 'axios';
import { Upload, X } from 'lucide-vue-next';

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
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(val);
};

const errorMessage = ref(null);

const fetchStatus = async () => {
    if (!props.type) return;
    loading.value = true;
    errorMessage.value = null;
    statusData.value = null;
    try {
        // Ensure route uses updated parameter name if needed, but usually ID passing is position-agnostic in Ziggy for single param
        const url = route('contributions.my-status', props.type.id);
        const res = await axios.get(url);
        statusData.value = res.data;
        // Reset selection
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
    }
});

const togglePeriod = (periodKey) => {
    if (selectedPeriods.value.includes(periodKey)) {
        selectedPeriods.value = selectedPeriods.value.filter(k => k !== periodKey);
    } else {
        selectedPeriods.value.push(periodKey);
    }
};

const submit = () => {
    if (!member.value) {
        alert("Data anggota tidak ditemukan. Silakan hubungi admin.");
        return; 
    }
    
    // Setup form
    form.member_ids = [member.value.id];
    form.contribution_type_id = props.type.id;
    form.amount = totalAmount.value;
    form.periods = selectedPeriods.value;
    form.payment_method = paymentMethod.value;
    
    // For manual handling of file in useForm
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

const selectAllUnpaid = () => {
    if (!statusData.value) return;
    statusData.value.periods.forEach(p => {
        if (p.status === 'unpaid' && !selectedPeriods.value.includes(p.period)) {
            selectedPeriods.value.push(p.period);
        }
    });
};
</script>

<template>
    <Dialog :open="show" @update:open="(val) => { if (!val) $emit('close'); }">
        <DialogContent class="max-w-2xl max-h-[85vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>Rincian Pembayaran</DialogTitle>
            </DialogHeader>

            <div v-if="loading" class="py-12 text-center text-muted-foreground text-sm">
                Memuat data...
            </div>

            <div v-else-if="errorMessage" class="py-8 text-center">
                <div class="text-destructive mb-2">
                    <svg class="h-10 w-10 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <p class="text-foreground font-bold">{{ errorMessage }}</p>
                <Button variant="outline" class="mt-4" @click="fetchStatus">Coba Lagi</Button>
            </div>

            <div v-else-if="statusData">
                <!-- Header Info -->
                <div class="mb-6 bg-muted p-4 rounded-xl border border">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-xs font-bold uppercase text-muted-foreground">Jenis Iuran</span>
                        <span class="text-sm font-black text-foreground">{{ type?.name }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-bold uppercase text-muted-foreground">Nominal per Periode</span>
                        <span class="text-primary font-bold">{{ formatCurrency(type?.amount) }}</span>
                    </div>
                </div>

                <!-- Periodic Section -->
                <div v-if="statusData.type !== 'once'" class="mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-sm font-bold text-foreground uppercase tracking-widest">Daftar Periode</h3>
                        <button @click="selectAllUnpaid" class="text-xs font-bold text-primary hover:text-primary uppercase">Pilih Semua Belum Bayar</button>
                    </div>

                    <div class="flex items-center gap-4 mb-4" v-if="statusData.summary">
                        <div class="flex-1 bg-muted rounded-full h-2 overflow-hidden">
                            <div class="bg-primary/100 h-full" :style="{ width: statusData.summary.percentage + '%' }"></div>
                        </div>
                        <span class="text-xs font-bold text-muted-foreground">{{ statusData.summary.paid }} / {{ statusData.summary.total }} ({{ statusData.summary.percentage }}%)</span>
                    </div>

                    <div class="max-h-60 overflow-y-auto border border rounded-xl">
                        <table class="min-w-full divide-y divide-border">
                            <thead class="bg-muted sticky top-0">
                                <tr>
                                    <th class="px-4 py-3 text-left text-[10px] font-bold uppercase text-muted-foreground">Periode</th>
                                    <th class="px-4 py-3 text-left text-[10px] font-bold uppercase text-muted-foreground">Jatuh Tempo</th>
                                    <th class="px-4 py-3 text-left text-[10px] font-bold uppercase text-muted-foreground">Status</th>
                                    <th class="px-4 py-3 text-center text-[10px] font-bold uppercase text-muted-foreground">Pilih</th>
                                </tr>
                            </thead>
                            <tbody class="bg-card divide-y divide-border text-sm">
                                <tr v-for="p in statusData.periods" :key="p.period" :class="{'bg-primary/10/50': selectedPeriods.includes(p.period)}">
                                    <td class="px-4 py-3 font-medium text-foreground">{{ p.label }}</td>
                                    <td class="px-4 py-3 text-xs text-muted-foreground">{{ p.due_date || '-' }}</td>
                                    <td class="px-4 py-3">
                                        <Badge v-if="p.status === 'paid'" variant="success">Lunas</Badge>
                                        <Badge v-else-if="p.status === 'pending'" variant="warning">Menunggu</Badge>
                                        <Badge v-else variant="destructive">Belum Bayar</Badge>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <Checkbox
                                            v-if="p.status === 'unpaid'"
                                            :checked="selectedPeriods.includes(p.period)"
                                            @update:checked="(val) => togglePeriod(p.period, val)"
                                        />
                                        <span v-else class="text-muted-foreground">-</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- One Time Section -->
                <div v-else class="mb-6">
                    <div v-if="statusData.status === 'unpaid'" class="p-4 bg-destructive/10 text-destructive rounded-xl text-center text-sm font-bold">
                        Anda belum membayar iuran ini.
                    </div>
                     <div v-else class="p-4 bg-green-100 text-green-700 rounded-xl text-center text-sm font-bold">
                        Status: {{ statusData.status === 'paid' ? 'LUNAS' : 'MENUNGGU VERIFIKASI' }}
                    </div>
                </div>

                <!-- Payment Form -->
                <div v-if="(statusData.type !== 'once' && selectedPeriods.length > 0) || (statusData.type === 'once' && statusData.status === 'unpaid')" class="mt-8 border-t border pt-6">
                    <h3 class="text-sm font-bold text-foreground uppercase tracking-widest mb-4">Konfirmasi Pembayaran</h3>

                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-muted-foreground">Total Periode</span>
                            <span class="font-bold text-foreground">{{ statusData.type === 'once' ? '1' : selectedPeriods.length }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-muted-foreground">Total Pembayaran</span>
                            <span class="font-black text-xl text-primary">{{ formatCurrency(statusData.type === 'once' ? type.amount : totalAmount) }}</span>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                             <Label class="text-[10px] uppercase text-muted-foreground mb-1">Metode Pembayaran</Label>
                             <select v-model="paymentMethod" class="w-full border rounded-xl font-bold text-sm text-foreground">
                                 <option value="transfer">Transfer Bank</option>
                                 <option value="cash">Tunai (Cash)</option>
                             </select>
                        </div>

                        <div v-if="paymentMethod === 'transfer'">
                            <Label class="text-[10px] uppercase text-muted-foreground mb-1">Bukti Transfer (Gambar)</Label>
                            <div
                                @dragover.prevent="isDraggingReceipt = true"
                                @dragleave.prevent="isDraggingReceipt = false"
                                @drop.prevent="handleReceiptDrop($event)"
                                @click="receiptInputRef?.click()"
                                class="relative border-2 border-dashed rounded-xl p-4 text-center cursor-pointer transition-all duration-200"
                                :class="isDraggingReceipt ? 'border-primary bg-primary/5 scale-[1.01]' : 'border-border hover:border-primary/50 hover:bg-muted/30'"
                            >
                                <input ref="receiptInputRef" type="file" accept="image/*" class="hidden" @change="handleReceiptSelect($event)" />
                                <template v-if="!receiptPreview">
                                    <Upload class="mx-auto h-7 w-7 text-muted-foreground mb-1.5" />
                                    <p class="text-xs font-medium text-primary">Upload Bukti Transfer</p>
                                    <p class="text-[11px] text-muted-foreground mt-0.5">Drag & drop atau klik. PNG, JPG max 2MB</p>
                                </template>
                                <template v-else>
                                    <div class="relative inline-block">
                                        <img :src="receiptPreview" class="max-h-28 rounded-lg mx-auto" />
                                        <button type="button" @click.stop="clearReceipt" class="absolute -top-2 -right-2 w-5 h-5 bg-destructive text-white rounded-full flex items-center justify-center hover:bg-destructive/80">
                                            <X class="w-3 h-3" />
                                        </button>
                                    </div>
                                    <p class="text-[11px] text-muted-foreground mt-1.5">{{ receiptFile?.name }}</p>
                                </template>
                            </div>
                        </div>

                         <div>
                             <Label class="text-[10px] uppercase text-muted-foreground mb-1">Catatan (Opsional)</Label>
                             <textarea v-model="form.notes" rows="2" class="w-full border rounded-xl text-sm"></textarea>
                        </div>
                    </div>

                    <DialogFooter class="mt-6">
                        <Button variant="outline" type="button" @click="$emit('close')">Batal</Button>
                        <Button type="submit"
                            :disabled="form.processing || (paymentMethod === 'transfer' && !receiptFile)"
                            @click="submit"
                        >
                            {{ form.processing ? 'Memproses...' : 'Kirim Pembayaran' }}
                        </Button>
                    </DialogFooter>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
