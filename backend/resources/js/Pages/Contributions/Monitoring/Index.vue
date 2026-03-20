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
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter, } from '@/components/ui/dialog';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue, } from '@/components/ui/select';
import {
    DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger, } from '@/components/ui/dropdown-menu';
import DeleteConfirmDialog from '@/Components/DeleteConfirmDialog.vue';
import {
    Coins, CheckCircle, Clock, ChevronRight, Users, AlertCircle, Loader2, User, Plus, Pencil, Trash2, MoreVertical, Settings2, PiggyBank } from 'lucide-vue-next';
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
<div class="flex items-center gap-2.5">
<PiggyBank class="w-5 h-5" />
<span>Kelola Iuran</span>
</div>
</h2>
                <Button v-if="hasPermission('manage_contribution_types')" size="sm" @click="openCreate">
                    <Plus class="w-4 h-4 mr-1" />
                    <span class="hidden sm:inline">Tambah Jenis Iuran</span>
                </Button>
            </div>
        </template>

        <div class="py-3 sm:py-5">
            <div class="mx-auto max-w-7xl px-3 sm:px-6 lg:px-8 space-y-3">

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="type in types"
                        :key="type.id"
                        class="group relative bg-card rounded-xl border overflow-hidden flex flex-col transition-all duration-200 hover:shadow-md"
                        :class="{ 'opacity-60': !type.is_active }"
                    >
                        <!-- Accent bar — always visible, primary if active, muted if not -->
                        <div
                            class="h-1 w-full shrink-0 transition-all duration-200"
                            :class="type.is_active ? 'bg-gradient-to-r from-primary via-primary/80 to-primary/40' : 'bg-muted'"
                        />

                        <!-- Dropdown menu (edit/hapus) -->
                        <div v-if="hasPermission('manage_contribution_types')" class="absolute top-2.5 right-2.5 z-10">
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="ghost" size="icon" class="h-6 w-6 bg-card/90 shadow-sm border" @click.prevent.stop>
                                        <MoreVertical class="w-3 h-3" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-36">
                                    <DropdownMenuItem @click="openEdit(type)">
                                        <Pencil class="w-3.5 h-3.5 mr-2" />
                                        Edit
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem class="text-destructive" @click="confirmDelete(type)">
                                        <Trash2 class="w-3.5 h-3.5 mr-2" />
                                        Hapus
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>

                        <Link
                            :href="route('contributions.monitoring.dashboard', type.id)"
                            class="flex-1 hover:bg-muted/20 transition-colors"
                        >
                            <!-- Mobile: horizontal layout -->
                            <div class="flex items-center gap-3 p-3 sm:hidden">
                                <div
                                    class="w-10 h-10 flex items-center justify-center rounded-xl shrink-0 transition-colors duration-200"
                                    :class="type.is_active ? 'bg-primary/10 text-primary group-hover:bg-primary group-hover:text-primary-foreground' : 'bg-muted text-muted-foreground'"
                                >
                                    <Coins class="w-5 h-5" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-foreground text-sm leading-tight truncate group-hover:text-primary transition-colors">{{ type.name }}</h3>
                                    <div class="flex items-center gap-1 mt-0.5">
                                        <span class="text-[10px] font-semibold uppercase tracking-wide px-1.5 py-px rounded"
                                            :class="type.is_active ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-muted text-muted-foreground'"
                                        >{{ type.is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                        <span class="text-[10px] text-muted-foreground uppercase">{{ type.period === 'once' ? 'Sekali' : type.period }}</span>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-sm font-extrabold text-foreground tabular-nums leading-tight">
                                        {{ formatCurrency(type.collected_amount || 0).replace('Rp', '').trim() }}
                                    </p>
                                    <span
                                        class="text-[11px] font-bold tabular-nums"
                                        :class="((type.current_period_paid || 0) / (type.target_count || 1)) >= 1 ? 'text-green-600' : 'text-primary'"
                                    >{{ ((type.current_period_paid || 0) / (type.target_count || 1) * 100).toFixed(0) }}%</span>
                                </div>
                            </div>
                            <!-- Mobile: progress bar -->
                            <div class="px-3 pb-2 sm:hidden">
                                <div class="w-full bg-muted rounded-full h-1.5 overflow-hidden">
                                    <div
                                        class="h-1.5 rounded-full transition-all duration-500"
                                        :class="((type.current_period_paid || 0) / (type.target_count || 1)) >= 1 ? 'bg-green-500' : 'bg-gradient-to-r from-primary to-primary/70'"
                                        :style="{ width: `${Math.min(((type.current_period_paid || 0) / (type.target_count || 1) * 100), 100)}%` }"
                                    />
                                </div>
                            </div>

                            <!-- Desktop: vertical layout -->
                            <div class="hidden sm:block p-4">
                                <!-- Header -->
                                <div class="flex items-start gap-2.5 mb-3 pr-5">
                                    <div
                                        class="w-9 h-9 flex items-center justify-center rounded-lg shrink-0 transition-colors duration-200"
                                        :class="type.is_active ? 'bg-primary/10 text-primary group-hover:bg-primary group-hover:text-primary-foreground' : 'bg-muted text-muted-foreground'"
                                    >
                                        <Coins class="w-4 h-4" />
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h3 class="font-semibold text-foreground text-sm leading-tight truncate group-hover:text-primary transition-colors">{{ type.name }}</h3>
                                        <div class="flex items-center gap-1 mt-0.5">
                                            <span class="text-[10px] font-semibold uppercase tracking-wide px-1.5 py-px rounded"
                                                :class="type.is_active ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-muted text-muted-foreground'"
                                            >{{ type.is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                            <span class="text-[10px] text-muted-foreground uppercase">{{ type.period === 'once' ? 'Sekali' : type.period }}</span>
                                        </div>
                                    </div>
                                </div>
                                <!-- Nominal -->
                                <div class="mb-2.5">
                                    <div class="flex items-end justify-between gap-1 mb-1">
                                        <p class="text-xl font-extrabold text-foreground tabular-nums leading-tight tracking-tight truncate">
                                            {{ formatCurrency(type.collected_amount || 0).replace('Rp', '').trim() }}
                                            <span class="text-[10px] font-medium text-muted-foreground ml-0.5">IDR</span>
                                        </p>
                                        <span
                                            class="text-xs font-bold shrink-0 tabular-nums"
                                            :class="((type.current_period_paid || 0) / (type.target_count || 1)) >= 1 ? 'text-green-600' : 'text-primary'"
                                        >{{ ((type.current_period_paid || 0) / (type.target_count || 1) * 100).toFixed(0) }}%</span>
                                    </div>
                                    <div class="w-full bg-muted rounded-full h-2 overflow-hidden">
                                        <div
                                            class="h-2 rounded-full transition-all duration-500"
                                            :class="((type.current_period_paid || 0) / (type.target_count || 1)) >= 1 ? 'bg-green-500' : 'bg-gradient-to-r from-primary to-primary/70'"
                                            :style="{ width: `${Math.min(((type.current_period_paid || 0) / (type.target_count || 1) * 100), 100)}%` }"
                                        />
                                    </div>
                                </div>
                            </div>
                        </Link>

                        <!-- Stats row: Lunas / Belum / Tunggak / Pending -->
                        <div class="px-3 sm:px-4 pb-3 grid grid-cols-4 gap-1 border-t pt-2">
                            <button
                                @click.stop="fetchMembers(type.id, type.name, 'paid')"
                                class="flex flex-col items-center gap-0.5 py-1 rounded hover:bg-green-500/10 transition-colors"
                            >
                                <span class="text-sm sm:text-base font-extrabold text-green-600 tabular-nums leading-none">{{ type.lunas_count || 0 }}</span>
                                <span class="text-[9px] text-green-600/70 font-medium leading-tight">Lunas</span>
                            </button>
                            <button
                                @click.stop="fetchMembers(type.id, type.name, 'unpaid')"
                                class="flex flex-col items-center gap-0.5 py-1 rounded hover:bg-amber-500/10 transition-colors"
                            >
                                <span class="text-sm sm:text-base font-extrabold text-amber-600 tabular-nums leading-none">{{ type.belum_count || 0 }}</span>
                                <span class="text-[9px] text-amber-600/70 font-medium leading-tight">Belum</span>
                            </button>
                            <button
                                @click.stop="fetchMembers(type.id, type.name, 'arrears')"
                                class="flex flex-col items-center gap-0.5 py-1 rounded hover:bg-red-500/10 transition-colors"
                            >
                                <span class="text-sm sm:text-base font-extrabold text-red-600 tabular-nums leading-none">{{ type.tunggak_count || 0 }}</span>
                                <span class="text-[9px] text-red-600/70 font-medium leading-tight">Tunggak</span>
                            </button>
                            <div class="flex flex-col items-center gap-0.5 py-1">
                                <span
                                    class="text-sm sm:text-base font-extrabold tabular-nums leading-none"
                                    :class="(type.pending_count || 0) > 0 ? 'text-blue-600' : 'text-muted-foreground'"
                                >{{ type.pending_count || 0 }}</span>
                                <span class="text-[9px] text-muted-foreground font-medium leading-tight">Pending</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="types.length === 0" class="bg-card border rounded-xl text-center py-12">
                    <div class="w-12 h-12 rounded-full bg-muted flex items-center justify-center mx-auto mb-3">
                        <Coins class="w-6 h-6 text-muted-foreground/50" />
                    </div>
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
