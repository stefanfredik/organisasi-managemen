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
import SearchableSelect from "@/Components/SearchableSelect.vue";
import CurrencyInput from "@/Components/CurrencyInput.vue";
import debounce from "lodash/debounce";
import axios from "axios";

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
const filteredMembers = ref([]);

const manualForm = useForm({
    member_id: "",
    contribution_type_id: "",
    amount: 0,
    payment_date: new Date().toISOString().split("T")[0],
    payment_period: "",
    payment_periods: [],
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

const selectedMember = computed(() => {
    return (
        props.members?.find((m) => m.id == manualForm.member_id) || null
    );
});

const loadUnpaidMembers = async () => {
    filteredMembers.value = [];
    if (!selectedType.value) return;
    try {
        const res = await axios.get(route("contributions.unpaid-members"), {
            params: {
                contribution_type_id: selectedType.value.id,
                payment_period: manualForm.payment_period || null,
            },
        });
        filteredMembers.value = res.data || [];
    } catch (e) {
        filteredMembers.value = props.members || [];
    }
};

const paidPeriods = ref([]);
const statusLoading = ref(false);
const fetchMemberStatus = async () => {
    paidPeriods.value = [];
    const isMember = page.props.auth.user.role === 'anggota';
    if ((!manualForm.member_id && !isMember) || !selectedType.value) return;
    statusLoading.value = true;
    try {
        const url = route("contributions.my-status", selectedType.value.id);
        const params = manualForm.member_id ? { member_id: manualForm.member_id } : {};
        const res = await axios.get(url, { params });
        const data = res.data;

        if (data.type === 'once') {
            if (data.status === 'paid') {
                paidPeriods.value = ['once'];
                manualForm.payment_periods = [];
            }
        } else if (data?.periods && Array.isArray(data.periods)) {
            paidPeriods.value = data.periods.filter(p => p.status === "paid").map(p => p.period);
            manualForm.payment_periods = manualForm.payment_periods.filter(pp => !paidPeriods.value.includes(pp));
        }
    } finally {
        statusLoading.value = false;
    }
};

watch(
    () => manualForm.contribution_type_id,
    () => {
        if (selectedType.value) {
            manualForm.amount = Number(selectedType.value.amount || 0);
            if (selectedType.value.wallet_id) {
                manualForm.wallet_id = selectedType.value.wallet_id;
            }
            manualForm.payment_period = "";
            manualForm.payment_periods = [];
            
            // Set default method for members
            if (page.props.auth.user.role === 'anggota') {
                manualForm.payment_method = 'transfer';
            }

            if (page.props.auth.user.role !== 'anggota') {
                loadUnpaidMembers();
            }
            fetchMemberStatus();
        } else {
            filteredMembers.value = props.members || [];
        }
    },
);

watch(
    () => manualForm.member_id,
    () => {
        manualForm.payment_periods = [];
        fetchMemberStatus();
    },
);

const memberOptions = computed(() => {
    const base = filteredMembers.value.length ? filteredMembers.value : (props.members || []);
    return base.map(m => ({
        value: m.id,
        label: m.full_name,
        subLabel: m.member_code
    }));
});

watch(
    () => manualForm.payment_periods,
    () => {
        if (!selectedType.value) return;
        if (selectedType.value.period !== "once") {
            manualForm.amount = Number(selectedType.value.amount || 0) * (manualForm.payment_periods?.length || 0);
        } else {
            manualForm.amount = Number(selectedType.value.amount || 0);
        }
    },
    { deep: true },
);

const onReceiptChange = (e) => {
    manualForm.receipt = e.target.files[0];
};

const submitManual = () => {
    const isBulk = selectedType.value && selectedType.value.period !== 'once' && manualForm.payment_periods.length > 0;
    if (!isBulk && !manualForm.payment_period) {
        manualForm.payment_period = manualForm.payment_periods[0] || "";
    }
    if (isBulk && !manualForm.member_id && page.props.auth.user.role !== 'anggota') {
        return;
    }

    if (page.props.auth.user.role === 'anggota' && !manualForm.receipt) {
        manualForm.setError('receipt', 'Bukti transfer wajib diunggah untuk verifikasi.');
        return;
    }

    if (isBulk) {
        manualForm.transform((data) => ({
            member_ids: data.member_id ? [data.member_id] : [],
            contribution_type_id: data.contribution_type_id,
            amount: data.amount,
            payment_date: data.payment_date,
            periods: data.payment_periods,
            payment_method: data.payment_method,
            wallet_id: data.wallet_id,
            notes: data.notes,
            receipt: data.receipt,
        }));
    } else {
        manualForm.transform((data) => ({
            member_id: data.member_id,
            contribution_type_id: data.contribution_type_id,
            amount: data.amount,
            payment_date: data.payment_date,
            payment_period: data.payment_period || null,
            payment_method: data.payment_method,
            wallet_id: data.wallet_id,
            notes: data.notes,
            receipt: data.receipt,
        }));
    }
    manualForm.post(isBulk ? route("contributions.bulk-store") : route("contributions.store"), {
        preserveScroll: true,
        onSuccess: () => {
            showManualModal.value = false;
            manualForm.reset();
            manualForm.payment_method = "cash";
            manualForm.payment_date = new Date().toISOString().split("T")[0];
            manualForm.payment_periods = [];
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

const manualPeriodOptions = computed(() => {
    const opts = [];
    const type = selectedType.value;
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

const verifyProcessingId = ref(null);
const rejectComment = ref('');
const verifyContribution = (id, action) => {
    if (!confirm(`Apakah Anda yakin ingin ${action === 'approve' ? 'menyetujui' : 'menolak'} pembayaran ini?`)) return;
    verifyProcessingId.value = id;
    if (action === 'reject' && !rejectComment.value.trim()) {
        alert('Komentar penolakan wajib diisi.');
        verifyProcessingId.value = null;
        return;
    }
    router.post(route('contributions.verify-action', id), { action, comment: rejectComment.value.trim() }, {
        preserveScroll: true,
        replace: true,
        onFinish: () => {
            verifyProcessingId.value = null;
            showVerifyModal.value = false;
            selectedContribution.value = null;
            rejectComment.value = '';
        },
    });
};

const showVerifyModal = ref(false);
const selectedContribution = ref(null);
const openVerifyModal = (row) => {
    selectedContribution.value = row;
    showVerifyModal.value = true;
    rejectComment.value = '';
};
const closeVerifyModal = () => {
    showVerifyModal.value = false;
    selectedContribution.value = null;
};

const startPayment = (type) => {
    manualForm.reset();
    manualForm.contribution_type_id = type.id;
    showManualModal.value = true;
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}
</style>

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
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Navigation -->
                <ContributionTabs active="history" v-if="$page.props.auth.user.role !== 'anggota'" />

                <!-- Active Contribution Types Cards -->
                <div class="mb-8" v-if="types && types.length > 0">
                    <div class="flex items-center justify-between mb-4">
                         <h3 class="text-lg font-black text-gray-800 tracking-tight">Semua Iuran Aktif</h3>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        <div 
                            v-for="type in types" 
                            :key="type.id"
                            @click="startPayment(type)"
                            class="group bg-white rounded-2xl p-5 border border-gray-100 shadow-[0_2px_10px_rgb(0,0,0,0.02)] hover:shadow-[0_8px_30px_rgb(0,0,0,0.06)] hover:border-indigo-100 transition-all duration-300 cursor-pointer relative overflow-hidden"
                        >
                            <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-50/50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                            
                            <div class="relative z-10">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="p-3 bg-indigo-50 rounded-xl text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </div>
                                    <div class="flex flex-col items-end gap-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800 uppercase tracking-wide">
                                            {{ type.period === 'once' ? 'Sekali' : (type.period === 'monthly' ? 'Bulanan' : (type.period === 'weekly' ? 'Mingguan' : 'Tahunan')) }}
                                        </span>
                                        <span v-if="type.user_progress?.current_period_status" :class="{
                                            'bg-green-100 text-green-700': type.user_progress.current_period_status === 'paid',
                                            'bg-amber-100 text-amber-700': type.user_progress.current_period_status === 'pending',
                                            'bg-red-100 text-red-700': type.user_progress.current_period_status === 'unpaid'
                                        }" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide">
                                            {{ type.user_progress.current_period_label }}: {{ type.user_progress.current_period_status === 'paid' ? 'LUNAS' : (type.user_progress.current_period_status === 'pending' ? 'PENDING' : 'BELUM') }}
                                        </span>
                                    </div>
                                </div>
                                
                                <h4 class="text-lg font-bold text-gray-900 mb-1 group-hover:text-indigo-600 transition-colors line-clamp-1">{{ type.name }}</h4>
                                <p class="text-2xl font-black text-gray-900 tracking-tight mb-1">
                                    {{ formatCurrency(type.amount) }}
                                </p>
                                
                                <div v-if="type.user_progress" class="mt-3 mb-2">
                                    <div class="flex justify-between text-xs mb-1">
                                        <span class="font-semibold text-gray-500">Kelunasan</span>
                                        <span class="font-bold text-indigo-600">{{ type.user_progress.percentage }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                        <div class="bg-indigo-500 h-1.5 rounded-full transition-all duration-500" :style="{ width: `${type.user_progress.percentage}%` }"></div>
                                    </div>
                                    <p class="text-[10px] text-gray-400 mt-1 text-right">{{ type.user_progress.text }}</p>
                                </div>
                                <p v-else class="text-xs text-gray-400 font-medium line-clamp-2 min-h-[2.5em]">
                                    {{ type.description || 'Tidak ada deskripsi' }}
                                </p>

                                <div class="mt-4 pt-3 border-t border-gray-50">
                                    <button 
                                        @click.stop="startPayment(type)"
                                        class="w-full inline-flex items-center justify-center px-4 py-2 bg-indigo-50 text-indigo-700 border border-indigo-100 rounded-xl font-bold text-xs uppercase tracking-wider hover:bg-indigo-600 hover:text-white hover:border-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-300 group-hover:shadow-md"
                                    >
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a1 1 0 11-2 0 1 1 0 012 0z" /></svg>
                                        {{ $page.props.auth.user.role === 'anggota' ? 'Bayar Sekarang' : 'Input Data' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

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
                            v-if="['admin', 'bendahara', 'anggota'].includes($page.props.auth.user.role)"
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
                                {{ $page.props.auth.user.role === 'anggota' ? 'Upload Bukti Bayar' : 'Input Pembayaran' }}
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
                            :striped="true"
                            :dense="true"
                            :searchable="false"
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
                                <div class="inline-flex items-center gap-1 justify-end w-full">
                                    <button
                                        v-if="row.status === 'pending' && ($page.props.auth.user.role === 'admin' || $page.props.auth.user.role === 'bendahara')"
                                        type="button"
                                        class="p-2 text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 rounded-xl transition"
                                        title="Verifikasi"
                                        @click="openVerifyModal(row)"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4" />
                                        </svg>
                                    </button>
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
                                </div>
                            </template>
                        </DataTable>
                    </div>
                </div>
            </div>
        </div>

        <!-- Verification Modal -->
        <Modal
            :show="showVerifyModal"
            @close="closeVerifyModal"
            maxWidth="2xl"
        >
            <div class="p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Verifikasi Pembayaran</h3>
                <div v-if="selectedContribution" class="space-y-3">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-[10px] font-black uppercase text-gray-400 tracking-widest mb-1">Anggota</p>
                            <p class="text-sm font-bold text-gray-900">{{ selectedContribution.member?.full_name || '-' }}</p>
                            <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">{{ selectedContribution.member?.member_code }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase text-gray-400 tracking-widest mb-1">Jenis Iuran</p>
                            <p class="text-sm font-bold text-gray-900">{{ selectedContribution.type?.name || '-' }}</p>
                            <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">
                                {{ selectedContribution.payment_period || '-' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase text-gray-400 tracking-widest mb-1">Jumlah</p>
                            <p class="text-sm font-black text-indigo-600 tabular-nums">{{ formatCurrency(selectedContribution.amount) }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase text-gray-400 tracking-widest mb-1">Tanggal & Metode</p>
                            <p class="text-sm font-bold text-slate-600">{{ formatDate(selectedContribution.payment_date) }}</p>
                            <p class="text-[10px] font-medium text-slate-400">
                                {{ selectedContribution.payment_method === 'transfer' ? 'Via Transfer' : 'Tunai' }}
                            </p>
                        </div>
                    </div>
                    <div class="mt-2">
                        <p class="text-[10px] font-black uppercase text-gray-400 tracking-widest mb-2">Bukti Pembayaran</p>
                        <div v-if="selectedContribution.receipt_path" class="bg-gray-50 border border-gray-100 rounded-lg p-3">
                            <img :src="`/storage/${selectedContribution.receipt_path}`" alt="Bukti Pembayaran" class="max-h-[40vh] object-contain rounded" />
                        </div>
                        <p v-else class="text-xs text-gray-400 italic">Tanpa bukti</p>
                    </div>
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <div class="flex-1">
                        <p class="text-[10px] font-black uppercase text-gray-400 tracking-widest mb-2">Komentar Penolakan</p>
                        <textarea
                            v-model="rejectComment"
                            class="block w-full border-gray-300 rounded-md text-sm"
                            rows="3"
                            placeholder="Tuliskan alasan penolakan"
                        ></textarea>
                    </div>
                    <button 
                        type="button"
                        class="px-3 py-2 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 text-xs font-bold transition-colors"
                        :disabled="verifyProcessingId === selectedContribution?.id"
                        @click="verifyContribution(selectedContribution.id, 'reject')"
                    >
                        Tolak
                    </button>
                    <PrimaryButton 
                        :disabled="verifyProcessingId === selectedContribution?.id"
                        @click="verifyContribution(selectedContribution.id, 'approve')"
                    >
                        Setujui
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <Modal
            :show="showManualModal"
            @close="showManualModal = false"
            maxWidth="2xl"
        >
            <div class="bg-white">
                <!-- Header Simple -->
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-white">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">
                            {{ $page.props.auth.user.role === 'anggota' ? 'Upload Bukti Pembayaran' : 'Input Pembayaran' }}
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ $page.props.auth.user.role === 'anggota' ? 'Unggah bukti transfer untuk pembayaran iuran Anda' : 'Catat transaksi pembayaran iuran anggota' }}
                        </p>
                    </div>
                    <div v-if="selectedType" class="text-right bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-100">
                        <p class="text-[10px] font-bold uppercase text-gray-400 tracking-wider">Tarif</p>
                        <p class="text-sm font-black text-gray-800">{{ formatCurrency(selectedType?.amount) }}</p>
                    </div>
                </div>

                <form @submit.prevent="submitManual" class="flex flex-col h-[calc(100vh-200px)] md:h-auto overflow-hidden">
                    <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                            
                            <!-- Bagian Kiri: Form Input (7 kolom) -->
                            <div class="md:col-span-7 space-y-3">
                                <!-- Jenis Iuran -->
                                <div>
                                    <InputLabel for="contribution_type_id" value="Jenis Iuran" />
                                    <select
                                        id="contribution_type_id"
                                        v-model="manualForm.contribution_type_id"
                                        class="mt-1 block w-full border-gray-200 rounded-lg text-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white"
                                    >
                                        <option value="">Pilih jenis iuran...</option>
                                        <option v-for="t in types" :key="t.id" :value="t.id">{{ t.name }}</option>
                                    </select>
                                    <InputError class="mt-1" :message="manualForm.errors.contribution_type_id" />
                                </div>

                                <!-- Anggota (Hanya tampil jika bukan role anggota) -->
                                <div v-if="$page.props.auth.user.role !== 'anggota'">
                                    <SearchableSelect
                                        label="Anggota"
                                        v-model="manualForm.member_id"
                                        :options="memberOptions"
                                        placeholder="Pilih anggota..."
                                        search-placeholder="Cari nama atau kode..."
                                        :error="manualForm.errors.member_id"
                                    />
                                </div>

                                <!-- Grid Nominal & Tanggal -->
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <InputLabel for="amount" value="Nominal" />
                                        <div class="relative mt-1">
                                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                                <span class="text-gray-500 sm:text-sm">Rp</span>
                                            </div>
                                            <CurrencyInput
                                                id="amount"
                                                v-model="manualForm.amount"
                                                class="block w-full rounded-lg border-gray-200 pl-10 text-gray-900 focus:ring-indigo-600 font-semibold"
                                            />
                                        </div>
                                        <InputError class="mt-1" :message="manualForm.errors.amount" />
                                    </div>
                                    <div>
                                        <InputLabel for="payment_date" value="Tanggal" />
                                        <TextInput
                                            id="payment_date"
                                            type="date"
                                            v-model="manualForm.payment_date"
                                            class="mt-1 block w-full rounded-lg border-gray-200 text-sm"
                                        />
                                        <InputError class="mt-1" :message="manualForm.errors.payment_date" />
                                    </div>
                                </div>

                                <!-- Grid Metode & Dompet -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div v-if="$page.props.auth.user.role !== 'anggota'">
                                        <InputLabel value="Metode Pembayaran" />
                                        <div class="grid grid-cols-2 gap-3 mt-1">
                                            <label class="cursor-pointer relative">
                                                <input
                                                    type="radio"
                                                    v-model="manualForm.payment_method"
                                                    value="cash"
                                                    class="peer sr-only"
                                                />
                                                <div class="rounded-lg border border-gray-200 p-3 hover:bg-gray-50 peer-checked:border-indigo-600 peer-checked:bg-indigo-50 peer-checked:text-indigo-600 text-gray-500 transition-all flex flex-col items-center justify-center gap-1 text-center h-full">
                                                    <svg class="w-6 h-6 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                                    </svg>
                                                    <span class="text-xs font-semibold">Tunai</span>
                                                </div>
                                            </label>
                                            <label class="cursor-pointer relative">
                                                <input
                                                    type="radio"
                                                    v-model="manualForm.payment_method"
                                                    value="transfer"
                                                    class="peer sr-only"
                                                />
                                                <div class="rounded-lg border border-gray-200 p-3 hover:bg-gray-50 peer-checked:border-indigo-600 peer-checked:bg-indigo-50 peer-checked:text-indigo-600 text-gray-500 transition-all flex flex-col items-center justify-center gap-1 text-center h-full">
                                                    <svg class="w-6 h-6 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                                    </svg>
                                                    <span class="text-xs font-semibold">Transfer</span>
                                                </div>
                                            </label>
                                        </div>
                                        <InputError class="mt-1" :message="manualForm.errors.payment_method" />
                                    </div>
                                    <div v-else class="col-span-1">
                                        <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 flex items-center gap-3 h-full">
                                            <div class="bg-blue-100 p-2 rounded-full text-blue-600">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-[10px] font-bold text-blue-900 uppercase tracking-wide">Metode Pembayaran</p>
                                                <p class="text-sm font-bold text-blue-700">Transfer Bank</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="!selectedType?.wallet_id && $page.props.auth.user.role !== 'anggota'">
                                        <InputLabel for="wallet_id" value="Dompet" />
                                        <select
                                            id="wallet_id"
                                            v-model="manualForm.wallet_id"
                                            class="mt-1 block w-full border-gray-200 rounded-lg text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                            <option value="">Pilih dompet...</option>
                                            <option v-for="w in wallets" :key="w.id" :value="w.id">{{ w.name }}</option>
                                        </select>
                                        <InputError class="mt-1" :message="manualForm.errors.wallet_id" />
                                    </div>
                                </div>

                                <!-- Catatan -->
                                <div>
                                    <InputLabel for="notes" value="Catatan (Opsional)" />
                                    <textarea
                                        id="notes"
                                        v-model="manualForm.notes"
                                        class="mt-1 block w-full border-gray-200 rounded-lg text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        rows="2"
                                        placeholder="Keterangan tambahan..."
                                    />
                                </div>

                                <!-- Bukti Transfer -->
                                <div>
                                    <InputLabel for="receipt" :value="$page.props.auth.user.role === 'anggota' ? 'Bukti Transfer' : 'Bukti Transfer (Opsional)'" />
                                    <input
                                        id="receipt"
                                        type="file"
                                        @change="onReceiptChange"
                                        class="mt-1 block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 transition-colors"
                                        accept="image/*"
                                    />
                                    <InputError class="mt-1" :message="manualForm.errors.receipt" />
                                </div>
                            </div>

                            <!-- Bagian Kanan: Periode (5 kolom) -->
                            <div class="md:col-span-5 border-l border-gray-100 md:pl-6">
                                <InputLabel value="Pilih Periode" class="mb-3" />
                                
                                <div class="bg-gray-50 rounded-xl p-3 h-[420px] overflow-y-auto custom-scrollbar border border-gray-100">
                                    <div v-if="manualPeriodOptions.length" class="space-y-1">
                                        <label
                                            v-for="opt in manualPeriodOptions"
                                            :key="opt.value"
                                            class="flex items-center justify-between px-3 py-1.5 rounded-lg border bg-white cursor-pointer transition-all hover:border-indigo-300"
                                            :class="[
                                                paidPeriods.includes(opt.value)
                                                    ? 'border-gray-100 opacity-50 cursor-not-allowed bg-gray-50'
                                                    : manualForm.payment_periods.includes(opt.value)
                                                        ? 'border-indigo-500 ring-1 ring-indigo-500 bg-indigo-50/30'
                                                        : 'border-gray-200'
                                            ]"
                                        >
                                            <div class="flex items-center gap-3">
                                                <div class="relative flex items-center">
                                                     <input
                                                        v-if="paidPeriods.includes(opt.value)"
                                                        type="checkbox"
                                                        class="h-4 w-4 rounded border-gray-300 text-gray-400 focus:ring-0"
                                                        checked
                                                        disabled
                                                    />
                                                    <input
                                                        v-else
                                                        type="checkbox"
                                                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                        :value="opt.value"
                                                        v-model="manualForm.payment_periods"
                                                    />
                                                </div>
                                                <span class="text-xs font-bold text-gray-700">{{ opt.label }}</span>
                                            </div>
                                            
                                            <span v-if="paidPeriods.includes(opt.value)" class="text-[10px] font-bold text-green-600 bg-green-50 px-2 py-0.5 rounded-full uppercase tracking-wider">
                                                Lunas
                                            </span>
                                        </label>
                                    </div>
                                    <div v-else class="flex flex-col items-center justify-center h-full text-center p-4">
                                        <svg class="w-8 h-8 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <p class="text-xs text-gray-400">Pilih jenis iuran untuk menampilkan periode.</p>
                                    </div>
                                </div>
                                <InputError class="mt-2" :message="manualForm.errors.payment_period" />
                                <InputError class="mt-1" :message="manualForm.errors.periods" />
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-bold uppercase text-gray-400 tracking-wider">Total Bayar</p>
                            <div class="flex items-baseline gap-2">
                                <p class="text-xl font-black text-gray-800">{{ formatCurrency(manualForm.amount) }}</p>
                                <p v-if="selectedMember" class="text-xs text-gray-500 hidden sm:inline-block">
                                    — {{ selectedMember?.full_name }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div v-if="selectedType?.period === 'once' && paidPeriods.includes('once')" class="text-xs text-green-600 font-bold mr-2">
                                Sudah Lunas
                            </div>
                            <button
                                type="button"
                                @click="showManualModal = false"
                                class="px-4 py-2 rounded-lg text-xs font-bold text-gray-600 hover:bg-gray-200 transition-colors"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                :disabled="manualForm.processing || (selectedType?.period === 'once' && paidPeriods.includes('once'))"
                                class="px-5 py-2 rounded-lg bg-gray-900 text-white text-xs font-bold uppercase tracking-wider hover:bg-gray-800 transition-colors shadow-sm disabled:opacity-50"
                            >
                                Simpan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
