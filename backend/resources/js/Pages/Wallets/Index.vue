<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import {
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter,
} from '@/components/ui/dialog';
import { Checkbox } from '@/components/ui/checkbox';
import { Textarea } from '@/components/ui/textarea';
import { Plus, Pencil } from 'lucide-vue-next';

const props = defineProps({
    wallets: Array,
    stats: Object,
});

const showModal = ref(false);
const isEditing = ref(false);

const form = useForm({
    id: null,
    name: '',
    description: '',
    balance: 0,
    is_active: true,
});

const openModal = (wallet = null) => {
    if (wallet) {
        isEditing.value = true;
        form.id = wallet.id;
        form.name = wallet.name;
        form.description = wallet.description;
        form.balance = wallet.balance;
        form.is_active = !!wallet.is_active;
    } else {
        isEditing.value = false;
        form.reset();
        form.balance = 0;
        form.is_active = true;
    }
    showModal.value = true;
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('wallets.update', form.id), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    } else {
        form.post(route('wallets.store'), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    }
};

const deleteWallet = (wallet) => {
    if (confirm(`Apakah Anda yakin ingin menghapus dompet ${wallet.name}?`)) {
        form.delete(route('wallets.destroy', wallet.id));
    }
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(amount);
};
</script>

<template>
    <Head title="Kas & Dompet" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold leading-tight text-foreground">Kas & Dompet</h2>
                <Button size="sm" @click="openModal()" v-if="$page.props.auth.user.role === 'admin' || $page.props.auth.user.role === 'bendahara'">
                    Tambah Dompet
                </Button>
            </div>
        </template>

        <div class="py-4 sm:py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Compact Summary Row -->
                <div class="grid grid-cols-3 gap-3 mb-4">
                    <div class="bg-card p-3 sm:p-4 rounded-xl border">
                        <p class="text-[10px] font-bold uppercase text-muted-foreground">Saldo</p>
                        <p class="text-base sm:text-lg font-bold text-foreground">{{ formatCurrency(stats.total_balance) }}</p>
                    </div>
                    <div class="bg-card p-3 sm:p-4 rounded-xl border">
                        <p class="text-[10px] font-bold uppercase text-muted-foreground">Masuk</p>
                        <p class="text-base sm:text-lg font-bold text-success-700">{{ formatCurrency(stats.total_income) }}</p>
                    </div>
                    <div class="bg-card p-3 sm:p-4 rounded-xl border">
                        <p class="text-[10px] font-bold uppercase text-muted-foreground">Keluar</p>
                        <p class="text-base sm:text-lg font-bold text-destructive">{{ formatCurrency(stats.total_expense) }}</p>
                    </div>
                </div>

                <div v-if="wallets.length > 0" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="(wallet, index) in wallets" :key="wallet.id" class="group relative perspective-1000">
                        <!-- Credit Card Container -->
                        <Link :href="route('wallets.show', wallet.id)" class="block relative w-full aspect-[1.586/1] rounded-2xl shadow-xl transition-all duration-500 transform group-hover:scale-[1.02] overflow-hidden text-white cursor-pointer"
                             :class="[
                                wallet.is_active 
                                    ? 'bg-gradient-to-br from-cyan-500 via-blue-600 to-blue-800' 
                                    : 'bg-gradient-to-br from-muted-foreground via-muted-foreground to-foreground/90 grayscale'
                             ]">
                            
                            <!-- Detailed background patterns for realism -->
                            <div class="absolute inset-0 opacity-10" 
                                 style="background-image: radial-gradient(circle at 50% -20%, rgba(255,255,255,0.8), transparent 50%);">
                            </div>
                            <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-card opacity-5 blur-3xl"></div>
                            <div class="absolute bottom-0 left-0 -ml-16 -mb-16 w-64 h-64 rounded-full bg-black opacity-10 blur-3xl"></div>

                            <div class="absolute inset-0 p-6 flex flex-col justify-between z-10">
                                <!-- Top Row: Name and Contactless Icon -->
                                <div class="flex justify-between items-start">
                                    <h3 class="font-bold text-lg tracking-wider uppercase truncate max-w-[70%] font-mono" :title="wallet.name">
                                        {{ wallet.name }}
                                    </h3>
                                    <!-- Contactless Icon -->
                                    <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                                    </svg>
                                </div>

                                <!-- Middle Row: Chip -->
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-9 bg-yellow-200 rounded-md relative overflow-hidden shadow-sm flex items-center justify-center opacity-90">
                                        <div class="absolute inset-0 border-[0.5px] border-yellow-600 opacity-40 rounded-md"></div>
                                        <div class="w-full h-[1px] bg-warning-600 opacity-40 absolute top-1/3"></div>
                                        <div class="w-full h-[1px] bg-warning-600 opacity-40 absolute bottom-1/3"></div>
                                        <div class="h-full w-[1px] bg-warning-600 opacity-40 absolute left-1/3"></div>
                                        <div class="h-full w-[1px] bg-warning-600 opacity-40 absolute right-1/3"></div>
                                    </div>
                                    <!-- Signal/WiFi Icon (Access Indicator) -->
                                    <svg v-if="wallet.is_active" class="w-5 h-5 opacity-60 rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>

                                <!-- Center/Bottom: Balance as Card Number -->
                                <div class="mt-2">
                                    <p class="text-xs text-blue-100 uppercase tracking-widest mb-1 opacity-80">Saldo Tersedia</p>
                                    <p class="text-2xl font-mono font-bold tracking-widest text-shadow-sm truncate">
                                        {{ formatCurrency(wallet.balance) }}
                                    </p>
                                </div>

                                <!-- Bottom Row: Details and Logo -->
                                <div class="flex justify-between items-end">
                                    <div class="flex flex-col mr-4">
                                        <span class="text-[8px] uppercase text-blue-100 opacity-70">DIBUAT</span>
                                        <span class="text-xs font-mono font-bold">{{ new Date(wallet.created_at).toLocaleDateString('id-ID', { month: '2-digit', year: '2-digit' }) }}</span>
                                        <span class="text-[10px] mt-1 opacity-80 uppercase truncate max-w-[100px]" :title="wallet.description">{{ wallet.description || 'Main Wallet' }}</span>
                                    </div>
                                    <div class="flex gap-4">
                                        <div class="flex flex-col">
                                            <span class="text-[8px] uppercase text-green-300 opacity-90 font-bold tracking-wider">MASUK</span>
                                            <span class="text-xs font-mono font-bold text-green-50">+ {{ formatCurrency(wallet.total_income || 0) }}</span>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-[8px] uppercase text-danger-300 opacity-90 font-bold tracking-wider">KELUAR</span>
                                            <span class="text-xs font-mono font-bold text-red-50">- {{ formatCurrency(wallet.total_expense || 0) }}</span>
                                        </div>
                                    </div>
                                    <h4 class="text-xl font-bold italic tracking-tighter opacity-90">VISA</h4>
                                </div>
                            </div>
                        </Link>

                        <!-- Action Buttons (Slide out or appear below) -->
                        <div class="absolute -bottom-4 left-0 right-0 flex justify-center opacity-0 group-hover:opacity-100 group-hover:bottom-[-2.5rem] transition-all duration-300 z-20" v-if="$page.props.auth.user.role === 'admin' || $page.props.auth.user.role === 'bendahara'">
                            <div class="bg-card rounded-full shadow-lg p-1.5 flex gap-2 border border transform scale-90 group-hover:scale-100">
                                <button @click="openModal(wallet)" class="p-2 bg-primary/10 text-primary rounded-full hover:bg-primary hover:text-white transition" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                </button>
                                <button @click="deleteWallet(wallet)" class="p-2 bg-destructive/10 text-destructive rounded-full hover:bg-destructive hover:text-white transition" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Stats below card (Visible always, subtle) -->
                        <div class="mt-4 flex justify-between px-2 text-xs text-muted-foreground font-medium opacity-60 group-hover:opacity-100 transition-opacity">
                            <span>{{ wallet.finances_count }} Transaksi</span>
                            <span>{{ wallet.contributions_count }} Iuran</span>
                        </div>
                    </div>
                    
                </div>

                <div v-else class="bg-card p-16 text-center rounded-xl shadow-sm border border">
                    <div class="w-16 h-16 bg-muted rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-foreground mb-1">Belum Ada Dompet</h3>
                    <p class="text-muted-foreground mb-6">Mulai dengan membuat dompet untuk mengelola dana organisasi.</p>
                    
                    <Button type="submit" @click="openModal()">
                        Buat Dompet Pertama
                    </Button>
                </div>
            </div>
        </div>

        <!-- Dialog Create/Edit -->
        <Dialog :open="showModal" @update:open="(val) => { if (!val) showModal = false; }">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {{ isEditing ? 'Edit Data Dompet' : 'Buat Dompet Baru' }}
                    </DialogTitle>
                </DialogHeader>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <Label for="name" class="text-xs font-bold uppercase text-muted-foreground">Nama Dompet</Label>
                        <Input id="name" type="text" class="mt-1 block w-full" v-model="form.name" required placeholder="Contoh: Kas Utama, Dana Sosial" />
                        <p v-if="form.errors.name" class="mt-2 text-sm text-destructive">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <Label for="description" class="text-xs font-bold uppercase text-muted-foreground">Deskripsi (Opsional)</Label>
                        <Textarea id="description" rows="3" class="mt-1 block w-full" v-model="form.description" placeholder="Jelaskan kegunaan dompet ini..." />
                        <p v-if="form.errors.description" class="mt-2 text-sm text-destructive">{{ form.errors.description }}</p>
                    </div>

                    <div v-if="!isEditing">
                        <Label for="balance" class="text-xs font-bold uppercase text-muted-foreground">Saldo Awal (Rp)</Label>
                        <Input id="balance" type="number" step="0.01" class="mt-1 block w-full" v-model="form.balance" />
                        <p class="mt-1 text-xs text-muted-foreground italic">* Saldo awal hanya bisa diatur saat pembuatan dompet.</p>
                        <p v-if="form.errors.balance" class="mt-2 text-sm text-destructive">{{ form.errors.balance }}</p>
                    </div>

                    <div class="flex items-center space-x-2 bg-muted p-3 rounded-lg">
                        <Checkbox id="is_active" :checked="form.is_active" @update:checked="(val) => form.is_active = val" />
                        <div class="ml-3">
                            <Label for="is_active" class="text-sm font-bold text-foreground">Status Aktif</Label>
                            <p class="text-xs text-muted-foreground">Dompet nonaktif tidak akan muncul di pilihan transaksi baru.</p>
                        </div>
                    </div>

                    <DialogFooter>
                        <Button variant="outline" type="button" @click="showModal = false">Batal</Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ isEditing ? 'Simpan Perubahan' : 'Konfirmasi & Buat' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AuthenticatedLayout>
</template>
