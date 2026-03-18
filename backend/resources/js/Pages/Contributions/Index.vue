<script setup>
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { usePage } from "@inertiajs/vue3";
import {
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogDescription,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import SearchBar from "@/Components/SearchBar.vue";
import FilterDropdown from "@/Components/FilterDropdown.vue";
import SearchableSelect from "@/Components/SearchableSelect.vue";
import CurrencyInput from "@/Components/CurrencyInput.vue";
import debounce from "lodash/debounce";
import axios from "axios";
import { Upload, X } from "lucide-vue-next";

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
            return "bg-success/20 text-success-foreground";
        case "pending":
            return "bg-warning-100 text-warning-800";
        case "partial":
            return "bg-primary/20 text-primary";
        case "rejected":
            return "bg-destructive/20 text-destructive";
        default:
            return "bg-muted text-foreground";
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

const manualReceiptInputRef = ref(null);
const isDraggingManualReceipt = ref(false);
const manualReceiptPreview = ref(null);

const handleManualReceiptSelect = (e) => {
    const file = e.target.files[0];
    if (file) setManualReceipt(file);
};

const handleManualReceiptDrop = (e) => {
    isDraggingManualReceipt.value = false;
    const file = e.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) setManualReceipt(file);
};

const setManualReceipt = (file) => {
    manualForm.receipt = file;
    const reader = new FileReader();
    reader.onload = (e) => { manualReceiptPreview.value = e.target.result; };
    reader.readAsDataURL(file);
};

const clearManualReceipt = () => {
    manualForm.receipt = null;
    manualReceiptPreview.value = null;
    if (manualReceiptInputRef.value) manualReceiptInputRef.value.value = '';
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
                <h2 class="text-xl font-semibold leading-tight text-foreground">
                    {{
                        $page.props.auth.user.role === "anggota"
                            ? "Iuran Saya"
                            : "Manajemen Iuran"
                    }}
                </h2>
            </div>
        </template>

        <div class="py-4">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <!-- Active Contribution Types Cards -->
                <div class="mb-4" v-if="types && types.length > 0">
                    <div class="flex items-center justify-between mb-2">
                         <h3 class="text-sm font-bold text-foreground">Iuran Aktif</h3>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                        <div 
                            v-for="type in types" 
                            :key="type.id"
                            @click="startPayment(type)"
                            class="group bg-card rounded-lg p-4 border border shadow-sm hover:shadow-md hover:border-primary-100 transition-all duration-200 cursor-pointer relative overflow-hidden"
                        >
                            <div class="relative">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="p-2 bg-primary/10 rounded-lg text-primary group-hover:bg-primary group-hover:text-white transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </div>
                                    <div class="flex flex-col items-end gap-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-success/20 text-success-foreground uppercase tracking-wide">
                                            {{ type.period === 'once' ? 'Sekali' : (type.period === 'monthly' ? 'Bulanan' : (type.period === 'weekly' ? 'Mingguan' : 'Tahunan')) }}
                                        </span>
                                        <span v-if="type.user_progress?.current_period_status" :class="{
                                            'bg-success/20 text-success-700': type.user_progress.current_period_status === 'paid',
                                            'bg-warning-100 text-warning-700': type.user_progress.current_period_status === 'pending',
                                            'bg-destructive/20 text-destructive': type.user_progress.current_period_status === 'unpaid'
                                        }" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide">
                                            {{ type.user_progress.current_period_label }}: {{ type.user_progress.current_period_status === 'paid' ? 'LUNAS' : (type.user_progress.current_period_status === 'pending' ? 'PENDING' : 'BELUM') }}
                                        </span>
                                    </div>
                                </div>
                                
                                <h4 class="text-sm font-bold text-foreground mb-1 group-hover:text-primary transition-colors line-clamp-1">{{ type.name }}</h4>
                                <p class="text-lg font-black text-foreground tracking-tight mb-1">
                                    {{ formatCurrency(type.amount) }}
                                </p>
                                
                                <div v-if="type.user_progress" class="mt-3 mb-2">
                                    <div class="flex justify-between text-xs mb-1">
                                        <span class="font-semibold text-muted-foreground">Kelunasan</span>
                                        <span class="font-bold text-primary">{{ type.user_progress.percentage }}%</span>
                                    </div>
                                    <div class="w-full bg-muted rounded-full h-1.5 overflow-hidden">
                                        <div class="bg-primary/100 h-1.5 rounded-full transition-all duration-500" :style="{ width: `${type.user_progress.percentage}%` }"></div>
                                    </div>
                                    <p class="text-[10px] text-muted-foreground mt-1 text-right">{{ type.user_progress.text }}</p>
                                </div>
                                <p v-else class="text-xs text-muted-foreground font-medium line-clamp-2 min-h-[2.5em]">
                                    {{ type.description || 'Tidak ada deskripsi' }}
                                </p>

                                <div class="mt-3 pt-2 border-t border">
                                    <div v-if="type.user_progress?.percentage >= 100" class="w-full text-center py-2 bg-success/10 text-success-700 border border-success-100 rounded-xl font-bold text-xs uppercase tracking-wider">
                                        Sudah Terbayar
                                    </div>
                                    <button 
                                        v-else
                                        @click.stop="startPayment(type)"
                                        class="w-full inline-flex items-center justify-center px-3 py-1.5 bg-primary/10 text-primary border border-primary-100 rounded-md font-bold text-xs uppercase tracking-wider hover:bg-primary hover:text-white hover:border-primary focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 transition-all duration-200"
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
                    class="bg-card rounded-lg shadow-sm border border overflow-hidden"
                >
                    <div
                        class="px-4 py-3 border-b border flex flex-col md:flex-row md:items-center justify-between gap-3"
                    >
                        <div>
                            <h3
                                class="text-sm font-bold text-foreground"
                            >
                                Riwayat Pembayaran
                            </h3>
                            <p class="text-xs text-muted-foreground">
                                Lacak semua transaksi iuran dan status verifikasinya.
                            </p>
                        </div>

                        <div
                            class="flex items-center gap-3"
                            v-if="['admin', 'bendahara', 'anggota'].includes($page.props.auth.user.role)"
                        >
                            <PrimaryButton
                                @click="showManualModal = true"
                                class="!rounded-xl shadow-lg shadow-sm"
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
                    <div class="px-4 pt-3">
                        <div class="flex flex-col gap-4">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div class="flex-1 w-full">
                                    <SearchBar v-model="contributionsFilters.search" placeholder="Cari nama anggota, periode, atau catatan..." />
                                </div>
                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        class="inline-flex items-center justify-center rounded-md border border bg-card px-3 py-2 text-xs font-bold uppercase tracking-widest text-foreground transition hover:bg-muted focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                                        @click="showFiltersDropdown = !showFiltersDropdown"
                                        :aria-expanded="showFiltersDropdown ? 'true' : 'false'"
                                    >
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h18M7 10h10M10 16h4" /></svg>
                                        Filter
                                        <span v-if="activeFilterCount" class="ml-2 inline-flex items-center rounded-full bg-primary text-white text-[10px] font-bold px-2 py-0.5">{{ activeFilterCount }}</span>
                                    </button>
                                    <SecondaryButton @click="resetFilters">Reset</SecondaryButton>
                                </div>
                            </div>

                            <div v-if="showFiltersDropdown" class="fixed inset-0 z-[998]" @click="showFiltersDropdown = false"></div>
                            <div v-if="showFiltersDropdown" class="fixed right-6 top-24 sm:top-28 z-[1000] w-[calc(100%-3rem)] sm:w-[28rem]">
                                <div class="rounded-xl border border bg-card shadow-2xl overflow-hidden">
                                    <div class="flex items-center justify-between px-4 py-3 border-b border">
                                        <span class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground">Filters</span>
                                        <button
                                            type="button"
                                            class="inline-flex items-center justify-center rounded-md px-2 py-1 text-[10px] font-bold uppercase tracking-widest text-primary hover:text-primary"
                                            @click="resetFilters"
                                        >
                                            Reset
                                        </button>
                                    </div>
                                    <div class="p-4 space-y-3 max-h-80 overflow-y-auto pr-1">
                                        <div class="space-y-2">
                                            <InputLabel value="Jenis Iuran" class="text-[10px] font-bold uppercase text-muted-foreground" />
                                            <select v-model="contributionsFilters.contribution_type_id" class="mt-1 block w-full border-input rounded-md text-sm">
                                                <option value="">Semua jenis</option>
                                                <option v-for="t in types" :key="t.id" :value="t.id">{{ t.name }}</option>
                                            </select>
                                        </div>
                                        <div class="space-y-2">
                                            <InputLabel value="Status" class="text-[10px] font-bold uppercase text-muted-foreground" />
                                            <FilterDropdown v-model="contributionsFilters.status" :options="statusOptions" label="Semua Status" />
                                        </div>
                                        <div class="space-y-2">
                                            <InputLabel value="Metode" class="text-[10px] font-bold uppercase text-muted-foreground" />
                                            <FilterDropdown v-model="contributionsFilters.payment_method" :options="methodOptions" label="Semua Metode" />
                                        </div>
                                        <div class="space-y-2">
                                            <InputLabel value="Dompet" class="text-[10px] font-bold uppercase text-muted-foreground" />
                                            <select v-model="contributionsFilters.wallet_id" class="mt-1 block w-full border-input rounded-md text-sm">
                                                <option value="">Semua dompet</option>
                                                <option v-for="w in wallets" :key="w.id" :value="w.id">{{ w.name }}</option>
                                            </select>
                                        </div>
                                        <div class="space-y-2">
                                            <InputLabel value="Periode Pembayaran" class="text-[10px] font-bold uppercase text-muted-foreground" />
                                            <FilterDropdown v-model="contributionsFilters.payment_period" :options="periodOptions" label="Semua Periode" />
                                        </div>
                                        <div class="space-y-2">
                                            <InputLabel value="Rentang Tanggal" class="text-[10px] font-bold uppercase text-muted-foreground" />
                                            <div class="flex items-center gap-2">
                                                <TextInput type="date" v-model="contributionsFilters.start_date" class="block flex-1 w-full" />
                                                <span class="text-xs text-muted-foreground">s.d.</span>
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
                                    class="inline-flex items-center gap-1 rounded-full bg-primary/10 text-primary border border-primary-200 px-3 py-1 text-xs font-semibold"
                                    @click="clearFilter(f.key)"
                                >
                                    <span>{{ f.label }}</span>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-1 rounded-full bg-muted text-foreground border border px-3 py-1 text-xs font-semibold"
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
                                        class="h-10 w-10 flex-shrink-0 rounded-full bg-muted border border flex items-center justify-center text-muted-foreground font-black text-xs"
                                    >
                                        {{
                                            (row.member?.full_name || "?")
                                                .charAt(0)
                                                .toUpperCase()
                                        }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span
                                            class="text-sm font-bold text-foreground leading-none mb-1"
                                        >
                                            {{ row.member?.full_name || "-" }}
                                        </span>
                                        <span
                                            class="text-[10px] font-black text-muted-foreground tracking-[0.1em] uppercase"
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
                                        class="text-sm font-bold text-foreground leading-none mb-1"
                                    >
                                        {{ row.type?.name || "-" }}
                                    </span>
                                    <span
                                        class="text-[10px] font-black text-muted-foreground tracking-wider uppercase"
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
                                    class="text-sm font-black text-primary tabular-nums"
                                >
                                    {{ formatCurrency(row.amount) }}
                                </span>
                            </template>

                            <template #cell-payment_date="{ row }">
                                <div class="flex flex-col">
                                    <span
                                        class="text-sm font-bold text-muted-foreground leading-none mb-1"
                                    >
                                        {{ formatDate(row.payment_date) }}
                                    </span>
                                    <span
                                        class="text-[10px] font-medium text-muted-foreground"
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
                                    class="inline-flex items-center px-2 py-1 rounded bg-muted border border"
                                >
                                    <span
                                        class="text-[10px] font-black text-muted-foreground tracking-wider uppercase shrink-0"
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
                                        class="p-2 text-primary hover:text-primary hover:bg-primary/10 rounded-xl transition"
                                        title="Verifikasi"
                                        @click="openVerifyModal(row)"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4" />
                                        </svg>
                                    </button>
                                    <Link
                                        :href="route('contributions.show', row)"
                                        class="p-2 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-xl transition-all"
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

        <!-- Verification Dialog -->
        <Dialog :open="showVerifyModal" @update:open="(val) => { if (!val) closeVerifyModal(); }">
            <DialogContent class="max-w-2xl">
                <DialogHeader>
                    <DialogTitle>Verifikasi Pembayaran</DialogTitle>
                </DialogHeader>
                <div v-if="selectedContribution" class="space-y-3">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-[10px] font-black uppercase text-muted-foreground tracking-widest mb-1">Anggota</p>
                            <p class="text-sm font-bold text-foreground">{{ selectedContribution.member?.full_name || '-' }}</p>
                            <p class="text-[10px] text-muted-foreground font-black uppercase tracking-widest">{{ selectedContribution.member?.member_code }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase text-muted-foreground tracking-widest mb-1">Jenis Iuran</p>
                            <p class="text-sm font-bold text-foreground">{{ selectedContribution.type?.name || '-' }}</p>
                            <p class="text-[10px] text-muted-foreground font-black uppercase tracking-widest">
                                {{ selectedContribution.payment_period || '-' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase text-muted-foreground tracking-widest mb-1">Jumlah</p>
                            <p class="text-sm font-black text-primary tabular-nums">{{ formatCurrency(selectedContribution.amount) }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase text-muted-foreground tracking-widest mb-1">Tanggal & Metode</p>
                            <p class="text-sm font-bold text-muted-foreground">{{ formatDate(selectedContribution.payment_date) }}</p>
                            <p class="text-[10px] font-medium text-muted-foreground">
                                {{ selectedContribution.payment_method === 'transfer' ? 'Via Transfer' : 'Tunai' }}
                            </p>
                        </div>
                    </div>
                    <div class="mt-2">
                        <p class="text-[10px] font-black uppercase text-muted-foreground tracking-widest mb-2">Bukti Pembayaran</p>
                        <div v-if="selectedContribution.receipt_path" class="bg-muted border border rounded-lg p-3">
                            <img :src="`/storage/${selectedContribution.receipt_path}`" alt="Bukti Pembayaran" class="max-h-[40vh] object-contain rounded" />
                        </div>
                        <p v-else class="text-xs text-muted-foreground italic">Tanpa bukti</p>
                    </div>
                </div>
                <DialogFooter class="gap-2">
                    <div class="flex-1">
                        <p class="text-[10px] font-black uppercase text-muted-foreground tracking-widest mb-2">Komentar Penolakan</p>
                        <Textarea
                            v-model="rejectComment"
                            rows="3"
                            placeholder="Tuliskan alasan penolakan"
                        />
                    </div>
                    <Button variant="outline" type="button" @click="verifyContribution(selectedContribution?.id, 'reject')" :disabled="verifyProcessingId === selectedContribution?.id">
                        Tolak
                    </Button>
                    <Button type="button" @click="verifyContribution(selectedContribution?.id, 'approve')" :disabled="verifyProcessingId === selectedContribution?.id">
                        Setujui
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <Dialog :open="showManualModal" @update:open="(val) => { if (!val) showManualModal = false; }">
            <DialogContent class="max-w-4xl max-h-[85vh] overflow-hidden flex flex-col">
                <!-- Header Simple -->
                <DialogHeader>
                    <DialogTitle>
                        {{ $page.props.auth.user.role === 'anggota' ? 'Upload Bukti Pembayaran' : 'Input Pembayaran' }}
                    </DialogTitle>
                    <DialogDescription>
                        {{ $page.props.auth.user.role === 'anggota' ? 'Unggah bukti transfer untuk pembayaran iuran Anda' : 'Catat transaksi pembayaran iuran anggota' }}
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitManual" class="flex flex-col flex-1 overflow-hidden">
                    <div class="flex-1 overflow-y-auto p-2">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

                            <!-- Bagian Kiri: Form Input -->
                            <div class="md:col-span-7 space-y-3">
                                <!-- Jenis Iuran -->
                                <div>
                                    <Label for="contribution_type_id">Jenis Iuran</Label>
                                    <Select v-model="manualForm.contribution_type_id">
                                        <SelectTrigger class="mt-1 w-full">
                                            <SelectValue placeholder="Pilih jenis iuran..." />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="t in types" :key="t.id" :value="t.id.toString()">{{ t.name }}</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="manualForm.errors.contribution_type_id" class="mt-1 text-sm text-destructive">{{ manualForm.errors.contribution_type_id }}</p>
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
                                        <Label for="amount">Nominal</Label>
                                        <div class="relative mt-1">
                                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                                <span class="text-muted-foreground sm:text-sm">Rp</span>
                                            </div>
                                            <CurrencyInput
                                                id="amount"
                                                v-model="manualForm.amount"
                                                class="block w-full rounded-lg border pl-10 text-foreground focus:ring-primary-600 font-semibold"
                                            />
                                        </div>
                                        <p v-if="manualForm.errors.amount" class="mt-1 text-sm text-destructive">{{ manualForm.errors.amount }}</p>
                                    </div>
                                    <div>
                                        <Label for="payment_date">Tanggal</Label>
                                        <Input
                                            id="payment_date"
                                            type="date"
                                            v-model="manualForm.payment_date"
                                            class="mt-1 block w-full rounded-lg border text-sm"
                                        />
                                        <p v-if="manualForm.errors.payment_date" class="mt-1 text-sm text-destructive">{{ manualForm.errors.payment_date }}</p>
                                    </div>
                                </div>

                                <!-- Grid Metode & Dompet -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div v-if="$page.props.auth.user.role !== 'anggota'">
                                        <Label>Metode Pembayaran</Label>
                                        <div class="grid grid-cols-2 gap-3 mt-1">
                                            <label class="cursor-pointer relative">
                                                <input
                                                    type="radio"
                                                    v-model="manualForm.payment_method"
                                                    value="cash"
                                                    class="peer sr-only"
                                                />
                                                <div class="rounded-lg border border p-3 hover:bg-muted peer-checked:border-primary peer-checked:bg-primary/10 peer-checked:text-primary text-muted-foreground transition-all flex flex-col items-center justify-center gap-1 text-center h-full">
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
                                                <div class="rounded-lg border border p-3 hover:bg-muted peer-checked:border-primary peer-checked:bg-primary/10 peer-checked:text-primary text-muted-foreground transition-all flex flex-col items-center justify-center gap-1 text-center h-full">
                                                    <svg class="w-6 h-6 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                                    </svg>
                                                    <span class="text-xs font-semibold">Transfer</span>
                                                </div>
                                            </label>
                                        </div>
                                        <p v-if="manualForm.errors.payment_method" class="mt-1 text-sm text-destructive">{{ manualForm.errors.payment_method }}</p>
                                    </div>
                                    <div v-else class="col-span-1">
                                        <div class="bg-primary/10 border border-primary-100 rounded-lg p-3 flex items-center gap-3 h-full">
                                            <div class="bg-primary/20 p-2 rounded-full text-primary">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-[10px] font-bold text-primary-900 uppercase tracking-wide">Metode Pembayaran</p>
                                                <p class="text-sm font-bold text-primary">Transfer Bank</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="!selectedType?.wallet_id && $page.props.auth.user.role !== 'anggota'">
                                        <Label for="wallet_id">Dompet</Label>
                                        <Select v-model="manualForm.wallet_id">
                                            <SelectTrigger class="mt-1 w-full">
                                                <SelectValue placeholder="Pilih dompet..." />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem v-for="w in wallets" :key="w.id" :value="w.id.toString()">{{ w.name }}</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <p v-if="manualForm.errors.wallet_id" class="mt-1 text-sm text-destructive">{{ manualForm.errors.wallet_id }}</p>
                                    </div>
                                </div>

                                <!-- Catatan -->
                                <div>
                                    <Label for="notes">Catatan (Opsional)</Label>
                                    <Textarea
                                        id="notes"
                                        v-model="manualForm.notes"
                                        rows="2"
                                        placeholder="Keterangan tambahan..."
                                    />
                                </div>

                                <!-- Bukti Transfer -->
                                <div>
                                    <Label>{{ $page.props.auth.user.role === 'anggota' ? 'Bukti Transfer' : 'Bukti Transfer (Opsional)' }}</Label>
                                    <div
                                        @dragover.prevent="isDraggingManualReceipt = true"
                                        @dragleave.prevent="isDraggingManualReceipt = false"
                                        @drop.prevent="handleManualReceiptDrop($event)"
                                        @click="manualReceiptInputRef?.click()"
                                        class="mt-1 relative border-2 border-dashed rounded-xl p-4 text-center cursor-pointer transition-all duration-200"
                                        :class="isDraggingManualReceipt ? 'border-primary bg-primary/5 scale-[1.01]' : 'border-border hover:border-primary/50 hover:bg-muted/30'"
                                    >
                                        <input ref="manualReceiptInputRef" type="file" accept="image/*" class="hidden" @change="handleManualReceiptSelect($event)" />
                                        <template v-if="!manualReceiptPreview">
                                            <Upload class="mx-auto h-7 w-7 text-muted-foreground mb-1.5" />
                                            <p class="text-xs font-medium text-primary">Upload Bukti Transfer</p>
                                            <p class="text-[11px] text-muted-foreground mt-0.5">Drag & drop atau klik. PNG, JPG max 2MB</p>
                                        </template>
                                        <template v-else>
                                            <div class="relative inline-block">
                                                <img :src="manualReceiptPreview" class="max-h-28 rounded-lg mx-auto" />
                                                <button type="button" @click.stop="clearManualReceipt" class="absolute -top-2 -right-2 w-5 h-5 bg-destructive text-white rounded-full flex items-center justify-center text-xs hover:bg-destructive/80">
                                                    <X class="w-3 h-3" />
                                                </button>
                                            </div>
                                            <p class="text-[11px] text-muted-foreground mt-1.5">{{ manualForm.receipt?.name }}</p>
                                        </template>
                                    </div>
                                    <p v-if="manualForm.errors.receipt" class="mt-1 text-sm text-destructive">{{ manualForm.errors.receipt }}</p>
                                </div>
                            </div>

                            <!-- Bagian Kanan: Periode -->
                            <div class="md:col-span-5 border-l border md:pl-6">
                                <Label class="mb-3">Pilih Periode</Label>

                                <div class="bg-muted rounded-xl p-3 h-[350px] overflow-y-auto border border">
                                    <div v-if="manualPeriodOptions.length" class="space-y-1">
                                        <label
                                            v-for="opt in manualPeriodOptions"
                                            :key="opt.value"
                                            class="flex items-center justify-between px-3 py-1.5 rounded-lg border bg-card cursor-pointer transition-all hover:border-primary-300"
                                            :class="[
                                                paidPeriods.includes(opt.value)
                                                    ? 'border opacity-50 cursor-not-allowed bg-muted'
                                                    : manualForm.payment_periods.includes(opt.value)
                                                        ? 'border-primary-500 ring-1 ring-primary-500 bg-primary/10/30'
                                                        : 'border'
                                            ]"
                                        >
                                            <div class="flex items-center gap-3">
                                                <div class="relative flex items-center">
                                                     <input
                                                        v-if="paidPeriods.includes(opt.value)"
                                                        type="checkbox"
                                                        class="h-4 w-4 rounded border-input text-muted-foreground focus:ring-0"
                                                        checked
                                                        disabled
                                                    />
                                                    <input
                                                        v-else
                                                        type="checkbox"
                                                        class="h-4 w-4 rounded border-input text-primary focus:ring-ring"
                                                        :value="opt.value"
                                                        v-model="manualForm.payment_periods"
                                                    />
                                                </div>
                                                <span class="text-xs font-bold text-foreground">{{ opt.label }}</span>
                                            </div>

                                            <span v-if="paidPeriods.includes(opt.value)" class="text-[10px] font-bold text-green-600 bg-green-100 px-2 py-0.5 rounded-full uppercase tracking-wider">
                                                Lunas
                                            </span>
                                        </label>
                                    </div>
                                    <div v-else class="flex flex-col items-center justify-center h-full text-center p-4">
                                        <svg class="w-8 h-8 text-muted-foreground mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <p class="text-xs text-muted-foreground">Pilih jenis iuran untuk menampilkan periode.</p>
                                    </div>
                                </div>
                                <p v-if="manualForm.errors.payment_period" class="mt-2 text-sm text-destructive">{{ manualForm.errors.payment_period }}</p>
                                <p v-if="manualForm.errors.periods" class="mt-1 text-sm text-destructive">{{ manualForm.errors.periods }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <DialogFooter class="px-2 py-4 border-t mt-4">
                        <div>
                            <p class="text-[10px] font-bold uppercase text-muted-foreground tracking-wider">Total Bayar</p>
                            <div class="flex items-baseline gap-2">
                                <p class="text-xl font-black text-foreground">{{ formatCurrency(manualForm.amount) }}</p>
                                <p v-if="selectedMember" class="text-xs text-muted-foreground hidden sm:inline-block">
                                    — {{ selectedMember?.full_name }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div v-if="selectedType?.period === 'once' && paidPeriods.includes('once')" class="text-xs text-green-600 font-bold mr-2">
                                Sudah Lunas
                            </div>
                            <Button variant="outline" type="button" @click="showManualModal = false">
                                Batal
                            </Button>
                            <Button type="submit" :disabled="manualForm.processing || (selectedType?.period === 'once' && paidPeriods.includes('once'))">
                                Simpan
                            </Button>
                        </div>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AuthenticatedLayout>
</template>
