<script setup>
import { ref } from 'vue';
import {
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogDescription,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { CheckCircle, XCircle, CreditCard, Banknote, ZoomIn } from 'lucide-vue-next';

const props = defineProps({
    open: Boolean,
    contribution: Object,
    formatCurrency: Function,
    formatDate: Function,
});

const emit = defineEmits(['update:open', 'verify']);

const rejectComment = ref('');
const showFullReceipt = ref(false);

const handleVerify = (action) => {
    if (action === 'reject' && !rejectComment.value.trim()) {
        alert('Komentar penolakan wajib diisi.');
        return;
    }
    emit('verify', { id: props.contribution?.id, action, comment: rejectComment.value.trim() });
    rejectComment.value = '';
};

const close = () => {
    emit('update:open', false);
    rejectComment.value = '';
};
</script>

<template>
    <Dialog :open="open" @update:open="(val) => { if (!val) close(); }">
        <DialogContent class="max-w-lg p-0 overflow-hidden">
            <!-- Header -->
            <div class="px-5 pt-5 pb-4 border-b">
                <DialogHeader>
                    <DialogTitle class="text-base">Verifikasi Pembayaran</DialogTitle>
                    <DialogDescription class="text-xs">Periksa detail dan bukti pembayaran sebelum memverifikasi.</DialogDescription>
                </DialogHeader>
            </div>

            <div v-if="contribution" class="px-5 py-4 space-y-4">
                <!-- Info Grid -->
                <div class="grid grid-cols-2 gap-2.5">
                    <div class="bg-muted/50 rounded-lg p-3">
                        <p class="text-[11px] font-medium uppercase text-muted-foreground tracking-wide mb-1">Anggota</p>
                        <p class="text-sm font-semibold text-foreground">{{ contribution.member?.full_name || '-' }}</p>
                        <p class="text-[11px] text-muted-foreground font-mono mt-0.5">{{ contribution.member?.member_code }}</p>
                    </div>
                    <div class="bg-muted/50 rounded-lg p-3">
                        <p class="text-[11px] font-medium uppercase text-muted-foreground tracking-wide mb-1">Jenis Iuran</p>
                        <p class="text-sm font-semibold text-foreground">{{ contribution.type?.name || '-' }}</p>
                        <p class="text-[11px] text-muted-foreground font-mono mt-0.5">{{ contribution.payment_period || '-' }}</p>
                    </div>
                    <div class="bg-muted/50 rounded-lg p-3">
                        <p class="text-[11px] font-medium uppercase text-muted-foreground tracking-wide mb-1">Jumlah</p>
                        <p class="text-sm font-bold text-primary tabular-nums">{{ formatCurrency(contribution.amount) }}</p>
                    </div>
                    <div class="bg-muted/50 rounded-lg p-3">
                        <p class="text-[11px] font-medium uppercase text-muted-foreground tracking-wide mb-1">Tanggal & Metode</p>
                        <p class="text-sm font-medium text-foreground">{{ formatDate(contribution.payment_date) }}</p>
                        <p class="text-[11px] text-muted-foreground flex items-center gap-1 mt-0.5">
                            <CreditCard v-if="contribution.payment_method === 'transfer'" class="w-3 h-3" />
                            <Banknote v-else class="w-3 h-3" />
                            {{ contribution.payment_method === 'transfer' ? 'Transfer' : 'Tunai' }}
                        </p>
                    </div>
                </div>

                <!-- Receipt -->
                <div>
                    <p class="text-[11px] font-medium uppercase text-muted-foreground tracking-wide mb-2">Bukti Pembayaran</p>
                    <div v-if="contribution.receipt_path" class="bg-muted/50 border rounded-lg p-3">
                        <img
                            :src="`/storage/${contribution.receipt_path}`"
                            alt="Bukti Pembayaran"
                            class="max-h-[35vh] object-contain rounded cursor-pointer hover:opacity-80 transition-opacity mx-auto"
                            @click="showFullReceipt = true"
                        />
                        <p class="text-[11px] text-muted-foreground mt-2 text-center flex items-center justify-center gap-1">
                            <ZoomIn class="w-3 h-3" />
                            Klik gambar untuk memperbesar
                        </p>
                    </div>
                    <p v-else class="text-xs text-muted-foreground italic">Tanpa bukti</p>
                </div>

                <!-- Reject Comment -->
                <div>
                    <Label class="text-xs text-muted-foreground">Komentar Penolakan</Label>
                    <Textarea
                        v-model="rejectComment"
                        rows="2"
                        class="mt-1"
                        placeholder="Wajib diisi jika menolak..."
                    />
                </div>
            </div>

            <!-- Footer -->
            <div class="px-5 py-3 border-t bg-muted/30 flex items-center justify-end gap-2">
                <Button variant="outline" size="sm" type="button" @click="handleVerify('reject')">
                    <XCircle class="w-4 h-4 mr-1.5" />
                    Tolak
                </Button>
                <Button size="sm" type="button" @click="handleVerify('approve')">
                    <CheckCircle class="w-4 h-4 mr-1.5" />
                    Setujui
                </Button>
            </div>
        </DialogContent>
    </Dialog>

    <!-- Full Receipt Lightbox -->
    <Dialog :open="showFullReceipt" @update:open="(val) => showFullReceipt = val">
        <DialogContent class="max-w-4xl max-h-[90vh] p-2">
            <DialogHeader class="sr-only">
                <DialogTitle>Bukti Pembayaran</DialogTitle>
                <DialogDescription>Lihat bukti pembayaran dalam ukuran penuh</DialogDescription>
            </DialogHeader>
            <div class="flex items-center justify-center overflow-auto">
                <img
                    v-if="contribution?.receipt_path"
                    :src="`/storage/${contribution.receipt_path}`"
                    alt="Bukti Pembayaran"
                    class="max-w-full max-h-[85vh] object-contain"
                />
            </div>
        </DialogContent>
    </Dialog>
</template>
