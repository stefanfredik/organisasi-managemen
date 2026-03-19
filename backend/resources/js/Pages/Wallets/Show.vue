<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, reactive, watch } from "vue";
import WalletCharts from "./Partials/WalletCharts.vue";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
    DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
    ArrowLeft, MoreVertical, Pencil, Trash2, Wallet, TrendingUp, TrendingDown,
    Calendar, Search, BarChart3, ArrowUpRight, ArrowDownRight, CreditCard,
    Users, Receipt,
} from "lucide-vue-next";
import DeleteConfirmDialog from '@/Components/DeleteConfirmDialog.vue';
import { debounce } from "lodash";
import { useToast } from '@/composables/useToast';

const toast = useToast();

const props = defineProps({
    wallet: Object,
    finances: Object,
    contributions: Object,
    stats: Object,
    chart_data: Object,
    filters: Object,
    available_years: Array,
});

const activeTab = ref("charts");

const financeFilters = reactive({
    finance_start_date: props.filters.finance_start_date || "",
    finance_end_date: props.filters.finance_end_date || "",
    finance_type: props.filters.finance_type || "",
    finance_search: props.filters.finance_search || "",
});

const contributionFilters = reactive({
    contrib_start_date: props.filters.contrib_start_date || "",
    contrib_end_date: props.filters.contrib_end_date || "",
    contrib_search: props.filters.contrib_search || "",
});

const chartFilter = reactive({
    chart_year: props.filters.chart_year || new Date().getFullYear(),
});

const applyFilters = debounce(() => {
    router.get(route("wallets.show", props.wallet.id), {
        ...financeFilters,
        ...contributionFilters,
        ...chartFilter,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}, 500);

watch(financeFilters, applyFilters);
watch(contributionFilters, applyFilters);

watch(() => chartFilter.chart_year, () => {
    router.get(route("wallets.show", props.wallet.id), {
        ...financeFilters,
        ...contributionFilters,
        ...chartFilter,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("id-ID", { day: "2-digit", month: "short", year: "numeric" });
};

const showDeleteModal = ref(false);
const deleteWallet = () => {
    showDeleteModal.value = true;
};
const confirmDeleteWallet = () => {
    router.delete(route("wallets.destroy", props.wallet.id), {
        onError: (errors) => toast.error(Object.values(errors).flat().join(', ') || 'Gagal menghapus dompet.'),
        onFinish: () => (showDeleteModal.value = false),
    });
};

const tabs = [
    { key: "charts", label: "Analisis", icon: BarChart3 },
    { key: "finances", label: "Transaksi", icon: Receipt },
    { key: "contributions", label: "Iuran", icon: Users },
];
</script>

<template>
    <Head :title="`Detail Dompet ${wallet.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <Link :href="route('wallets.index')" class="shrink-0 p-1.5 rounded-lg text-muted-foreground hover:text-foreground hover:bg-muted transition-colors">
                        <ArrowLeft class="w-5 h-5" />
                    </Link>
                    <h2 class="text-lg font-semibold leading-tight text-foreground truncate">{{ wallet.name }}</h2>
                    <Badge v-if="!wallet.is_active" variant="secondary" class="shrink-0">Nonaktif</Badge>
                </div>
                <DropdownMenu v-if="hasPermission('manage_wallets')">
                    <DropdownMenuTrigger as-child>
                        <Button variant="outline" size="sm">
                            <MoreVertical class="w-4 h-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuItem as-child>
                            <Link :href="route('wallets.edit', wallet)" class="flex items-center gap-2">
                                <Pencil class="w-4 h-4" />
                                Edit Dompet
                            </Link>
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem @click="deleteWallet" class="text-destructive flex items-center gap-2">
                            <Trash2 class="w-4 h-4" />
                            Hapus Dompet
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </template>

        <div class="py-4 sm:py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">

                <!-- Top: Balance Card + Stats -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <!-- Wallet Card -->
                    <div
                        class="relative w-full rounded-2xl overflow-hidden text-white shadow-lg p-5"
                        :class="[
                            wallet.is_active
                                ? 'bg-gradient-to-br from-cyan-500 via-blue-600 to-blue-800'
                                : 'bg-gradient-to-br from-gray-400 via-gray-500 to-gray-600'
                        ]"
                    >
                        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 50% -20%, rgba(255,255,255,0.8), transparent 50%);"></div>
                        <div class="absolute top-0 right-0 -mr-12 -mt-12 w-48 h-48 rounded-full bg-white opacity-5 blur-3xl"></div>

                        <div class="relative z-10">
                            <div class="flex items-start justify-between mb-5">
                                <div>
                                    <h3 class="font-bold text-base tracking-wide">{{ wallet.name }}</h3>
                                    <p v-if="wallet.description" class="text-[11px] text-white/60 mt-0.5">{{ wallet.description }}</p>
                                </div>
                                <CreditCard class="w-6 h-6 opacity-50 shrink-0" />
                            </div>

                            <div class="mb-4">
                                <p class="text-[10px] uppercase tracking-widest text-white/60 mb-0.5">Saldo</p>
                                <p class="text-2xl font-bold font-mono tracking-wide">{{ formatCurrency(wallet.balance) }}</p>
                            </div>

                            <div class="flex items-end justify-between">
                                <div class="flex gap-4">
                                    <div>
                                        <p class="text-[9px] uppercase text-white/50">Masuk</p>
                                        <p class="text-xs font-mono font-semibold text-green-200">+{{ formatCurrency(stats.total_income) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[9px] uppercase text-white/50">Keluar</p>
                                        <p class="text-xs font-mono font-semibold text-red-200">-{{ formatCurrency(stats.total_expense) }}</p>
                                    </div>
                                </div>
                                <div class="w-10 h-7 bg-yellow-200/80 rounded-sm relative overflow-hidden shadow-sm">
                                    <div class="absolute inset-0 border-[0.5px] border-yellow-600/30 rounded-sm"></div>
                                    <div class="w-full h-px bg-yellow-600/30 absolute top-1/3"></div>
                                    <div class="w-full h-px bg-yellow-600/30 absolute bottom-1/3"></div>
                                    <div class="h-full w-px bg-yellow-600/30 absolute left-1/3"></div>
                                    <div class="h-full w-px bg-yellow-600/30 absolute right-1/3"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="bg-card rounded-xl border p-4">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-9 h-9 rounded-lg bg-green-500/10 flex items-center justify-center shrink-0">
                                    <TrendingUp class="w-4 h-4 text-green-500" />
                                </div>
                                <p class="text-[10px] font-bold uppercase text-muted-foreground tracking-wider">Total Pemasukan</p>
                            </div>
                            <p class="text-xl font-bold text-foreground mb-2">{{ formatCurrency(stats.total_income) }}</p>
                            <div class="space-y-1.5 pt-2 border-t">
                                <div class="flex justify-between text-xs">
                                    <span class="text-muted-foreground">Transaksi Umum</span>
                                    <span class="font-semibold text-foreground">{{ formatCurrency(stats.finance_income) }}</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-muted-foreground">Iuran Anggota</span>
                                    <span class="font-semibold text-foreground">{{ formatCurrency(stats.contribution_income) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-card rounded-xl border p-4">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-9 h-9 rounded-lg bg-red-500/10 flex items-center justify-center shrink-0">
                                    <TrendingDown class="w-4 h-4 text-red-500" />
                                </div>
                                <p class="text-[10px] font-bold uppercase text-muted-foreground tracking-wider">Total Pengeluaran</p>
                            </div>
                            <p class="text-xl font-bold text-foreground mb-2">{{ formatCurrency(stats.total_expense) }}</p>
                            <div class="pt-2 border-t">
                                <p class="text-xs text-muted-foreground">Total dana yang dikeluarkan dari dompet ini.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs Section -->
                <div class="bg-card rounded-xl border overflow-hidden">
                    <!-- Tab Headers -->
                    <div class="flex border-b overflow-x-auto">
                        <button
                            v-for="tab in tabs"
                            :key="tab.key"
                            @click="activeTab = tab.key"
                            :class="[
                                'flex items-center gap-1.5 px-4 py-3 text-xs font-semibold transition-colors whitespace-nowrap border-b-2',
                                activeTab === tab.key
                                    ? 'border-primary text-primary'
                                    : 'border-transparent text-muted-foreground hover:text-foreground'
                            ]"
                        >
                            <component :is="tab.icon" class="w-3.5 h-3.5" />
                            {{ tab.label }}
                        </button>
                    </div>

                    <div class="p-4">
                        <!-- Charts Tab -->
                        <div v-if="activeTab === 'charts'">
                            <div class="flex items-center justify-end mb-4">
                                <div class="flex items-center gap-2">
                                    <label class="text-xs font-medium text-muted-foreground">Tahun:</label>
                                    <select
                                        v-model="chartFilter.chart_year"
                                        class="text-sm border border-input bg-background rounded-md px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-ring"
                                    >
                                        <option v-for="year in available_years" :key="year" :value="year">{{ year }}</option>
                                    </select>
                                </div>
                            </div>
                            <WalletCharts :data="chart_data" />
                        </div>

                        <!-- Finances Tab -->
                        <div v-if="activeTab === 'finances'">
                            <!-- Filters -->
                            <div class="rounded-lg border bg-muted/30 p-3 mb-4 space-y-3">
                                <!-- Row 1: Search + Type -->
                                <div class="flex flex-col sm:flex-row gap-2">
                                    <div class="relative flex-1">
                                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-muted-foreground pointer-events-none" />
                                        <input
                                            v-model="financeFilters.finance_search"
                                            type="text"
                                            placeholder="Cari transaksi..."
                                            class="w-full h-9 pl-8 pr-3 text-sm border border-input bg-background rounded-lg focus:outline-none focus:ring-2 focus:ring-ring"
                                        />
                                    </div>
                                    <select
                                        v-model="financeFilters.finance_type"
                                        class="h-9 text-sm border border-input bg-background rounded-lg px-3 focus:outline-none focus:ring-2 focus:ring-ring sm:w-36"
                                    >
                                        <option value="">Semua Jenis</option>
                                        <option value="in">Masuk</option>
                                        <option value="out">Keluar</option>
                                    </select>
                                </div>
                                <!-- Row 2: Date range -->
                                <div class="flex items-center gap-2">
                                    <Calendar class="w-3.5 h-3.5 text-muted-foreground shrink-0 hidden sm:block" />
                                    <input
                                        v-model="financeFilters.finance_start_date"
                                        type="date"
                                        class="h-9 text-sm border border-input bg-background rounded-lg px-3 focus:outline-none focus:ring-2 focus:ring-ring flex-1"
                                    />
                                    <span class="text-xs text-muted-foreground shrink-0">s/d</span>
                                    <input
                                        v-model="financeFilters.finance_end_date"
                                        type="date"
                                        class="h-9 text-sm border border-input bg-background rounded-lg px-3 focus:outline-none focus:ring-2 focus:ring-ring flex-1"
                                    />
                                </div>
                            </div>

                            <!-- Desktop Table -->
                            <div class="hidden md:block overflow-x-auto">
                                <table class="min-w-full divide-y divide-border">
                                    <thead class="bg-muted/50">
                                        <tr>
                                            <th class="px-4 py-2.5 text-left text-xs font-medium text-muted-foreground uppercase">Tanggal</th>
                                            <th class="px-4 py-2.5 text-left text-xs font-medium text-muted-foreground uppercase">Kategori & Keterangan</th>
                                            <th class="px-4 py-2.5 text-right text-xs font-medium text-muted-foreground uppercase">Jumlah</th>
                                            <th class="px-4 py-2.5 text-left text-xs font-medium text-muted-foreground uppercase">Oleh</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-border">
                                        <tr v-for="finance in finances.data" :key="finance.id" class="hover:bg-muted/50 transition-colors">
                                            <td class="px-4 py-3 text-sm text-muted-foreground whitespace-nowrap">{{ formatDate(finance.transaction_date) }}</td>
                                            <td class="px-4 py-3">
                                                <p class="text-sm font-medium text-foreground">{{ finance.category }}</p>
                                                <p v-if="finance.description" class="text-xs text-muted-foreground mt-0.5">{{ finance.description }}</p>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-right font-mono font-semibold whitespace-nowrap" :class="finance.type === 'in' ? 'text-green-600' : 'text-destructive'">
                                                <span class="inline-flex items-center gap-1">
                                                    <ArrowUpRight v-if="finance.type === 'in'" class="w-3.5 h-3.5" />
                                                    <ArrowDownRight v-else class="w-3.5 h-3.5" />
                                                    {{ formatCurrency(finance.amount) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-xs text-muted-foreground">{{ finance.creator?.name || "-" }}</td>
                                        </tr>
                                        <tr v-if="finances.data.length === 0">
                                            <td colspan="4" class="px-4 py-12 text-center text-sm text-muted-foreground">Belum ada transaksi.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Mobile Cards -->
                            <div class="md:hidden space-y-2">
                                <div
                                    v-for="finance in finances.data"
                                    :key="finance.id"
                                    class="flex items-center gap-3 p-3 rounded-lg border"
                                >
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                                         :class="finance.type === 'in' ? 'bg-green-500/10' : 'bg-red-500/10'">
                                        <ArrowUpRight v-if="finance.type === 'in'" class="w-4 h-4 text-green-500" />
                                        <ArrowDownRight v-else class="w-4 h-4 text-red-500" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-medium text-foreground truncate">{{ finance.category }}</p>
                                        <p class="text-[10px] text-muted-foreground">{{ formatDate(finance.transaction_date) }}</p>
                                    </div>
                                    <p class="text-xs font-mono font-semibold shrink-0" :class="finance.type === 'in' ? 'text-green-600' : 'text-destructive'">
                                        {{ finance.type === 'in' ? '+' : '-' }}{{ formatCurrency(finance.amount) }}
                                    </p>
                                </div>
                                <div v-if="finances.data.length === 0" class="py-12 text-center text-sm text-muted-foreground">
                                    Belum ada transaksi.
                                </div>
                            </div>

                            <!-- Pagination -->
                            <div v-if="finances.links && finances.links.length > 3" class="mt-4 flex justify-end">
                                <div class="flex gap-1">
                                    <Link
                                        v-for="(link, k) in finances.links"
                                        :key="k"
                                        :href="link.url"
                                        v-html="link.label"
                                        class="px-2.5 py-1 border rounded-md text-xs transition-colors"
                                        :class="link.active ? 'bg-primary text-primary-foreground border-primary' : 'bg-card text-muted-foreground hover:bg-muted'"
                                        :preserve-scroll="true"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Contributions Tab -->
                        <div v-if="activeTab === 'contributions'">
                            <!-- Filters -->
                            <div class="rounded-lg border bg-muted/30 p-3 mb-4 space-y-3">
                                <!-- Row 1: Search -->
                                <div class="relative">
                                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-muted-foreground pointer-events-none" />
                                    <input
                                        v-model="contributionFilters.contrib_search"
                                        type="text"
                                        placeholder="Cari nama atau kode anggota..."
                                        class="w-full h-9 pl-8 pr-3 text-sm border border-input bg-background rounded-lg focus:outline-none focus:ring-2 focus:ring-ring"
                                    />
                                </div>
                                <!-- Row 2: Date range -->
                                <div class="flex items-center gap-2">
                                    <Calendar class="w-3.5 h-3.5 text-muted-foreground shrink-0 hidden sm:block" />
                                    <input
                                        v-model="contributionFilters.contrib_start_date"
                                        type="date"
                                        class="h-9 text-sm border border-input bg-background rounded-lg px-3 focus:outline-none focus:ring-2 focus:ring-ring flex-1"
                                    />
                                    <span class="text-xs text-muted-foreground shrink-0">s/d</span>
                                    <input
                                        v-model="contributionFilters.contrib_end_date"
                                        type="date"
                                        class="h-9 text-sm border border-input bg-background rounded-lg px-3 focus:outline-none focus:ring-2 focus:ring-ring flex-1"
                                    />
                                </div>
                            </div>

                            <!-- Desktop Table -->
                            <div class="hidden md:block overflow-x-auto">
                                <table class="min-w-full divide-y divide-border">
                                    <thead class="bg-muted/50">
                                        <tr>
                                            <th class="px-4 py-2.5 text-left text-xs font-medium text-muted-foreground uppercase">Tgl Bayar</th>
                                            <th class="px-4 py-2.5 text-left text-xs font-medium text-muted-foreground uppercase">Anggota</th>
                                            <th class="px-4 py-2.5 text-left text-xs font-medium text-muted-foreground uppercase">Jenis Iuran</th>
                                            <th class="px-4 py-2.5 text-right text-xs font-medium text-muted-foreground uppercase">Jumlah</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-border">
                                        <tr v-for="contrib in contributions.data" :key="contrib.id" class="hover:bg-muted/50 transition-colors">
                                            <td class="px-4 py-3 text-sm text-muted-foreground whitespace-nowrap">{{ formatDate(contrib.payment_date) }}</td>
                                            <td class="px-4 py-3">
                                                <p class="text-sm font-medium text-foreground">{{ contrib.member?.full_name }}</p>
                                                <p class="text-[10px] text-muted-foreground">{{ contrib.member?.member_code }}</p>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-muted-foreground">{{ contrib.type?.name }}</td>
                                            <td class="px-4 py-3 text-sm text-right font-mono font-semibold text-green-600">
                                                <span class="inline-flex items-center gap-1">
                                                    <ArrowUpRight class="w-3.5 h-3.5" />
                                                    {{ formatCurrency(contrib.amount) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr v-if="contributions.data.length === 0">
                                            <td colspan="4" class="px-4 py-12 text-center text-sm text-muted-foreground">Belum ada iuran masuk.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Mobile Cards -->
                            <div class="md:hidden space-y-2">
                                <div
                                    v-for="contrib in contributions.data"
                                    :key="contrib.id"
                                    class="flex items-center gap-3 p-3 rounded-lg border"
                                >
                                    <div class="w-8 h-8 rounded-lg bg-green-500/10 flex items-center justify-center shrink-0">
                                        <ArrowUpRight class="w-4 h-4 text-green-500" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-medium text-foreground truncate">{{ contrib.member?.full_name }}</p>
                                        <p class="text-[10px] text-muted-foreground">{{ contrib.type?.name }} &middot; {{ formatDate(contrib.payment_date) }}</p>
                                    </div>
                                    <p class="text-xs font-mono font-semibold text-green-600 shrink-0">
                                        +{{ formatCurrency(contrib.amount) }}
                                    </p>
                                </div>
                                <div v-if="contributions.data.length === 0" class="py-12 text-center text-sm text-muted-foreground">
                                    Belum ada iuran masuk.
                                </div>
                            </div>

                            <!-- Pagination -->
                            <div v-if="contributions.links && contributions.links.length > 3" class="mt-4 flex justify-end">
                                <div class="flex gap-1">
                                    <Link
                                        v-for="(link, k) in contributions.links"
                                        :key="k"
                                        :href="link.url"
                                        v-html="link.label"
                                        class="px-2.5 py-1 border rounded-md text-xs transition-colors"
                                        :class="link.active ? 'bg-primary text-primary-foreground border-primary' : 'bg-card text-muted-foreground hover:bg-muted'"
                                        :preserve-scroll="true"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <DeleteConfirmDialog
            :open="showDeleteModal"
            title="Hapus Dompet"
            description="Apakah Anda yakin ingin menghapus dompet ini? Semua transaksi terkait juga akan dihapus. Tindakan ini tidak dapat dibatalkan."
            @confirm="confirmDeleteWallet"
            @cancel="showDeleteModal = false"
        />
    </AuthenticatedLayout>
</template>
