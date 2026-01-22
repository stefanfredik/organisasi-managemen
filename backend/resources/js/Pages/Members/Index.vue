<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SearchBar from '@/Components/SearchBar.vue';
import FilterDropdown from '@/Components/FilterDropdown.vue';
import Pagination from '@/Components/Pagination.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';

const props = defineProps({
    members: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const gender = ref(props.filters.gender || '');
const hasEmail = ref(props.filters.has_email || '');
const hasPhone = ref(props.filters.has_phone || '');
const bpjsHealth = ref(props.filters.bpjs_health || '');
const bpjsEmployment = ref(props.filters.bpjs_employment || '');
const joinStart = ref(props.filters.join_start || '');
const joinEnd = ref(props.filters.join_end || '');
const hasUser = ref(props.filters.has_user || '');
const occupation = ref(props.filters.occupation || '');
const sortBy = ref(props.filters.sort_by || 'created_at');
const sortDir = ref(props.filters.sort_dir || 'desc');
const showFilters = ref(false);
const showFiltersModal = ref(false);
const showFiltersDropdown = ref(false);

const optionLabel = (opts, val, fallback = '') => {
    const f = opts.find(o => o.value === val);
    return f ? f.label : fallback;
};

const activeFilters = computed(() => {
    const items = [];
    if (status.value) {
        items.push({ key: 'status', label: `Status: ${optionLabel(statusOptions, status.value)}` });
    }
    if (gender.value) {
        items.push({ key: 'gender', label: `Gender: ${optionLabel(genderOptions, gender.value)}` });
    }
    if (hasEmail.value) {
        items.push({ key: 'has_email', label: `Email: ${optionLabel(yesNoOptions, hasEmail.value)}` });
    }
    if (hasPhone.value) {
        items.push({ key: 'has_phone', label: `Telepon: ${optionLabel(yesNoOptions, hasPhone.value)}` });
    }
    if (bpjsHealth.value) {
        items.push({ key: 'bpjs_health', label: `BPJS Kesehatan: ${optionLabel(bpjsOptions, bpjsHealth.value)}` });
    }
    if (bpjsEmployment.value) {
        items.push({ key: 'bpjs_employment', label: `BPJS Ketenagakerjaan: ${optionLabel(bpjsOptions, bpjsEmployment.value)}` });
    }
    if (joinStart.value || joinEnd.value) {
        const range = `${joinStart.value || '—'} s.d. ${joinEnd.value || '—'}`;
        items.push({ key: 'join_range', label: `Bergabung: ${range}` });
    }
    if (occupation.value) {
        items.push({ key: 'occupation', label: `Pekerjaan: ${occupation.value}` });
    }
    if (hasUser.value) {
        items.push({ key: 'has_user', label: `Akun User: ${optionLabel(yesNoOptions, hasUser.value)}` });
    }
    return items;
});

const activeFilterCount = computed(() => activeFilters.value.length);

const clearFilter = (key) => {
    if (key === 'status') status.value = '';
    else if (key === 'gender') gender.value = '';
    else if (key === 'has_email') hasEmail.value = '';
    else if (key === 'has_phone') hasPhone.value = '';
    else if (key === 'bpjs_health') bpjsHealth.value = '';
    else if (key === 'bpjs_employment') bpjsEmployment.value = '';
    else if (key === 'join_range') { joinStart.value = ''; joinEnd.value = ''; }
    else if (key === 'occupation') occupation.value = '';
    else if (key === 'has_user') hasUser.value = '';
};

const resetFilters = () => {
    status.value = '';
    gender.value = '';
    hasEmail.value = '';
    hasPhone.value = '';
    bpjsHealth.value = '';
    bpjsEmployment.value = '';
    joinStart.value = '';
    joinEnd.value = '';
    occupation.value = '';
    hasUser.value = '';
    sortBy.value = 'created_at';
    sortDir.value = 'desc';

    router.get('/members', {
        search: search.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const clearAllFilters = () => {
    resetFilters();
    showFilters = false;
};

const statusOptions = [
    { value: 'active', label: 'Aktif' },
    { value: 'inactive', label: 'Tidak Aktif' },
];
const genderOptions = [
    { value: 'male', label: 'Laki-laki' },
    { value: 'female', label: 'Perempuan' },
];
const yesNoOptions = [
    { value: 'yes', label: 'Ada' },
    { value: 'no', label: 'Tidak Ada' },
];
const bpjsOptions = [
    { value: 'yes', label: 'Aktif' },
    { value: 'no', label: 'Tidak Aktif' },
];
const sortByOptions = [
    { value: 'created_at', label: 'Dibuat' },
    { value: 'full_name', label: 'Nama' },
    { value: 'member_code', label: 'Kode Anggota' },
    { value: 'join_date', label: 'Tanggal Bergabung' },
    { value: 'status', label: 'Status' },
];
const sortDirOptions = [
    { value: 'asc', label: 'Naik' },
    { value: 'desc', label: 'Turun' },
];

// Watch for filter changes and update URL
watch(
    [search, status, gender, hasEmail, hasPhone, bpjsHealth, bpjsEmployment, joinStart, joinEnd, hasUser, occupation, sortBy, sortDir],
    ([
        newSearch,
        newStatus,
        newGender,
        newHasEmail,
        newHasPhone,
        newBpjsHealth,
        newBpjsEmployment,
        newJoinStart,
        newJoinEnd,
        newHasUser,
        newOccupation,
        newSortBy,
        newSortDir,
    ]) => {
        router.get('/members', {
            search: newSearch,
            status: newStatus,
            gender: newGender,
            has_email: newHasEmail,
            has_phone: newHasPhone,
            bpjs_health: newBpjsHealth,
            bpjs_employment: newBpjsEmployment,
            join_start: newJoinStart,
            join_end: newJoinEnd,
            has_user: newHasUser,
            occupation: newOccupation,
            sort_by: newSortBy,
            sort_dir: newSortDir,
        }, {
            preserveState: true,
            replace: true,
        });
    }
);

const deleteMember = (member) => {
    if (confirm(`Apakah Anda yakin ingin menghapus anggota ${member.full_name}?`)) {
        router.delete(`/members/${member.id}`, {
            preserveScroll: true,
        });
    }
};

const getStatusBadgeClass = (memberStatus) => {
    return memberStatus === 'active'
        ? 'bg-green-100 text-green-800'
        : 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Daftar Anggota" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Daftar Anggota
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <!-- Header with Search and Filters -->
                    <div class="p-6 border-b border-gray-200 relative">
                        <div class="flex flex-col gap-4">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div class="flex-1 w-full">
                                    <SearchBar
                                        v-model="search"
                                        placeholder="Cari nama, email, telepon, atau kode anggota..."
                                    />
                                </div>
                                <div class="flex items-center gap-3">
                                    <button
                                        type="button"
                                        class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-xs font-bold uppercase tracking-widest text-gray-700 transition duration-200 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                        @click="showFiltersDropdown = !showFiltersDropdown; showFilters = false; showFiltersModal = false"
                                        :aria-expanded="showFiltersDropdown ? 'true' : 'false'"
                                        title="Filter"
                                    >
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h18M7 10h10M10 16h4" />
                                        </svg>
                                        Filter
                                        <span v-if="activeFilterCount" class="ml-2 inline-flex items-center rounded-full bg-indigo-600 text-white text-[10px] font-bold px-2 py-0.5">{{ activeFilterCount }}</span>
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-xs font-bold uppercase tracking-widest text-gray-700 transition duration-200 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                        @click="showFiltersModal = true"
                                        title="Popup"
                                    >
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                        </svg>
                                        Popup
                                    </button>
                                    <Link
                                        v-if="hasPermission('manage_members')"
                                        href="/members/create"
                                        class="inline-flex items-center justify-center rounded-xl border border-transparent bg-indigo-600 px-4 py-3 text-xs font-bold uppercase tracking-widest text-white transition duration-200 ease-in-out hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 shadow-md shadow-indigo-100"
                                    >
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Tambah Anggota
                                    </Link>
                                </div>
                            </div>
                            <div v-if="showFiltersDropdown" class="fixed inset-0 z-40" @click="showFiltersDropdown = false"></div>
                            <div v-if="showFiltersDropdown" class="absolute right-6 top-6 z-50 w-80 rounded-xl border border-gray-200 bg-white shadow-xl overflow-hidden">
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
                                        <InputLabel value="Status" class="text-[10px] font-bold uppercase text-gray-500" />
                                        <FilterDropdown v-model="status" :options="statusOptions" label="Semua Status" />
                                    </div>
                                    <div class="space-y-2">
                                        <InputLabel value="Gender" class="text-[10px] font-bold uppercase text-gray-500" />
                                        <FilterDropdown v-model="gender" :options="genderOptions" label="Semua Gender" />
                                    </div>
                                    <div class="space-y-2">
                                        <InputLabel value="Email" class="text-[10px] font-bold uppercase text-gray-500" />
                                        <FilterDropdown v-model="hasEmail" :options="yesNoOptions" label="Email" />
                                    </div>
                                    <div class="space-y-2">
                                        <InputLabel value="Telepon" class="text-[10px] font-bold uppercase text-gray-500" />
                                        <FilterDropdown v-model="hasPhone" :options="yesNoOptions" label="Telepon" />
                                    </div>
                                    <div class="space-y-2">
                                        <InputLabel value="BPJS Kesehatan" class="text-[10px] font-bold uppercase text-gray-500" />
                                        <FilterDropdown v-model="bpjsHealth" :options="bpjsOptions" label="BPJS Kesehatan" />
                                    </div>
                                    <div class="space-y-2">
                                        <InputLabel value="BPJS Ketenagakerjaan" class="text-[10px] font-bold uppercase text-gray-500" />
                                        <FilterDropdown v-model="bpjsEmployment" :options="bpjsOptions" label="BPJS Ketenagakerjaan" />
                                    </div>
                                    <div class="space-y-2">
                                        <InputLabel value="Akun User" class="text-[10px] font-bold uppercase text-gray-500" />
                                        <FilterDropdown v-model="hasUser" :options="yesNoOptions" label="Akun User" />
                                    </div>
                                    <div class="space-y-2">
                                        <InputLabel value="Urutkan" class="text-[10px] font-bold uppercase text-gray-500" />
                                        <FilterDropdown v-model="sortBy" :options="sortByOptions" label="Urutkan" />
                                    </div>
                                    <div class="space-y-2">
                                        <InputLabel value="Arah" class="text-[10px] font-bold uppercase text-gray-500" />
                                        <FilterDropdown v-model="sortDir" :options="sortDirOptions" label="Arah" />
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
                                    :title="`Hapus ${f.label}`"
                                >
                                    <span>{{ f.label }}</span>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-1 rounded-full bg-gray-100 text-gray-700 border border-gray-200 px-3 py-1 text-xs font-semibold"
                                    @click="resetFilters"
                                    title="Bersihkan semua"
                                >
                                    Bersihkan semua
                                </button>
                            </div>
                            <div v-if="showFilters" id="filters-panel" class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                                <div class="grid md:grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <InputLabel value="Status" class="text-[10px] font-bold uppercase text-gray-500" />
                                        <FilterDropdown v-model="status" :options="statusOptions" label="Semua Status" />
                                    </div>
                                    <div class="space-y-2">
                                        <InputLabel value="Gender" class="text-[10px] font-bold uppercase text-gray-500" />
                                        <FilterDropdown v-model="gender" :options="genderOptions" label="Semua Gender" />
                                    </div>
                                    <div class="space-y-2">
                                        <InputLabel value="Email" class="text-[10px] font-bold uppercase text-gray-500" />
                                        <FilterDropdown v-model="hasEmail" :options="yesNoOptions" label="Email" />
                                    </div>
                                    <div class="space-y-2">
                                        <InputLabel value="Telepon" class="text-[10px] font-bold uppercase text-gray-500" />
                                        <FilterDropdown v-model="hasPhone" :options="yesNoOptions" label="Telepon" />
                                    </div>
                                    <div class="space-y-2">
                                        <InputLabel value="BPJS Kesehatan" class="text-[10px] font-bold uppercase text-gray-500" />
                                        <FilterDropdown v-model="bpjsHealth" :options="bpjsOptions" label="BPJS Kesehatan" />
                                    </div>
                                    <div class="space-y-2">
                                        <InputLabel value="BPJS Ketenagakerjaan" class="text-[10px] font-bold uppercase text-gray-500" />
                                        <FilterDropdown v-model="bpjsEmployment" :options="bpjsOptions" label="BPJS Ketenagakerjaan" />
                                    </div>
                                    <div class="space-y-2 md:col-span-2">
                                        <InputLabel value="Periode Bergabung" class="text-[10px] font-bold uppercase text-gray-500" />
                                        <div class="flex items-center gap-2">
                                            <input v-model="joinStart" type="date" class="block flex-1 w-full py-2 px-3 text-sm border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                                            <span class="text-xs text-gray-500">s.d.</span>
                                            <input v-model="joinEnd" type="date" class="block flex-1 w-full py-2 px-3 text-sm border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <InputLabel value="Pekerjaan" class="text-[10px] font-bold uppercase text-gray-500" />
                                        <input v-model="occupation" type="text" placeholder="Pekerjaan" class="block w-full py-2 px-3 text-sm border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                                    </div>
                                    <div class="space-y-2">
                                        <InputLabel value="Akun User" class="text-[10px] font-bold uppercase text-gray-500" />
                                        <FilterDropdown v-model="hasUser" :options="yesNoOptions" label="Akun User" />
                                    </div>
                                    <div class="space-y-2">
                                        <InputLabel value="Urutkan" class="text-[10px] font-bold uppercase text-gray-500" />
                                        <FilterDropdown v-model="sortBy" :options="sortByOptions" label="Urutkan" />
                                    </div>
                                    <div class="space-y-2">
                                        <InputLabel value="Arah" class="text-[10px] font-bold uppercase text-gray-500" />
                                        <FilterDropdown v-model="sortDir" :options="sortDirOptions" label="Arah" />
                                    </div>
                                    <div class="md:col-span-2 pt-2 flex items-center gap-2">
                                        <button
                                            type="button"
                                            class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-bold uppercase tracking-widest text-gray-700 transition duration-200 ease-in-out hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                            @click="resetFilters"
                                        >
                                            Reset
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <Modal :show="showFiltersModal" @close="showFiltersModal = false" maxWidth="2xl">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-widest">Filter Anggota</h3>
                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center rounded-md px-2 py-1 text-xs text-gray-500 hover:text-gray-700"
                                    @click="showFiltersModal = false"
                                >
                                    Tutup
                                </button>
                            </div>
                            <div class="grid md:grid-cols-2 gap-4 max-h-[60vh] overflow-y-auto pr-1">
                                <div class="space-y-2">
                                    <InputLabel value="Status" class="text-[10px] font-bold uppercase text-gray-500" />
                                    <FilterDropdown v-model="status" :options="statusOptions" label="Semua Status" />
                                </div>
                                <div class="space-y-2">
                                    <InputLabel value="Gender" class="text-[10px] font-bold uppercase text-gray-500" />
                                    <FilterDropdown v-model="gender" :options="genderOptions" label="Semua Gender" />
                                </div>
                                <div class="space-y-2">
                                    <InputLabel value="Email" class="text-[10px] font-bold uppercase text-gray-500" />
                                    <FilterDropdown v-model="hasEmail" :options="yesNoOptions" label="Email" />
                                </div>
                                <div class="space-y-2">
                                    <InputLabel value="Telepon" class="text-[10px] font-bold uppercase text-gray-500" />
                                    <FilterDropdown v-model="hasPhone" :options="yesNoOptions" label="Telepon" />
                                </div>
                                <div class="space-y-2">
                                    <InputLabel value="BPJS Kesehatan" class="text-[10px] font-bold uppercase text-gray-500" />
                                    <FilterDropdown v-model="bpjsHealth" :options="bpjsOptions" label="BPJS Kesehatan" />
                                </div>
                                <div class="space-y-2">
                                    <InputLabel value="BPJS Ketenagakerjaan" class="text-[10px] font-bold uppercase text-gray-500" />
                                    <FilterDropdown v-model="bpjsEmployment" :options="bpjsOptions" label="BPJS Ketenagakerjaan" />
                                </div>
                                <div class="space-y-2 md:col-span-2">
                                    <InputLabel value="Periode Bergabung" class="text-[10px] font-bold uppercase text-gray-500" />
                                    <div class="flex items-center gap-2">
                                        <input v-model="joinStart" type="date" class="block flex-1 w-full py-2 px-3 text-sm border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                                        <span class="text-xs text-gray-500">s.d.</span>
                                        <input v-model="joinEnd" type="date" class="block flex-1 w-full py-2 px-3 text-sm border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <InputLabel value="Pekerjaan" class="text-[10px] font-bold uppercase text-gray-500" />
                                    <input v-model="occupation" type="text" placeholder="Pekerjaan" class="block w-full py-2 px-3 text-sm border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                                </div>
                                <div class="space-y-2">
                                    <InputLabel value="Akun User" class="text-[10px] font-bold uppercase text-gray-500" />
                                    <FilterDropdown v-model="hasUser" :options="yesNoOptions" label="Akun User" />
                                </div>
                                <div class="space-y-2">
                                    <InputLabel value="Urutkan" class="text-[10px] font-bold uppercase text-gray-500" />
                                    <FilterDropdown v-model="sortBy" :options="sortByOptions" label="Urutkan" />
                                </div>
                                <div class="space-y-2">
                                    <InputLabel value="Arah" class="text-[10px] font-bold uppercase text-gray-500" />
                                    <FilterDropdown v-model="sortDir" :options="sortDirOptions" label="Arah" />
                                </div>
                                <div class="md:col-span-2 pt-2 flex items-center gap-2">
                                    <button
                                        type="button"
                                        class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-bold uppercase tracking-widest text-gray-700 transition duration-200 ease-in-out hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                        @click="resetFilters"
                                    >
                                        Reset
                                    </button>
                                    <button
                                        type="button"
                                        class="inline-flex items-center justify-center rounded-xl border border-indigo-600 bg-indigo-600 px-3 py-2 text-xs font-bold uppercase tracking-widest text-white transition duration-200 ease-in-out hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                        @click="showFiltersModal = false"
                                    >
                                        Terapkan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </Modal>

                    <!-- Members Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">
                                        Foto
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">
                                        Kode
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">
                                        Nama
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider hidden md:table-cell">
                                        Email
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider hidden md:table-cell">
                                        Telepon
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">
                                        Tanggal Bergabung
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-400 uppercase tracking-wider">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-if="members.data.length === 0">
                                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                        Tidak ada data anggota
                                    </td>
                                </tr>
                                <tr v-for="member in members.data" :key="member.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <img
                                            v-if="member.photo_url"
                                            :src="member.photo_url"
                                            :alt="member.full_name"
                                            class="h-10 w-10 rounded-full object-cover"
                                        />
                                        <div
                                            v-else
                                            class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center"
                                        >
                                            <span class="text-gray-500 font-medium text-sm">
                                                {{ member.full_name.charAt(0).toUpperCase() }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ member.member_code }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ member.full_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden md:table-cell">
                                        {{ member.email || '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden md:table-cell">
                                        {{ member.phone || '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ new Date(member.join_date).toLocaleDateString('id-ID') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full"
                                            :class="getStatusBadgeClass(member.status)">
                                            {{ member.status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            <Link
                                                :href="`/members/${member.id}`"
                                                class="text-indigo-600 hover:text-indigo-900 p-1 rounded-full hover:bg-indigo-50"
                                                title="Lihat"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                            </Link>
                                            <Link
                                                v-if="hasPermission('manage_members')"
                                                :href="`/members/${member.id}/edit`"
                                                class="text-yellow-600 hover:text-yellow-900 p-1 rounded-full hover:bg-yellow-50"
                                                title="Edit"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                            </Link>
                                            <button
                                                v-if="hasPermission('manage_members')"
                                                type="button"
                                                class="text-red-600 hover:text-red-900 p-1 rounded-full hover:bg-red-50"
                                                title="Hapus"
                                                @click="deleteMember(member)"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t border-gray-200">
                        <Pagination :links="members.links" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
