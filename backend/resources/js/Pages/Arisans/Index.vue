<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow, } from "@/components/ui/table";
import { Card, CardContent } from '@/components/ui/card';
import { Plus, PiggyBank, Eye, Trash2, Users, TrendingUp, ChevronRight, Trophy } from 'lucide-vue-next';
import { useToast } from '@/composables/useToast';
import DeleteConfirmDialog from '@/Components/DeleteConfirmDialog.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    arisans: Array,
});

const page = usePage();
const canManage = computed(() => {
    if (page.props.auth.user.role === 'admin') return true;
    return page.props.auth.user.permissions?.includes('manage_arisans');
});

const toast = useToast();
const targetDeleteId = ref(null);

const confirmDelete = () => {
    if (!targetDeleteId.value) return;
    router.delete(route('arisans.destroy', targetDeleteId.value), {
        onSuccess: () => { targetDeleteId.value = null; },
        onError: () => toast.error('Gagal menghapus arisan.'),
    });
};

const stats = computed(() => {
    const total = props.arisans.length;
    const active = props.arisans.filter(a => a.status === 'active').length;
    const totalParticipants = props.arisans.reduce((sum, a) => sum + (a.participants_count || 0), 0);
    return { total, active, totalParticipants };
});

const formatDate = (dateValue) => {
    return new Date(dateValue).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

const formatRupiah = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};
</script>

<template>
    <Head title="Manajemen Arisan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <Trophy class="w-5 h-5 text-primary" />
                    <h2 class="text-lg font-semibold leading-tight text-foreground">
                        Program Arisan
                    </h2>
                </div>
                <Button v-if="canManage" size="sm" class="hidden sm:flex" as-child>
                    <Link :href="route('arisans.create')">
                        <Plus class="w-4 h-4 mr-1" />
                        Buat Arisan Baru
                    </Link>
                </Button>
            </div>
        </template>

        <div class="py-3 sm:py-6">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-4">

                <!-- Mobile CTA -->
                <div v-if="canManage" class="sm:hidden">
                    <Button class="w-full" as-child>
                        <Link :href="route('arisans.create')">
                            <Plus class="w-4 h-4 mr-2" />
                            Program Arisan Baru
                        </Link>
                    </Button>
                </div>

                <!-- Stats Summary -->
                <div v-if="arisans.length > 0" class="grid grid-cols-3 gap-2 sm:gap-3">
                    <Card>
                        <CardContent class="p-3 sm:p-4 flex items-center gap-2 sm:gap-3">
                            <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                                <PiggyBank class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-primary" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-xl sm:text-2xl font-bold text-foreground leading-none">{{ stats.total }}</p>
                                <p class="text-[10px] sm:text-[11px] text-muted-foreground mt-0.5">Total Program</p>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent class="p-3 sm:p-4 flex items-center gap-2 sm:gap-3">
                            <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-emerald-500/10 flex items-center justify-center shrink-0">
                                <TrendingUp class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-emerald-500" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-xl sm:text-2xl font-bold text-foreground leading-none">{{ stats.active }}</p>
                                <p class="text-[10px] sm:text-[11px] text-muted-foreground mt-0.5">Sedang Aktif</p>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent class="p-3 sm:p-4 flex items-center gap-2 sm:gap-3">
                            <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-blue-500/10 flex items-center justify-center shrink-0">
                                <Users class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-blue-500" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-xl sm:text-2xl font-bold text-foreground leading-none">{{ stats.totalParticipants }}</p>
                                <p class="text-[10px] sm:text-[11px] text-muted-foreground mt-0.5">Total Peserta</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Mobile: Card List -->
                <div class="sm:hidden space-y-2">
                    <Link v-for="arisan in arisans" :key="arisan.id" :href="route('arisans.show', arisan.id)"
                          class="block bg-card border rounded-xl p-3.5 active:bg-muted/50 transition-colors">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-semibold text-foreground truncate">{{ arisan.name }}</p>
                                    <Badge :variant="arisan.status === 'active' ? 'default' : 'outline'" class="h-4 px-1.5 text-[9px] shrink-0"
                                           :class="arisan.status === 'active' ? 'bg-emerald-500' : ''">
                                        {{ arisan.status === 'active' ? 'Aktif' : 'Selesai' }}
                                    </Badge>
                                </div>
                                <p class="text-[11px] text-muted-foreground mt-0.5">Mulai {{ formatDate(arisan.start_date) }}</p>
                            </div>
                            <ChevronRight class="w-4 h-4 text-muted-foreground shrink-0 mt-0.5" />
                        </div>
                        <div class="flex items-center gap-4 mt-2.5 pt-2.5 border-t">
                            <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
                                <PiggyBank class="w-3.5 h-3.5" />
                                <span class="font-medium text-foreground">{{ formatRupiah(arisan.amount_per_month) }}</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
                                <Users class="w-3.5 h-3.5" />
                                <span class="font-medium text-foreground">{{ arisan.participants_count }}</span>
                                <span>orang</span>
                            </div>
                        </div>
                    </Link>

                    <!-- Mobile: Delete via long press or swipe is not implemented, keep simple -->
                </div>

                <!-- Desktop: Table -->
                <div class="hidden sm:block bg-card rounded-xl border overflow-hidden">
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow class="bg-muted/50">
                                    <TableHead class="text-[11px] uppercase tracking-wider">Nama Program</TableHead>
                                    <TableHead class="text-[11px] uppercase tracking-wider">Iuran / Bulan</TableHead>
                                    <TableHead class="text-[11px] uppercase tracking-wider text-center">Peserta</TableHead>
                                    <TableHead class="text-[11px] uppercase tracking-wider text-center">Status</TableHead>
                                    <TableHead class="text-right text-[11px] uppercase tracking-wider">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="arisan in arisans" :key="arisan.id" class="hover:bg-muted/30 transition-colors group">
                                    <TableCell>
                                        <Link :href="route('arisans.show', arisan.id)" class="block">
                                            <p class="text-sm font-semibold truncate max-w-[220px] group-hover:text-primary transition-colors">{{ arisan.name }}</p>
                                            <p class="text-[11px] text-muted-foreground mt-0.5">Mulai {{ formatDate(arisan.start_date) }}</p>
                                        </Link>
                                    </TableCell>
                                    <TableCell>
                                        <span class="text-sm font-mono font-medium">{{ formatRupiah(arisan.amount_per_month) }}</span>
                                    </TableCell>
                                    <TableCell class="text-center">
                                        <div class="inline-flex items-center gap-1.5">
                                            <Users class="w-3.5 h-3.5 text-muted-foreground" />
                                            <span class="text-sm font-bold">{{ arisan.participants_count }}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-center">
                                        <Badge :variant="arisan.status === 'active' ? 'default' : 'outline'"
                                               :class="arisan.status === 'active' ? 'bg-emerald-500 hover:bg-emerald-600' : 'text-muted-foreground'">
                                            {{ arisan.status === 'active' ? 'Aktif' : 'Selesai' }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-1">
                                            <Button variant="ghost" size="icon" as-child class="text-primary hover:text-primary hover:bg-primary/10">
                                                <Link :href="route('arisans.show', arisan.id)">
                                                    <Eye class="w-4 h-4" />
                                                </Link>
                                            </Button>
                                            <Button v-if="canManage" variant="ghost" size="icon" @click="targetDeleteId = arisan.id" class="text-destructive hover:text-destructive hover:bg-destructive/10">
                                                <Trash2 class="w-4 h-4" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="arisans.length === 0" class="bg-card rounded-xl border p-8 text-center">
                    <div class="w-14 h-14 rounded-full bg-muted/50 flex items-center justify-center mb-3 mx-auto">
                        <PiggyBank class="w-7 h-7 text-muted-foreground/40" />
                    </div>
                    <p class="text-sm font-medium text-foreground">Belum Ada Program Arisan</p>
                    <p class="text-xs text-muted-foreground mt-1 max-w-[250px] mx-auto">Buat program arisan pertama untuk mulai mengelola iuran dan undian.</p>
                    <Button v-if="canManage" size="sm" class="mt-4" as-child>
                        <Link :href="route('arisans.create')">
                            <Plus class="w-4 h-4 mr-1" />
                            Buat Arisan
                        </Link>
                    </Button>
                </div>

                <DeleteConfirmDialog
                    :open="!!targetDeleteId"
                    title="Hapus Program Arisan"
                    description="Anda yakin ingin menghapus program Arisan ini? Seluruh data penerima arisan, historis iuran, dan catatan pemenang akan hilang selamanya."
                    @confirm="confirmDelete"
                    @cancel="targetDeleteId = null"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
