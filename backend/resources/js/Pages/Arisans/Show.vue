<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogScrollContent } from '@/components/ui/dialog';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetDescription } from '@/components/ui/sheet';
import { ArrowLeft, Users, Trophy, Plus, Trash2, CalendarHeart, Edit3, PiggyBank, Search, CheckCircle2, CircleDot, Check, Wallet, Eye, X as XIcon } from 'lucide-vue-next';
import { useToast } from '@/composables/useToast';
import { ref, computed } from 'vue';
import DeleteConfirmDialog from '@/Components/DeleteConfirmDialog.vue';

const props = defineProps({
    arisan: Object,
    allMembers: Array,
});

const toast = useToast();
const activeTab = ref('peserta');

const tabs = [
    { key: 'peserta', label: 'Peserta', icon: Users },
    { key: 'kocokan', label: 'Penerima Arisan', icon: Trophy },
    { key: 'setoran', label: 'Setoran', icon: Wallet },
];

const getTabBadge = (key) => {
    if (key === 'peserta') return props.arisan.participants.length;
    if (key === 'kocokan') return props.arisan.draws.length;
    if (key === 'setoran') return paymentStats.value.paidCount;
    return 0;
};

// ======================== Participant ========================
const addParticipantForm = useForm({ member_id: '' });

const availableMembers = computed(() => {
    const existingIds = props.arisan.participants.map(p => p.member_id);
    return (props.allMembers || []).filter(m => !existingIds.includes(m.id));
});

const submitParticipant = () => {
    addParticipantForm.post(route('arisans.participants.store', props.arisan.id), {
        onSuccess: () => { addParticipantForm.reset(); },
        onError: () => toast.error('Gagal menambahkan peserta.'),
    });
};

const targetParticipantDelete = ref(null);
const confirmDeleteParticipant = () => {
    if (!targetParticipantDelete.value) return;
    router.delete(route('arisans.participants.destroy', [props.arisan.id, targetParticipantDelete.value]), {
        onSuccess: () => { targetParticipantDelete.value = null; }
    });
};

// ======================== Draw / Winner ========================
const winnerIds = computed(() => props.arisan.draws.flatMap(d => d.winners.map(w => w.member_id)));

const getEligibleMembers = computed(() => {
    return props.arisan.participants.filter(p => p.member && !winnerIds.value.includes(p.member.id)).map(p => p.member);
});

const progress = computed(() => {
    const total = props.arisan.participants.length;
    const won = winnerIds.value.length;
    return { total, won, remaining: total - won, pct: total > 0 ? Math.round((won / total) * 100) : 0 };
});

const drawForm = useForm({ period_month: '', notes: '', winner_ids: [] });
const drawWinnerModalOpen = ref(false);
const winnerSearch = ref('');

const filteredEligible = computed(() => {
    if (!winnerSearch.value) return getEligibleMembers.value;
    const q = winnerSearch.value.toLowerCase();
    return getEligibleMembers.value.filter(m => m.full_name.toLowerCase().includes(q));
});

const submitDraw = () => {
    if (drawForm.winner_ids.length === 0) { toast.error('Pilih minimal 1 pemenang.'); return; }
    drawForm.post(route('arisans.draws.store', props.arisan.id), {
        onSuccess: () => { drawForm.reset(); winnerSearch.value = ''; drawWinnerModalOpen.value = false; },
        onError: () => toast.error('Gagal mencatat penerima arisan.'),
    });
};

const targetDrawDelete = ref(null);
const confirmDeleteDraw = () => {
    if (!targetDrawDelete.value) return;
    router.delete(route('arisans.draws.destroy', [props.arisan.id, targetDrawDelete.value]), {
        onSuccess: () => { targetDrawDelete.value = null; }
    });
};

const toggleWinnerSelection = (id) => {
    const idx = drawForm.winner_ids.indexOf(id);
    if (idx > -1) drawForm.winner_ids.splice(idx, 1); else drawForm.winner_ids.push(id);
};

// ======================== Payments ========================
const paymentMonths = computed(() => {
    return props.arisan.draws
        .map(d => d.period_month)
        .sort();
});

const paymentMap = computed(() => {
    const map = {};
    (props.arisan.payments || []).forEach(p => { map[`${p.member_id}-${p.month}`] = p.id; });
    return map;
});

const getPaymentId = (mid, month) => paymentMap.value[`${mid}-${month}`] || null;
const hasPaid = (mid, month) => !!getPaymentId(mid, month);
const paymentProcessing = ref(null);

// Map bulan -> array member_id pemenang bulan tersebut
const winnerOfMonthMap = computed(() => {
    const map = {};
    props.arisan.draws.forEach(d => {
        map[d.period_month] = d.winners.map(w => w.member_id);
    });
    return map;
});
const isWinnerOfMonth = (mid, month) => (winnerOfMonthMap.value[month] || []).includes(mid);

const togglePayment = (memberId, month) => {
    const key = `${memberId}-${month}`;
    if (paymentProcessing.value === key) return;
    paymentProcessing.value = key;
    const existingId = getPaymentId(memberId, month);
    if (existingId) {
        router.delete(route('arisans.payments.destroy', [props.arisan.id, existingId]), {
            preserveScroll: true,
            onFinish: () => { paymentProcessing.value = null; },
        });
    } else {
        router.post(route('arisans.payments.store', props.arisan.id), { member_id: memberId, month }, {
            preserveScroll: true,
            onFinish: () => { paymentProcessing.value = null; },
        });
    }
};

const paymentStats = computed(() => {
    const payments = props.arisan.payments || [];
    const totalCollected = payments.length * props.arisan.amount_per_month;
    // Hitung total cell yang seharusnya bayar (exclude penerima arisan tiap bulan)
    let winnerCells = 0;
    paymentMonths.value.forEach(month => {
        winnerCells += (winnerOfMonthMap.value[month] || []).filter(wid =>
            props.arisan.participants.some(p => p.member_id === wid)
        ).length;
    });
    const totalCells = paymentMonths.value.length * props.arisan.participants.length - winnerCells;
    const totalExpected = totalCells * props.arisan.amount_per_month;
    return { totalCollected, totalExpected, paidCount: payments.length, totalCells };
});

const getMemberPaymentCount = (mid) => {
    const winMonths = paymentMonths.value.filter(m => isWinnerOfMonth(mid, m)).length;
    const paid = (props.arisan.payments || []).filter(p => p.member_id === mid).length;
    return { paid, expected: paymentMonths.value.length - winMonths };
};

// ======================== Helpers ========================
const formatRupiah = (v) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v);
const formatPeriod = (s) => { const [y, m] = s.split('-'); return ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][parseInt(m)-1] + ' ' + y; };
const formatDate = (d) => new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
const formatMonthShort = (s) => { const [y, m] = s.split('-'); return ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'][parseInt(m)-1] + ' ' + y.slice(2); };
const isWinner = (mid) => winnerIds.value.includes(mid);

// ======================== Draw Detail Modal ========================
const selectedDrawId = ref(null);
const selectedDraw = computed(() => props.arisan.draws.find(d => d.id === selectedDrawId.value) || null);

const openDrawDetail = (drawId) => {
    selectedDrawId.value = drawId;
};

const getDrawPaymentDetail = (draw) => {
    if (!draw) return { paid: [], unpaid: [], winners: [], total: 0 };
    const month = draw.period_month;
    const drawWinnerIds = draw.winners.map(w => w.member_id);
    const paid = [];
    const unpaid = [];
    const winners = [];
    props.arisan.participants.forEach(p => {
        if (!p.member) return;
        if (drawWinnerIds.includes(p.member_id)) {
            winners.push(p.member);
        } else if (hasPaid(p.member_id, month)) {
            paid.push(p.member);
        } else {
            unpaid.push(p.member);
        }
    });
    return { paid, unpaid, winners, total: paid.length + unpaid.length };
};
</script>

<template>
    <Head :title="arisan.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <Button variant="ghost" size="icon" as-child class="shrink-0 rounded-full hover:bg-muted">
                        <Link :href="route('arisans.index')"><ArrowLeft class="w-5 h-5 text-muted-foreground" /></Link>
                    </Button>
                    <div>
                        <h2 class="text-lg font-semibold leading-tight text-foreground">{{ arisan.name }}</h2>
                        <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                            <span class="text-xs text-muted-foreground">{{ formatDate(arisan.start_date) }}</span>
                            <Badge :variant="arisan.status === 'active' ? 'default' : 'outline'" class="h-4 px-1.5 text-[10px]"
                                   :class="arisan.status === 'active' ? 'bg-emerald-500 hover:bg-emerald-600' : ''">
                                {{ arisan.status === 'active' ? 'Aktif' : 'Selesai' }}
                            </Badge>
                        </div>
                    </div>
                </div>
                <Button variant="outline" size="sm" as-child>
                    <Link :href="route('arisans.edit', arisan.id)">
                        <Edit3 class="w-3.5 h-3.5 sm:mr-1.5" /> <span class="hidden sm:inline">Edit</span>
                    </Link>
                </Button>
            </div>
        </template>

        <div class="py-3 sm:py-6">
            <div class="max-w-6xl mx-auto px-3 sm:px-6 lg:px-8">

                <!-- Summary Cards -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4 sm:mb-6">
                    <Card>
                        <CardContent class="p-3 sm:p-4 text-center">
                            <PiggyBank class="w-5 h-5 text-primary mx-auto mb-1.5" />
                            <p class="text-base sm:text-lg font-bold leading-none">{{ formatRupiah(arisan.amount_per_month) }}</p>
                            <p class="text-[10px] sm:text-[11px] text-muted-foreground mt-1">Iuran / Bulan</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent class="p-3 sm:p-4 text-center">
                            <Users class="w-5 h-5 text-blue-500 mx-auto mb-1.5" />
                            <p class="text-base sm:text-lg font-bold leading-none">{{ progress.total }}</p>
                            <p class="text-[10px] sm:text-[11px] text-muted-foreground mt-1">Peserta</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent class="p-3 sm:p-4 text-center">
                            <Trophy class="w-5 h-5 text-amber-500 mx-auto mb-1.5" />
                            <p class="text-base sm:text-lg font-bold leading-none">{{ progress.won }}</p>
                            <p class="text-[10px] sm:text-[11px] text-muted-foreground mt-1">Sudah Menang</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent class="p-3 sm:p-4 text-center">
                            <CircleDot class="w-5 h-5 text-orange-500 mx-auto mb-1.5" />
                            <p class="text-base sm:text-lg font-bold leading-none">{{ progress.remaining }}</p>
                            <p class="text-[10px] sm:text-[11px] text-muted-foreground mt-1">Belum Menang</p>
                        </CardContent>
                    </Card>
                </div>

                <!-- Progress Bar -->
                <div v-if="progress.total > 0" class="mb-4 sm:mb-6">
                    <div class="flex items-center justify-between text-xs text-muted-foreground mb-1.5">
                        <span>Progres Arisan</span>
                        <span class="font-medium">{{ progress.won }}/{{ progress.total }} ({{ progress.pct }}%)</span>
                    </div>
                    <div class="w-full h-2 bg-muted rounded-full overflow-hidden">
                        <div class="h-full bg-emerald-500 rounded-full transition-all duration-500" :style="{ width: progress.pct + '%' }"></div>
                    </div>
                </div>

                <!-- Sidebar + Content (Settings-style) -->
                <div class="flex flex-col lg:flex-row gap-3 sm:gap-4">

                    <!-- Sidebar Tabs -->
                    <div class="w-full lg:w-56 shrink-0">
                        <div class="flex lg:flex-col gap-1.5 overflow-x-auto pb-1 -mx-3 px-3 lg:mx-0 lg:px-0">
                            <button
                                v-for="tab in tabs"
                                :key="tab.key"
                                @click="activeTab = tab.key"
                                class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-all shrink-0 lg:w-full"
                                :class="activeTab === tab.key
                                    ? 'bg-primary text-primary-foreground'
                                    : 'bg-card border text-muted-foreground hover:bg-muted hover:text-foreground'"
                            >
                                <component :is="tab.icon" class="w-4 h-4 shrink-0" />
                                <span class="whitespace-nowrap">{{ tab.label }}</span>
                                <Badge
                                    variant="secondary"
                                    class="ml-auto text-[10px] h-5 min-w-[20px] px-1.5 justify-center"
                                    :class="activeTab === tab.key ? 'bg-primary-foreground/20 text-primary-foreground' : ''"
                                >{{ getTabBadge(tab.key) }}</Badge>
                            </button>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="flex-1 min-w-0">
                        <div class="bg-card rounded-xl border overflow-hidden">
                            <div class="h-1 w-full bg-primary" />

                            <!-- ==================== TAB: Peserta ==================== -->
                            <div v-if="activeTab === 'peserta'" class="p-4 sm:p-6">
                                <div class="mb-4 sm:mb-5 pb-3 sm:pb-4 border-b">
                                    <div class="flex items-center gap-2.5">
                                        <Users class="w-5 h-5 text-primary shrink-0" />
                                        <div>
                                            <h3 class="text-sm sm:text-base font-semibold text-foreground">Daftar Peserta</h3>
                                            <p class="text-[10px] sm:text-xs text-muted-foreground">{{ arisan.participants.length }} orang terdaftar di program ini.</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Add Form -->
                                <form @submit.prevent="submitParticipant" class="flex gap-2 mb-4">
                                    <select v-model="addParticipantForm.member_id"
                                        class="flex h-9 w-full rounded-md border border-input bg-background text-foreground px-3 py-1 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
                                        <option value="" disabled>Pilih Anggota...</option>
                                        <option v-for="m in availableMembers" :key="m.id" :value="m.id">{{ m.full_name }}</option>
                                    </select>
                                    <Button type="submit" size="sm" class="shrink-0 h-9 px-3" :disabled="addParticipantForm.processing || !addParticipantForm.member_id">
                                        <Plus class="w-4 h-4" />
                                    </Button>
                                </form>

                                <!-- List -->
                                <div class="divide-y rounded-lg border overflow-hidden">
                                    <div v-for="(part, index) in arisan.participants" :key="part.id"
                                         class="flex items-center justify-between px-3 sm:px-4 py-2 sm:py-2.5 text-xs sm:text-sm hover:bg-muted/30 transition-colors"
                                         :class="part.member && isWinner(part.member.id) ? 'bg-emerald-50/50 dark:bg-emerald-950/10' : ''">
                                        <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                                            <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-muted flex items-center justify-center text-[10px] sm:text-[11px] font-bold text-muted-foreground shrink-0">
                                                {{ index + 1 }}
                                            </div>
                                            <span class="truncate font-medium text-foreground">{{ part.member?.full_name || 'Anggota tidak ditemukan' }}</span>
                                            <Trophy v-if="part.member && isWinner(part.member.id)" class="w-3.5 h-3.5 text-amber-500 shrink-0" />
                                        </div>
                                        <Button variant="ghost" size="icon" class="h-7 w-7 text-muted-foreground hover:text-destructive shrink-0" @click="targetParticipantDelete = part.id">
                                            <Trash2 class="w-3.5 h-3.5" />
                                        </Button>
                                    </div>
                                    <div v-if="arisan.participants.length === 0" class="text-center py-10 text-muted-foreground">
                                        <Users class="w-7 h-7 mx-auto mb-2 opacity-20" />
                                        <p class="text-sm font-medium text-foreground">Belum Ada Peserta</p>
                                        <p class="text-xs mt-1">Tambahkan peserta menggunakan form di atas.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- ==================== TAB: Penerima Arisan ==================== -->
                            <div v-if="activeTab === 'kocokan'" class="p-4 sm:p-6">
                                <div class="mb-4 sm:mb-5 pb-3 sm:pb-4 border-b">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2.5">
                                            <Trophy class="w-5 h-5 text-amber-500 shrink-0" />
                                            <div>
                                                <h3 class="text-sm sm:text-base font-semibold text-foreground">Riwayat Penerima Arisan</h3>
                                                <p class="text-[10px] sm:text-xs text-muted-foreground">{{ arisan.draws.length }} periode tercatat.</p>
                                            </div>
                                        </div>
                                        <Button size="sm" @click="drawWinnerModalOpen = !drawWinnerModalOpen"
                                            :variant="drawWinnerModalOpen ? 'secondary' : 'default'"
                                            :disabled="getEligibleMembers.length === 0 && !drawWinnerModalOpen">
                                            <Plus class="w-4 h-4 sm:mr-1" /> <span class="hidden sm:inline">Catat Penerima</span>
                                        </Button>
                                    </div>
                                </div>

                                <!-- Form Penerima Arisan -->
                                <Transition
                                    enter-active-class="transition duration-200 ease-out"
                                    enter-from-class="opacity-0 -translate-y-2"
                                    enter-to-class="opacity-100 translate-y-0"
                                    leave-active-class="transition duration-150 ease-in"
                                    leave-from-class="opacity-100 translate-y-0"
                                    leave-to-class="opacity-0 -translate-y-2">
                                    <div v-if="drawWinnerModalOpen" class="mb-6 p-4 border border-primary/20 bg-primary/5 rounded-xl">
                                        <form @submit.prevent="submitDraw">
                                            <h4 class="font-semibold text-sm mb-4">Catat Pemenang Baru</h4>
                                            <div class="grid sm:grid-cols-2 gap-4 mb-4">
                                                <div class="space-y-2">
                                                    <Label>Periode Bulan *</Label>
                                                    <Input v-model="drawForm.period_month" type="month" required />
                                                    <p v-if="drawForm.errors.period_month" class="text-xs text-destructive">{{ drawForm.errors.period_month }}</p>
                                                </div>
                                                <div class="space-y-2">
                                                    <Label>Catatan (Opsional)</Label>
                                                    <Input v-model="drawForm.notes" placeholder="Lokasi undian / keterangan" />
                                                </div>
                                            </div>

                                            <div class="space-y-2 mb-4">
                                                <div class="flex items-center justify-between">
                                                    <Label>Pilih Pemenang *</Label>
                                                    <span class="text-xs text-muted-foreground">{{ drawForm.winner_ids.length }} terpilih</span>
                                                </div>
                                                <div class="relative" v-if="getEligibleMembers.length > 6">
                                                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-muted-foreground" />
                                                    <Input v-model="winnerSearch" placeholder="Cari..." class="pl-9 h-8 text-xs" />
                                                </div>
                                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 max-h-[220px] overflow-y-auto p-1">
                                                    <div v-for="em in filteredEligible" :key="em.id"
                                                         @click="toggleWinnerSelection(em.id)"
                                                         class="cursor-pointer border rounded-lg p-2 sm:p-2.5 text-xs sm:text-sm flex items-center gap-2 transition-all select-none"
                                                         :class="drawForm.winner_ids.includes(em.id) ? 'bg-primary text-primary-foreground border-primary shadow-sm' : 'bg-background hover:border-primary/40'">
                                                        <CheckCircle2 v-if="drawForm.winner_ids.includes(em.id)" class="w-4 h-4 shrink-0" />
                                                        <div v-else class="w-4 h-4 rounded-full border-2 border-muted-foreground/30 shrink-0"></div>
                                                        <span class="truncate">{{ em.full_name }}</span>
                                                    </div>
                                                    <div v-if="filteredEligible.length === 0" class="col-span-full py-6 text-center text-sm text-muted-foreground border border-dashed rounded-lg">
                                                        {{ winnerSearch ? 'Tidak ditemukan.' : 'Semua peserta sudah menerima arisan.' }}
                                                    </div>
                                                </div>
                                                <p v-if="drawForm.errors.winner_ids" class="text-xs text-destructive">{{ drawForm.errors.winner_ids }}</p>
                                            </div>

                                            <div class="flex justify-end gap-2">
                                                <Button variant="outline" size="sm" type="button" @click="drawWinnerModalOpen = false">Batal</Button>
                                                <Button size="sm" type="submit" :disabled="drawForm.processing || drawForm.winner_ids.length === 0">
                                                    <Trophy class="w-3.5 h-3.5 mr-1.5" /> Simpan Penerima
                                                </Button>
                                            </div>
                                        </form>
                                    </div>
                                </Transition>

                                <!-- Draw List -->
                                <div class="space-y-3">
                                    <div v-for="draw in arisan.draws" :key="draw.id" class="border rounded-xl overflow-hidden">
                                        <!-- Header -->
                                        <div class="bg-muted/30 px-3 sm:px-4 py-2.5 border-b flex items-center justify-between gap-2">
                                            <div class="flex items-center gap-1.5 sm:gap-2 min-w-0">
                                                <CalendarHeart class="w-4 h-4 text-primary shrink-0" />
                                                <h4 class="font-semibold text-xs sm:text-sm text-foreground truncate">{{ formatPeriod(draw.period_month) }}</h4>
                                                <Badge variant="secondary" class="text-[9px] sm:text-[10px] h-4 sm:h-5 px-1 sm:px-1.5 font-mono shrink-0">
                                                    {{ getDrawPaymentDetail(draw).paid.length }}/{{ getDrawPaymentDetail(draw).total }}
                                                </Badge>
                                            </div>
                                            <div class="flex items-center gap-0.5 shrink-0">
                                                <Button variant="ghost" size="icon" class="h-7 w-7 text-muted-foreground hover:text-primary hover:bg-primary/10" @click="openDrawDetail(draw.id)">
                                                    <Eye class="w-4 h-4" />
                                                </Button>
                                                <Button variant="ghost" size="icon" class="h-7 w-7 text-muted-foreground hover:text-destructive hover:bg-destructive/10" @click="targetDrawDelete = draw.id">
                                                    <Trash2 class="w-3.5 h-3.5" />
                                                </Button>
                                            </div>
                                        </div>

                                        <!-- Winners & Notes -->
                                        <div class="px-3 sm:px-4 py-2.5 sm:py-3 cursor-pointer hover:bg-muted/10 transition-colors" @click="openDrawDetail(draw.id)">
                                            <p v-if="draw.notes" class="text-[11px] sm:text-xs text-muted-foreground mb-2">{{ draw.notes }}</p>
                                            <div class="flex gap-1.5 sm:gap-2 flex-wrap">
                                                <div v-for="w in draw.winners" :key="w.id"
                                                     class="inline-flex items-center gap-1 sm:gap-1.5 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 px-2 sm:px-3 py-1 sm:py-1.5 rounded-full text-xs sm:text-sm font-medium">
                                                    <Trophy class="w-3 h-3 sm:w-3.5 sm:h-3.5" />
                                                    {{ w.member.full_name }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="arisan.draws.length === 0" class="text-center py-12 text-muted-foreground border border-dashed rounded-xl">
                                        <Trophy class="w-8 h-8 mx-auto mb-3 opacity-20" />
                                        <p class="text-sm font-medium text-foreground">Belum Ada Penerima Arisan</p>
                                        <p class="text-xs mt-1">Klik "Catat Penerima" untuk mencatat pemenang pertama.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- ==================== TAB: Setoran ==================== -->
                            <div v-if="activeTab === 'setoran'" class="p-4 sm:p-6">
                                <div class="mb-4 sm:mb-5 pb-3 sm:pb-4 border-b">
                                    <div class="flex items-center justify-between flex-wrap gap-3">
                                        <div class="flex items-center gap-2.5">
                                            <Wallet class="w-5 h-5 text-primary shrink-0" />
                                            <div>
                                                <h3 class="text-sm sm:text-base font-semibold text-foreground">Pencatatan Setoran</h3>
                                                <p class="text-[10px] sm:text-xs text-muted-foreground">Klik cell untuk mencatat / membatalkan setoran. {{ formatRupiah(arisan.amount_per_month) }} per bulan.</p>
                                            </div>
                                        </div>
                                        <div class="hidden sm:flex items-center gap-4 text-xs text-muted-foreground">
                                            <span class="flex items-center gap-1.5">
                                                <span class="w-5 h-5 rounded bg-emerald-500/20 border border-emerald-300 dark:border-emerald-700 flex items-center justify-center">
                                                    <Check class="w-3 h-3 text-emerald-600 dark:text-emerald-400" />
                                                </span>
                                                Lunas
                                            </span>
                                            <span class="flex items-center gap-1.5">
                                                <span class="w-5 h-5 rounded bg-amber-500/20 border border-amber-300 dark:border-amber-700 flex items-center justify-center">
                                                    <Trophy class="w-3 h-3 text-amber-500" />
                                                </span>
                                                Penerima
                                            </span>
                                            <span class="flex items-center gap-1.5">
                                                <span class="w-5 h-5 rounded bg-muted border"></span>
                                                Belum
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payment Stats -->
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-5">
                                    <div class="bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800 rounded-lg px-3 py-2">
                                        <p class="text-[10px] sm:text-xs text-emerald-600 dark:text-emerald-400">Terkumpul</p>
                                        <p class="text-sm font-bold text-emerald-700 dark:text-emerald-300">{{ formatRupiah(paymentStats.totalCollected) }}</p>
                                    </div>
                                    <div class="bg-muted/50 border rounded-lg px-3 py-2">
                                        <p class="text-[10px] sm:text-xs text-muted-foreground">Seharusnya</p>
                                        <p class="text-sm font-bold text-foreground">{{ formatRupiah(paymentStats.totalExpected) }}</p>
                                    </div>
                                    <div class="bg-muted/50 border rounded-lg px-3 py-2">
                                        <p class="text-[10px] sm:text-xs text-muted-foreground">Rasio Setoran</p>
                                        <p class="text-sm font-bold text-foreground">{{ paymentStats.paidCount }} / {{ paymentStats.totalCells }}</p>
                                    </div>
                                </div>

                                <!-- Payment Matrix Table -->
                                <div v-if="arisan.participants.length > 0 && paymentMonths.length > 0" class="overflow-x-auto -mx-4 sm:-mx-6">
                                    <table class="w-full text-xs sm:text-sm">
                                        <thead>
                                            <tr class="border-y bg-muted/40">
                                                <th class="text-left text-[10px] sm:text-[11px] uppercase tracking-wider font-medium text-muted-foreground px-2 sm:px-3 py-2 sm:py-2.5 sticky left-0 bg-muted/40 z-10 min-w-[100px] sm:min-w-[140px]">Peserta</th>
                                                <th v-for="month in paymentMonths" :key="month"
                                                    class="text-center text-[9px] sm:text-[10px] uppercase tracking-wider font-medium text-muted-foreground px-0.5 sm:px-1 py-2 sm:py-2.5 min-w-[40px] sm:min-w-[52px]">
                                                    {{ formatMonthShort(month) }}
                                                </th>
                                                <th class="text-center text-[10px] sm:text-[11px] uppercase tracking-wider font-medium text-muted-foreground px-1 sm:px-2 py-2 sm:py-2.5 min-w-[40px] sm:min-w-[48px]">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="part in arisan.participants" :key="'pay-' + part.id" class="border-b last:border-b-0 hover:bg-muted/20 transition-colors">
                                                <td class="px-2 sm:px-3 py-1.5 sm:py-2 sticky left-0 bg-card z-10 border-r">
                                                    <span class="font-medium text-foreground text-xs sm:text-sm truncate block max-w-[90px] sm:max-w-[130px]">{{ part.member?.full_name || 'N/A' }}</span>
                                                </td>
                                                <td v-for="month in paymentMonths" :key="month" class="text-center px-0.5 sm:px-1 py-1 sm:py-1.5">
                                                    <!-- Penerima arisan bulan ini, tidak perlu setor -->
                                                    <div v-if="isWinnerOfMonth(part.member_id, month)"
                                                         class="w-6 h-6 sm:w-7 sm:h-7 rounded-md border border-amber-300 dark:border-amber-700 bg-amber-500/20 flex items-center justify-center mx-auto"
                                                         title="Penerima arisan bulan ini">
                                                        <Trophy class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-amber-500" />
                                                    </div>
                                                    <!-- Peserta biasa, bisa setor -->
                                                    <button v-else
                                                        @click="togglePayment(part.member_id, month)"
                                                        :disabled="paymentProcessing === `${part.member_id}-${month}`"
                                                        class="w-6 h-6 sm:w-7 sm:h-7 rounded-md border transition-all duration-150 flex items-center justify-center mx-auto cursor-pointer disabled:opacity-50"
                                                        :class="hasPaid(part.member_id, month)
                                                            ? 'bg-emerald-500/20 border-emerald-300 dark:border-emerald-700 hover:bg-emerald-500/30'
                                                            : 'bg-background border-input hover:border-primary/50 hover:bg-muted/50'">
                                                        <Check v-if="hasPaid(part.member_id, month)" class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-emerald-600 dark:text-emerald-400" />
                                                    </button>
                                                </td>
                                                <td class="text-center px-1 sm:px-2 py-1.5 sm:py-2">
                                                    <Badge variant="secondary" class="text-[9px] sm:text-[10px] font-mono">
                                                        {{ getMemberPaymentCount(part.member_id).paid }}/{{ getMemberPaymentCount(part.member_id).expected }}
                                                    </Badge>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div v-else class="text-center py-10 text-muted-foreground">
                                    <Wallet class="w-7 h-7 mx-auto mb-2 opacity-20" />
                                    <p class="text-sm font-medium text-foreground">Belum Bisa Mencatat Setoran</p>
                                    <p class="text-xs mt-1">{{ arisan.participants.length === 0 ? 'Tambahkan peserta terlebih dahulu.' : 'Catat penerima arisan terlebih dahulu agar periode bulan muncul.' }}</p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Desktop: Dialog Detail Penerima Arisan -->
        <Dialog :open="!!selectedDrawId" @update:open="(val) => { if (!val) selectedDrawId = null }">
            <DialogContent class="hidden sm:block sm:max-w-xl max-h-[90vh] overflow-y-auto">
                <DialogHeader v-if="selectedDraw">
                    <DialogTitle>Detail Penerima Arisan: {{ formatPeriod(selectedDraw.period_month) }}</DialogTitle>
                    <DialogDescription v-if="selectedDraw.notes">{{ selectedDraw.notes }}</DialogDescription>
                </DialogHeader>

                <div v-if="selectedDraw" class="mt-2 space-y-6">
                    <!-- Progress Info -->
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <div class="flex-1 h-2 bg-muted rounded-full overflow-hidden">
                                <div class="h-full bg-emerald-500 rounded-full transition-all duration-300"
                                     :style="{ width: (getDrawPaymentDetail(selectedDraw).total > 0 ? (getDrawPaymentDetail(selectedDraw).paid.length / getDrawPaymentDetail(selectedDraw).total * 100) : 0) + '%' }"></div>
                            </div>
                            <span class="text-xs font-medium text-muted-foreground shrink-0">
                                {{ getDrawPaymentDetail(selectedDraw).paid.length }} / {{ getDrawPaymentDetail(selectedDraw).total }} Lunas
                            </span>
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <!-- Sudah Setor -->
                        <div class="border rounded-lg overflow-hidden shadow-sm">
                            <div class="bg-emerald-50 dark:bg-emerald-950/20 px-3 py-2.5 border-b border-emerald-200 dark:border-emerald-800 flex items-center justify-between">
                                <div class="flex items-center gap-2 text-emerald-700 dark:text-emerald-300">
                                    <Check class="w-4 h-4" />
                                    <span class="text-xs font-semibold">Sudah Setor</span>
                                </div>
                                <span class="bg-emerald-200/50 dark:bg-emerald-800/50 text-emerald-800 dark:text-emerald-200 text-[10px] px-2 py-0.5 rounded-full font-bold">{{ getDrawPaymentDetail(selectedDraw).paid.length }}</span>
                            </div>
                            <div class="divide-y max-h-[300px] overflow-y-auto">
                                <div v-for="member in getDrawPaymentDetail(selectedDraw).paid" :key="'paid-' + member.id" class="px-3 py-2 text-sm flex items-center justify-between group">
                                    <span class="truncate text-foreground text-sm group-hover:text-primary transition-colors">{{ member.full_name }}</span>
                                    <Trophy v-if="isWinner(member.id) && selectedDraw.winners.find(w => w.member_id === member.id)" class="w-3.5 h-3.5 text-amber-500 shrink-0" />
                                </div>
                                <div v-if="getDrawPaymentDetail(selectedDraw).paid.length === 0" class="px-3 py-8 text-center text-xs text-muted-foreground flex flex-col items-center">
                                    <Wallet class="w-6 h-6 mb-2 opacity-20" />
                                    Belum ada yang setor
                                </div>
                            </div>
                        </div>

                        <!-- Belum Setor -->
                        <div class="border rounded-lg overflow-hidden shadow-sm">
                            <div class="bg-red-50 dark:bg-red-950/20 px-3 py-2.5 border-b border-red-200 dark:border-red-800 flex items-center justify-between">
                                <div class="flex items-center gap-2 text-red-700 dark:text-red-300">
                                    <XIcon class="w-4 h-4" />
                                    <span class="text-xs font-semibold">Belum Setor</span>
                                </div>
                                <span class="bg-red-200/50 dark:bg-red-800/50 text-red-800 dark:text-red-200 text-[10px] px-2 py-0.5 rounded-full font-bold">{{ getDrawPaymentDetail(selectedDraw).unpaid.length }}</span>
                            </div>
                            <div class="divide-y max-h-[300px] overflow-y-auto">
                                <div v-for="member in getDrawPaymentDetail(selectedDraw).unpaid" :key="'unpaid-' + member.id" class="px-3 py-2 text-sm flex items-center justify-between group">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <XIcon class="w-3.5 h-3.5 text-red-400 shrink-0" />
                                        <span class="truncate text-foreground text-sm group-hover:text-primary transition-colors">{{ member.full_name }}</span>
                                    </div>
                                    <Trophy v-if="isWinner(member.id) && selectedDraw.winners.find(w => w.member_id === member.id)" class="w-3.5 h-3.5 text-amber-500 shrink-0" />
                                </div>
                                <div v-if="getDrawPaymentDetail(selectedDraw).unpaid.length === 0" class="px-3 py-8 text-center text-xs text-muted-foreground flex flex-col items-center">
                                    <CheckCircle2 class="w-6 h-6 mb-2 text-emerald-500/50" />
                                    Semua sudah lunas!
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Mobile: Sheet Detail Penerima Arisan (dari bawah) -->
        <Sheet :open="!!selectedDrawId" @update:open="(val) => { if (!val) selectedDrawId = null }">
            <SheetContent side="bottom" class="sm:hidden rounded-t-2xl max-h-[80vh] overflow-y-auto px-4 pb-6 pt-3">
                <SheetHeader v-if="selectedDraw" class="text-left pb-2.5 border-b mb-3">
                    <SheetTitle class="text-sm">Detail Penerima Arisan: {{ formatPeriod(selectedDraw.period_month) }}</SheetTitle>
                    <SheetDescription v-if="selectedDraw.notes">{{ selectedDraw.notes }}</SheetDescription>
                    <SheetDescription v-else class="sr-only">Detail informasi penerima arisan</SheetDescription>
                </SheetHeader>

                <div v-if="selectedDraw" class="space-y-4">
                    <!-- Progress Info -->
                    <div class="flex items-center gap-3">
                        <div class="flex-1 h-2 bg-muted rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500 rounded-full transition-all duration-300"
                                 :style="{ width: (getDrawPaymentDetail(selectedDraw).total > 0 ? (getDrawPaymentDetail(selectedDraw).paid.length / getDrawPaymentDetail(selectedDraw).total * 100) : 0) + '%' }"></div>
                        </div>
                        <span class="text-xs font-medium text-muted-foreground shrink-0">
                            {{ getDrawPaymentDetail(selectedDraw).paid.length }} / {{ getDrawPaymentDetail(selectedDraw).total }} Lunas
                        </span>
                    </div>

                    <!-- Pemenang -->
                    <div class="flex gap-1.5 flex-wrap">
                        <div v-for="w in selectedDraw.winners" :key="'sw-' + w.id"
                             class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 px-2 py-1 rounded-full text-xs font-medium">
                            <Trophy class="w-3 h-3" />
                            {{ w.member.full_name }}
                        </div>
                    </div>

                    <!-- Sudah Setor -->
                    <div class="border rounded-lg overflow-hidden">
                        <div class="bg-emerald-50 dark:bg-emerald-950/20 px-3 py-2 border-b border-emerald-200 dark:border-emerald-800 flex items-center justify-between">
                            <div class="flex items-center gap-2 text-emerald-700 dark:text-emerald-300">
                                <Check class="w-3.5 h-3.5" />
                                <span class="text-xs font-semibold">Sudah Setor</span>
                            </div>
                            <span class="bg-emerald-200/50 dark:bg-emerald-800/50 text-emerald-800 dark:text-emerald-200 text-[10px] px-2 py-0.5 rounded-full font-bold">{{ getDrawPaymentDetail(selectedDraw).paid.length }}</span>
                        </div>
                        <div class="divide-y max-h-[200px] overflow-y-auto">
                            <div v-for="member in getDrawPaymentDetail(selectedDraw).paid" :key="'spaid-' + member.id" class="px-3 py-2 text-xs flex items-center justify-between">
                                <span class="truncate text-foreground">{{ member.full_name }}</span>
                                <Trophy v-if="isWinner(member.id) && selectedDraw.winners.find(w => w.member_id === member.id)" class="w-3 h-3 text-amber-500 shrink-0" />
                            </div>
                            <div v-if="getDrawPaymentDetail(selectedDraw).paid.length === 0" class="px-3 py-6 text-center text-xs text-muted-foreground">
                                Belum ada yang setor
                            </div>
                        </div>
                    </div>

                    <!-- Belum Setor -->
                    <div class="border rounded-lg overflow-hidden">
                        <div class="bg-red-50 dark:bg-red-950/20 px-3 py-2 border-b border-red-200 dark:border-red-800 flex items-center justify-between">
                            <div class="flex items-center gap-2 text-red-700 dark:text-red-300">
                                <XIcon class="w-3.5 h-3.5" />
                                <span class="text-xs font-semibold">Belum Setor</span>
                            </div>
                            <span class="bg-red-200/50 dark:bg-red-800/50 text-red-800 dark:text-red-200 text-[10px] px-2 py-0.5 rounded-full font-bold">{{ getDrawPaymentDetail(selectedDraw).unpaid.length }}</span>
                        </div>
                        <div class="divide-y max-h-[200px] overflow-y-auto">
                            <div v-for="member in getDrawPaymentDetail(selectedDraw).unpaid" :key="'sunpaid-' + member.id" class="px-3 py-2 text-xs flex items-center justify-between">
                                <div class="flex items-center gap-2 min-w-0">
                                    <XIcon class="w-3 h-3 text-red-400 shrink-0" />
                                    <span class="truncate text-foreground">{{ member.full_name }}</span>
                                </div>
                            </div>
                            <div v-if="getDrawPaymentDetail(selectedDraw).unpaid.length === 0" class="px-3 py-6 text-center text-xs text-muted-foreground">
                                Semua sudah lunas!
                            </div>
                        </div>
                    </div>
                </div>
            </SheetContent>
        </Sheet>

        <DeleteConfirmDialog :open="!!targetParticipantDelete" title="Keluarkan Peserta" description="Peserta akan dikeluarkan dari program arisan ini." @confirm="confirmDeleteParticipant" @cancel="targetParticipantDelete = null" />
        <DeleteConfirmDialog :open="!!targetDrawDelete" title="Hapus Rekam Penerima Arisan" description="Semua data pemenang di periode ini akan ikut terhapus dan mereka dianggap belum menerima arisan." @confirm="confirmDeleteDraw" @cancel="targetDrawDelete = null" />
    </AuthenticatedLayout>
</template>
