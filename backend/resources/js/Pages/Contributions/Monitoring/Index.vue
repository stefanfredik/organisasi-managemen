<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
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
import DeleteConfirmDialog from '@/Components/DeleteConfirmDialog.vue';
import {
    Coins, CheckCircle, Clock, ChevronRight, Users, AlertCircle, Loader2, User,
    Plus, Pencil, Trash2, MoreVertical, Settings2,
} from 'lucide-vue-next';
import axios from 'axios';
import { useToast } from '@/composables/useToast';

const toast = useToast();

const props = defineProps({
    types: Array,
    wallets: Array,
});

const formatCurrency = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val);

// ─── Members by status modal ─────────────────────────────────────────
const showMembersModal = ref(false);
const membersLoading = ref(false);
const membersList = ref([]);
const membersCategory = ref('');
const membersTypeName = ref('');

const categoryConfig = {
    paid: { label: 'Lunas', icon: CheckCircle, color: 'text-green-600', bg: 'bg-green-500/10' },
    unpaid: { label: 'Belum Bayar', icon: Users, color: 'text-amber-600', bg: 'bg-amber-500/10' },
    arrears: { label: 'Menunggak', icon: AlertCircle, color: 'text-red-600', bg: 'bg-red-500/10' },
};

const fetchMembers = async (typeId, typeName, status) => {
    membersCategory.value = status;
    membersTypeName.value = typeName;
    membersList.value = [];
    showMembersModal.value = true;
    membersLoading.value = true;
    try {
        const res = await axios.get(route('contributions.monitoring.members-by-status', typeId), {
            params: { status },
        });
        membersList.value = res.data;
    } catch (e) {
        console.error(e);
    } finally {
        membersLoading.value = false;
    }
};

// ─── CRUD Jenis Iuran ────────────────────────────────────────────────
const periods = {
    once: 'Sekali Bayar',
    daily: 'Harian',
    weekly: 'Mingguan',
    monthly: 'Bulanan',
    yearly: 'Tahunan',
};

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
        onError: (errors) => {
            toast.error(Object.values(errors).flat().join(', ') || 'Gagal menghapus jenis iuran.');
            showDeleteDialog.value = false;
            deletingType.value = null;
        },
    });
};
</script>

<template>
    <Head title="Kelola Iuran" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-foreground">
                    Kelola Iuran
                </h2>
                <Button v-if="hasPermission('manage_contribution_types')" size="sm" @click="openCreate">
                    <Plus class="w-4 h-4 mr-1" />
                    <span class="hidden sm:inline">Tambah Jenis Iuran</span>
                </Button>
            </div>
        </template>

        <div class="py-4">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-4">

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="type in types"
                        :key="type.id"
                        class="group relative bg-card rounded-xl border shadow-sm overflow-hidden"
                        :class="{ 'opacity-50': !type.is_active }"
                    >
                        <!-- Gradient accent top -->
                        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-primary to-primary/60 opacity-0 group-hover:opacity-100 transition-opacity duration-300" />

                        <!-- Dropdown menu (edit/hapus) -->
                        <div v-if="hasPermission('manage_contribution_types')" class="absolute top-3 right-3 z-10">
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="ghost" size="icon" class="h-7 w-7 bg-card/80 backdrop-blur-sm shadow-sm border" @click.prevent.stop>
                                        <MoreVertical class="w-3.5 h-3.5" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-40">
                                    <DropdownMenuItem @click="openEdit(type)">
                                        <Pencil class="w-4 h-4 mr-2" />
                                        Edit
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem class="text-destructive" @click="confirmDelete(type)">
                                        <Trash2 class="w-4 h-4 mr-2" />
                                        Hapus
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>

                        <!-- Nonaktif badge -->
                        <div v-if="!type.is_active" class="absolute top-3 left-3 z-10">
                            <Badge variant="secondary" class="text-[10px] bg-muted text-muted-foreground">Nonaktif</Badge>
                        </div>

                        <Link
                            :href="route('contributions.monitoring.dashboard', type.id)"
                            class="block p-5 pb-3 hover:bg-muted/30 transition-colors"
                        >
                            <!-- Header -->
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 flex items-center justify-center bg-primary/10 text-primary rounded-xl group-hover:bg-primary group-hover:text-primary-foreground transition-colors duration-300 shrink-0">
                                        <Coins class="w-5 h-5" />
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="font-semibold text-foreground text-sm leading-tight group-hover:text-primary transition-colors truncate">
                                            {{ type.name }}
                                        </h3>
                                        <Badge variant="secondary" class="mt-1 text-[10px] uppercase tracking-wider">
                                            {{ type.period === 'once' ? 'Sekali Bayar' : type.period }}
                                        </Badge>
                                    </div>
                                </div>
                                <ChevronRight class="w-4 h-4 text-muted-foreground opacity-0 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all duration-300 shrink-0" />
                            </div>

                            <!-- Collected amount -->
                            <div class="bg-muted/50 rounded-lg p-3.5 mb-3">
                                <div class="flex justify-between items-center mb-1.5">
                                    <p class="text-[10px] font-semibold text-muted-foreground uppercase tracking-widest">Terkumpul</p>
                                    <span class="text-xs font-bold text-primary">
                                        {{ ((type.current_period_paid || 0) / (type.target_count || 1) * 100).toFixed(0) }}%
                                    </span>
                                </div>
                                <p class="text-xl font-extrabold text-foreground tracking-tight mb-2.5">
                                    {{ formatCurrency(type.collected_amount || 0).replace('Rp', '').trim() }}
                                    <span class="text-xs font-semibold text-muted-foreground">IDR</span>
                                </p>
                                <div class="w-full bg-muted rounded-full h-1.5 overflow-hidden">
                                    <div
                                        class="bg-gradient-to-r from-primary to-primary/70 h-1.5 rounded-full transition-all duration-500"
                                        :style="{ width: `${Math.min(((type.current_period_paid || 0) / (type.target_count || 1) * 100), 100)}%` }"
                                    />
                                </div>
                            </div>
                        </Link>

                        <!-- Stats list (compact vertical with percentage bars) -->
                        <div class="px-5 pb-4 pt-0 space-y-0.5">
                            <button @click.stop="fetchMembers(type.id, type.name, 'paid')" class="w-full flex items-center gap-2 py-1 rounded hover:bg-green-500/5 transition-colors cursor-pointer group/item">
                                <span class="w-1 h-3.5 rounded-full bg-green-500 shrink-0" />
                                <span class="text-[11px] text-muted-foreground group-hover/item:text-green-600 transition-colors">Lunas</span>
                                <div class="flex-1 bg-muted rounded-full h-1 overflow-hidden mx-1">
                                    <div class="bg-green-500 h-1 rounded-full transition-all duration-500" :style="{ width: `${Math.min(((type.lunas_count || 0) / (type.target_count || 1)) * 100, 100)}%` }" />
                                </div>
                                <span class="text-[11px] font-bold text-green-600 tabular-nums shrink-0">{{ type.lunas_count || 0 }}</span>
                            </button>
                            <button @click.stop="fetchMembers(type.id, type.name, 'unpaid')" class="w-full flex items-center gap-2 py-1 rounded hover:bg-amber-500/5 transition-colors cursor-pointer group/item">
                                <span class="w-1 h-3.5 rounded-full bg-amber-500 shrink-0" />
                                <span class="text-[11px] text-muted-foreground group-hover/item:text-amber-600 transition-colors">Belum</span>
                                <div class="flex-1 bg-muted rounded-full h-1 overflow-hidden mx-1">
                                    <div class="bg-amber-500 h-1 rounded-full transition-all duration-500" :style="{ width: `${Math.min(((type.belum_count || 0) / (type.target_count || 1)) * 100, 100)}%` }" />
                                </div>
                                <span class="text-[11px] font-bold text-amber-600 tabular-nums shrink-0">{{ type.belum_count || 0 }}</span>
                            </button>
                            <button @click.stop="fetchMembers(type.id, type.name, 'arrears')" class="w-full flex items-center gap-2 py-1 rounded hover:bg-red-500/5 transition-colors cursor-pointer group/item">
                                <span class="w-1 h-3.5 rounded-full bg-red-500 shrink-0" />
                                <span class="text-[11px] text-muted-foreground group-hover/item:text-red-600 transition-colors">Tunggak</span>
                                <div class="flex-1 bg-muted rounded-full h-1 overflow-hidden mx-1">
                                    <div class="bg-red-500 h-1 rounded-full transition-all duration-500" :style="{ width: `${Math.min(((type.tunggak_count || 0) / (type.target_count || 1)) * 100, 100)}%` }" />
                                </div>
                                <span class="text-[11px] font-bold text-red-600 tabular-nums shrink-0">{{ type.tunggak_count || 0 }}</span>
                            </button>
                            <div class="flex items-center gap-2 py-1">
                                <span class="w-1 h-3.5 rounded-full shrink-0" :class="(type.pending_count || 0) > 0 ? 'bg-blue-500' : 'bg-muted-foreground/30'" />
                                <span class="text-[11px] text-muted-foreground">Pending</span>
                                <div class="flex-1 bg-muted rounded-full h-1 overflow-hidden mx-1">
                                    <div class="h-1 rounded-full transition-all duration-500" :class="(type.pending_count || 0) > 0 ? 'bg-blue-500' : ''" :style="{ width: `${Math.min(((type.pending_count || 0) / (type.target_count || 1)) * 100, 100)}%` }" />
                                </div>
                                <span class="text-[11px] font-bold tabular-nums shrink-0" :class="(type.pending_count || 0) > 0 ? 'text-blue-600' : 'text-muted-foreground'">{{ type.pending_count || 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="types.length === 0" class="text-center py-12">
                    <Coins class="w-10 h-10 text-muted-foreground/50 mx-auto mb-3" />
                    <p class="text-sm text-muted-foreground mb-4">Belum ada jenis iuran.</p>
                    <Button v-if="hasPermission('manage_contribution_types')" size="sm" @click="openCreate">
                        <Plus class="w-4 h-4 mr-1" />
                        Tambah Jenis Iuran
                    </Button>
                </div>

            </div>
        </div>

        <!-- Members List Modal -->
        <Dialog v-model:open="showMembersModal">
            <DialogContent class="max-w-md max-h-[80vh] flex flex-col">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <div
                            class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0"
                            :class="categoryConfig[membersCategory]?.bg"
                        >
                            <component
                                :is="categoryConfig[membersCategory]?.icon"
                                class="w-4 h-4"
                                :class="categoryConfig[membersCategory]?.color"
                            />
                        </div>
                        Anggota {{ categoryConfig[membersCategory]?.label }}
                    </DialogTitle>
                    <DialogDescription>
                        Daftar anggota dengan status <strong>{{ categoryConfig[membersCategory]?.label?.toLowerCase() }}</strong> untuk iuran {{ membersTypeName }}.
                    </DialogDescription>
                </DialogHeader>

                <div v-if="membersLoading" class="py-8 flex flex-col items-center gap-2 text-muted-foreground">
                    <Loader2 class="w-5 h-5 animate-spin" />
                    <p class="text-xs">Memuat data...</p>
                </div>

                <div v-else-if="membersList.length > 0" class="flex-1 overflow-y-auto -mx-1 px-1">
                    <div class="space-y-1">
                        <div
                            v-for="(member, idx) in membersList"
                            :key="member.id"
                            class="flex items-center gap-3 p-2.5 rounded-lg hover:bg-muted/50 transition-colors"
                        >
                            <div class="w-8 h-8 rounded-full bg-primary/10 border flex items-center justify-center shrink-0">
                                <span class="text-xs font-bold text-primary">{{ (member.full_name || '?').charAt(0).toUpperCase() }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-foreground truncate">{{ member.full_name }}</p>
                                <p class="text-[10px] text-muted-foreground uppercase tracking-wider">{{ member.member_code }}</p>
                            </div>
                            <span class="text-xs text-muted-foreground tabular-nums shrink-0">{{ idx + 1 }}</span>
                        </div>
                    </div>
                </div>

                <div v-else class="py-8 text-center">
                    <div class="w-10 h-10 rounded-xl bg-muted flex items-center justify-center mx-auto mb-2">
                        <User class="w-5 h-5 text-muted-foreground" />
                    </div>
                    <p class="text-sm text-muted-foreground">Tidak ada anggota dalam kategori ini.</p>
                </div>

                <div v-if="!membersLoading && membersList.length > 0" class="pt-3 border-t mt-2">
                    <p class="text-xs text-muted-foreground text-center">
                        Total: <span class="font-bold text-foreground">{{ membersList.length }}</span> anggota
                    </p>
                </div>
            </DialogContent>
        </Dialog>

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
                        <div>
                            <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Nama Iuran</Label>
                            <Input type="text" class="mt-1.5 w-full" v-model="form.name" required placeholder="Contoh: Iuran Bulanan 2026" />
                            <p v-if="form.errors.name" class="mt-1 text-xs text-destructive">{{ form.errors.name }}</p>
                        </div>

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

                        <div>
                            <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Deskripsi <span class="normal-case font-normal">(Opsional)</span></Label>
                            <Textarea v-model="form.description" rows="2" class="mt-1.5" placeholder="Rincian mengenai iuran ini..." />
                        </div>

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

                        <div v-if="form.period === 'monthly'">
                            <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Tanggal Jatuh Tempo</Label>
                            <Input type="number" min="1" max="31" class="mt-1.5 w-full" v-model="form.recurring_day" placeholder="Contoh: 10 (Setiap tanggal 10)" />
                            <p v-if="form.errors.recurring_day" class="mt-1 text-xs text-destructive">{{ form.errors.recurring_day }}</p>
                        </div>

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

                        <div v-if="['once', 'yearly'].includes(form.period)">
                            <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Tanggal Jatuh Tempo</Label>
                            <Input type="date" class="mt-1.5 w-full" v-model="form.due_date" />
                            <p v-if="form.errors.due_date" class="mt-1 text-xs text-destructive">{{ form.errors.due_date }}</p>
                        </div>

                        <div class="flex items-center gap-3 p-3.5 rounded-xl bg-muted/50 border">
                            <Checkbox id="is_active_type" :checked="form.is_active" @update:checked="(val) => form.is_active = val" />
                            <div>
                                <Label for="is_active_type" class="text-sm font-semibold text-foreground cursor-pointer">Status Aktif</Label>
                                <p class="text-xs text-muted-foreground">Iuran nonaktif tidak bisa dipilih saat mencatat pembayaran.</p>
                            </div>
                        </div>
                    </div>

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
        <DeleteConfirmDialog
            :open="showDeleteDialog"
            title="Hapus Jenis Iuran?"
            :description="`Jenis iuran ${deletingType?.name} akan dihapus. Tindakan ini tidak bisa dibatalkan.`"
            @confirm="executeDelete"
            @cancel="showDeleteDialog = false; deletingType = null"
        />
    </AuthenticatedLayout>
</template>
