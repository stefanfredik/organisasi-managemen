<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription,
} from '@/components/ui/dialog';
import ChartWidget from '@/Components/ChartWidget.vue';
import { ref } from 'vue';
import axios from 'axios';
import {
    ArrowLeft,
    LayoutDashboard,
    Grid3x3,
    Clock,
    CheckCircle,
    AlertCircle,
    TrendingUp,
    Filter,
    Banknote,
    Users,
    Loader2,
    User,
    X,
} from 'lucide-vue-next';

const props = defineProps({
    type: Object,
    stats: Object,
    charts: Object,
    filters: Object,
});

const form = ref({
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
    period_filter: props.filters.period_filter || '',
});

const formatCurrency = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);

const applyFilter = () => {
    router.get(route('contributions.monitoring.dashboard', props.type.id), form.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const navItems = [
    { route: 'contributions.monitoring.dashboard', label: 'Dashboard', icon: LayoutDashboard },
    { route: 'contributions.monitoring.matrix', label: 'Matrix', icon: Grid3x3 },
    { route: 'contributions.monitoring.history', label: 'Riwayat', icon: Clock },
];

// Members by status modal
const showMembersModal = ref(false);
const membersLoading = ref(false);
const membersList = ref([]);
const membersCategory = ref('');

const categoryConfig = {
    paid: { label: 'Lunas', icon: CheckCircle, color: 'text-green-600', bg: 'bg-green-500/10' },
    unpaid: { label: 'Belum Bayar', icon: Users, color: 'text-amber-600', bg: 'bg-amber-500/10' },
    arrears: { label: 'Menunggak', icon: AlertCircle, color: 'text-red-600', bg: 'bg-red-500/10' },
};

const fetchMembers = async (status) => {
    membersCategory.value = status;
    membersList.value = [];
    showMembersModal.value = true;
    membersLoading.value = true;
    try {
        const res = await axios.get(route('contributions.monitoring.members-by-status', props.type.id), {
            params: {
                status,
                period_filter: form.value.period_filter || null,
            },
        });
        membersList.value = res.data;
    } catch (e) {
        console.error(e);
    } finally {
        membersLoading.value = false;
    }
};
</script>

<template>
    <Head :title="`Monitoring - ${type.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('contributions.monitoring.index')" class="shrink-0 p-1.5 rounded-lg text-muted-foreground hover:text-foreground hover:bg-muted transition-colors">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div class="min-w-0">
                    <h2 class="text-lg font-semibold leading-tight text-foreground truncate">{{ type.name }}</h2>
                </div>
            </div>
        </template>

        <div class="py-4 sm:py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-4">

                <!-- Navigation Tabs (compact) -->
                <div class="inline-flex items-center gap-1 bg-muted p-1 rounded-lg">
                    <Link
                        v-for="item in navItems"
                        :key="item.route"
                        :href="route(item.route, type.id)"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md transition-colors"
                        :class="route().current(item.route)
                            ? 'bg-card text-foreground shadow-sm'
                            : 'text-muted-foreground hover:text-foreground'"
                    >
                        <component :is="item.icon" class="w-3.5 h-3.5" />
                        {{ item.label }}
                    </Link>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                    <!-- Clickable member stats -->
                    <button
                        @click="fetchMembers('paid')"
                        class="bg-card rounded-lg border p-3 text-left hover:border-green-300 hover:shadow-sm transition-all cursor-pointer group"
                    >
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-6 h-6 rounded bg-green-500/10 flex items-center justify-center shrink-0 group-hover:bg-green-500/20 transition-colors">
                                <CheckCircle class="w-3.5 h-3.5 text-green-600" />
                            </div>
                            <p class="text-[10px] font-medium text-muted-foreground uppercase">Lunas</p>
                        </div>
                        <p class="text-lg font-bold text-green-600">{{ stats.member_status?.paid || 0 }}</p>
                    </button>
                    <button
                        @click="fetchMembers('unpaid')"
                        class="bg-card rounded-lg border p-3 text-left hover:border-amber-300 hover:shadow-sm transition-all cursor-pointer group"
                    >
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-6 h-6 rounded bg-amber-500/10 flex items-center justify-center shrink-0 group-hover:bg-amber-500/20 transition-colors">
                                <Users class="w-3.5 h-3.5 text-amber-600" />
                            </div>
                            <p class="text-[10px] font-medium text-muted-foreground uppercase">Belum</p>
                        </div>
                        <p class="text-lg font-bold text-foreground">{{ stats.member_status?.unpaid || 0 }}</p>
                    </button>
                    <button
                        @click="fetchMembers('arrears')"
                        class="bg-card rounded-lg border p-3 text-left hover:border-red-300 hover:shadow-sm transition-all cursor-pointer group"
                    >
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-6 h-6 rounded bg-red-500/10 flex items-center justify-center shrink-0 group-hover:bg-red-500/20 transition-colors">
                                <AlertCircle class="w-3.5 h-3.5 text-red-600" />
                            </div>
                            <p class="text-[10px] font-medium text-muted-foreground uppercase">Tunggak</p>
                        </div>
                        <p class="text-lg font-bold text-destructive">{{ stats.member_status?.arrears || 0 }}</p>
                    </button>
                    <!-- Financial stats (non-clickable) -->
                    <div class="bg-card rounded-lg border p-3">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-6 h-6 rounded bg-primary/10 flex items-center justify-center shrink-0">
                                <Banknote class="w-3.5 h-3.5 text-primary" />
                            </div>
                            <p class="text-[10px] font-medium text-muted-foreground uppercase">Terkumpul</p>
                        </div>
                        <p class="text-sm sm:text-lg font-bold text-primary">{{ formatCurrency(stats.total_collected) }}</p>
                    </div>
                    <div class="bg-card rounded-lg border p-3">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-6 h-6 rounded bg-amber-500/10 flex items-center justify-center shrink-0">
                                <Clock class="w-3.5 h-3.5 text-amber-500" />
                            </div>
                            <p class="text-[10px] font-medium text-muted-foreground uppercase">Pending</p>
                        </div>
                        <p class="text-lg font-bold text-amber-500">{{ stats.total_pending }}</p>
                    </div>
                    <div class="bg-card rounded-lg border p-3">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-6 h-6 rounded bg-muted flex items-center justify-center shrink-0">
                                <TrendingUp class="w-3.5 h-3.5 text-muted-foreground" />
                            </div>
                            <p class="text-[10px] font-medium text-muted-foreground uppercase">Total Trx</p>
                        </div>
                        <p class="text-lg font-bold text-foreground">{{ stats.total_transactions }}</p>
                    </div>
                </div>

                <!-- Filter (inline compact) -->
                <div class="bg-card rounded-lg border p-3">
                    <div class="flex flex-wrap items-end gap-2">
                        <div v-if="['monthly', 'yearly', 'weekly'].includes(type.period)">
                            <label class="block text-[10px] font-medium text-muted-foreground uppercase mb-1">Periode</label>
                            <Input v-if="type.period === 'monthly'" type="month" v-model="form.period_filter" class="h-8 text-xs w-36" />
                            <Input v-else-if="type.period === 'weekly'" type="week" v-model="form.period_filter" class="h-8 text-xs w-36" />
                            <Input v-else-if="type.period === 'yearly'" type="number" placeholder="YYYY" v-model="form.period_filter" class="h-8 text-xs w-20" />
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium text-muted-foreground uppercase mb-1">Dari</label>
                            <Input type="date" v-model="form.start_date" class="h-8 text-xs" />
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium text-muted-foreground uppercase mb-1">Sampai</label>
                            <Input type="date" v-model="form.end_date" class="h-8 text-xs" />
                        </div>
                        <Button size="sm" variant="outline" @click="applyFilter" class="h-8 text-xs gap-1">
                            <Filter class="w-3 h-3" />
                            Filter
                        </Button>
                    </div>
                </div>

                <!-- Charts -->
                <div v-if="charts" class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <ChartWidget
                        title="Tren Pembayaran (Per Bulan)"
                        :data="charts.monthly"
                        type="line"
                        color="primary"
                        height="250px"
                    />
                    <ChartWidget
                        v-if="charts.status_distribution"
                        title="Distribusi Status"
                        :data="charts.status_distribution"
                        type="doughnut"
                        height="250px"
                    />
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
                        Daftar anggota dengan status <strong>{{ categoryConfig[membersCategory]?.label?.toLowerCase() }}</strong> untuk iuran {{ type.name }}.
                    </DialogDescription>
                </DialogHeader>

                <!-- Loading -->
                <div v-if="membersLoading" class="py-8 flex flex-col items-center gap-2 text-muted-foreground">
                    <Loader2 class="w-5 h-5 animate-spin" />
                    <p class="text-xs">Memuat data...</p>
                </div>

                <!-- Members List -->
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

                <!-- Empty State -->
                <div v-else class="py-8 text-center">
                    <div class="w-10 h-10 rounded-xl bg-muted flex items-center justify-center mx-auto mb-2">
                        <User class="w-5 h-5 text-muted-foreground" />
                    </div>
                    <p class="text-sm text-muted-foreground">Tidak ada anggota dalam kategori ini.</p>
                </div>

                <!-- Footer count -->
                <div v-if="!membersLoading && membersList.length > 0" class="pt-3 border-t mt-2">
                    <p class="text-xs text-muted-foreground text-center">
                        Total: <span class="font-bold text-foreground">{{ membersList.length }}</span> anggota
                    </p>
                </div>
            </DialogContent>
        </Dialog>
    </AuthenticatedLayout>
</template>
