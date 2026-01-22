<script setup>
import { ref, computed, watch } from 'vue';
import { Head, useForm, Link, usePage, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import SearchBar from '@/Components/SearchBar.vue';
import FilterDropdown from '@/Components/FilterDropdown.vue';
import axios from 'axios';
import MemberContributionDetailModal from './Partials/MemberContributionDetailModal.vue';

const props = defineProps({
    contributions: Object,
    types: Array,
    wallets: Array,
    members: Array,
    context: {
        type: String,
        default: 'default', // 'default', 'admin-history'
    },
    type: Object, // For admin-history context
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const isAdminOrTreasurer = computed(() => ['admin', 'bendahara'].includes(user.value.role));
const isAdminHistory = computed(() => props.context === 'admin-history');

const showCreateModal = ref(false);
const showVerifyModal = ref(false);
const showEditModal = ref(false);
const showMemberDetailModal = ref(false);
const activeTab = ref('riwayat');
const showFiltersModal = ref(false);
const showFiltersDropdown = ref(false);
const selectedContribution = ref(null);
const selectedTypeForDetail = ref(null);
const filteredMembers = ref([]);
const payAllUnpaid = ref(false);
const periodsCount = ref(1);
const generatedPeriods = ref([]);

const filters = ref({
    search: page.props.filters?.search || '',
    contribution_type_id: page.props.filters?.contribution_type_id || '',
    status: page.props.filters?.status || '',
    payment_method: page.props.filters?.payment_method || '',
    wallet_id: page.props.filters?.wallet_id || '',
    payment_period: page.props.filters?.payment_period || '',
    start_date: page.props.filters?.start_date || '',
    end_date: page.props.filters?.end_date || '',
    min_amount: page.props.filters?.min_amount || '',
    max_amount: page.props.filters?.max_amount || '',
});

const statusOptions = [
    { value: 'pending', label: 'Pending' },
    { value: 'paid', label: 'Dibayar' },
    { value: 'rejected', label: 'Ditolak' },
];
const paymentMethodOptions = [
    { value: 'transfer', label: 'Transfer' },
    { value: 'cash', label: 'Cash' },
];
const typeOptions = computed(() => (props.types || []).map(t => ({ value: String(t.id), label: t.name })));
const walletOptions = computed(() => (props.wallets || []).map(w => ({ value: String(w.id), label: w.name })));

const applyFilters = () => {
    const params = { ...filters.value };
    Object.keys(params).forEach(k => {
        if (params[k] === '' || params[k] === null) delete params[k];
    });
    router.get(route('contributions.index'), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const resetFilters = () => {
    filters.value = {
        search: '',
        contribution_type_id: '',
        status: '',
        payment_method: '',
        wallet_id: '',
        payment_period: '',
        start_date: '',
        end_date: '',
        min_amount: '',
        max_amount: '',
    };
    applyFilters();
};

let filtersDebounce = null;
watch(filters, () => {
    if (!isAdminOrTreasurer.value) return;
    if (filtersDebounce) clearTimeout(filtersDebounce);
    filtersDebounce = setTimeout(() => applyFilters(), 300);
}, { deep: true });
const form = useForm({
    member_id: user.value.role === 'anggota' ? user.value.member?.id : '',
    contribution_type_id: '',
    amount: '',
    payment_date: new Date().toISOString().split('T')[0],
    payment_period: '',
    payment_method: 'transfer',
    wallet_id: '',
    notes: '',
    receipt: null,
    member_ids: [],
    periods: [],
});

const verifyForm = useForm({
    status: 'paid',
});

const editForm = useForm({
    amount: '',
    payment_date: '',
    payment_period: '',
    payment_method: 'transfer',
    notes: '',
    receipt: null,
});

const periodic = ref({
    typeId: '',
    date: new Date().toISOString().split('T')[0],
    periodKey: '',
    summary: null,
    loading: false,
});

const updatePeriodicKey = () => {
    const t = props.types.find(tt => String(tt.id) === String(periodic.value.typeId));
    periodic.value.periodKey = t ? makePeriod(t.period, periodic.value.date) : '';
};

const loadPeriodSummary = async () => {
    updatePeriodicKey();
    const typeId = periodic.value.typeId;
    const periodKey = periodic.value.periodKey;
    if (!typeId || !periodKey) {
        periodic.value.summary = null;
        return;
    }
    periodic.value.loading = true;
    try {
        const res = await axios.get(route('contributions.period-summary'), {
            params: { contribution_type_id: typeId, payment_period: periodKey },
        });
        periodic.value.summary = res.data;
    } catch (e) {
        periodic.value.summary = null;
    } finally {
        periodic.value.loading = false;
    }
};

watch(() => activeTab.value, (tab) => {
    if (tab === 'periodik') {
        loadPeriodSummary();
    }
});

watch([() => periodic.value.typeId, () => periodic.value.date], () => {
    if (activeTab.value === 'periodik') {
        loadPeriodSummary();
    } else {
        updatePeriodicKey();
    }
});

const formatPercent = (p) => {
    const v = Math.max(0, Math.min(100, Number(p) || 0));
    return v;
};

const openVerifyModal = (contribution) => {
    selectedContribution.value = contribution;
    showVerifyModal.value = true;
};

const openEditModal = (contribution) => {
    selectedContribution.value = contribution;
    editForm.amount = contribution.amount;
    editForm.payment_date = contribution.payment_date;
    editForm.payment_period = contribution.payment_period || makePeriod(contribution.type?.period, contribution.payment_date);
    editForm.payment_method = contribution.payment_method || 'transfer';
    editForm.notes = contribution.notes || '';
    editForm.receipt = null;
    showEditModal.value = true;
};

const submitVerify = () => {
    verifyForm.post(route('contributions.verify', selectedContribution.value.id), {
        onSuccess: () => {
            showVerifyModal.value = false;
            selectedContribution.value = null;
        },
    });
};

const onEditPaymentDateChange = () => {
    const period = selectedContribution.value?.type?.period;
    editForm.payment_period = makePeriod(period, editForm.payment_date);
};

const submitEdit = () => {
    editForm.put(route('contributions.update', selectedContribution.value.id), {
        onSuccess: () => {
            showEditModal.value = false;
            selectedContribution.value = null;
            editForm.reset();
        },
    });
};

const submitCreate = () => {
    const type = props.types.find(t => t.id == form.contribution_type_id);
    const isRecurring = type && type.period && type.period !== 'once';
    form.periods = isRecurring ? generatedPeriods.value : [];

    // Ensure member id stays set for anggota
    if (!isAdminOrTreasurer.value) {
        form.member_id = user.value.member?.id;
    }

    if (isAdminOrTreasurer.value && payAllUnpaid.value) {
        form.member_ids = filteredMembers.value.map(m => m.id);
        form.post(route('contributions.bulk-store'), {
            onSuccess: () => {
                showCreateModal.value = false;
                form.reset();
                filteredMembers.value = [];
                payAllUnpaid.value = false;
                generatedPeriods.value = [];
                periodsCount.value = 1;
            },
        });
    } else if (isRecurring && generatedPeriods.value.length > 1) {
        form.member_ids = form.member_id ? [form.member_id] : [];
        form.post(route('contributions.bulk-store'), {
            onSuccess: () => {
                showCreateModal.value = false;
                form.reset();
                filteredMembers.value = [];
                generatedPeriods.value = [];
                periodsCount.value = 1;
            },
        });
    } else {
        form.post(route('contributions.store'), {
            onSuccess: () => {
                showCreateModal.value = false;
                form.reset();
                filteredMembers.value = [];
                generatedPeriods.value = [];
                periodsCount.value = 1;
            },
        });
    }
};

const onContributionTypeChange = async (e) => {
    const typeId = e.target.value;
    const type = props.types.find(t => t.id == typeId);
    
    if (type) {
        form.amount = type.amount;
        // Auto-generate payment period for recurring contributions
        updatePaymentPeriod(type.period, form.payment_date);
        updateGeneratedPeriods(type.period, form.payment_date, periodsCount.value);
    }
    
    if (isAdminOrTreasurer.value) {
        form.member_id = '';
    } else {
        form.member_id = user.value.member?.id || form.member_id;
    }
    
    await new Promise(resolve => setTimeout(resolve, 0));
    await fetchUnpaidMembers();
};

const onPaymentDateChange = () => {
    const type = props.types.find(t => t.id == form.contribution_type_id);
    if (type) {
        updatePaymentPeriod(type.period, form.payment_date);
        updateGeneratedPeriods(type.period, form.payment_date, periodsCount.value);
        fetchUnpaidMembers();
    }
};

const updatePaymentPeriod = (period, date) => {
    if (!date || period === 'once') {
        form.payment_period = '';
        return;
    }
    const paymentDate = new Date(date);
    const year = paymentDate.getFullYear();
    const month = String(paymentDate.getMonth() + 1).padStart(2, '0');
    const day = String(paymentDate.getDate()).padStart(2, '0');
    
    switch (period) {
        case 'monthly':
            form.payment_period = `${year}-${month}`;
            break;
        case 'yearly':
            form.payment_period = `${year}`;
            break;
        case 'weekly':
            // Calculate week number
            const firstDayOfYear = new Date(year, 0, 1);
            const pastDaysOfYear = (paymentDate - firstDayOfYear) / 86400000;
            const weekNumber = Math.ceil((pastDaysOfYear + firstDayOfYear.getDay() + 1) / 7);
            form.payment_period = `${year}-W${String(weekNumber).padStart(2, '0')}`;
            break;
        case 'daily':
            form.payment_period = `${year}-${month}-${day}`;
            break;
        default:
            form.payment_period = '';
    }
};

const makePeriod = (period, date) => {
    if (!date || period === 'once') return '';
    const paymentDate = new Date(date);
    const year = paymentDate.getFullYear();
    const month = String(paymentDate.getMonth() + 1).padStart(2, '0');
    const day = String(paymentDate.getDate()).padStart(2, '0');
    switch (period) {
        case 'monthly':
            return `${year}-${month}`;
        case 'yearly':
            return `${year}`;
        case 'weekly': {
            const firstDayOfYear = new Date(year, 0, 1);
            const pastDaysOfYear = (paymentDate - firstDayOfYear) / 86400000;
            const weekNumber = Math.ceil((pastDaysOfYear + firstDayOfYear.getDay() + 1) / 7);
            return `${year}-W${String(weekNumber).padStart(2, '0')}`;
        }
        case 'daily':
            return `${year}-${month}-${day}`;
        default:
            return '';
    }
};

const isTypePaidForCurrentPeriod = (type) => {
    if (user.value.role !== 'anggota') return false;
    const today = new Date().toISOString().split('T')[0];
    const periodKey = makePeriod(type.period, today);
    const myId = user.value.member?.id;
    if (!myId) return false;
    return (props.contributions?.data || []).some(c => {
        const sameType = c.contribution_type_id === type.id || c.type?.id === type.id;
        const sameMember = c.member_id === myId;
        const isPaid = c.status === 'paid';
        if (!sameType || !sameMember || !isPaid) return false;
        if (type.period === 'once') return true;
        return c.payment_period === periodKey;
    });
};

const updateGeneratedPeriods = (period, date, count) => {
    generatedPeriods.value = [];
    if (!period || period === 'once' || !date || count <= 0) return;
    const start = new Date(date);
    for (let i = 0; i < count; i++) {
        const d = new Date(start);
        if (period === 'monthly') {
            d.setMonth(start.getMonth() + i);
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            generatedPeriods.value.push(`${y}-${m}`);
        } else if (period === 'yearly') {
            d.setFullYear(start.getFullYear() + i);
            const y = d.getFullYear();
            generatedPeriods.value.push(`${y}`);
        } else if (period === 'weekly') {
            const d2 = new Date(start);
            d2.setDate(start.getDate() + i * 7);
            const y = d2.getFullYear();
            const firstDayOfYear = new Date(y, 0, 1);
            const pastDaysOfYear = (d2 - firstDayOfYear) / 86400000;
            const weekNumber = Math.ceil((pastDaysOfYear + firstDayOfYear.getDay() + 1) / 7);
            generatedPeriods.value.push(`${y}-W${String(weekNumber).padStart(2, '0')}`);
        } else if (period === 'daily') {
            d.setDate(start.getDate() + i);
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            generatedPeriods.value.push(`${y}-${m}-${day}`);
        }
    }
};

const fetchUnpaidMembers = async () => {
    const typeId = form.contribution_type_id;
    if (typeId && isAdminOrTreasurer.value) {
        try {
            const params = { contribution_type_id: typeId };
            if (form.payment_period) params.payment_period = form.payment_period;
            const response = await axios.get(route('contributions.unpaid-members'), { params });
            filteredMembers.value = response.data;
        } catch (error) {
            filteredMembers.value = [];
        }
    } else {
        filteredMembers.value = [];
    }
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(amount);
};

const openPayForType = async (type) => {
    try {
        console.log('Opening payment for type:', type);
        // Direct check on role to avoid computed issues
        const role = user.value?.role;
        const isOfficial = ['admin', 'bendahara'].includes(role);

        if (!isOfficial) {
            console.log('User is member, opening detail modal');
            // Member View: Open New Modal
            selectedTypeForDetail.value = type;
            showMemberDetailModal.value = true;
            return;
        }
        
        console.log('User is admin, opening create modal');
        // Admin View: Use old method
        form.contribution_type_id = String(type.id);
        form.amount = type.amount;
        updatePaymentPeriod(type.period, form.payment_date);
        periodsCount.value = 1;
        updateGeneratedPeriods(type.period, form.payment_date, periodsCount.value);
        await fetchUnpaidMembers();
        showCreateModal.value = true;
    } catch (e) {
        console.error('Error opening payment modal:', e);
    }
};

const getStatusBadge = (status) => {
    const map = {
        pending: 'bg-yellow-100 text-yellow-700',
        paid: 'bg-green-100 text-green-700',
        rejected: 'bg-red-100 text-red-700',
    };
    return map[status] || 'bg-gray-100 text-gray-700';
};

const statusLabels = {
    pending: 'Menunggu Verifikasi',
    paid: 'Sudah Dibayar',
    rejected: 'Ditolak',
};
</script>

<template>
    <Head :title="isAdminHistory ? `Riwayat - ${type?.name || ''}` : 'Iuran Anggota'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                 <div v-if="isAdminHistory" class="flex items-center">
                     <Link :href="route('contributions.monitoring.index')" class="mr-3 text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        Riwayat: {{ type?.name || '' }}
                    </h2>
                </div>
                <h2 v-else class="text-xl font-semibold leading-tight text-gray-800">
                    {{ isAdminOrTreasurer ? 'Manajemen Iuran Anggota' : 'Riwayat Iuran Saya' }}
                </h2>
                <PrimaryButton v-if="isAdminOrTreasurer && !isAdminHistory" @click="showCreateModal = true">
                    Bayar Iuran Manual
                </PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8" :class="{ 'flex flex-col md:flex-row gap-6': isAdminHistory }">
                
                <!-- Sidebar (Only for History Mode) -->
                <div v-if="isAdminHistory" class="w-full md:w-64 shrink-0">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden sticky top-6">
                        <div class="p-4 bg-gray-50 border-b border-gray-100">
                            <h3 class="font-bold text-gray-700">Menu</h3>
                        </div>
                        <div class="p-2 space-y-1">
                            <Link :href="route('contributions.monitoring.dashboard', type?.id)" 
                                class="flex items-center px-4 py-3 text-sm font-bold rounded-xl transition-colors"
                                :class="route().current('contributions.monitoring.dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50'"
                            >
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                                Dashboard
                            </Link>
                            <Link :href="route('contributions.monitoring.matrix', type?.id)" 
                                class="flex items-center px-4 py-3 text-sm font-bold rounded-xl transition-colors"
                                :class="route().current('contributions.monitoring.matrix') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50'"
                            >
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7-8v8m14-8v8M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                Matrix
                            </Link>
                            <Link :href="route('contributions.monitoring.history', type?.id)" 
                                class="flex items-center px-4 py-3 text-sm font-bold rounded-xl transition-colors"
                                :class="route().current('contributions.monitoring.history') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50'"
                            >
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Riwayat Transaksi
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Global Admin Navigation Tabs (Only if NOT History mode) -->
                <div v-if="isAdminOrTreasurer && !isAdminHistory" class="flex space-x-2 border-b border-gray-200 pb-2 overflow-x-auto mb-6">
                    <Link :href="route('contributions.monitoring.index')" class="px-4 py-2 text-sm font-bold rounded-lg text-gray-600 hover:bg-gray-50">
                        Jenis Iuran Aktif
                    </Link>
                    <Link :href="route('contributions.verification')" class="px-4 py-2 text-sm font-bold rounded-lg text-gray-600 hover:bg-gray-50">
                        Verifikasi
                    </Link>
                    <Link :href="route('contributions.index')" class="px-4 py-2 text-sm font-bold rounded-lg bg-indigo-50 text-indigo-700">
                        Riwayat Transaksi
                    </Link>
                </div>

                <!-- Main Content Container -->
                 <div :class="{ 'flex-1 space-y-6': isAdminHistory }">

                <!-- Active Dues -->
                <div class="bg-white overflow-hidden shadow-sm rounded-xl mb-8">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <h3 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Daftar Iuran Aktif</h3>
                    </div>
                    <div class="p-6">
                        <div v-if="types && types.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div v-for="type in types" :key="type.id" class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition bg-white">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-xs font-bold uppercase text-gray-500">Jenis Iuran</p>
                                        <p class="text-sm font-black text-gray-900">{{ type.name }}</p>
                                    </div>
                                    <span class="px-2 py-1 rounded-lg text-[11px] font-bold uppercase tracking-widest"
                                        :class="type.period === 'monthly' ? 'bg-indigo-50 text-indigo-600 border border-indigo-200' 
                                            : type.period === 'yearly' ? 'bg-blue-50 text-blue-600 border border-blue-200'
                                            : type.period === 'weekly' ? 'bg-amber-50 text-amber-600 border border-amber-200'
                                            : type.period === 'daily' ? 'bg-green-50 text-green-600 border border-green-200'
                                            : 'bg-gray-50 text-gray-600 border border-gray-200'">
                                        {{ (type.period || 'once') }}
                                    </span>
                                </div>
                                <div class="mt-3">
                                    <p class="text-xs font-bold uppercase text-gray-500">Nominal</p>
                                    <p class="text-base font-black text-indigo-600">{{ formatCurrency(type.amount) }}</p>
                                </div>
                                
                                <div v-if="user.role === 'anggota' && type.user_progress && type.period !== 'once'" class="mt-3">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-[10px] font-bold uppercase text-gray-400">Progres</span>
                                        <span class="text-[10px] font-black" :class="type.user_progress.percentage === 100 ? 'text-green-600' : 'text-gray-700'">{{ type.user_progress.text }} ({{ type.user_progress.percentage }}%)</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-500" 
                                            :class="type.user_progress.percentage === 100 ? 'bg-green-500' : 'bg-indigo-500'" 
                                            :style="{ width: type.user_progress.percentage + '%' }">
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <div v-if="isTypePaidForCurrentPeriod(type) && user.role === 'anggota'" class="w-full px-4 py-2.5 rounded-xl text-sm font-black uppercase tracking-widest text-green-700 bg-green-50 border border-green-200 text-center">
                                        Sudah dibayarkan untuk periode ini
                                    </div>
                                    <div v-else-if="user.role === 'anggota' && !type.wallet_id" class="w-full px-4 py-2.5 rounded-xl text-sm font-black uppercase tracking-widest text-amber-700 bg-amber-50 border border-amber-200 text-center">
                                        Belum tersedia dompet tujuan
                                    </div>
                                    <button v-else type="button" @click="openPayForType(type)"
                                        class="w-full px-4 py-2.5 rounded-xl text-sm font-black uppercase tracking-widest text-white bg-gray-900 hover:bg-gray-800 transition">
                                        Bayar Iuran Ini
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center text-gray-400 text-sm py-8">
                            Belum ada jenis iuran aktif.
                        </div>
                    </div>
                </div>
                <!-- Data Table -->
                <div class="bg-white overflow-hidden shadow-sm rounded-xl">
                    <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                        <div class="flex items-center justify-between gap-4">
                            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Iuran Anggota</h3>
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="activeTab = 'riwayat'"
                                    class="px-3 py-2 rounded-lg text-xs font-bold uppercase tracking-widest transition"
                                    :class="activeTab === 'riwayat' ? 'bg-gray-900 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50'"
                                >
                                    Riwayat
                                </button>
                                <button
                                    type="button"
                                    @click="activeTab = 'periodik'"
                                    class="px-3 py-2 rounded-lg text-xs font-bold uppercase tracking-widest transition"
                                    :class="activeTab === 'periodik' ? 'bg-gray-900 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50'"
                                >
                                    Periodik
                                </button>
                            </div>
                        </div>
                        <div v-if="activeTab === 'riwayat'">
                            <div class="mt-4 flex items-center justify-end gap-2" v-if="isAdminOrTreasurer">
                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-xs font-bold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                                    @click="showFiltersDropdown = !showFiltersDropdown"
                                    title="Dropdown"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    </svg>
                                    Dropdown
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-xs font-bold uppercase tracking-widest text-gray-700 transition hover:bg-gray-50"
                                    @click="showFiltersModal = true"
                                    title="Popup"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    </svg>
                                    Popup
                                </button>
                            </div>
                            <div v-if="isAdminOrTreasurer" class="mt-4">
                                <div class="max-w-md">
                                    <SearchBar
                                        v-model="filters.search"
                                        placeholder="Cari nama, kode anggota, atau catatan..."
                                    />
                                </div>
                            </div>
                            <div v-if="showFiltersDropdown" class="fixed inset-0 z-40" @click="showFiltersDropdown = false"></div>
                            <div v-if="showFiltersDropdown && isAdminOrTreasurer" class="relative">
                                <div class="absolute right-6 top-6 z-50 w-80 rounded-xl border border-gray-200 bg-white shadow-xl overflow-hidden">
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
                                    <div class="p-4 space-y-4">
                                        <div class="space-y-2">
                                            <InputLabel value="Jenis Iuran" class="text-[10px] font-bold uppercase text-gray-500" />
                                            <FilterDropdown v-model="filters.contribution_type_id" :options="typeOptions" label="Semua Jenis" />
                                        </div>
                                        <div class="space-y-2">
                                            <InputLabel value="Status" class="text-[10px] font-bold uppercase text-gray-500" />
                                            <FilterDropdown v-model="filters.status" :options="statusOptions" label="Semua Status" />
                                        </div>
                                        <div class="space-y-2">
                                            <InputLabel value="Metode" class="text-[10px] font-bold uppercase text-gray-500" />
                                            <FilterDropdown v-model="filters.payment_method" :options="paymentMethodOptions" label="Semua Metode" />
                                        </div>
                                        <div class="space-y-2">
                                            <InputLabel value="Dompet" class="text-[10px] font-bold uppercase text-gray-500" />
                                            <FilterDropdown v-model="filters.wallet_id" :options="walletOptions" label="Semua Dompet" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="mt-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <InputLabel value="Jenis Iuran" class="text-[10px] font-bold uppercase text-gray-500 mb-1" />
                                    <select v-model="periodic.typeId" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                        <option value="">-- Pilih Jenis --</option>
                                        <option v-for="t in types" :key="t.id" :value="String(t.id)">{{ t.name }}</option>
                                    </select>
                                </div>
                                <div>
                                    <InputLabel value="Tanggal Acuan" class="text-[10px] font-bold uppercase text-gray-500 mb-1" />
                                    <input type="date" v-model="periodic.date" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
                                </div>
                                <div>
                                    <InputLabel value="Periode" class="text-[10px] font-bold uppercase text-gray-500 mb-1" />
                                    <div class="w-full rounded-md border border-indigo-200 bg-indigo-50 px-3 py-2 text-sm font-black text-indigo-700">
                                        {{ periodic.periodKey || '-' }}
                                    </div>
                                </div>
                            </div>
                            <div class="mt-6">
                                <div v-if="periodic.loading" class="p-6 border border-gray-200 rounded-xl text-sm text-gray-500">
                                    Memuat ringkasan...
                                </div>
                                <div v-else-if="periodic.summary" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                    <div class="p-6 border border-gray-200 rounded-xl">
                                        <p class="text-[10px] uppercase font-bold text-gray-500 mb-2">Progres Terkumpul</p>
                                        <div class="h-3 rounded-full bg-gray-100 overflow-hidden">
                                            <div class="h-3 bg-indigo-600" :style="{ width: formatPercent(periodic.summary.percentage) + '%' }"></div>
                                        </div>
                                        <p class="mt-2 text-sm font-black text-indigo-700">{{ formatPercent(periodic.summary.percentage) }}%</p>
                                    </div>
                                    <div class="p-6 border border-gray-200 rounded-xl">
                                        <p class="text-[10px] uppercase font-bold text-gray-500 mb-2">Ringkasan Anggota</p>
                                        <div class="text-sm font-black text-gray-900">Aktif: {{ periodic.summary.active_count }}</div>
                                        <div class="text-sm font-black text-green-700">Sudah: {{ periodic.summary.paid_count }}</div>
                                        <div class="text-sm font-black text-red-700">Belum: {{ periodic.summary.unpaid_count }}</div>
                                    </div>
                                    <div class="p-6 border border-gray-200 rounded-xl">
                                        <p class="text-[10px] uppercase font-bold text-gray-500 mb-2">Nominal</p>
                                        <div class="text-sm font-black text-indigo-700">Terkumpul: {{ formatCurrency(periodic.summary.collected_amount) }}</div>
                                        <div class="text-sm font-black text-gray-900">Target: {{ formatCurrency(periodic.summary.expected_amount) }}</div>
                                    </div>
                                </div>
                                <div v-else class="p-6 border border-gray-200 rounded-xl text-sm text-gray-500">
                                    Pilih jenis iuran dan tanggal acuan untuk melihat ringkasan.
                                </div>
                            </div>
                            <div v-if="periodic.summary" class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <div class="border border-gray-200 rounded-xl overflow-hidden">
                                    <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-100 text-[10px] font-bold uppercase tracking-widest text-gray-500">
                                        Sudah Membayar
                                    </div>
                                    <div class="p-6 overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50/50 uppercase tracking-widest text-[10px] font-bold text-gray-400">
                                                <tr>
                                                    <th class="px-6 py-3 text-left">Nama</th>
                                                    <th class="px-6 py-3 text-left">Kode</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                <tr v-for="m in periodic.summary.paid_members" :key="m.id" class="hover:bg-gray-50/50 transition">
                                                    <td class="px-6 py-3 text-sm font-bold text-gray-900">{{ m.full_name }}</td>
                                                    <td class="px-6 py-3 text-sm font-bold text-gray-500">{{ m.member_code }}</td>
                                                </tr>
                                                <tr v-if="!periodic.summary.paid_members.length">
                                                    <td colspan="2" class="px-6 py-3 text-sm text-gray-400">Belum ada pembayaran untuk periode ini.</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="border border-gray-200 rounded-xl overflow-hidden">
                                    <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-100 text-[10px] font-bold uppercase tracking-widest text-gray-500">
                                        Belum Membayar
                                    </div>
                                    <div class="p-6 overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50/50 uppercase tracking-widest text-[10px] font-bold text-gray-400">
                                                <tr>
                                                    <th class="px-6 py-3 text-left">Nama</th>
                                                    <th class="px-6 py-3 text-left">Kode</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                <tr v-for="m in periodic.summary.unpaid_members" :key="m.id" class="hover:bg-gray-50/50 transition">
                                                    <td class="px-6 py-3 text-sm font-bold text-gray-900">{{ m.full_name }}</td>
                                                    <td class="px-6 py-3 text-sm font-bold text-gray-500">{{ m.member_code }}</td>
                                                </tr>
                                                <tr v-if="!periodic.summary.unpaid_members.length">
                                                    <td colspan="2" class="px-6 py-3 text-sm text-gray-400">Semua anggota sudah membayar untuk periode ini.</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <Modal :show="showFiltersModal" @close="showFiltersModal = false" maxWidth="2xl">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-widest">Filter Iuran</h3>
                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center rounded-md px-2 py-1 text-xs text-gray-500 hover:text-gray-700"
                                    @click="showFiltersModal = false"
                                >
                                    Tutup
                                </button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="space-y-2">
                                    <InputLabel value="Jenis Iuran" class="text-[10px] font-bold uppercase text-gray-500" />
                                    <FilterDropdown v-model="filters.contribution_type_id" :options="typeOptions" label="Semua Jenis" />
                                </div>
                                <div class="space-y-2">
                                    <InputLabel value="Status" class="text-[10px] font-bold uppercase text-gray-500" />
                                    <FilterDropdown v-model="filters.status" :options="statusOptions" label="Semua Status" />
                                </div>
                                <div class="space-y-2">
                                    <InputLabel value="Metode" class="text-[10px] font-bold uppercase text-gray-500" />
                                    <FilterDropdown v-model="filters.payment_method" :options="paymentMethodOptions" label="Semua Metode" />
                                </div>
                                <div class="space-y-2">
                                    <InputLabel value="Dompet" class="text-[10px] font-bold uppercase text-gray-500" />
                                    <FilterDropdown v-model="filters.wallet_id" :options="walletOptions" label="Semua Dompet" />
                                </div>
                                <div class="space-y-2">
                                    <InputLabel value="Periode" class="text-[10px] font-bold uppercase text-gray-500" />
                                    <input v-model="filters.payment_period" type="text" placeholder="YYYY-MM atau YYYY" class="block w-full py-2 px-3 text-sm border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                                </div>
                                <div class="space-y-2">
                                    <InputLabel value="Tanggal Bayar" class="text-[10px] font-bold uppercase text-gray-500" />
                                    <div class="flex gap-2">
                                        <input v-model="filters.start_date" type="date" class="block flex-1 w-full py-2 px-3 text-sm border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                                        <input v-model="filters.end_date" type="date" class="block flex-1 w-full py-2 px-3 text-sm border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <InputLabel value="Nominal" class="text-[10px] font-bold uppercase text-gray-500" />
                                    <div class="flex gap-2">
                                        <input v-model="filters.min_amount" type="number" placeholder="Min" class="block flex-1 w-full py-2 px-3 text-sm border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                                        <input v-model="filters.max_amount" type="number" placeholder="Max" class="block flex-1 w-full py-2 px-3 text-sm border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                                    </div>
                                </div>
                            </div>
                            <div class="mt-6 flex justify-end gap-2">
                                <SecondaryButton @click="resetFilters" class="px-4 py-2 rounded-md">Reset</SecondaryButton>
                                <PrimaryButton @click="applyFilters" class="px-4 py-2 rounded-md">Terapkan</PrimaryButton>
                            </div>
                        </div>
                    </Modal>

                    <div v-if="activeTab === 'riwayat'" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50/50 uppercase tracking-widest text-[10px] font-bold text-gray-400">
                                <tr>
                                    <th v-if="isAdminOrTreasurer" class="px-6 py-4 text-left">Anggota</th>
                                    <th class="px-6 py-4 text-left">Jenis Iuran</th>
                                    <th class="px-6 py-4 text-left">Tanggal Bayar</th>
                                    <th class="px-6 py-4 text-left">Periode</th>
                                    <th class="px-6 py-4 text-left">Jumlah</th>
                                    <th class="px-6 py-4 text-left">Status</th>
                                    <th class="px-6 py-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 italic">
                                <tr v-for="contribution in contributions.data" :key="contribution.id" class="hover:bg-gray-50/50 transition font-medium">
                                    <td v-if="isAdminOrTreasurer" class="px-6 py-4">
                                        <div class="text-sm font-bold text-gray-900">{{ contribution.member?.full_name || 'Anggota Terhapus' }}</div>
                                        <div class="text-[10px] text-gray-400">{{ contribution.member?.member_code || '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-gray-700">{{ contribution.type?.name || '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 font-bold">
                                        {{ contribution.payment_date ? new Date(contribution.payment_date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 font-bold">
                                        {{ contribution.payment_period || '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-black text-indigo-600">
                                        {{ formatCurrency(contribution.amount) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tight" :class="getStatusBadge(contribution.status)">
                                            {{ statusLabels[contribution.status] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <a v-if="contribution.receipt_path" :href="'/storage/' + contribution.receipt_path" target="_blank" class="p-2 text-indigo-400 hover:bg-indigo-50 rounded-lg transition" title="Lihat Bukti Bayar">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                            <button v-if="isAdminOrTreasurer && contribution.status === 'pending' && hasPermission('manage_contributions')" @click="openVerifyModal(contribution)" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-md shadow-indigo-100 hover:bg-indigo-700 transition">
                                                Verifikasi
                                            </button>
                                            <button v-if="isAdminOrTreasurer && contribution.status === 'paid' && hasPermission('manage_contributions')" @click="openEditModal(contribution)" class="px-4 py-2 bg-white text-gray-700 border border-gray-200 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-gray-50 transition">
                                                Ubah
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="contributions.data.length === 0">
                                    <td :colspan="isAdminOrTreasurer ? 7 : 6" class="px-6 py-12 text-center text-gray-500 italic font-medium">Belum ada riwayat pembayaran iuran.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="contributions.links.length > 3" class="px-6 py-4 border-t border-gray-100 flex justify-center">
                        <div class="flex flex-wrap gap-1">
                            <template v-for="(link, index) in contributions.links" :key="index">
                                <span v-if="!link.url"
                                    class="px-4 py-2 text-[10px] font-black rounded-lg uppercase tracking-tighter bg-white text-gray-400 border border-gray-100 opacity-50 cursor-not-allowed"
                                    v-html="link.label" />
                                <Link v-else
                                    :href="link.url"
                                    :preserve-scroll="true"
                                    class="px-4 py-2 text-[10px] font-black rounded-lg transition uppercase tracking-tighter"
                                    :class="link.active ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-gray-500 hover:bg-gray-50 border border-gray-100'"
                                    v-html="link.label" />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

                </div> <!-- End Main Content Container -->

        <!-- Modal Bayar Iuran -->
        <Modal :show="showCreateModal" @close="showCreateModal = false">
            <div class="bg-white rounded-2xl overflow-hidden max-w-2xl mx-auto">
                <div class="bg-gray-50/50 p-6 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-xl font-black text-gray-900 uppercase tracking-tight">Bayar Iuran Baru</h2>
                    <button @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600 transition p-2 rounded-full hover:bg-white">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-8">
                    <form @submit.prevent="submitCreate" class="space-y-6">
                        <!-- Contribution Type Selection (First) -->
                        <div>
                            <InputLabel value="Jenis Iuran" class="text-xs font-bold uppercase text-gray-500 mb-3" />
                            <div class="grid grid-cols-2 gap-3">
                                <select v-model="form.contribution_type_id" 
                                    @change="onContributionTypeChange"
                                    class="col-span-2 w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm font-bold text-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" 
                                    required>
                                    <option value="">-- Pilih Jenis Iuran --</option>
                                    <option v-for="type in types" :key="type.id" :value="type.id">
                                        {{ type.name }} ({{ formatCurrency(type.amount) }})
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Member Selection (Filtered) -->
                        <div v-if="form.contribution_type_id && isAdminOrTreasurer">
                            <InputLabel value="Pilih Anggota" class="text-xs font-bold uppercase text-gray-500 mb-3" />
                            <div class="flex items-center justify-between mb-3">
                                <label class="flex items-center gap-2 text-xs font-bold text-gray-600">
                                    <input type="checkbox" v-model="payAllUnpaid" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    Bayar untuk semua anggota yang belum membayar
                                </label>
                            </div>
                            <select v-model="form.member_id" 
                                class="w-full px-4 py-3 border-2 border-indigo-100 rounded-xl text-sm font-bold text-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition bg-white" 
                                :required="!payAllUnpaid" :disabled="payAllUnpaid">
                                <option value="">-- Cari Nama/Kode Anggota --</option>
                                <option v-for="member in filteredMembers" :key="member.id" :value="member.id">
                                    {{ member.full_name }} ({{ member.member_code }})
                                </option>
                            </select>
                            <p v-if="filteredMembers.length === 0" class="mt-2 text-xs text-amber-600 italic">
                                ⚠️ Semua anggota sudah membayar iuran ini.
                            </p>
                            <p v-else class="mt-2 text-xs text-gray-500 italic">
                                {{ filteredMembers.length }} anggota belum membayar
                            </p>
                            <InputError class="mt-2" :message="form.errors.member_id" />
                        </div>

                        <!-- Date and Amount -->
                    <div class="grid grid-cols-2 gap-4">
                            <div>
                                <InputLabel value="Tanggal Bayar" class="text-xs font-bold uppercase text-gray-500 mb-3" />
                                <input type="date" v-model="form.payment_date" @change="onPaymentDateChange" required
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm font-bold text-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" />
                            </div>
                            <div>
                                <InputLabel value="Nominal (Rp)" class="text-xs font-bold uppercase text-gray-500 mb-3" />
                                <input type="number" v-model="form.amount" required step="0.01"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm font-black text-indigo-600 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" />
                                <InputError class="mt-2" :message="form.errors.amount" />
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div v-if="isAdminOrTreasurer">
                            <InputLabel value="Metode Pembayaran" class="text-xs font-bold uppercase text-gray-500 mb-3" />
                            <select v-model="form.payment_method" required
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm font-bold text-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition bg-white">
                                <option value="transfer">Transfer</option>
                                <option value="cash">Cash</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.payment_method" />
                        </div>

                        <!-- Wallet Selection (only if type has no wallet) -->
                        <div v-if="isAdminOrTreasurer && form.contribution_type_id && !types.find(t => t.id == form.contribution_type_id)?.wallet_id">
                            <InputLabel value="Pilih Dompet Tujuan" class="text-xs font-bold uppercase text-gray-500 mb-3" />
                            <select v-model="form.wallet_id" required
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm font-bold text-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition bg-white">
                                <option value="">-- Pilih Dompet --</option>
                                <option v-for="w in wallets" :key="w.id" :value="w.id">
                                    {{ w.name }}
                                </option>
                            </select>
                            <p class="mt-2 text-xs text-amber-600 italic">Jenis iuran belum terhubung ke dompet. Silakan pilih dompet tujuan.</p>
                            <InputError class="mt-2" :message="form.errors.wallet_id" />
                        </div>

                    <!-- Periods Count for Recurring -->
                    <div v-if="form.contribution_type_id && props.types.find(t => t.id == form.contribution_type_id)?.period !== 'once'">
                        <InputLabel value="Jumlah Periode" class="text-xs font-bold uppercase text-gray-500 mb-3" />
                        <div class="grid grid-cols-2 gap-4 items-end">
                            <div>
                                <input type="number" min="1" v-model.number="periodsCount" @change="updateGeneratedPeriods(props.types.find(t => t.id == form.contribution_type_id)?.period, form.payment_date, periodsCount)"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm font-bold text-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" />
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-bold uppercase text-gray-500">Total Nominal</p>
                                <p class="text-sm font-black text-indigo-600">{{ formatCurrency((generatedPeriods.length || 1) * (Number(form.amount) || 0)) }}</p>
                            </div>
                        </div>
                        <div v-if="generatedPeriods.length" class="mt-3 p-3 border border-gray-200 rounded-xl bg-gray-50">
                            <p class="text-[10px] uppercase font-bold text-gray-500 mb-2">Periode yang akan dibayar</p>
                            <div class="flex flex-wrap gap-2">
                                <span v-for="p in generatedPeriods" :key="p" class="px-2 py-1 rounded-lg text-[11px] font-bold uppercase tracking-widest bg-indigo-50 text-indigo-600 border border-indigo-200">
                                    {{ p }}
                                </span>
                            </div>
                        </div>
                    </div>

                        <!-- Payment Period (for recurring contributions) -->
                        <div v-if="form.payment_period" class="bg-indigo-50 border-2 border-indigo-100 rounded-xl p-4">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <div class="flex-1">
                                    <p class="text-[10px] uppercase font-black text-indigo-400 mb-1">Periode Pembayaran</p>
                                    <p class="text-sm font-black text-indigo-900">{{ form.payment_period }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <InputLabel value="Catatan Tambahan (Opsional)" class="text-xs font-bold uppercase text-gray-500 mb-3" />
                            <textarea v-model="form.notes" rows="2" 
                                placeholder="Misal: Pembayaran lewat transfer BCA..."
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm font-medium text-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition resize-none"></textarea>
                        </div>

                        <!-- File Upload -->
                        <div>
                            <InputLabel value="Lampirkan Bukti Foto/Struk" class="text-xs font-bold uppercase text-indigo-600 mb-3" />
                            <div class="relative border-2 border-dashed border-gray-200 hover:border-indigo-400 rounded-xl p-6 text-center transition cursor-pointer group" @click="$refs.receiptInput.click()">
                                <input ref="receiptInput" type="file" @input="form.receipt = $event.target.files[0]" accept="image/*" class="hidden" />
                                <div class="space-y-2">
                                    <svg class="mx-auto h-10 w-10 text-gray-400 group-hover:text-indigo-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <div class="text-sm">
                                        <span v-if="!form.receipt" class="font-bold text-indigo-600 group-hover:text-indigo-700">BROWSE...</span>
                                        <span v-else class="font-bold text-green-600">{{ form.receipt.name }}</span>
                                    </div>
                                    <p v-if="!form.receipt" class="text-xs text-gray-400">* Maksimal ukuran file 2MB (format gambar).</p>
                                </div>
                            </div>
                            <InputError class="mt-2" :message="form.errors.receipt" />
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-4 pt-4 border-t border-gray-100">
                            <button type="button" @click="showCreateModal = false" 
                                class="flex-1 py-3.5 rounded-xl text-sm font-black uppercase tracking-widest text-gray-500 hover:bg-gray-100 transition">
                                Batal
                            </button>
                            <button type="submit" 
                                :disabled="form.processing"
                                class="flex-[2] py-3.5 rounded-xl text-sm font-black uppercase tracking-widest text-white shadow-lg transition transform active:scale-95 flex items-center justify-center gap-2"
                                :class="form.processing ? 'bg-indigo-400 cursor-wait' : 'bg-gray-900 hover:bg-gray-800 shadow-gray-300'">
                                <span v-if="form.processing">Menyimpan...</span>
                                <span v-else>Konfirmasi & Kirim</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Modal>

        <!-- Modal Verifikasi -->
        <Modal :show="showVerifyModal" @close="showVerifyModal = false">
            <div class="p-8">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-xl font-black text-gray-900 uppercase tracking-tight">Verifikasi Pembayaran</h2>
                    <button @click="showVerifyModal = false" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div v-if="selectedContribution" class="mb-8 p-6 bg-indigo-50 rounded-2xl border border-indigo-100">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-[10px] uppercase font-black text-indigo-300 mb-1">Penyetor</p>
                            <p class="text-sm font-black text-indigo-900">{{ selectedContribution.member?.full_name || 'Anggota Terhapus' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-black text-indigo-300 mb-1">Nominal</p>
                            <p class="text-sm font-black text-indigo-900">{{ formatCurrency(selectedContribution.amount) }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-[10px] uppercase font-black text-indigo-300 mb-1">Peruntukan</p>
                            <p class="text-sm font-black text-indigo-900">{{ selectedContribution.type.name }}</p>
                        </div>
                    </div>
                    <div v-if="selectedContribution.notes" class="mt-4 pt-4 border-t border-indigo-100">
                        <p class="text-[10px] uppercase font-black text-indigo-300 mb-1">Catatan</p>
                        <p class="text-xs italic text-indigo-600">"{{ selectedContribution.notes }}"</p>
                    </div>
                </div>

                <form @submit.prevent="submitVerify" class="space-y-8 text-center">
                    <div>
                        <InputLabel value="Tentukan Status Pembayaran" class="text-[10px] font-black uppercase text-gray-400 mb-6" />
                        <div class="flex gap-4">
                            <button type="button" @click="verifyForm.status = 'paid'" class="flex-1 py-5 rounded-2xl border-4 font-black transition text-center text-xs tracking-widest" :class="verifyForm.status === 'paid' ? 'border-green-500 bg-green-50 text-green-700 shadow-xl shadow-green-100' : 'border-gray-50 text-gray-300 hover:bg-gray-50'">
                                TERIMA & VALIDASI
                            </button>
                            <button type="button" @click="verifyForm.status = 'rejected'" class="flex-1 py-5 rounded-2xl border-4 font-black transition text-center text-xs tracking-widest" :class="verifyForm.status === 'rejected' ? 'border-red-500 bg-red-50 text-red-700 shadow-xl shadow-red-100' : 'border-gray-50 text-gray-300 hover:bg-gray-50'">
                                TOLAK PEMBAYARAN
                            </button>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 font-bold pt-4 border-t border-gray-50">
                        <SecondaryButton @click="showVerifyModal = false" class="px-8 py-3 rounded-xl border-0"> Batal </SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': verifyForm.processing }" :disabled="verifyForm.processing" class="px-10 py-4 rounded-2xl shadow-xl shadow-indigo-100 uppercase tracking-widest text-[10px] font-black">
                            Simpan Perubahan
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
        
        <!-- Modal Edit -->
        <Modal :show="showEditModal" @close="showEditModal = false">
            <div class="p-8">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-xl font-black text-gray-900 uppercase tracking-tight">Ubah Data Iuran</h2>
                    <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div v-if="selectedContribution" class="mb-6 grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-[10px] uppercase font-black text-gray-400 mb-1">Anggota</p>
                        <p class="text-sm font-black text-gray-900">{{ selectedContribution.member?.full_name || '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-black text-gray-400 mb-1">Jenis Iuran</p>
                        <p class="text-sm font-black text-gray-900">{{ selectedContribution.type?.name || '-' }}</p>
                    </div>
                </div>

                <form @submit.prevent="submitEdit" class="space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Tanggal Bayar" class="text-xs font-bold uppercase text-gray-500 mb-3" />
                            <input type="date" v-model="editForm.payment_date" @change="onEditPaymentDateChange" required
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm font-bold text-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" />
                        </div>
                        <div>
                            <InputLabel value="Nominal (Rp)" class="text-xs font-bold uppercase text-gray-500 mb-3" />
                            <input type="number" v-model.number="editForm.amount" required step="0.01"
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm font-black text-indigo-600 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Metode Pembayaran" class="text-xs font-bold uppercase text-gray-500 mb-3" />
                            <select v-model="editForm.payment_method" required
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm font-bold text-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition bg-white">
                                <option value="transfer">Transfer</option>
                                <option value="cash">Cash</option>
                            </select>
                        </div>
                        <div v-if="editForm.payment_period">
                            <InputLabel value="Periode Pembayaran" class="text-xs font-bold uppercase text-gray-500 mb-3" />
                            <div class="px-4 py-3 border-2 border-indigo-100 rounded-xl text-sm font-black text-indigo-900 bg-indigo-50">
                                {{ editForm.payment_period }}
                            </div>
                        </div>
                    </div>

                    <div>
                        <InputLabel value="Catatan" class="text-xs font-bold uppercase text-gray-500 mb-3" />
                        <textarea v-model="editForm.notes" rows="2"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-sm font-medium text-gray-700 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition resize-none"></textarea>
                    </div>

                    <div>
                        <InputLabel value="Perbarui Bukti Bayar (Opsional)" class="text-xs font-bold uppercase text-indigo-600 mb-3" />
                        <div class="relative border-2 border-dashed border-gray-200 hover:border-indigo-400 rounded-xl p-6 text-center transition cursor-pointer group" @click="$refs.editReceiptInput.click()">
                            <input ref="editReceiptInput" type="file" @input="editForm.receipt = $event.target.files[0]" accept="image/*" class="hidden" />
                            <div class="space-y-2">
                                <svg class="mx-auto h-10 w-10 text-gray-400 group-hover:text-indigo-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <div class="text-sm">
                                    <span v-if="!editForm.receipt" class="font-bold text-indigo-600 group-hover:text-indigo-700">BROWSE...</span>
                                    <span v-else class="font-bold text-green-600">{{ editForm.receipt.name }}</span>
                                </div>
                                <p v-if="!editForm.receipt" class="text-xs text-gray-400">* Maksimal ukuran file 2MB (format gambar).</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 font-bold pt-4 border-t border-gray-50">
                        <SecondaryButton @click="showEditModal = false" class="px-8 py-3 rounded-xl border-0"> Batal </SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25': editForm.processing }" :disabled="editForm.processing" class="px-10 py-4 rounded-2xl shadow-xl shadow-indigo-100 uppercase tracking-widest text-[10px] font-black">
                            Simpan Perubahan
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <MemberContributionDetailModal 
            :show="showMemberDetailModal" 
            :type="selectedTypeForDetail" 
            @close="showMemberDetailModal = false"
            @success="showMemberDetailModal = false"
        />
    </AuthenticatedLayout>
</template>
