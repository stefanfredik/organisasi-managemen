<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { usePage } from "@inertiajs/vue3";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import DataTable from "@/Components/DataTable.vue";
import debounce from "lodash/debounce";
import {
    Plus, Search, SlidersHorizontal, X, MoreVertical,
    CheckCircle, Clock, XCircle, CreditCard, Banknote, Eye, Receipt,
} from "lucide-vue-next";
import {
    Sheet, SheetContent, SheetHeader, SheetTitle, SheetDescription,
} from '@/components/ui/sheet';

// Sub-components
import ActiveContributionCards from "./Partials/ActiveContributionCards.vue";
import MemberContributionDetailModal from "./Partials/MemberContributionDetailModal.vue";
import PaymentInputDialog from "./Partials/PaymentInputDialog.vue";
import VerificationDialog from "./Partials/VerificationDialog.vue";
import FilterSheet from "./Partials/FilterSheet.vue";

const props = defineProps({
    contributions: Object,
    types: Array,
    wallets: Array,
    members: Array,
    filters: Object,
    context: String,
    type: Object,
});

const page = usePage();
const userRole = computed(() => page.props.auth.user.role);

// ─── Formatting Helpers ─────────────────────────────────────────────
const formatCurrency = (value) => {
    const currency = page.props.appSettings?.financial?.currency || "Rp";
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    })
        .format(Number(value || 0))
        .replace("Rp", currency);
};

const formatDate = (value) => {
    if (!value) return "-";
    try {
        return new Date(value).toLocaleDateString("id-ID", {
            day: "2-digit",
            month: "short",
            year: "numeric",
        });
    } catch {
        return value;
    }
};

const getStatusBadge = (status) => {
    const map = {
        paid: "bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400",
        pending: "bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400",
        partial: "bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400",
        rejected: "bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400",
    };
    return map[status] || "bg-muted text-foreground";
};

const getStatusLabel = (status) => {
    const map = { paid: "Lunas", pending: "Pending", partial: "Sebagian", rejected: "Ditolak" };
    return map[status] || status || "-";
};

const getStatusIcon = (status) => {
    const map = { paid: CheckCircle, pending: Clock, rejected: XCircle };
    return map[status] || null;
};

const getPeriodLabel = (period) => {
    const map = { once: "Sekali Bayar", monthly: "Bulanan", weekly: "Mingguan", yearly: "Tahunan" };
    return map[period] || period;
};

// ─── Shared Period Options Builder ──────────────────────────────────
const buildPeriodOptions = (type) => {
    const opts = [];
    const year = new Date().getFullYear();
    if (!type) return opts;
    if (type.period === "monthly") {
        for (let m = 1; m <= 12; m++) {
            const date = new Date(year, m - 1, 1);
            const label = date.toLocaleDateString("id-ID", { month: "short" }) + " " + year;
            const value = `${year}-${String(m).padStart(2, "0")}`;
            opts.push({ value, label });
        }
    } else if (type.period === "yearly") {
        for (let i = 0; i < 5; i++) {
            const y = year - i;
            opts.push({ value: String(y), label: String(y) });
        }
    } else if (type.period === "weekly") {
        for (let w = 1; w <= 52; w++) {
            const value = `${year}-W${String(w).padStart(2, "0")}`;
            const label = `Minggu ${String(w).padStart(2, "0")} ${year}`;
            opts.push({ value, label });
        }
    } else if (type.period === "once") {
        opts.push({ value: "once", label: "Sekali" });
    }
    return opts;
};

// ─── Table Columns ──────────────────────────────────────────────────
const columns = [
    { key: "member", label: "Anggota", headerClass: "w-48" },
    { key: "type", label: "Jenis", headerClass: "w-40" },
    { key: "amount", label: "Nominal", headerClass: "w-32 text-right", cellClass: "text-right" },
    { key: "payment_date", label: "Tanggal Bayar", headerClass: "w-36" },
    { key: "payment_period", label: "Periode", headerClass: "w-28" },
    { key: "status", label: "Status", headerClass: "w-28" },
];

// ─── Route ──────────────────────────────────────────────────────────
const searchRoute = computed(() => {
    if (props.context === "admin-history" && props.type?.id) {
        return route("contributions.monitoring.history", props.type.id);
    }
    return route("contributions.index");
});

// ─── Filters ────────────────────────────────────────────────────────
const contributionsFilters = ref({
    search: props.filters?.search || "",
    contribution_type_id: props.filters?.contribution_type_id || "",
    status: props.filters?.status || "",
    payment_method: props.filters?.payment_method || "",
    wallet_id: props.filters?.wallet_id || "",
    payment_period: props.filters?.payment_period || "",
    start_date: props.filters?.start_date || "",
    end_date: props.filters?.end_date || "",
});

const statusOptions = [
    { value: "", label: "Semua Status" },
    { value: "paid", label: "Dibayar" },
    { value: "pending", label: "Pending" },
    { value: "rejected", label: "Ditolak" },
];

const methodOptions = [
    { value: "", label: "Semua Metode" },
    { value: "cash", label: "Tunai" },
    { value: "transfer", label: "Transfer" },
];

const selectedFilterType = computed(() => {
    return props.types?.find(t => t.id == contributionsFilters.value.contribution_type_id) || null;
});

const periodOptions = computed(() => buildPeriodOptions(selectedFilterType.value));

watch(() => contributionsFilters.value.contribution_type_id, () => {
    contributionsFilters.value.payment_period = "";
});

const showFiltersSheet = ref(false);

const activeFilters = computed(() => {
    const f = contributionsFilters.value;
    const items = [];
    if (f.contribution_type_id) {
        const t = props.types?.find(x => x.id == f.contribution_type_id);
        items.push({ key: "contribution_type_id", label: `Jenis: ${t?.name || f.contribution_type_id}` });
    }
    if (f.status) {
        const s = statusOptions.find(x => x.value === f.status)?.label || f.status;
        items.push({ key: "status", label: `Status: ${s}` });
    }
    if (f.payment_method) {
        const m = methodOptions.find(x => x.value === f.payment_method)?.label || f.payment_method;
        items.push({ key: "payment_method", label: `Metode: ${m}` });
    }
    if (f.wallet_id) {
        const w = props.wallets?.find(x => x.id == f.wallet_id);
        items.push({ key: "wallet_id", label: `Dompet: ${w?.name || f.wallet_id}` });
    }
    if (f.payment_period) {
        const p = periodOptions.value.find(x => x.value === f.payment_period)?.label || f.payment_period;
        items.push({ key: "payment_period", label: `Periode: ${p}` });
    }
    if (f.start_date || f.end_date) {
        items.push({ key: "date_range", label: `Tanggal: ${f.start_date || "-"} s.d. ${f.end_date || "-"}` });
    }
    return items;
});

const activeFilterCount = computed(() => activeFilters.value.length);

const clearFilter = (key) => {
    if (key === "date_range") {
        contributionsFilters.value.start_date = "";
        contributionsFilters.value.end_date = "";
    } else {
        contributionsFilters.value[key] = "";
    }
};

const resetFilters = () => {
    contributionsFilters.value = {
        search: "",
        contribution_type_id: "",
        status: "",
        payment_method: "",
        wallet_id: "",
        payment_period: "",
        start_date: "",
        end_date: "",
    };
    applyFilters();
};

const applyFilters = () => {
    router.get(searchRoute.value, { ...contributionsFilters.value }, {
        preserveState: true,
        replace: true,
    });
};

const triggerFilter = debounce(() => {
    applyFilters();
}, 300);

watch(contributionsFilters, () => {
    triggerFilter();
}, { deep: true });

// ─── Payment Modals ─────────────────────────────────────────────────
const showManualModal = ref(false);
const showMemberModal = ref(false);
const selectedPaymentType = ref(null);

const startPayment = (type) => {
    selectedPaymentType.value = type;
    if (userRole.value === "anggota") {
        showMemberModal.value = true;
    } else {
        showManualModal.value = true;
    }
};

const handlePaymentSuccess = () => {
    router.reload({ preserveScroll: true });
};

// ─── Verification ───────────────────────────────────────────────────
const showVerifyModal = ref(false);
const selectedContribution = ref(null);
const verifyProcessingId = ref(null);

const openVerifyModal = (row) => {
    selectedContribution.value = row;
    showVerifyModal.value = true;
};

const handleVerify = ({ id, action, comment }) => {
    verifyProcessingId.value = id;
    router.post(route("contributions.verify-action", id), { action, comment }, {
        preserveScroll: true,
        replace: true,
        onFinish: () => {
            verifyProcessingId.value = null;
            showVerifyModal.value = false;
            selectedContribution.value = null;
        },
    });
};

// ─── Mobile Detail Sheet ────────────────────────────────────────────
const showDetailSheet = ref(false);
const detailRow = ref(null);
const openDetailSheet = (row) => { detailRow.value = row; showDetailSheet.value = true; };
const closeDetailSheet = () => { showDetailSheet.value = false; detailRow.value = null; };
</script>

<template>
    <Head
        :title="userRole === 'anggota' ? 'Iuran Saya' : 'Manajemen Iuran'"
    />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold leading-tight text-foreground">
                    {{ userRole === "anggota" ? "Iuran Saya" : "Manajemen Iuran" }}
                </h2>
                <Button
                    v-if="['admin', 'bendahara', 'anggota'].includes(userRole)"
                    size="sm"
                    @click="userRole === 'anggota' ? (showMemberModal = true) : (showManualModal = true)"
                >
                    <Plus class="w-4 h-4 mr-1" />
                    <span class="hidden sm:inline">
                        {{ userRole === "anggota" ? "Bayar Iuran" : "Input Pembayaran" }}
                    </span>
                </Button>
            </div>
        </template>

        <div class="py-3 sm:py-6">
            <div class="mx-auto max-w-7xl px-3 sm:px-6 lg:px-8 space-y-3 sm:space-y-5">

                <!-- Active Contribution Types -->
                <ActiveContributionCards
                    :types="types"
                    :user-role="userRole"
                    :format-currency="formatCurrency"
                    @pay="startPayment"
                />

                <!-- Transaction History -->
                <div class="bg-card rounded-xl border overflow-hidden">
                    <!-- Header + Search + Filter -->
                    <div class="px-3 sm:px-5 py-2.5 sm:py-4 space-y-2 sm:space-y-3">
                        <!-- Title row -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                                    <Receipt class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-primary" />
                                </div>
                                <div>
                                    <h3 class="text-xs sm:text-sm font-semibold text-foreground">Riwayat Pembayaran</h3>
                                    <p class="text-xs text-muted-foreground hidden sm:block">Lacak transaksi dan status verifikasi</p>
                                </div>
                            </div>
                            <Button
                                variant="outline"
                                size="sm"
                                class="h-7 sm:h-9"
                                @click="showFiltersSheet = true"
                            >
                                <SlidersHorizontal class="w-3.5 h-3.5 sm:w-4 sm:h-4 sm:mr-1.5" />
                                <span class="hidden sm:inline">Filter</span>
                                <Badge
                                    v-if="activeFilterCount"
                                    class="ml-1 sm:ml-1.5 h-4 sm:h-5 min-w-4 sm:min-w-5 px-1 flex items-center justify-center text-[10px] sm:text-xs rounded-full"
                                >
                                    {{ activeFilterCount }}
                                </Badge>
                            </Button>
                        </div>

                        <!-- Search bar -->
                        <div class="relative">
                            <Search class="absolute left-2.5 sm:left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 sm:h-4 sm:w-4 text-muted-foreground pointer-events-none" />
                            <Input
                                v-model="contributionsFilters.search"
                                placeholder="Cari nama anggota..."
                                class="pl-8 sm:pl-9 h-7 sm:h-9 text-xs sm:text-sm bg-muted/40 border-transparent focus:bg-card focus:border-input transition-colors"
                            />
                        </div>

                        <!-- Active Filter Chips -->
                        <div v-if="activeFilters.length" class="flex flex-wrap items-center gap-1">
                            <button
                                v-for="f in activeFilters"
                                :key="f.key"
                                type="button"
                                class="group inline-flex items-center gap-0.5 rounded-full bg-primary/10 text-primary pl-2 pr-1 py-px text-[10px] sm:text-xs font-medium hover:bg-primary/15 transition-colors"
                                @click="clearFilter(f.key)"
                            >
                                <span>{{ f.label }}</span>
                                <X class="w-2.5 h-2.5 sm:w-3 sm:h-3 opacity-50 group-hover:opacity-100" />
                            </button>
                            <button
                                type="button"
                                class="text-[10px] sm:text-xs text-muted-foreground hover:text-foreground font-medium transition-colors ml-1"
                                @click="resetFilters"
                            >
                                Hapus semua
                            </button>
                        </div>
                    </div>

                    <div class="border-t" />

                    <!-- Mobile: Compact List -->
                    <div class="md:hidden divide-y">
                        <div
                            v-for="row in contributions.data"
                            :key="'m-' + row.id"
                            class="px-3 py-2 flex items-center gap-2.5"
                            @click="openDetailSheet(row)"
                        >
                            <!-- Status dot -->
                            <div
                                class="w-1.5 h-1.5 rounded-full shrink-0"
                                :class="row.status === 'paid' ? 'bg-green-500' : row.status === 'pending' ? 'bg-yellow-500' : 'bg-red-500'"
                            />
                            <!-- Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-1">
                                    <p class="text-xs font-semibold text-foreground truncate">
                                        {{ row.member?.full_name || '-' }}
                                    </p>
                                    <span class="text-xs font-bold text-primary tabular-nums shrink-0">{{ formatCurrency(row.amount) }}</span>
                                </div>
                                <div class="flex items-center gap-1.5 text-[10px] text-muted-foreground mt-0.5">
                                    <span>{{ row.type?.name || '-' }}</span>
                                    <span class="text-border">·</span>
                                    <span>{{ formatDate(row.payment_date) }}</span>
                                    <span class="text-border">·</span>
                                    <span>{{ row.payment_period || '-' }}</span>
                                </div>
                            </div>
                            <!-- Hamburger -->
                            <Button variant="ghost" size="icon" class="h-7 w-7 shrink-0" @click.stop="openDetailSheet(row)">
                                <MoreVertical class="w-4 h-4 text-muted-foreground" />
                            </Button>
                        </div>
                        <div v-if="!contributions.data || contributions.data.length === 0" class="py-8 text-center">
                            <Search class="h-6 w-6 text-muted-foreground/50 mx-auto mb-1.5" />
                            <p class="text-xs text-muted-foreground">Data tidak ditemukan</p>
                        </div>
                        <!-- Mobile Pagination -->
                        <div v-if="contributions.total > contributions.per_page" class="px-3 py-2 flex items-center justify-between bg-muted/30">
                            <p class="text-[10px] text-muted-foreground">{{ contributions.from }}-{{ contributions.to }} dari {{ contributions.total }}</p>
                            <div class="flex gap-1">
                                <Button v-if="contributions.prev_page_url" variant="outline" size="sm" class="h-6 text-[10px] px-2" as-child>
                                    <Link :href="contributions.prev_page_url">Sebelumnya</Link>
                                </Button>
                                <Button v-if="contributions.next_page_url" variant="outline" size="sm" class="h-6 text-[10px] px-2" as-child>
                                    <Link :href="contributions.next_page_url">Selanjutnya</Link>
                                </Button>
                            </div>
                        </div>
                    </div>

                    <!-- Desktop Table -->
                    <div class="hidden md:block">
                        <DataTable
                            :data="contributions"
                            :columns="columns"
                            :filters="contributionsFilters"
                            :searchRoute="searchRoute"
                            :striped="true"
                            :dense="true"
                            :searchable="false"
                            class="!border-0 shadow-none"
                        >
                            <template #cell-member="{ row }">
                                <div class="flex items-center gap-2.5">
                                    <div class="h-8 w-8 shrink-0 rounded-full bg-primary/10 border flex items-center justify-center text-primary font-bold text-xs">
                                        {{ (row.member?.full_name || "?").charAt(0).toUpperCase() }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-foreground leading-tight truncate">
                                            {{ row.member?.full_name || "-" }}
                                        </p>
                                        <p class="text-[11px] text-muted-foreground font-mono">
                                            {{ row.member?.member_code || "NON-MEMBER" }}
                                        </p>
                                    </div>
                                </div>
                            </template>

                            <template #cell-type="{ row }">
                                <div>
                                    <p class="text-sm font-medium text-foreground leading-tight">
                                        {{ row.type?.name || "-" }}
                                    </p>
                                    <p class="text-[11px] text-muted-foreground">
                                        {{ getPeriodLabel(row.type?.period) }}
                                    </p>
                                </div>
                            </template>

                            <template #cell-amount="{ row }">
                                <span class="text-sm font-semibold text-foreground tabular-nums">
                                    {{ formatCurrency(row.amount) }}
                                </span>
                            </template>

                            <template #cell-payment_date="{ row }">
                                <div>
                                    <p class="text-sm text-foreground leading-tight">
                                        {{ formatDate(row.payment_date) }}
                                    </p>
                                    <p class="text-[11px] text-muted-foreground flex items-center gap-1 mt-0.5">
                                        <CreditCard v-if="row.payment_method === 'transfer'" class="w-3 h-3" />
                                        <Banknote v-else class="w-3 h-3" />
                                        {{ row.payment_method === "transfer" ? "Transfer" : "Tunai" }}
                                    </p>
                                </div>
                            </template>

                            <template #cell-payment_period="{ row }">
                                <Badge variant="outline" class="text-[11px] font-mono">
                                    {{ row.payment_period || "-" }}
                                </Badge>
                            </template>

                            <template #cell-status="{ row }">
                                <span
                                    :class="[
                                        'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold',
                                        getStatusBadge(row.status),
                                    ]"
                                >
                                    <component :is="getStatusIcon(row.status)" v-if="getStatusIcon(row.status)" class="w-3 h-3" />
                                    {{ getStatusLabel(row.status) }}
                                </span>
                            </template>

                            <template #actions="{ row }">
                                <div class="inline-flex items-center gap-1 justify-end w-full">
                                    <Button
                                        v-if="row.status === 'pending' && (userRole === 'admin' || userRole === 'bendahara')"
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 text-green-600 hover:text-green-700 hover:bg-green-50 dark:hover:bg-green-950/30"
                                        title="Verifikasi"
                                        @click="openVerifyModal(row)"
                                    >
                                        <CheckCircle class="w-4 h-4" />
                                    </Button>
                                    <Button
                                        as-child
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8"
                                        title="Detail"
                                    >
                                        <Link :href="route('contributions.show', row)">
                                            <Eye class="w-4 h-4" />
                                        </Link>
                                    </Button>
                                </div>
                            </template>
                        </DataTable>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Sheet -->
        <FilterSheet
            v-model:open="showFiltersSheet"
            :filters="contributionsFilters"
            :types="types"
            :wallets="wallets"
            :status-options="statusOptions"
            :method-options="methodOptions"
            :period-options="periodOptions"
            @reset="resetFilters"
        />

        <!-- Verification Dialog -->
        <VerificationDialog
            v-model:open="showVerifyModal"
            :contribution="selectedContribution"
            :format-currency="formatCurrency"
            :format-date="formatDate"
            @verify="handleVerify"
        />

        <!-- Member Payment Modal (for anggota) -->
        <MemberContributionDetailModal
            :show="showMemberModal"
            :type="selectedPaymentType"
            @close="showMemberModal = false"
            @success="handlePaymentSuccess"
        />

        <!-- Admin Payment Input Dialog -->
        <PaymentInputDialog
            v-model:open="showManualModal"
            :types="types"
            :wallets="wallets"
            :members="members"
            :initial-type-id="selectedPaymentType?.id"
            :format-currency="formatCurrency"
            :build-period-options="buildPeriodOptions"
            @success="handlePaymentSuccess"
        />

        <!-- Mobile: Transaction Detail Sheet -->
        <Sheet :open="showDetailSheet" @update:open="val => { if (!val) closeDetailSheet(); }">
            <SheetContent side="bottom" class="rounded-t-2xl max-h-[80vh] overflow-y-auto px-4 pb-6 pt-3">
                <SheetHeader class="text-left pb-2.5 border-b mb-3">
                    <SheetTitle class="text-sm">Detail Pembayaran</SheetTitle>
                    <SheetDescription class="sr-only">Detail pembayaran iuran</SheetDescription>
                </SheetHeader>

                <template v-if="detailRow">
                    <!-- Member info -->
                    <div class="flex items-center gap-2.5 mb-3">
                        <div class="h-9 w-9 shrink-0 rounded-full bg-primary/10 border flex items-center justify-center text-primary font-bold text-xs">
                            {{ (detailRow.member?.full_name || '?').charAt(0).toUpperCase() }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-foreground truncate">{{ detailRow.member?.full_name || '-' }}</p>
                            <p class="text-[11px] text-muted-foreground font-mono">{{ detailRow.member?.member_code || 'NON-MEMBER' }}</p>
                        </div>
                        <span
                            :class="['px-1.5 py-0.5 rounded-full text-[10px] font-semibold shrink-0', getStatusBadge(detailRow.status)]"
                        >
                            {{ getStatusLabel(detailRow.status) }}
                        </span>
                    </div>

                    <!-- Amount -->
                    <div class="bg-primary/5 border border-primary/20 rounded-lg p-3 text-center mb-3">
                        <p class="text-[10px] text-muted-foreground uppercase font-medium">Nominal</p>
                        <p class="text-lg font-bold text-primary tabular-nums">{{ formatCurrency(detailRow.amount) }}</p>
                    </div>

                    <!-- Detail grid -->
                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <div class="bg-muted/50 rounded-lg p-2.5">
                            <p class="text-[10px] text-muted-foreground uppercase font-medium">Jenis Iuran</p>
                            <p class="text-xs font-medium text-foreground mt-0.5">{{ detailRow.type?.name || '-' }}</p>
                            <p class="text-[10px] text-muted-foreground">{{ getPeriodLabel(detailRow.type?.period) }}</p>
                        </div>
                        <div class="bg-muted/50 rounded-lg p-2.5">
                            <p class="text-[10px] text-muted-foreground uppercase font-medium">Periode</p>
                            <p class="text-xs font-medium text-foreground mt-0.5">{{ detailRow.payment_period || '-' }}</p>
                        </div>
                        <div class="bg-muted/50 rounded-lg p-2.5">
                            <p class="text-[10px] text-muted-foreground uppercase font-medium">Tanggal Bayar</p>
                            <p class="text-xs font-medium text-foreground mt-0.5">{{ formatDate(detailRow.payment_date) }}</p>
                        </div>
                        <div class="bg-muted/50 rounded-lg p-2.5">
                            <p class="text-[10px] text-muted-foreground uppercase font-medium">Metode</p>
                            <p class="text-xs font-medium text-foreground mt-0.5 flex items-center gap-1">
                                <CreditCard v-if="detailRow.payment_method === 'transfer'" class="w-3 h-3" />
                                <Banknote v-else class="w-3 h-3" />
                                {{ detailRow.payment_method === 'transfer' ? 'Transfer' : 'Tunai' }}
                            </p>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div v-if="detailRow.notes" class="bg-muted/50 rounded-lg p-2.5 mb-3">
                        <p class="text-[10px] text-muted-foreground uppercase font-medium">Catatan</p>
                        <p class="text-xs text-foreground mt-0.5">{{ detailRow.notes }}</p>
                    </div>

                    <!-- Receipt -->
                    <div v-if="detailRow.receipt_path" class="mb-3">
                        <a :href="'/storage/' + detailRow.receipt_path" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-primary font-medium underline">
                            <Eye class="w-3.5 h-3.5" />
                            Lihat Bukti Pembayaran
                        </a>
                    </div>

                    <!-- Actions -->
                    <div class="space-y-2 border-t pt-3">
                        <Button
                            v-if="detailRow.status === 'pending' && (userRole === 'admin' || userRole === 'bendahara')"
                            variant="outline"
                            size="sm"
                            class="w-full justify-start"
                            @click="closeDetailSheet(); openVerifyModal(detailRow);"
                        >
                            <CheckCircle class="w-4 h-4 mr-2 text-green-600" />
                            Verifikasi Pembayaran
                        </Button>
                        <Button variant="outline" size="sm" class="w-full justify-start" as-child>
                            <Link :href="route('contributions.show', detailRow)" @click="closeDetailSheet()">
                                <Eye class="w-4 h-4 mr-2" />
                                Lihat Detail
                            </Link>
                        </Button>
                    </div>
                </template>
            </SheetContent>
        </Sheet>
    </AuthenticatedLayout>
</template>
