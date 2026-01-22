<script setup>
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import DataTable from "@/Components/DataTable.vue";
import { usePage } from "@inertiajs/vue3";
import Modal from "@/Components/Modal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import ContributionTabs from "@/Pages/Contributions/Partials/ContributionTabs.vue";
import SearchBar from "@/Components/SearchBar.vue";
import FilterDropdown from "@/Components/FilterDropdown.vue";
import debounce from "lodash/debounce";

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

const searchRoute = computed(() => {
    if (props.context === "admin-history" && props.type?.id) {
        return route("contributions.monitoring.history", props.type.id);
    }
    return route("contributions.index");
});

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
    switch (status) {
        case "paid":
            return "bg-green-100 text-green-800";
        case "pending":
            return "bg-amber-100 text-amber-800";
        case "partial":
            return "bg-blue-100 text-blue-800";
        case "rejected":
            return "bg-red-100 text-red-800";
        default:
            return "bg-gray-100 text-gray-700";
    }
};

const columns = [
    { key: "member", label: "Anggota", headerClass: "w-48" },
    { key: "type", label: "Jenis", headerClass: "w-40" },
    {
        key: "amount",
        label: "Nominal",
        headerClass: "w-32 text-right",
        cellClass: "text-right",
    },
    { key: "payment_date", label: "Tanggal Bayar", headerClass: "w-36" },
    { key: "payment_period", label: "Periode", headerClass: "w-28" },
    { key: "status", label: "Status", headerClass: "w-28" },
];

const showManualModal = ref(false);

const manualForm = useForm({
    member_id: "",
    contribution_type_id: "",
    amount: 0,
    payment_date: new Date().toISOString().split("T")[0],
    payment_period: "",
    payment_method: "cash",
    wallet_id: "",
    notes: "",
    receipt: null,
});

const selectedType = computed(() => {
    return (
        props.types?.find((t) => t.id === manualForm.contribution_type_id) ||
        null
    );
});

watch(
    () => manualForm.contribution_type_id,
    () => {
        if (selectedType.value) {
            manualForm.amount = Number(selectedType.value.amount || 0);
            if (selectedType.value.wallet_id) {
                manualForm.wallet_id = selectedType.value.wallet_id;
            }
        }
    },
);

const onReceiptChange = (e) => {
    manualForm.receipt = e.target.files[0];
};

const submitManual = () => {
    manualForm.post(route("contributions.store"), {
        preserveScroll: true,
        onSuccess: () => {
            showManualModal.value = false;
            manualForm.reset();
            manualForm.payment_method = "cash";
            manualForm.payment_date = new Date().toISOString().split("T")[0];
        },
    });
};

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

const periodOptions = computed(() => {
    const opts = [];
    const type = selectedFilterType.value;
    const year = new Date().getFullYear();
    if (!type) return opts;
    if (type.period === 'monthly') {
        for (let m = 1; m <= 12; m++) {
            const date = new Date(year, m - 1, 1);
            const label = date.toLocaleDateString('id-ID', { month: 'short' }) + ' ' + year;
            const value = `${year}-${String(m).padStart(2, '0')}`;
            opts.push({ value, label });
        }
    } else if (type.period === 'yearly') {
        for (let i = 0; i < 5; i++) {
            const y = year - i;
            opts.push({ value: String(y), label: String(y) });
        }
    } else if (type.period === 'weekly') {
        for (let w = 1; w <= 52; w++) {
            const value = `${year}-${String(w).padStart(2, '0')}`;
            const label = `Minggu ${String(w).padStart(2, '0')} ${year}`;
            opts.push({ value, label });
        }
    } else if (type.period === 'once') {
        opts.push({ value: 'once', label: 'Sekali' });
    }
    return opts;
});

watch(() => contributionsFilters.value.contribution_type_id, () => {
    contributionsFilters.value.payment_period = '';
});

const applyFilters = () => {
    router.get(searchRoute.value, { ...contributionsFilters.value }, {
        preserveState: true,
        replace: true,
    });
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

const showFiltersDropdown = ref(false);

const activeFilters = computed(() => {
    const f = contributionsFilters.value;
    const items = [];
    if (f.contribution_type_id) {
        const t = props.types?.find(x => x.id == f.contribution_type_id);
        items.push({ key: 'contribution_type_id', label: `Jenis: ${t?.name || f.contribution_type_id}` });
    }
    if (f.status) {
        const s = statusOptions.find(x => x.value === f.status)?.label || f.status;
        items.push({ key: 'status', label: `Status: ${s}` });
    }
    if (f.payment_method) {
        const m = methodOptions.find(x => x.value === f.payment_method)?.label || f.payment_method;
        items.push({ key: 'payment_method', label: `Metode: ${m}` });
    }
    if (f.wallet_id) {
        const w = props.wallets?.find(x => x.id == f.wallet_id);
        items.push({ key: 'wallet_id', label: `Dompet: ${w?.name || f.wallet_id}` });
    }
    if (f.payment_period) {
        const p = periodOptions.value.find(x => x.value === f.payment_period)?.label || f.payment_period;
        items.push({ key: 'payment_period', label: `Periode: ${p}` });
    }
    if (f.start_date || f.end_date) items.push({ key: 'date_range', label: `Tanggal: ${f.start_date || '-'} s.d. ${f.end_date || '-'}` });
    return items;
});

const activeFilterCount = computed(() => activeFilters.value.length);

const clearFilter = (key) => {
    if (key === 'date_range') {
        contributionsFilters.value.start_date = '';
        contributionsFilters.value.end_date = '';
    } else {
        contributionsFilters.value[key] = '';
    }
};

const triggerFilter = debounce(() => {
    router.get(searchRoute.value, { ...contributionsFilters.value }, {
        preserveState: true,
        replace: true,
    });
}, 300);

watch(contributionsFilters, () => {
    triggerFilter();
}, { deep: true });
</script>

<template>
    <Head
        :title="
            $page.props.auth.user.role === 'anggota'
                ? 'Iuran Saya'
                : 'Manajemen Iuran'
        "
    />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    {{
                        $page.props.auth.user.role === "anggota"
                            ? "Iuran Saya"
                            : "Manajemen Iuran"
                    }}
                </h2>
                <div
                    class="flex items-center gap-2"
                    v-if="$page.props.auth.user.role !== 'anggota'"
                >
                    <Link
                        :href="route('contribution-types.index')"
                        class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-md text-xs font-semibold text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition"
                    >
                        Jenis Iuran
                    </Link>
                    <Link
                        :href="route('contributions.verification')"
                        class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-md text-xs font-semibold text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition"
                    >
                        Verifikasi Pembayaran
                    </Link>
                    <PrimaryButton
                        v-if="
                            $page.props.auth.user.role === 'admin' ||
                            $page.props.auth.user.role === 'bendahara'
                        "
                        @click="showManualModal = true"
                    >
                        Tambah Pembayaran
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Navigation -->
                <ContributionTabs active="history" />

                <!-- Main Content Card -->
                <div
                    class="bg-white rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100/50 overflow-hidden"
                >
                    <div
                        class="p-8 border-b border-gray-50 flex flex-col md:flex-row md:items-center justify-between gap-4"
                    >
                        <div>
                            <h3
                                class="text-xl font-black text-gray-900 tracking-tight"
                            >
                                Riwayat Pembayaran
                            </h3>
                            <p class="text-sm text-gray-400 font-medium">
                                Lacak semua transaksi iuran dan status
                                verifikasinya.
                            </p>
                        </div>

                        <div
                            class="flex items-center gap-3"
                            v-if="
                                $page.props.auth.user.role === 'admin' ||
                                $page.props.auth.user.role === 'bendahara'
                            "
                        >
                            <PrimaryButton
                                @click="showManualModal = true"
                                class="!rounded-xl shadow-lg shadow-indigo-100"
                            >
                                <svg
                                    class="w-4 h-4 mr-2"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 4v16m8-8H4"
                                    ></path>
                                </svg>
                                Input Pembayaran
                            </PrimaryButton>
                        </div>
                    </div>

                    <div class="p-0">
                    <div class="px-8 pt-6">
                        <div class="flex flex-col gap-4">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div class="flex-1 w-full">
                                    <SearchBar v-model="contributionsFilters.search" placeholder="Cari nama anggota, periode, atau catatan..." />
                                </div>
                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-xs font-bold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                        @click="showFiltersDropdown = !showFiltersDropdown"
                                        :aria-expanded="showFiltersDropdown ? 'true' : 'false'"
                                    >
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h18M7 10h10M10 16h4" /></svg>
                                        Filter
                                        <span v-if="activeFilterCount" class="ml-2 inline-flex items-center rounded-full bg-indigo-600 text-white text-[10px] font-bold px-2 py-0.5">{{ activeFilterCount }}</span>
                                    </button>
                                    <SecondaryButton @click="resetFilters">Reset</SecondaryButton>
                                </div>
                            </div>

                            <div v-if="showFiltersDropdown" class="fixed inset-0 z-[998]" @click="showFiltersDropdown = false"></div>
                            <div v-if="showFiltersDropdown" class="fixed right-6 top-24 sm:top-28 z-[1000] w-[calc(100%-3rem)] sm:w-[28rem]">
                                <div class="rounded-xl border border-gray-200 bg-white shadow-2xl overflow-hidden">
                                    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
                                        <span class="text-[10px] font-bold uppercase tracking-widest text-gray-500">Filters</span>
                                        <button
                                            type="button"
                                            class="inline-flex items-center justify-center rounded-md px-2 py-1 text-[10px] font-bold uppercase tracking-widest text-indigo-600 hover:text-indigo-700"
                                            @click="resetFilters"
                                        >
                                            Reset
                                        </button>
                                    </div>
                                    <div class="p-4 space-y-3 max-h-80 overflow-y-auto pr-1">
                                        <div class="space-y-2">
                                            <InputLabel value="Jenis Iuran" class="text-[10px] font-bold uppercase text-gray-500" />
                                            <select v-model="contributionsFilters.contribution_type_id" class="mt-1 block w-full border-gray-300 rounded-md text-sm">
                                                <option value="">Semua jenis</option>
                                                <option v-for="t in types" :key="t.id" :value="t.id">{{ t.name }}</option>
                                            </select>
                                        </div>
                                        <div class="space-y-2">
                                            <InputLabel value="Status" class="text-[10px] font-bold uppercase text-gray-500" />
                                            <FilterDropdown v-model="contributionsFilters.status" :options="statusOptions" label="Semua Status" />
                                        </div>
                                        <div class="space-y-2">
                                            <InputLabel value="Metode" class="text-[10px] font-bold uppercase text-gray-500" />
                                            <FilterDropdown v-model="contributionsFilters.payment_method" :options="methodOptions" label="Semua Metode" />
                                        </div>
                                        <div class="space-y-2">
                                            <InputLabel value="Dompet" class="text-[10px] font-bold uppercase text-gray-500" />
                                            <select v-model="contributionsFilters.wallet_id" class="mt-1 block w-full border-gray-300 rounded-md text-sm">
                                                <option value="">Semua dompet</option>
                                                <option v-for="w in wallets" :key="w.id" :value="w.id">{{ w.name }}</option>
                                            </select>
                                        </div>
                                        <div class="space-y-2">
                                            <InputLabel value="Periode Pembayaran" class="text-[10px] font-bold uppercase text-gray-500" />
                                            <FilterDropdown v-model="contributionsFilters.payment_period" :options="periodOptions" label="Semua Periode" />
                                        </div>
                                        <div class="space-y-2">
                                            <InputLabel value="Rentang Tanggal" class="text-[10px] font-bold uppercase text-gray-500" />
                                            <div class="flex items-center gap-2">
                                                <TextInput type="date" v-model="contributionsFilters.start_date" class="block flex-1 w-full" />
                                                <span class="text-xs text-gray-500">s.d.</span>
                                                <TextInput type="date" v-model="contributionsFilters.end_date" class="block flex-1 w-full" />
                                            </div>
                                        </div>
                                        <div class="pt-2 flex items-center gap-2">
                                            <SecondaryButton @click="showFiltersDropdown = false">Tutup</SecondaryButton>
                                            <SecondaryButton @click="resetFilters">Bersihkan</SecondaryButton>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="activeFilters.length" class="flex flex-wrap gap-2">
                                <button
                                    v-for="f in activeFilters"
                                    :key="f.key"
                                    type="button"
                                    class="inline-flex items-center gap-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200 px-3 py-1 text-xs font-semibold"
                                    @click="clearFilter(f.key)"
                                >
                                    <span>{{ f.label }}</span>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-1 rounded-full bg-gray-100 text-gray-700 border border-gray-200 px-3 py-1 text-xs font-semibold"
                                    @click="resetFilters"
                                >
                                    Bersihkan semua
                                </button>
                            </div>
                        </div>
                    </div>
                        <DataTable
                            :data="contributions"
                            :columns="columns"
                        :filters="contributionsFilters"
                            :searchRoute="searchRoute"
                            class="!border-0 shadow-none"
                        >
                            <template #cell-member="{ row }">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="h-10 w-10 flex-shrink-0 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 font-black text-xs"
                                    >
                                        {{
                                            (row.member?.full_name || "?")
                                                .charAt(0)
                                                .toUpperCase()
                                        }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span
                                            class="text-sm font-bold text-slate-700 leading-none mb-1"
                                        >
                                            {{ row.member?.full_name || "-" }}
                                        </span>
                                        <span
                                            class="text-[10px] font-black text-slate-400 tracking-[0.1em] uppercase"
                                        >
                                            {{
                                                row.member?.member_code ||
                                                "NON-MEMBER"
                                            }}
                                        </span>
                                    </div>
                                </div>
                            </template>

                            <template #cell-type="{ row }">
                                <div class="flex flex-col">
                                    <span
                                        class="text-sm font-bold text-slate-700 leading-none mb-1"
                                    >
                                        {{ row.type?.name || "-" }}
                                    </span>
                                    <span
                                        class="text-[10px] font-black text-slate-300 tracking-wider uppercase"
                                    >
                                        {{
                                            row.type?.period === "once"
                                                ? "Sekali Bayar"
                                                : row.type?.period
                                        }}
                                    </span>
                                </div>
                            </template>

                            <template #cell-amount="{ row }">
                                <span
                                    class="text-sm font-black text-indigo-600 tabular-nums"
                                >
                                    {{ formatCurrency(row.amount) }}
                                </span>
                            </template>

                            <template #cell-payment_date="{ row }">
                                <div class="flex flex-col">
                                    <span
                                        class="text-sm font-bold text-slate-600 leading-none mb-1"
                                    >
                                        {{ formatDate(row.payment_date) }}
                                    </span>
                                    <span
                                        class="text-[10px] font-medium text-slate-400"
                                    >
                                        {{
                                            row.payment_method === "transfer"
                                                ? "Via Transfer"
                                                : "Tunai"
                                        }}
                                    </span>
                                </div>
                            </template>

                            <template #cell-payment_period="{ row }">
                                <div
                                    class="inline-flex items-center px-2 py-1 rounded bg-slate-50 border border-slate-100"
                                >
                                    <span
                                        class="text-[10px] font-black text-slate-500 tracking-wider uppercase shrink-0"
                                    >
                                        {{ row.payment_period || "-" }}
                                    </span>
                                </div>
                            </template>

                            <template #cell-status="{ row }">
                                <span
                                    :class="[
                                        'px-3 py-1 rounded-full text-[10px] font-black tracking-widest uppercase',
                                        getStatusBadge(row.status),
                                    ]"
                                >
                                    {{ (row.status || "-").toUpperCase() }}
                                </span>
                            </template>

                            <template #actions="{ row }">
                                <Link
                                    :href="route('contributions.show', row)"
                                    class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all"
                                    title="Detail"
                                >
                                    <svg
                                        class="w-5 h-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                        ></path>
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                        ></path>
                                    </svg>
                                </Link>
                            </template>
                        </DataTable>
                    </div>
                </div>
            </div>
        </div>

        <Modal
            :show="showManualModal"
            @close="showManualModal = false"
            maxWidth="2xl"
        >
            <div class="p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">
                    Tambah Pembayaran Manual
                </h3>
                <form @submit.prevent="submitManual" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div v-if="$page.props.auth.user.role !== 'anggota'">
                            <InputLabel for="member_id" value="Anggota" />
                            <select
                                id="member_id"
                                v-model="manualForm.member_id"
                                class="mt-1 block w-full border-gray-300 rounded-md text-sm"
                            >
                                <option value="">Pilih anggota...</option>
                                <option
                                    v-for="m in members"
                                    :key="m.id"
                                    :value="m.id"
                                >
                                    {{ m.full_name }} ({{ m.member_code }})
                                </option>
                            </select>
                            <InputError
                                class="mt-2"
                                :message="manualForm.errors.member_id"
                            />
                        </div>
                        <div>
                            <InputLabel
                                for="contribution_type_id"
                                value="Jenis Iuran"
                            />
                            <select
                                id="contribution_type_id"
                                v-model="manualForm.contribution_type_id"
                                class="mt-1 block w-full border-gray-300 rounded-md text-sm"
                            >
                                <option value="">Pilih jenis...</option>
                                <option
                                    v-for="t in types"
                                    :key="t.id"
                                    :value="t.id"
                                >
                                    {{ t.name }}
                                </option>
                            </select>
                            <InputError
                                class="mt-2"
                                :message="
                                    manualForm.errors.contribution_type_id
                                "
                            />
                        </div>
                        <div>
                            <InputLabel for="amount" value="Nominal" />
                            <TextInput
                                id="amount"
                                type="number"
                                step="1"
                                v-model="manualForm.amount"
                                class="mt-1 block w-full"
                            />
                            <InputError
                                class="mt-2"
                                :message="manualForm.errors.amount"
                            />
                        </div>
                        <div>
                            <InputLabel
                                for="payment_date"
                                value="Tanggal Bayar"
                            />
                            <TextInput
                                id="payment_date"
                                type="date"
                                v-model="manualForm.payment_date"
                                class="mt-1 block w-full"
                            />
                            <InputError
                                class="mt-2"
                                :message="manualForm.errors.payment_date"
                            />
                        </div>
                        <div>
                            <InputLabel
                                for="payment_period"
                                value="Periode Pembayaran"
                            />
                            <TextInput
                                id="payment_period"
                                type="text"
                                v-model="manualForm.payment_period"
                                class="mt-1 block w-full"
                                placeholder="Contoh: 2026-01 (bulanan), 2026 (tahunan)"
                            />
                            <InputError
                                class="mt-2"
                                :message="manualForm.errors.payment_period"
                            />
                        </div>
                        <div>
                            <InputLabel for="payment_method" value="Metode" />
                            <select
                                id="payment_method"
                                v-model="manualForm.payment_method"
                                class="mt-1 block w-full border-gray-300 rounded-md text-sm"
                            >
                                <option value="cash">Tunai</option>
                                <option value="transfer">Transfer</option>
                            </select>
                            <InputError
                                class="mt-2"
                                :message="manualForm.errors.payment_method"
                            />
                        </div>
                        <div v-if="!selectedType?.wallet_id">
                            <InputLabel for="wallet_id" value="Dompet Tujuan" />
                            <select
                                id="wallet_id"
                                v-model="manualForm.wallet_id"
                                class="mt-1 block w-full border-gray-300 rounded-md text-sm"
                            >
                                <option value="">Pilih dompet...</option>
                                <option
                                    v-for="w in wallets"
                                    :key="w.id"
                                    :value="w.id"
                                >
                                    {{ w.name }}
                                </option>
                            </select>
                            <InputError
                                class="mt-2"
                                :message="manualForm.errors.wallet_id"
                            />
                        </div>
                        <div class="md:col-span-2">
                            <InputLabel
                                for="notes"
                                value="Catatan (Opsional)"
                            />
                            <textarea
                                id="notes"
                                v-model="manualForm.notes"
                                class="mt-1 block w-full border-gray-300 rounded-md text-sm"
                                rows="3"
                            />
                            <InputError
                                class="mt-2"
                                :message="manualForm.errors.notes"
                            />
                        </div>
                        <div class="md:col-span-2">
                            <InputLabel
                                for="receipt"
                                value="Bukti (Opsional)"
                            />
                            <input
                                id="receipt"
                                type="file"
                                @change="onReceiptChange"
                                class="mt-1 block w-full text-sm"
                                accept="image/*"
                            />
                            <InputError
                                class="mt-2"
                                :message="manualForm.errors.receipt"
                            />
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 mt-4">
                        <SecondaryButton
                            type="button"
                            @click="showManualModal = false"
                        >
                            Batal
                        </SecondaryButton>
                        <PrimaryButton
                            type="submit"
                            :disabled="manualForm.processing"
                        >
                            Simpan
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
