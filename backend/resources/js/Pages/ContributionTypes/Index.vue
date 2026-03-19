<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import { Badge } from '@/components/ui/badge';
import {
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter,
} from '@/components/ui/dialog';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import {
    DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent,
    AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import {
    Plus, Pencil, Trash2, MoreVertical, Settings2, Wallet,
    CalendarClock, Repeat, CircleDot, TrendingUp, Users,
    Loader2, Search, CalendarDays, Clock,
} from 'lucide-vue-next';

const props = defineProps({
    types: Array,
    wallets: Array,
});

const page = usePage();

// ─── Helpers ─────────────────────────────────────────────────────────
const formatCurrency = (amount) => {
    const currency = page.props.appSettings?.financial?.currency || 'Rp';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency', currency: 'IDR', minimumFractionDigits: 0,
    }).format(Number(amount || 0)).replace('Rp', currency);
};

const periods = {
    once: 'Sekali Bayar',
    daily: 'Harian',
    weekly: 'Mingguan',
    monthly: 'Bulanan',
    yearly: 'Tahunan',
};

const periodIcons = {
    once: CircleDot,
    daily: Clock,
    weekly: CalendarDays,
    monthly: CalendarClock,
    yearly: Repeat,
};

const dayNames = ['', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

const formatDate = (val) => {
    if (!val) return '-';
    try {
        return new Date(val).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    } catch { return val; }
};

const getScheduleLabel = (type) => {
    if (type.period === 'monthly' && type.recurring_day) return `Setiap tgl ${type.recurring_day}`;
    if (type.period === 'weekly' && type.recurring_day) return `Setiap ${dayNames[type.recurring_day] || ''}`;
    if (['once', 'yearly'].includes(type.period) && type.due_date) return `Jatuh tempo: ${formatDate(type.due_date)}`;
    return null;
};

// ─── Search ──────────────────────────────────────────────────────────
const searchQuery = ref('');

const filteredTypes = computed(() => {
    if (!searchQuery.value.trim()) return props.types;
    const q = searchQuery.value.toLowerCase();
    return props.types.filter(t =>
        t.name.toLowerCase().includes(q) ||
        (t.description || '').toLowerCase().includes(q) ||
        (t.wallet?.name || '').toLowerCase().includes(q)
    );
});

// ─── Stats ───────────────────────────────────────────────────────────
const stats = computed(() => {
    const all = props.types || [];
    const active = all.filter(t => t.is_active);
    const totalCollected = all.reduce((sum, t) => sum + Number(t.contributions_sum_amount || 0), 0);
    const totalPayments = all.reduce((sum, t) => sum + Number(t.contributions_count || 0), 0);
    return { total: all.length, active: active.length, totalCollected, totalPayments };
});

// ─── Modal Form ──────────────────────────────────────────────────────
const showModal = ref(false);
const editingType = ref(null);

const form = useForm({
    name: '',
    wallet_id: '',
    amount: '',
    period: 'monthly',
    description: '',
    is_active: true,
    start_date: '',
    end_date: '',
    due_date: '',
    recurring_day: '',
});

const openCreate = () => {
    editingType.value = null;
    form.reset();
    form.period = 'monthly';
    form.is_active = true;
    showModal.value = true;
};

const openEdit = (type) => {
    editingType.value = type;
    form.name = type.name;
    form.wallet_id = type.wallet_id ? String(type.wallet_id) : '';
    form.amount = type.amount;
    form.period = type.period;
    form.description = type.description || '';
    form.is_active = !!type.is_active;
    form.start_date = type.start_date ? type.start_date.split('T')[0] : '';
    form.end_date = type.end_date ? type.end_date.split('T')[0] : '';
    form.due_date = type.due_date ? type.due_date.split('T')[0] : '';
    form.recurring_day = type.recurring_day ? String(type.recurring_day) : '';
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
    editingType.value = null;
};

const submit = () => {
    if (editingType.value) {
        form.put(route('contribution-types.update', editingType.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('contribution-types.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

// ─── Delete ──────────────────────────────────────────────────────────
const showDeleteDialog = ref(false);
const deletingType = ref(null);
const deleteForm = useForm({});

const confirmDelete = (type) => {
    deletingType.value = type;
    showDeleteDialog.value = true;
};

const executeDelete = () => {
    if (!deletingType.value) return;
    deleteForm.delete(route('contribution-types.destroy', deletingType.value.id), {
        onSuccess: () => {
            showDeleteDialog.value = false;
            deletingType.value = null;
        },
        onError: () => {
            showDeleteDialog.value = false;
            deletingType.value = null;
        },
    });
};
</script>

<template>
    <Head title="Jenis Iuran" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold leading-tight text-foreground">Jenis Iuran</h2>
                <Button v-if="hasPermission('manage_contribution_types')" size="sm" @click="openCreate">
                    <Plus class="w-4 h-4 mr-1" />
                    <span class="hidden sm:inline">Tambah Jenis</span>
                </Button>
            </div>
        </template>

        <div class="py-4 sm:py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- Stats Cards -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                    <div class="bg-card border rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                                <Settings2 class="w-4.5 h-4.5 text-primary" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs text-muted-foreground font-medium">Total Jenis</p>
                                <p class="text-lg font-bold text-foreground tabular-nums">{{ stats.total }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-card border rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-green-500/10 flex items-center justify-center shrink-0">
                                <CircleDot class="w-4.5 h-4.5 text-green-600 dark:text-green-400" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs text-muted-foreground font-medium">Aktif</p>
                                <p class="text-lg font-bold text-foreground tabular-nums">{{ stats.active }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-card border rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-blue-500/10 flex items-center justify-center shrink-0">
                                <Users class="w-4.5 h-4.5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs text-muted-foreground font-medium">Pembayaran</p>
                                <p class="text-lg font-bold text-foreground tabular-nums">{{ stats.totalPayments.toLocaleString('id-ID') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-card border rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-amber-500/10 flex items-center justify-center shrink-0">
                                <TrendingUp class="w-4.5 h-4.5 text-amber-600 dark:text-amber-400" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs text-muted-foreground font-medium">Terkumpul</p>
                                <p class="text-lg font-bold text-foreground tabular-nums truncate">{{ formatCurrency(stats.totalCollected) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search -->
                <div class="relative">
                    <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground pointer-events-none" />
                    <Input
                        v-model="searchQuery"
                        placeholder="Cari jenis iuran..."
                        class="pl-10 h-10 bg-card border"
                    />
                </div>

                <!-- Type Cards Grid -->
                <div v-if="filteredTypes.length" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    <div
                        v-for="t in filteredTypes"
                        :key="t.id"
                        class="bg-card border rounded-xl overflow-hidden transition-shadow hover:shadow-md"
                    >
                        <!-- Card Header -->
                        <div class="px-5 pt-5 pb-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-start gap-3 min-w-0">
                                    <div
                                        class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                                        :class="t.is_active ? 'bg-primary/10' : 'bg-muted'"
                                    >
                                        <component
                                            :is="periodIcons[t.period] || CalendarClock"
                                            class="w-5 h-5"
                                            :class="t.is_active ? 'text-primary' : 'text-muted-foreground'"
                                        />
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="text-sm font-semibold text-foreground leading-tight truncate">{{ t.name }}</h3>
                                        <div class="flex items-center gap-1.5 mt-1">
                                            <Badge
                                                variant="secondary"
                                                class="text-xs border-0 px-1.5 py-0"
                                                :class="t.is_active
                                                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                                    : 'bg-muted text-muted-foreground'"
                                            >
                                                {{ t.is_active ? 'Aktif' : 'Nonaktif' }}
                                            </Badge>
                                            <Badge variant="outline" class="text-xs px-1.5 py-0">
                                                {{ periods[t.period] || t.period }}
                                            </Badge>
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions Dropdown -->
                                <DropdownMenu v-if="hasPermission('manage_contribution_types')">
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" size="icon" class="h-8 w-8 shrink-0 -mt-1 -mr-2">
                                            <MoreVertical class="w-4 h-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end" class="w-40">
                                        <DropdownMenuItem @click="openEdit(t)">
                                            <Pencil class="w-4 h-4 mr-2" />
                                            Edit
                                        </DropdownMenuItem>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem class="text-destructive" @click="confirmDelete(t)">
                                            <Trash2 class="w-4 h-4 mr-2" />
                                            Hapus
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>

                            <p v-if="t.description" class="text-xs text-muted-foreground mt-2 line-clamp-2">{{ t.description }}</p>
                        </div>

                        <!-- Amount -->
                        <div class="px-5 pb-4">
                            <p class="text-xl font-bold text-primary tabular-nums">{{ formatCurrency(t.amount) }}</p>
                            <p v-if="getScheduleLabel(t)" class="text-xs text-muted-foreground mt-0.5">{{ getScheduleLabel(t) }}</p>
                        </div>

                        <!-- Card Footer -->
                        <div class="px-5 py-3 bg-muted/30 border-t flex items-center justify-between gap-3">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <Wallet class="w-3.5 h-3.5 text-muted-foreground shrink-0" />
                                <span v-if="t.wallet" class="text-xs font-medium text-foreground truncate">{{ t.wallet.name }}</span>
                                <span v-else class="text-xs text-destructive italic">Belum diatur</span>
                            </div>
                            <div class="flex items-center gap-3 shrink-0 text-xs text-muted-foreground">
                                <span class="tabular-nums" :title="`${t.contributions_count || 0} pembayaran`">
                                    {{ t.contributions_count || 0 }} trx
                                </span>
                                <span v-if="t.start_date || t.end_date" class="hidden sm:inline">
                                    {{ t.start_date ? formatDate(t.start_date) : '...' }}
                                    –
                                    {{ t.end_date ? formatDate(t.end_date) : '...' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-card border rounded-xl py-16 text-center">
                    <div class="w-14 h-14 rounded-full bg-muted flex items-center justify-center mx-auto mb-4">
                        <Settings2 class="w-7 h-7 text-muted-foreground" />
                    </div>
                    <p class="text-sm font-semibold text-foreground mb-1">
                        {{ searchQuery ? 'Tidak ada hasil' : 'Belum ada jenis iuran' }}
                    </p>
                    <p class="text-xs text-muted-foreground mb-4">
                        {{ searchQuery ? 'Coba ubah kata kunci pencarian.' : 'Buat jenis iuran pertama untuk mulai mengelola pembayaran.' }}
                    </p>
                    <Button v-if="!searchQuery && hasPermission('manage_contribution_types')" size="sm" @click="openCreate">
                        <Plus class="w-4 h-4 mr-1" />
                        Tambah Jenis Iuran
                    </Button>
                </div>
            </div>
        </div>

        <!-- Create/Edit Dialog -->
        <Dialog :open="showModal" @update:open="(val) => { if (!val) closeModal(); }">
            <DialogContent class="max-w-lg max-h-[90vh] overflow-hidden flex flex-col p-0">
                <div class="px-6 pt-6 pb-4 border-b bg-muted/30">
                    <DialogHeader>
                        <DialogTitle class="flex items-center gap-2 text-base">
                            <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                                <Settings2 class="w-4 h-4 text-primary" />
                            </div>
                            {{ editingType ? 'Edit Jenis Iuran' : 'Tambah Jenis Iuran' }}
                        </DialogTitle>
                        <DialogDescription class="mt-1">
                            {{ editingType ? 'Perbarui detail jenis iuran ini.' : 'Buat jenis iuran baru untuk organisasi.' }}
                        </DialogDescription>
                    </DialogHeader>
                </div>

                <form @submit.prevent="submit" class="flex-1 overflow-y-auto">
                    <div class="px-6 py-5 space-y-5">
                        <!-- Name -->
                        <div>
                            <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Nama Iuran</Label>
                            <Input type="text" class="mt-1.5 w-full" v-model="form.name" required placeholder="Contoh: Iuran Bulanan 2026" />
                            <p v-if="form.errors.name" class="mt-1 text-xs text-destructive">{{ form.errors.name }}</p>
                        </div>

                        <!-- Amount & Period -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Besaran (Rp)</Label>
                                <Input type="number" class="mt-1.5 w-full" v-model="form.amount" required placeholder="0" min="0" />
                                <p v-if="form.errors.amount" class="mt-1 text-xs text-destructive">{{ form.errors.amount }}</p>
                            </div>
                            <div>
                                <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Periode</Label>
                                <Select v-model="form.period" required>
                                    <SelectTrigger class="mt-1.5 w-full">
                                        <SelectValue placeholder="Pilih Periode" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="(label, value) in periods" :key="value" :value="value">{{ label }}</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.period" class="mt-1 text-xs text-destructive">{{ form.errors.period }}</p>
                            </div>
                        </div>

                        <!-- Wallet -->
                        <div>
                            <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Dompet Tujuan</Label>
                            <Select v-model="form.wallet_id" required>
                                <SelectTrigger class="mt-1.5 w-full">
                                    <SelectValue placeholder="Pilih Dompet" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="w in wallets" :key="w.id" :value="w.id.toString()">
                                        {{ w.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.wallet_id" class="mt-1 text-xs text-destructive">{{ form.errors.wallet_id }}</p>
                            <p class="mt-1 text-xs text-muted-foreground">Iuran yang dibayar akan masuk ke dompet ini.</p>
                        </div>

                        <!-- Description -->
                        <div>
                            <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Deskripsi <span class="normal-case font-normal">(Opsional)</span></Label>
                            <Textarea v-model="form.description" rows="2" class="mt-1.5" placeholder="Rincian mengenai iuran ini..." />
                        </div>

                        <!-- Date Range -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Tanggal Mulai <span class="normal-case font-normal">(Opsional)</span></Label>
                                <Input type="date" class="mt-1.5 w-full" v-model="form.start_date" />
                                <p v-if="form.errors.start_date" class="mt-1 text-xs text-destructive">{{ form.errors.start_date }}</p>
                            </div>
                            <div>
                                <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Batas Akhir <span class="normal-case font-normal">(Opsional)</span></Label>
                                <Input type="date" class="mt-1.5 w-full" v-model="form.end_date" />
                                <p v-if="form.errors.end_date" class="mt-1 text-xs text-destructive">{{ form.errors.end_date }}</p>
                            </div>
                        </div>

                        <!-- Recurring Day (Monthly) -->
                        <div v-if="form.period === 'monthly'">
                            <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Tanggal Jatuh Tempo</Label>
                            <Input type="number" min="1" max="31" class="mt-1.5 w-full" v-model="form.recurring_day" placeholder="Contoh: 10 (Setiap tanggal 10)" />
                            <p v-if="form.errors.recurring_day" class="mt-1 text-xs text-destructive">{{ form.errors.recurring_day }}</p>
                        </div>

                        <!-- Day Selector (Weekly) -->
                        <div v-if="form.period === 'weekly'">
                            <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Hari Pembayaran</Label>
                            <Select v-model="form.recurring_day">
                                <SelectTrigger class="mt-1.5 w-full">
                                    <SelectValue placeholder="Pilih Hari" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="1">Senin</SelectItem>
                                    <SelectItem value="2">Selasa</SelectItem>
                                    <SelectItem value="3">Rabu</SelectItem>
                                    <SelectItem value="4">Kamis</SelectItem>
                                    <SelectItem value="5">Jumat</SelectItem>
                                    <SelectItem value="6">Sabtu</SelectItem>
                                    <SelectItem value="7">Minggu</SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.recurring_day" class="mt-1 text-xs text-destructive">{{ form.errors.recurring_day }}</p>
                        </div>

                        <!-- Due Date (Once / Yearly) -->
                        <div v-if="['once', 'yearly'].includes(form.period)">
                            <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Tanggal Jatuh Tempo</Label>
                            <Input type="date" class="mt-1.5 w-full" v-model="form.due_date" />
                            <p v-if="form.errors.due_date" class="mt-1 text-xs text-destructive">{{ form.errors.due_date }}</p>
                        </div>

                        <!-- Active Toggle -->
                        <div class="flex items-center gap-3 p-3.5 rounded-xl bg-muted/50 border">
                            <Checkbox id="is_active_type" :checked="form.is_active" @update:checked="(val) => form.is_active = val" />
                            <div>
                                <Label for="is_active_type" class="text-sm font-semibold text-foreground cursor-pointer">Status Aktif</Label>
                                <p class="text-xs text-muted-foreground">Iuran nonaktif tidak bisa dipilih saat mencatat pembayaran.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 border-t bg-card flex items-center justify-end gap-2">
                        <Button variant="outline" type="button" @click="closeModal">Batal</Button>
                        <Button type="submit" :disabled="form.processing">
                            <Loader2 v-if="form.processing" class="w-4 h-4 mr-1.5 animate-spin" />
                            {{ editingType ? 'Simpan Perubahan' : 'Buat Jenis Iuran' }}
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation -->
        <AlertDialog :open="showDeleteDialog" @update:open="(val) => { if (!val) { showDeleteDialog = false; deletingType = null; } }">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Hapus Jenis Iuran?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Jenis iuran <span class="font-semibold text-foreground">{{ deletingType?.name }}</span> akan dihapus.
                        Tindakan ini tidak bisa dibatalkan.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel @click="showDeleteDialog = false; deletingType = null">Batal</AlertDialogCancel>
                    <AlertDialogAction
                        class="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                        :disabled="deleteForm.processing"
                        @click="executeDelete"
                    >
                        <Loader2 v-if="deleteForm.processing" class="w-4 h-4 mr-1.5 animate-spin" />
                        Hapus
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AuthenticatedLayout>
</template>
