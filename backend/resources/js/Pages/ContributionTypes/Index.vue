<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter,
} from '@/components/ui/dialog';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { Plus } from 'lucide-vue-next';

const props = defineProps({
    types: Array,
    wallets: Array,
});

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

const openCreateModal = () => {
    editingType.value = null;
    form.reset();
    showModal.value = true;
};

const openEditModal = (type) => {
    editingType.value = type;
    form.name = type.name;
    form.wallet_id = type.wallet_id;
    form.amount = type.amount;
    form.period = type.period;
    form.description = type.description;
    form.is_active = !!type.is_active;
    form.start_date = type.start_date;
    form.end_date = type.end_date;
    form.due_date = type.due_date;
    form.recurring_day = type.recurring_day;
    showModal.value = true;
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

const closeModal = () => {
    showModal.value = false;
    form.reset();
    editingType.value = null;
};

const deleteType = (type) => {
    if (confirm(`Apakah Anda yakin ingin menghapus jenis iuran ${type.name}?`)) {
        form.delete(route('contribution-types.destroy', type.id));
    }
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(amount);
};

const periods = {
    once: 'Sekali',
    daily: 'Harian',
    weekly: 'Mingguan',
    monthly: 'Bulanan',
    yearly: 'Tahunan',
};

const columns = [
    {
        key: 'name',
        label: 'Nama Iuran',
        sortable: true,
        format: (value, row) => {
            return {
                type: 'custom',
                main: value,
                sub: row.description || '-'
            };
        },
    },
    {
        key: 'wallet',
        label: 'Dompet Tujuan',
        sortable: true,
        format: (value) => {
            if (!value) return { type: 'badge', value: 'Belum diatur', props: { variant: 'destructive', class: 'italic' } };
            return { type: 'badge', value: value.name, props: { variant: 'default', class: 'font-bold' } };
        },
    },
    {
        key: 'amount',
        label: 'Besaran',
        sortable: true,
        format: (value) => formatCurrency(value),
    },
    {
        key: 'period',
        label: 'Periode',
        sortable: true,
        format: (value) => periods[value] || value,
    },
    {
        key: 'is_active',
        label: 'Status',
        sortable: true,
        type: 'badge',
        badgeVariant: (value) => ({ variant: value ? 'success' : 'secondary' }),
        badgeLabel: (value) => value ? 'Aktif' : 'Tidak Aktif',
    },
];

const handleAction = ({ action, row }) => {
    if (action === 'edit') {
        openEditModal(row);
    } else if (action === 'delete') {
        deleteType(row);
    }
};
</script>

<template>
    <Head title="Jenis Iuran" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold leading-tight text-foreground">Jenis Iuran</h2>
                <Button size="sm" @click="openCreateModal" v-if="hasPermission('manage_contribution_types')">
                    Tambah Jenis
                </Button>
            </div>
        </template>

        <div class="py-4 sm:py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="bg-card border rounded-xl overflow-hidden">
                    <!-- ContributionTypes Table using DataTable Component -->
                    <DataTable
                        :data="{ data: types }"
                        :columns="columns"
                        :sortable="true"
                        :show-column-filter="true"
                        :striped="true"
                        :searchable="false"
                        @action="handleAction"
                    >
                        <template #cell-name="{ row }">
                            <div>
                                <div class="text-sm font-bold text-foreground">{{ row.name }}</div>
                                <div class="text-xs text-muted-foreground">{{ row.description || '-' }}</div>
                            </div>
                        </template>

                        <template #cell-wallet="{ row }">
                            <span v-if="row.wallet" class="px-2 py-1 bg-primary/10 text-primary rounded text-xs font-bold">
                                {{ row.wallet.name }}
                            </span>
                            <span v-else class="text-xs text-destructive italic">Belum diatur</span>
                        </template>

                        <template #actions="{ row }">
                            <div class="flex justify-end gap-2">
                                <Button 
                                    variant="ghost" 
                                    size="sm"
                                    @click="openEditModal(row)"
                                >
                                    Edit
                                </Button>
                                <Button 
                                    variant="ghost" 
                                    size="sm"
                                    class="text-destructive"
                                    @click="deleteType(row)"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </Button>
                            </div>
                        </template>
                    </DataTable>
                </div>
            </div>
        </div>

        <!-- Dialog Create/Edit -->
        <Dialog :open="showModal" @update:open="(val) => { if (!val) closeModal(); }">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {{ editingType ? 'Edit Jenis Iuran' : 'Tambah Jenis Iuran Baru' }}
                    </DialogTitle>
                </DialogHeader>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <Label class="text-[10px] font-bold uppercase text-muted-foreground">Nama Iuran</Label>
                        <Input type="text" class="w-full" v-model="form.name" required placeholder="Contoh: Iuran Bulanan 2026" />
                        <p v-if="form.errors.name" class="mt-2 text-sm text-destructive">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <Label class="text-[10px] font-bold uppercase text-muted-foreground">Dompet Tujuan</Label>
                        <Select v-model="form.wallet_id" required>
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Pilih Dompet" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="wallet in wallets" :key="wallet.id" :value="wallet.id.toString()">
                                    {{ wallet.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.wallet_id" class="mt-2 text-sm text-destructive">{{ form.errors.wallet_id }}</p>
                        <p class="mt-1 text-xs text-muted-foreground italic">Iuran yang dibayar akan masuk ke dompet ini secara otomatis.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <Label class="text-[10px] font-bold uppercase text-muted-foreground">Besaran (Rp)</Label>
                            <Input type="number" class="w-full" v-model="form.amount" required placeholder="0" />
                            <p v-if="form.errors.amount" class="mt-2 text-sm text-destructive">{{ form.errors.amount }}</p>
                        </div>
                        <div>
                            <Label class="text-[10px] font-bold uppercase text-muted-foreground">Periode Pembayaran</Label>
                            <Select v-model="form.period" required>
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Pilih Periode" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="(label, value) in periods" :key="value" :value="value">{{ label }}</SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.period" class="mt-2 text-sm text-destructive">{{ form.errors.period }}</p>
                        </div>
                    </div>

                    <div>
                        <Label class="text-[10px] font-bold uppercase text-muted-foreground">Deskripsi (Opsional)</Label>
                        <Textarea v-model="form.description" rows="3" placeholder="Rincian mengenai iuran ini..." />
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                             <Label class="text-[10px] font-bold uppercase text-muted-foreground">Tanggal Mulai (Opsional)</Label>
                             <Input type="date" class="w-full" v-model="form.start_date" />
                             <p v-if="form.errors.start_date" class="mt-2 text-sm text-destructive">{{ form.errors.start_date }}</p>
                        </div>
                        <div>
                             <Label class="text-[10px] font-bold uppercase text-muted-foreground">Batas Akhir (Opsional)</Label>
                             <Input type="date" class="w-full" v-model="form.end_date" />
                             <p v-if="form.errors.end_date" class="mt-2 text-sm text-destructive">{{ form.errors.end_date }}</p>
                        </div>
                    </div>

                    <div v-if="form.period === 'monthly'">
                         <Label class="text-[10px] font-bold uppercase text-muted-foreground">Tanggal Jatuh Tempo (Harian)</Label>
                         <Input type="number" min="1" max="31" class="w-full" v-model="form.recurring_day" placeholder="Contoh: 10 (Setiap tanggal 10)" />
                         <p v-if="form.errors.recurring_day" class="mt-2 text-sm text-destructive">{{ form.errors.recurring_day }}</p>
                    </div>

                     <div v-if="form.period === 'weekly'">
                         <Label class="text-[10px] font-bold uppercase text-muted-foreground">Jadwal (Hari)</Label>
                         <Select v-model="form.recurring_day">
                            <SelectTrigger class="w-full">
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
                         <p v-if="form.errors.recurring_day" class="mt-2 text-sm text-destructive">{{ form.errors.recurring_day }}</p>
                    </div>

                     <div v-if="['once', 'yearly'].includes(form.period)">
                         <Label class="text-[10px] font-bold uppercase text-muted-foreground">Tanggal Jatuh Tempo</Label>
                         <Input type="date" class="w-full" v-model="form.due_date" />
                         <p v-if="form.errors.due_date" class="mt-2 text-sm text-destructive">{{ form.errors.due_date }}</p>
                    </div>

                    <div class="flex items-center space-x-2 bg-muted p-4 rounded-xl border">
                        <Checkbox id="is_active_type" :checked="form.is_active" @update:checked="(val) => form.is_active = val" />
                        <div class="ml-3">
                            <Label for="is_active_type" class="text-sm font-bold text-foreground uppercase tracking-tight">Status Aktif</Label>
                            <p class="text-xs text-muted-foreground">Iuran nonaktif tidak bisa dipilih saat mencatat pembayaran baru.</p>
                        </div>
                    </div>

                    <DialogFooter>
                        <Button variant="outline" type="button" @click="closeModal">Batal</Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ editingType ? 'Simpan Perubahan' : 'Buat Jenis Iuran' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AuthenticatedLayout>
</template>
