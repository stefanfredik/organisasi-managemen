<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter,
} from '@/components/ui/dialog';
import { Badge } from '@/components/ui/badge';
import { ref } from 'vue';

const props = defineProps({
    pendingTransactions: Object,
});

const formatCurrency = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(val);
const formatDate = (date) => new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });

const showingProofModal = ref(false);
const selectedProofUrl = ref(null);
const processingId = ref(null);

const showProof = (url) => {
    selectedProofUrl.value = url;
    showingProofModal.value = true;
};

const closeProofModal = () => {
    showingProofModal.value = false;
    selectedProofUrl.value = null;
};

const verifyTransaction = (id, action) => {
    if (!confirm(`Apakah Anda yakin ingin ${action === 'approve' ? 'menyetujui' : 'menolak'} transaksi ini?`)) return;
    processingId.value = id;
    router.post(route('contributions.verify-action', id), { action }, {
        onFinish: () => processingId.value = null,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Verifikasi Iuran" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-foreground">
                Verifikasi Pembayaran
            </h2>
        </template>

        <div class="py-4">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-4">
                
                <!-- Navigation Tabs -->
                <div class="flex space-x-1 border-b border pb-1 overflow-x-auto">
                    <Link :href="route('contributions.monitoring.index')" class="px-3 py-1.5 text-xs font-bold rounded-md text-muted-foreground hover:bg-muted">
                        Jenis Iuran Aktif
                    </Link>
                    <Link :href="route('contributions.verification')" class="px-3 py-1.5 text-xs font-bold rounded-md bg-primary/10 text-primary">
                        Verifikasi
                    </Link>
                    <Link :href="route('contributions.index')" class="px-3 py-1.5 text-xs font-bold rounded-md text-muted-foreground hover:bg-muted">
                        Riwayat Transaksi
                    </Link>
                </div>

                <div class="bg-card rounded-lg shadow-sm border border overflow-hidden">
                    <div class="px-4 py-3 border-b border flex justify-between items-center">
                        <h3 class="text-sm font-bold text-foreground">Menunggu Verifikasi</h3>
                        <span class="bg-warning-100 text-warning-800 text-[10px] font-bold px-2 py-0.5 rounded-full">
                            {{ pendingTransactions.total }} Permintaan
                        </span>
                    </div>

                    <div v-if="pendingTransactions.data.length === 0" class="p-8 text-center">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-success/20 mb-3">
                            <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <h3 class="text-sm font-bold text-foreground">Semua Bersih!</h3>
                        <p class="text-xs text-muted-foreground mt-1">Tidak ada pembayaran menunggu verifikasi.</p>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-muted text-xs uppercase font-bold text-muted-foreground">
                                <tr>
                                    <th class="px-4 py-2.5">Tanggal</th>
                                    <th class="px-4 py-2.5">Anggota</th>
                                    <th class="px-4 py-2.5">Jenis</th>
                                    <th class="px-4 py-2.5">Periode</th>
                                    <th class="px-4 py-2.5">Jumlah</th>
                                    <th class="px-4 py-2.5">Bukti</th>
                                    <th class="px-4 py-2.5 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="trx in pendingTransactions.data" :key="trx.id" class="hover:bg-muted transition-colors">
                                    <td class="px-4 py-2.5 font-medium text-foreground text-xs">{{ formatDate(trx.created_at) }}</td>
                                    <td class="px-4 py-2.5">
                                        <div class="text-sm font-bold text-foreground">{{ trx.member?.full_name || 'Unknown' }}</div>
                                        <div class="text-[10px] text-muted-foreground">{{ trx.member?.member_code }}</div>
                                    </td>
                                    <td class="px-4 py-2.5 text-sm">{{ trx.type?.name }}</td>
                                    <td class="px-4 py-2.5">
                                        <span class="text-[10px] bg-muted px-1.5 py-0.5 rounded text-muted-foreground">{{ trx.payment_period || '-' }}</span>
                                    </td>
                                    <td class="px-4 py-2.5 font-bold text-foreground text-sm">{{ formatCurrency(trx.amount) }}</td>
                                    <td class="px-4 py-2.5">
                                        <button v-if="trx.receipt_path" @click="showProof('/storage/' + trx.receipt_path)" class="text-primary hover:text-primary font-bold text-xs underline">Lihat</button>
                                        <span v-else class="text-muted-foreground text-xs italic">-</span>
                                    </td>
                                    <td class="px-4 py-2.5 text-right space-x-1">
                                        <button 
                                            @click="verifyTransaction(trx.id, 'reject')" 
                                            :disabled="processingId === trx.id"
                                            class="px-2 py-1 rounded border border-danger-200 text-destructive hover:bg-destructive/10 text-xs font-bold transition-colors disabled:opacity-50">
                                            Tolak
                                        </button>
                                        <button 
                                            @click="verifyTransaction(trx.id, 'approve')" 
                                            :disabled="processingId === trx.id"
                                            class="px-2 py-1 rounded bg-primary text-white hover:bg-primary/90 text-xs font-bold transition-colors disabled:opacity-50">
                                            Setujui
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Proof Dialog -->
        <Dialog :open="showingProofModal" @update:open="(val) => { if (!val) closeProofModal(); }">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Bukti Pembayaran</DialogTitle>
                </DialogHeader>
                <div class="flex justify-center bg-muted rounded-lg p-3">
                    <img :src="selectedProofUrl" alt="Bukti Transfer" class="max-h-[60vh] object-contain rounded shadow-sm" />
                </div>
                <DialogFooter>
                    <Button variant="outline" size="sm" @click="closeProofModal">Tutup</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AuthenticatedLayout>
</template>
