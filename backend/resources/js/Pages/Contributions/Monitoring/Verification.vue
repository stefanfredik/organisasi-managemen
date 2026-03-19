<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter,
} from '@/components/ui/dialog';
import { Badge } from '@/components/ui/badge';
import { CheckCircle, XCircle, Eye, Image, Clock, CircleDollarSign } from 'lucide-vue-next';
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

const tabs = [
    { label: 'Jenis Iuran Aktif', route: 'contributions.monitoring.index', icon: CircleDollarSign },
    { label: 'Verifikasi', route: 'contributions.verification', icon: Clock },
    { label: 'Riwayat Transaksi', route: 'contributions.index', icon: CheckCircle },
];
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
                <nav class="flex gap-1 rounded-lg bg-muted p-1 overflow-x-auto">
                    <Link
                        v-for="tab in tabs"
                        :key="tab.route"
                        :href="route(tab.route)"
                        :class="[
                            'flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-md whitespace-nowrap transition-colors',
                            tab.route === 'contributions.verification'
                                ? 'bg-background text-primary shadow-sm'
                                : 'text-muted-foreground hover:text-foreground hover:bg-background/50'
                        ]"
                    >
                        <component :is="tab.icon" class="w-3.5 h-3.5" />
                        {{ tab.label }}
                    </Link>
                </nav>

                <div class="bg-card rounded-lg shadow-sm border overflow-hidden">
                    <div class="px-4 py-3 border-b flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <Clock class="w-4 h-4 text-muted-foreground" />
                            <h3 class="text-sm font-bold text-foreground">Menunggu Verifikasi</h3>
                        </div>
                        <Badge variant="outline" class="text-[10px] font-bold">
                            {{ pendingTransactions.total }} Permintaan
                        </Badge>
                    </div>

                    <!-- Empty State -->
                    <div v-if="pendingTransactions.data.length === 0" class="p-12 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 dark:bg-emerald-900/30 mb-4">
                            <CheckCircle class="w-8 h-8 text-emerald-600 dark:text-emerald-400" />
                        </div>
                        <h3 class="text-sm font-bold text-foreground">Semua Bersih!</h3>
                        <p class="text-xs text-muted-foreground mt-1 max-w-xs mx-auto">
                            Tidak ada pembayaran yang menunggu verifikasi saat ini.
                        </p>
                    </div>

                    <!-- Desktop Table -->
                    <div v-else class="hidden md:block overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-muted/50 text-xs uppercase font-semibold text-muted-foreground">
                                <tr>
                                    <th class="px-4 py-3">Tanggal</th>
                                    <th class="px-4 py-3">Anggota</th>
                                    <th class="px-4 py-3">Jenis</th>
                                    <th class="px-4 py-3">Periode</th>
                                    <th class="px-4 py-3">Jumlah</th>
                                    <th class="px-4 py-3">Bukti</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="trx in pendingTransactions.data" :key="trx.id" class="hover:bg-muted/30 transition-colors">
                                    <td class="px-4 py-3 text-xs text-muted-foreground">{{ formatDate(trx.created_at) }}</td>
                                    <td class="px-4 py-3">
                                        <div class="text-sm font-semibold text-foreground">{{ trx.member?.full_name || 'Unknown' }}</div>
                                        <div class="text-[10px] text-muted-foreground">{{ trx.member?.member_code }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-foreground">{{ trx.type?.name }}</td>
                                    <td class="px-4 py-3">
                                        <Badge variant="secondary" class="text-[10px]">{{ trx.payment_period || '-' }}</Badge>
                                    </td>
                                    <td class="px-4 py-3 font-bold text-foreground text-sm">{{ formatCurrency(trx.amount) }}</td>
                                    <td class="px-4 py-3">
                                        <Button v-if="trx.receipt_path" variant="ghost" size="sm" class="h-7 px-2 text-xs gap-1" @click="showProof('/storage/' + trx.receipt_path)">
                                            <Image class="w-3.5 h-3.5" />
                                            Lihat
                                        </Button>
                                        <span v-else class="text-muted-foreground text-xs italic">-</span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                class="h-7 px-2.5 text-xs gap-1 text-destructive border-destructive/30 hover:bg-destructive/10"
                                                :disabled="processingId === trx.id"
                                                @click="verifyTransaction(trx.id, 'reject')"
                                            >
                                                <XCircle class="w-3.5 h-3.5" />
                                                Tolak
                                            </Button>
                                            <Button
                                                size="sm"
                                                class="h-7 px-2.5 text-xs gap-1"
                                                :disabled="processingId === trx.id"
                                                @click="verifyTransaction(trx.id, 'approve')"
                                            >
                                                <CheckCircle class="w-3.5 h-3.5" />
                                                Setujui
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div v-if="pendingTransactions.data.length > 0" class="md:hidden divide-y divide-border">
                        <div v-for="trx in pendingTransactions.data" :key="'m-' + trx.id" class="p-4 space-y-3">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="text-sm font-semibold text-foreground">{{ trx.member?.full_name || 'Unknown' }}</div>
                                    <div class="text-[10px] text-muted-foreground">{{ trx.member?.member_code }}</div>
                                </div>
                                <Badge variant="secondary" class="text-[10px]">{{ trx.payment_period || '-' }}</Badge>
                            </div>

                            <div class="flex items-center justify-between text-xs">
                                <span class="text-muted-foreground">{{ trx.type?.name }}</span>
                                <span class="font-bold text-foreground text-sm">{{ formatCurrency(trx.amount) }}</span>
                            </div>

                            <div class="flex items-center justify-between text-xs text-muted-foreground">
                                <span>{{ formatDate(trx.created_at) }}</span>
                                <Button v-if="trx.receipt_path" variant="ghost" size="sm" class="h-6 px-2 text-[10px] gap-1" @click="showProof('/storage/' + trx.receipt_path)">
                                    <Eye class="w-3 h-3" />
                                    Bukti
                                </Button>
                                <span v-else class="italic">Tanpa bukti</span>
                            </div>

                            <div class="flex gap-2 pt-1">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    class="flex-1 h-8 text-xs gap-1 text-destructive border-destructive/30 hover:bg-destructive/10"
                                    :disabled="processingId === trx.id"
                                    @click="verifyTransaction(trx.id, 'reject')"
                                >
                                    <XCircle class="w-3.5 h-3.5" />
                                    Tolak
                                </Button>
                                <Button
                                    size="sm"
                                    class="flex-1 h-8 text-xs gap-1"
                                    :disabled="processingId === trx.id"
                                    @click="verifyTransaction(trx.id, 'approve')"
                                >
                                    <CheckCircle class="w-3.5 h-3.5" />
                                    Setujui
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Proof Dialog -->
        <Dialog :open="showingProofModal" @update:open="(val) => { if (!val) closeProofModal(); }">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <Image class="w-4 h-4" />
                        Bukti Pembayaran
                    </DialogTitle>
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
