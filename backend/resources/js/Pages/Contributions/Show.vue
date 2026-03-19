<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import DeleteConfirmDialog from '@/Components/DeleteConfirmDialog.vue';
import {
    DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    ArrowLeft, Trash2, MoreVertical, CheckCircle, Clock, XCircle,
    User, CalendarDays, CreditCard, Banknote, Receipt, ShieldCheck, FileImage, Wallet,
} from 'lucide-vue-next';
import { useToast } from '@/composables/useToast';

const toast = useToast();
const page = usePage();

const props = defineProps({
    contribution: Object,
});

const isAdmin = computed(() => ['admin', 'bendahara'].includes(page.props.auth.user.role));

const formatCurrency = (value) => {
    const currency = page.props.appSettings?.financial?.currency || 'Rp';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    })
        .format(Number(value || 0))
        .replace('Rp', currency);
};

const formatDate = (value) => {
    if (!value) return '-';
    return new Date(value).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};

const formatDateTime = (value) => {
    if (!value) return '-';
    return new Date(value).toLocaleString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getStatusConfig = (status) => {
    const map = {
        paid: { label: 'Lunas', class: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400', icon: CheckCircle },
        pending: { label: 'Pending', class: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400', icon: Clock },
        partial: { label: 'Sebagian', class: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400', icon: Clock },
        rejected: { label: 'Ditolak', class: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400', icon: XCircle },
    };
    return map[status] || { label: status || '-', class: 'bg-muted text-foreground', icon: Clock };
};

const getMethodLabel = (method) => {
    const map = { cash: 'Tunai', transfer: 'Transfer', online: 'Online' };
    return map[method] || method || '-';
};

const showDeleteDialog = ref(false);
const confirmDelete = () => {
    router.delete(route('contributions.destroy', props.contribution.id), {
        onError: (errors) => toast.error(Object.values(errors).flat().join(', ') || 'Gagal menghapus iuran.'),
        onFinish: () => (showDeleteDialog.value = false),
    });
};

const statusConfig = computed(() => getStatusConfig(props.contribution.status));
</script>

<template>
    <Head title="Detail Iuran" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <Button variant="ghost" size="icon" class="h-8 w-8 shrink-0" as-child>
                        <Link :href="route('contributions.index')">
                            <ArrowLeft class="w-4 h-4" />
                        </Link>
                    </Button>
                    <h2 class="text-lg font-semibold leading-tight text-foreground truncate">Detail Iuran</h2>
                </div>
                <DropdownMenu v-if="isAdmin">
                    <DropdownMenuTrigger as-child>
                        <Button variant="ghost" size="icon" class="h-8 w-8 shrink-0">
                            <MoreVertical class="w-4 h-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuItem class="text-destructive focus:text-destructive flex items-center gap-2" @click="showDeleteDialog = true">
                            <Trash2 class="w-4 h-4" /> Hapus
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </template>

        <div class="py-3 sm:py-6">
            <div class="max-w-4xl mx-auto px-3 sm:px-6 lg:px-8 space-y-4">
                <!-- Status Banner -->
                <div class="bg-card rounded-xl border overflow-hidden">
                    <div
                        class="h-1 w-full"
                        :class="{
                            'bg-green-500': contribution.status === 'paid',
                            'bg-yellow-500': contribution.status === 'pending',
                            'bg-blue-500': contribution.status === 'partial',
                            'bg-red-500': contribution.status === 'rejected',
                        }"
                    />
                    <div class="p-4 sm:p-6">
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div>
                                <p class="text-sm text-muted-foreground">Jumlah Pembayaran</p>
                                <p class="text-2xl sm:text-3xl font-bold text-foreground">{{ formatCurrency(contribution.amount) }}</p>
                            </div>
                            <span :class="['px-2.5 py-1 rounded-full text-xs font-semibold shrink-0', statusConfig.class]">
                                {{ statusConfig.label }}
                            </span>
                        </div>

                        <div class="flex flex-wrap gap-x-4 gap-y-1.5 text-xs text-muted-foreground">
                            <div class="flex items-center gap-1.5" v-if="contribution.type">
                                <Receipt class="w-3.5 h-3.5 text-primary" />
                                <span>{{ contribution.type.name }}</span>
                            </div>
                            <div class="flex items-center gap-1.5" v-if="contribution.payment_period">
                                <CalendarDays class="w-3.5 h-3.5 text-primary" />
                                <span>Periode: {{ contribution.payment_period }}</span>
                            </div>
                            <div class="flex items-center gap-1.5" v-if="contribution.payment_date">
                                <CalendarDays class="w-3.5 h-3.5 text-primary" />
                                <span>{{ formatDate(contribution.payment_date) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Member Info -->
                <div class="bg-card rounded-xl border p-4 sm:p-6">
                    <h3 class="text-sm font-semibold text-foreground mb-3 flex items-center gap-2">
                        <User class="w-4 h-4 text-primary" />
                        Informasi Anggota
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-muted-foreground text-xs">Nama</p>
                            <p class="font-medium text-foreground">{{ contribution.member?.full_name || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-muted-foreground text-xs">Kode Anggota</p>
                            <p class="font-medium text-foreground">{{ contribution.member?.member_code || '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Payment Details -->
                <div class="bg-card rounded-xl border p-4 sm:p-6">
                    <h3 class="text-sm font-semibold text-foreground mb-3 flex items-center gap-2">
                        <CreditCard class="w-4 h-4 text-primary" />
                        Detail Pembayaran
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-muted-foreground text-xs">Metode Pembayaran</p>
                            <p class="font-medium text-foreground">{{ getMethodLabel(contribution.payment_method) }}</p>
                        </div>
                        <div>
                            <p class="text-muted-foreground text-xs">Wallet Tujuan</p>
                            <p class="font-medium text-foreground">{{ contribution.wallet?.name || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-muted-foreground text-xs">Tanggal Bayar</p>
                            <p class="font-medium text-foreground">{{ formatDate(contribution.payment_date) }}</p>
                        </div>
                        <div>
                            <p class="text-muted-foreground text-xs">Periode</p>
                            <p class="font-medium text-foreground">{{ contribution.payment_period || '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Receipt -->
                <div v-if="contribution.receipt_path" class="bg-card rounded-xl border p-4 sm:p-6">
                    <h3 class="text-sm font-semibold text-foreground mb-3 flex items-center gap-2">
                        <FileImage class="w-4 h-4 text-primary" />
                        Bukti Pembayaran
                    </h3>
                    <div class="rounded-lg overflow-hidden border bg-muted/50">
                        <img :src="`/storage/${contribution.receipt_path}`" alt="Bukti Pembayaran" class="max-w-full max-h-96 mx-auto object-contain" />
                    </div>
                </div>

                <!-- Notes -->
                <div v-if="contribution.notes" class="bg-card rounded-xl border p-4 sm:p-6">
                    <h3 class="text-sm font-semibold text-foreground mb-2">Catatan</h3>
                    <p class="text-sm text-muted-foreground whitespace-pre-wrap">{{ contribution.notes }}</p>
                </div>

                <!-- Verification Info -->
                <div v-if="contribution.verified_at" class="bg-card rounded-xl border p-4 sm:p-6">
                    <h3 class="text-sm font-semibold text-foreground mb-3 flex items-center gap-2">
                        <ShieldCheck class="w-4 h-4 text-primary" />
                        Verifikasi
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-muted-foreground text-xs">Diverifikasi Oleh</p>
                            <p class="font-medium text-foreground">{{ contribution.verifier?.name || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-muted-foreground text-xs">Tanggal Verifikasi</p>
                            <p class="font-medium text-foreground">{{ formatDateTime(contribution.verified_at) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation -->
        <DeleteConfirmDialog
            :open="showDeleteDialog"
            title="Hapus Iuran"
            :description="`Apakah Anda yakin ingin menghapus data iuran ini? Tindakan ini tidak dapat dibatalkan.`"
            @confirm="confirmDelete"
            @cancel="showDeleteDialog = false"
        />
    </AuthenticatedLayout>
</template>
