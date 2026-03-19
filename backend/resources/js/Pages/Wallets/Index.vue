<script setup>
import { ref, reactive, computed } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputError from "@/Components/InputError.vue";
import { Button } from "@/components/ui/button";
import { Label } from "@/components/ui/label";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import {
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter,
} from "@/components/ui/dialog";
import { Checkbox } from "@/components/ui/checkbox";
import { Textarea } from "@/components/ui/textarea";
import {
    DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
    Plus, Pencil, Trash2, Wallet, TrendingUp, TrendingDown, ArrowRight,
    MoreVertical, Eye, CreditCard, Save, Banknote, FileText, ToggleLeft,
} from "lucide-vue-next";
import DeleteConfirmDialog from '@/Components/DeleteConfirmDialog.vue';
import { useToast } from '@/composables/useToast';

const toast = useToast();

const props = defineProps({
    wallets: Array,
    stats: Object,
});

const showModal = ref(false);
const isEditing = ref(false);

const form = useForm({
    id: null,
    name: "",
    description: "",
    balance: 0,
    is_active: true,
});

const clientErrors = reactive({
    name: "",
});

const touched = reactive({
    name: false,
});

const validateName = () => {
    if (!form.name.trim()) {
        clientErrors.name = "Nama dompet harus diisi.";
    } else if (form.name.trim().length > 255) {
        clientErrors.name = "Nama dompet maksimal 255 karakter.";
    } else {
        clientErrors.name = "";
    }
};

const balanceDisplay = ref("0");

const formatNumber = (num) => {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
};

const onBalanceInput = (e) => {
    const raw = e.target.value.replace(/\./g, "");
    const num = parseInt(raw, 10);
    if (isNaN(num)) {
        balanceDisplay.value = "";
        form.balance = 0;
    } else {
        balanceDisplay.value = formatNumber(num);
        form.balance = num;
    }
};

const openModal = (wallet = null) => {
    clientErrors.name = "";
    touched.name = false;
    if (wallet) {
        isEditing.value = true;
        form.id = wallet.id;
        form.name = wallet.name;
        form.description = wallet.description;
        form.balance = wallet.balance;
        balanceDisplay.value = formatNumber(wallet.balance || 0);
        form.is_active = !!wallet.is_active;
    } else {
        isEditing.value = false;
        form.reset();
        form.balance = 0;
        balanceDisplay.value = "0";
        form.is_active = true;
    }
    showModal.value = true;
};

const submit = () => {
    touched.name = true;
    validateName();
    if (clientErrors.name) return;

    if (isEditing.value) {
        form.put(route("wallets.update", form.id), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    } else {
        form.post(route("wallets.store"), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    }
};

const deleteTarget = ref(null);
const deleteWallet = (wallet) => {
    deleteTarget.value = wallet;
};
const confirmDeleteWallet = () => {
    if (deleteTarget.value) {
        form.delete(route("wallets.destroy", deleteTarget.value.id), {
            onError: (errors) => toast.error(Object.values(errors).flat().join(', ') || 'Gagal menghapus dompet.'),
            onFinish: () => (deleteTarget.value = null),
        });
    }
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

const cardGradients = [
    "from-cyan-500 via-blue-600 to-blue-800",
    "from-violet-500 via-purple-600 to-purple-800",
    "from-emerald-500 via-green-600 to-green-800",
    "from-orange-500 via-amber-600 to-amber-800",
    "from-rose-500 via-pink-600 to-pink-800",
];

const getGradient = (index) => cardGradients[index % cardGradients.length];
</script>

<template>
    <Head title="Kas & Dompet" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold leading-tight text-foreground">Kas & Dompet</h2>
                <Button
                    v-if="$page.props.auth.user.role === 'admin' || $page.props.auth.user.role === 'bendahara'"
                    size="sm"
                    @click="openModal()"
                >
                    <Plus class="w-4 h-4 mr-1" />
                    <span class="hidden sm:inline">Tambah Dompet</span>
                </Button>
            </div>
        </template>

        <div class="py-4 sm:py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <!-- Summary Stats -->
                <div class="grid grid-cols-3 gap-3 mb-6">
                    <div class="bg-card rounded-xl border p-3 sm:p-4">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-7 h-7 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                                <Wallet class="w-3.5 h-3.5 text-primary" />
                            </div>
                            <p class="text-[10px] font-bold uppercase text-muted-foreground">Total Saldo</p>
                        </div>
                        <p class="text-sm sm:text-lg font-bold text-foreground">{{ formatCurrency(stats.total_balance) }}</p>
                    </div>
                    <div class="bg-card rounded-xl border p-3 sm:p-4">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-7 h-7 rounded-lg bg-green-500/10 flex items-center justify-center shrink-0">
                                <TrendingUp class="w-3.5 h-3.5 text-green-500" />
                            </div>
                            <p class="text-[10px] font-bold uppercase text-muted-foreground">Masuk</p>
                        </div>
                        <p class="text-sm sm:text-lg font-bold text-green-600">{{ formatCurrency(stats.total_income) }}</p>
                    </div>
                    <div class="bg-card rounded-xl border p-3 sm:p-4">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-7 h-7 rounded-lg bg-red-500/10 flex items-center justify-center shrink-0">
                                <TrendingDown class="w-3.5 h-3.5 text-red-500" />
                            </div>
                            <p class="text-[10px] font-bold uppercase text-muted-foreground">Keluar</p>
                        </div>
                        <p class="text-sm sm:text-lg font-bold text-destructive">{{ formatCurrency(stats.total_expense) }}</p>
                    </div>
                </div>

                <!-- Wallet Cards -->
                <div v-if="wallets.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div
                        v-for="(wallet, index) in wallets"
                        :key="wallet.id"
                        class="group relative"
                    >
                        <!-- Card -->
                        <Link
                            :href="route('wallets.show', wallet.id)"
                            class="block relative w-full rounded-2xl overflow-hidden text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5"
                            :class="[
                                wallet.is_active
                                    ? `bg-gradient-to-br ${getGradient(index)}`
                                    : 'bg-gradient-to-br from-gray-400 via-gray-500 to-gray-600'
                            ]"
                        >
                            <!-- BG pattern -->
                            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 50% -20%, rgba(255,255,255,0.8), transparent 50%);"></div>
                            <div class="absolute top-0 right-0 -mr-12 -mt-12 w-48 h-48 rounded-full bg-white opacity-5 blur-3xl"></div>

                            <div class="relative z-10 p-5">
                                <!-- Top -->
                                <div class="flex items-start justify-between mb-6">
                                    <div class="min-w-0">
                                        <h3 class="font-bold text-base tracking-wide truncate" :title="wallet.name">
                                            {{ wallet.name }}
                                        </h3>
                                        <p v-if="wallet.description" class="text-[11px] text-white/60 truncate mt-0.5 max-w-[180px]">
                                            {{ wallet.description }}
                                        </p>
                                    </div>
                                    <Badge v-if="!wallet.is_active" variant="secondary" class="text-[10px] shrink-0 bg-white/20 text-white border-0">
                                        Nonaktif
                                    </Badge>
                                    <CreditCard v-else class="w-6 h-6 opacity-50 shrink-0" />
                                </div>

                                <!-- Balance -->
                                <div class="mb-5">
                                    <p class="text-[10px] uppercase tracking-widest text-white/60 mb-0.5">Saldo</p>
                                    <p class="text-xl font-bold font-mono tracking-wide">{{ formatCurrency(wallet.balance) }}</p>
                                </div>

                                <!-- Bottom -->
                                <div class="flex items-end justify-between">
                                    <div class="flex gap-4">
                                        <div>
                                            <p class="text-[9px] uppercase text-white/50">Masuk</p>
                                            <p class="text-xs font-mono font-semibold text-green-200">+{{ formatCurrency(wallet.total_income || 0) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[9px] uppercase text-white/50">Keluar</p>
                                            <p class="text-xs font-mono font-semibold text-red-200">-{{ formatCurrency(wallet.total_expense || 0) }}</p>
                                        </div>
                                    </div>
                                    <!-- Chip -->
                                    <div class="w-10 h-7 bg-yellow-200/80 rounded-sm relative overflow-hidden shadow-sm">
                                        <div class="absolute inset-0 border-[0.5px] border-yellow-600/30 rounded-sm"></div>
                                        <div class="w-full h-px bg-yellow-600/30 absolute top-1/3"></div>
                                        <div class="w-full h-px bg-yellow-600/30 absolute bottom-1/3"></div>
                                        <div class="h-full w-px bg-yellow-600/30 absolute left-1/3"></div>
                                        <div class="h-full w-px bg-yellow-600/30 absolute right-1/3"></div>
                                    </div>
                                </div>
                            </div>
                        </Link>

                        <!-- Stats + Actions below card -->
                        <div class="flex items-center justify-between mt-2.5 px-1">
                            <div class="flex items-center gap-3 text-[11px] text-muted-foreground">
                                <span>{{ wallet.finances_count || 0 }} transaksi</span>
                                <span>{{ wallet.contributions_count || 0 }} iuran</span>
                            </div>
                            <DropdownMenu v-if="$page.props.auth.user.role === 'admin' || $page.props.auth.user.role === 'bendahara'">
                                <DropdownMenuTrigger class="p-1 rounded-md text-muted-foreground hover:text-foreground hover:bg-muted transition-colors">
                                    <MoreVertical class="w-4 h-4" />
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuItem as-child>
                                        <Link :href="route('wallets.show', wallet.id)" class="flex items-center gap-2">
                                            <Eye class="w-4 h-4" />
                                            Detail
                                        </Link>
                                    </DropdownMenuItem>
                                    <DropdownMenuItem @click="openModal(wallet)" class="flex items-center gap-2">
                                        <Pencil class="w-4 h-4" />
                                        Edit
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem @click="deleteWallet(wallet)" class="text-destructive flex items-center gap-2">
                                        <Trash2 class="w-4 h-4" />
                                        Hapus
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="flex flex-col items-center justify-center py-16 text-center bg-card rounded-xl border">
                    <div class="w-16 h-16 rounded-2xl bg-muted flex items-center justify-center mb-4">
                        <Wallet class="w-8 h-8 text-muted-foreground" />
                    </div>
                    <p class="text-sm font-medium text-foreground mb-1">Belum Ada Dompet</p>
                    <p class="text-xs text-muted-foreground mb-4">Mulai dengan membuat dompet untuk mengelola dana organisasi.</p>
                    <Button @click="openModal()">
                        <Plus class="w-4 h-4 mr-1" />
                        Buat Dompet Pertama
                    </Button>
                </div>
            </div>
        </div>

        <DeleteConfirmDialog
            :open="!!deleteTarget"
            title="Hapus Dompet"
            description="Apakah Anda yakin ingin menghapus dompet ini? Semua transaksi terkait juga akan dihapus. Tindakan ini tidak dapat dibatalkan."
            @confirm="confirmDeleteWallet"
            @cancel="deleteTarget = null"
        />

        <!-- Dialog Create/Edit -->
        <Dialog v-model:open="showModal">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{{ isEditing ? "Edit Dompet" : "Buat Dompet Baru" }}</DialogTitle>
                    <DialogDescription>{{ isEditing ? "Ubah informasi dompet." : "Isi informasi untuk membuat dompet baru." }}</DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <Label for="name" class="flex items-center gap-1.5">
                            <Wallet class="w-3.5 h-3.5 text-primary" />
                            Nama Dompet
                            <span class="text-destructive">*</span>
                        </Label>
                        <Input
                            id="name"
                            type="text"
                            class="mt-1.5 block w-full"
                            v-model="form.name"
                            placeholder="Contoh: Kas Utama, Dana Sosial"
                            @blur="touched.name = true; validateName()"
                            @input="touched.name && validateName()"
                        />
                        <InputError class="mt-1.5" :message="clientErrors.name || form.errors.name" />
                    </div>

                    <div>
                        <Label for="description" class="flex items-center gap-1.5">
                            <FileText class="w-3.5 h-3.5 text-primary" />
                            Deskripsi
                        </Label>
                        <p class="text-[10px] text-muted-foreground">Opsional</p>
                        <Textarea
                            id="description"
                            rows="2"
                            class="mt-1 block w-full"
                            v-model="form.description"
                            placeholder="Jelaskan kegunaan dompet ini..."
                        />
                        <InputError class="mt-1.5" :message="form.errors.description" />
                    </div>

                    <div v-if="!isEditing">
                        <Label for="balance" class="flex items-center gap-1.5">
                            <Banknote class="w-3.5 h-3.5 text-primary" />
                            Saldo Awal
                        </Label>
                        <div class="relative mt-1.5">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground font-medium">Rp</span>
                            <Input
                                id="balance"
                                type="text"
                                inputmode="numeric"
                                class="block w-full pl-10"
                                :value="balanceDisplay"
                                @input="onBalanceInput"
                            />
                        </div>
                        <p class="mt-1 text-[10px] text-muted-foreground">Saldo awal hanya bisa diatur saat pembuatan dompet.</p>
                        <InputError class="mt-1.5" :message="form.errors.balance" />
                    </div>

                    <div class="flex items-center gap-3 bg-muted/50 p-3 rounded-lg border">
                        <Checkbox
                            id="is_active"
                            :checked="form.is_active"
                            @update:checked="(val) => form.is_active = val"
                        />
                        <div>
                            <Label for="is_active" class="flex items-center gap-1.5 text-sm font-medium text-foreground">
                                <ToggleLeft class="w-3.5 h-3.5 text-primary" />
                                Status Aktif
                            </Label>
                            <p class="text-[10px] text-muted-foreground">Dompet nonaktif tidak akan muncul di pilihan transaksi baru.</p>
                        </div>
                    </div>

                    <DialogFooter>
                        <Button variant="outline" type="button" @click="showModal = false">Batal</Button>
                        <Button type="submit" :disabled="form.processing">
                            <Save class="w-3.5 h-3.5 mr-1.5" />
                            {{ form.processing ? 'Menyimpan...' : (isEditing ? 'Simpan Perubahan' : 'Buat Dompet') }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AuthenticatedLayout>
</template>
