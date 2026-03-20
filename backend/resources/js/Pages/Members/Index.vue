<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetDescription,
    SheetFooter,
} from '@/components/ui/sheet';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import FilterDropdown from '@/Components/FilterDropdown.vue';
import { Plus, MoreHorizontal, MoreVertical, Eye, Pencil, Trash2, SlidersHorizontal, X, Search, Phone, ChevronRight } from 'lucide-vue-next';
import DeleteConfirmDialog from '@/Components/DeleteConfirmDialog.vue';
import { useToast } from '@/composables/useToast';

const toast = useToast();

const props = defineProps({
    members: Object,
    filters: Object,
    positions: Object,
});

// Filter state
const status = ref(props.filters?.status || '');
const gender = ref(props.filters?.gender || '');
const position = ref(props.filters?.position_id || '');
const hasEmail = ref(props.filters?.has_email || '');
const hasPhone = ref(props.filters?.has_phone || '');
const bpjsHealth = ref(props.filters?.bpjs_health || '');
const bpjsEmployment = ref(props.filters?.bpjs_employment || '');
const joinStart = ref(props.filters?.join_start || '');
const joinEnd = ref(props.filters?.join_end || '');
const hasUser = ref(props.filters?.has_user || '');
const occupation = ref(props.filters?.occupation || '');
const showFiltersSheet = ref(false);

// Filter options
const statusOptions = [
    { value: 'active', label: 'Aktif' },
    { value: 'inactive', label: 'Tidak Aktif' },
];
const genderOptions = [
    { value: 'male', label: 'Laki-laki' },
    { value: 'female', label: 'Perempuan' },
];
const positionOptions = computed(() =>
    Object.entries(props.positions || {}).map(([value, label]) => ({ value, label }))
);
const yesNoOptions = [
    { value: '1', label: 'Ya' },
    { value: '0', label: 'Tidak' },
];
const bpjsOptions = [
    { value: '1', label: 'Terdaftar' },
    { value: '0', label: 'Belum Terdaftar' },
];

// Active filters
const activeFilters = computed(() => {
    const items = [];
    const optionLabel = (opts, val) => {
        const f = opts.find(o => o.value === val);
        return f ? f.label : val;
    };
    
    if (status.value) items.push({ key: 'status', label: `Status: ${optionLabel(statusOptions, status.value)}` });
    if (gender.value) items.push({ key: 'gender', label: `Gender: ${optionLabel(genderOptions, gender.value)}` });
    if (position.value) items.push({ key: 'position', label: `Posisi: ${props.positions?.[position.value] || position.value}` });
    if (hasEmail.value) items.push({ key: 'has_email', label: `Email: ${optionLabel(yesNoOptions, hasEmail.value)}` });
    if (hasPhone.value) items.push({ key: 'has_phone', label: `Telepon: ${optionLabel(yesNoOptions, hasPhone.value)}` });
    if (bpjsHealth.value) items.push({ key: 'bpjs_health', label: `BPJS Kes: ${optionLabel(bpjsOptions, bpjsHealth.value)}` });
    if (bpjsEmployment.value) items.push({ key: 'bpjs_employment', label: `BPJS TK: ${optionLabel(bpjsOptions, bpjsEmployment.value)}` });
    if (hasUser.value) items.push({ key: 'has_user', label: `Akun: ${optionLabel(yesNoOptions, hasUser.value)}` });
    if (joinStart.value) items.push({ key: 'join_start', label: `Dari: ${joinStart.value}` });
    if (joinEnd.value) items.push({ key: 'join_end', label: `Sampai: ${joinEnd.value}` });
    if (occupation.value) items.push({ key: 'occupation', label: `Pekerjaan: ${occupation.value}` });
    return items;
});

const activeFilterCount = computed(() => activeFilters.value.length);

const clearFilter = (key) => {
    const map = { status, gender, position, has_email: hasEmail, has_phone: hasPhone, bpjs_health: bpjsHealth, bpjs_employment: bpjsEmployment, has_user: hasUser, join_start: joinStart, join_end: joinEnd, occupation };
    if (map[key]) map[key].value = '';
};

const resetFilters = () => {
    status.value = ''; gender.value = ''; position.value = ''; hasEmail.value = ''; hasPhone.value = '';
    bpjsHealth.value = ''; bpjsEmployment.value = ''; hasUser.value = '';
    joinStart.value = ''; joinEnd.value = ''; occupation.value = '';
    applyFilters();
};

const applyFilters = () => {
    router.get('/members', {
        search: new URLSearchParams(window.location.search).get('search') || '',
        status: status.value,
        gender: gender.value,
        position_id: position.value,
        has_email: hasEmail.value,
        has_phone: hasPhone.value,
        bpjs_health: bpjsHealth.value,
        bpjs_employment: bpjsEmployment.value,
        join_start: joinStart.value,
        join_end: joinEnd.value,
        has_user: hasUser.value,
        occupation: occupation.value,
    }, { preserveState: true, replace: true });
};

const deleteTarget = ref(null);
const deleteMember = (member) => {
    deleteTarget.value = member;
};
const confirmDeleteMember = () => {
    if (deleteTarget.value) {
        router.delete(`/members/${deleteTarget.value.id}`, {
            onError: (errors) => toast.error(Object.values(errors).flat().join(', ') || 'Gagal menghapus anggota.'),
            onFinish: () => (deleteTarget.value = null),
        });
    }
};

const columns = [
    { key: 'full_name', label: 'Nama', sortable: true },
    { key: 'phone', label: 'Telepon', sortable: false },
    { key: 'join_date', label: 'Bergabung', sortable: true, format: (value) => new Date(value).toLocaleDateString('id-ID') },
];

const handleAction = (action, row) => {
    if (action === 'view') router.visit(`/members/${row.id}`);
    else if (action === 'edit') router.visit(`/members/${row.id}/edit`);
    else if (action === 'delete') deleteMember(row);
};

const handleDropdownAction = (action, row) => {
    setTimeout(() => {
        handleAction(action, row);
    }, 100);
};

const formatName = (row) => {
    if (row.nickname) {
        return `${row.full_name} (${row.nickname})`;
    }
    return row.full_name;
};

const getPositionBadgeClass = (pos) => {
    switch (pos) {
        case 'ketua': return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400';
        case 'bendahara': return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400';
        case 'sekretaris': return 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400';
        case 'anggota': return 'bg-muted text-muted-foreground';
        default: return 'bg-muted text-muted-foreground';
    }
};
</script>

<template>
    <Head title="Daftar Anggota" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold leading-tight text-foreground">Daftar Anggota</h2>
                <Link v-if="hasPermission('manage_members')" href="/members/create">
                    <Button size="sm">
                        <Plus class="w-4 h-4 mr-1" />
                        <span class="hidden sm:inline">Tambah</span>
                    </Button>
                </Link>
            </div>
        </template>

        <div class="py-4 sm:py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Mobile: WhatsApp-style contact list -->
                <div class="md:hidden">
                    <!-- Mobile Search & Filter Bar -->
                    <div class="flex items-center gap-2 mb-3">
                        <div class="relative flex-1">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                            <input
                                type="text"
                                :value="filters?.search || ''"
                                placeholder="Cari anggota..."
                                class="w-full pl-9 pr-3 py-2.5 bg-card border rounded-xl text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary"
                                @input="router.get('/members', { search: $event.target.value }, { preserveState: true, replace: true })"
                            />
                        </div>
                        <Button variant="outline" size="icon" class="shrink-0 h-10 w-10" @click="showFiltersSheet = true">
                            <SlidersHorizontal class="w-4 h-4" />
                            <Badge v-if="activeFilterCount" variant="default" class="absolute -top-1 -right-1 h-4 w-4 p-0 justify-center text-[10px]">{{ activeFilterCount }}</Badge>
                        </Button>
                    </div>

                    <!-- Active filter chips mobile -->
                    <div v-if="activeFilters.length" class="flex flex-wrap gap-1.5 mb-3">
                        <button
                            v-for="f in activeFilters" :key="f.key"
                            class="inline-flex items-center gap-1 rounded-full bg-primary/10 text-primary px-2.5 py-0.5 text-xs font-medium"
                            @click="clearFilter(f.key)"
                        >
                            {{ f.label }}
                            <X class="w-3 h-3" />
                        </button>
                        <button class="text-xs text-muted-foreground px-2" @click="resetFilters">Reset</button>
                    </div>

                    <!-- Contact List -->
                    <div class="bg-card border rounded-xl overflow-hidden divide-y divide-border">
                        <div
                            v-for="member in members.data"
                            :key="member.id"
                            class="flex items-center gap-3 px-4 py-3 active:bg-muted/50 transition-colors"
                        >
                            <!-- Avatar - clickable to detail -->
                            <Link :href="`/members/${member.id}`" class="shrink-0">
                                <img
                                    v-if="member.photo_url"
                                    :src="member.photo_url"
                                    :alt="member.full_name"
                                    class="h-12 w-12 rounded-full object-cover"
                                />
                                <div v-else class="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center">
                                    <span class="text-primary font-semibold text-lg">{{ member.full_name.charAt(0).toUpperCase() }}</span>
                                </div>
                            </Link>

                            <!-- Name & Phone + Position badge - clickable to detail -->
                            <Link :href="`/members/${member.id}`" class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-foreground truncate">
                                    {{ member.full_name }}
                                    <span v-if="member.nickname" class="font-normal text-muted-foreground"> ({{ member.nickname }})</span>
                                </p>
                                <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                    <span v-if="member.position" :class="getPositionBadgeClass(member.position.code)" class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full">
                                        {{ member.position.name }}
                                    </span>
                                    <span v-if="member.phone" class="text-xs text-muted-foreground flex items-center gap-1">
                                        <Phone class="w-3 h-3" />
                                        {{ member.phone }}
                                    </span>
                                    <span v-else class="text-xs text-muted-foreground">Belum ada nomor</span>
                                </div>
                            </Link>

                            <!-- Status badge -->
                            <Badge
                                :variant="member.status === 'active' ? 'default' : 'secondary'"
                                class="shrink-0 text-[10px] px-1.5 py-0.5"
                            >
                                {{ member.status === 'active' ? 'Aktif' : 'Nonaktif' }}
                            </Badge>

                            <!-- Hamburger menu -->
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <button class="shrink-0 w-8 h-8 flex items-center justify-center rounded-full hover:bg-muted transition-colors">
                                        <MoreVertical class="w-4 h-4 text-muted-foreground" />
                                    </button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuItem @click="handleDropdownAction('view', member)">
                                        <Eye class="h-4 w-4 mr-2" />
                                        Lihat Detail
                                    </DropdownMenuItem>
                                    <DropdownMenuItem v-if="hasPermission('manage_members')" @click="handleDropdownAction('edit', member)">
                                        <Pencil class="h-4 w-4 mr-2" />
                                        Ubah
                                    </DropdownMenuItem>
                                    <DropdownMenuItem v-if="hasPermission('manage_members')" @click="handleDropdownAction('delete', member)" class="text-destructive">
                                        <Trash2 class="h-4 w-4 mr-2" />
                                        Hapus
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>

                        <!-- Empty state -->
                        <div v-if="members.data.length === 0" class="px-4 py-12 text-center">
                            <p class="text-muted-foreground text-sm">Tidak ada anggota ditemukan.</p>
                        </div>
                    </div>

                    <!-- Mobile Pagination -->
                    <div v-if="members.links?.length > 3" class="flex justify-center gap-1 mt-4">
                        <Link
                            v-for="link in members.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            class="min-w-[40px] min-h-[40px] px-3 py-2 rounded-lg text-xs font-medium flex items-center justify-center transition-colors"
                            :class="link.active
                                ? 'bg-primary text-primary-foreground'
                                : link.url
                                    ? 'bg-card border text-muted-foreground hover:bg-muted'
                                    : 'text-muted-foreground/40 pointer-events-none'"
                            v-html="link.label"
                        />
                    </div>
                </div>

                <!-- Desktop: DataTable -->
                <div class="hidden md:block bg-card border rounded-xl overflow-hidden">
                    <!-- Active filter chips -->
                    <div v-if="activeFilters.length" class="px-4 py-2 border-b flex flex-wrap gap-1.5">
                        <button
                            v-for="f in activeFilters" :key="f.key"
                            class="inline-flex items-center gap-1 rounded-full bg-primary/10 text-primary px-2.5 py-0.5 text-xs font-medium hover:bg-primary/20"
                            @click="clearFilter(f.key)"
                        >
                            {{ f.label }}
                            <X class="w-3 h-3" />
                        </button>
                        <button class="text-xs text-muted-foreground hover:text-foreground px-2" @click="resetFilters">
                            Reset
                        </button>
                    </div>

                    <!-- Table -->
                    <DataTable
                        :data="members"
                        :columns="columns"
                        :filters="filters"
                        search-route="members.index"
                        :sortable="true"
                        :striped="true"
                        @action="handleAction"
                    >
                        <template #header-actions>
                            <div class="flex items-center gap-2">
                                <Button variant="outline" size="sm" @click="showFiltersSheet = true" class="gap-1.5">
                                    <SlidersHorizontal class="w-4 h-4" />
                                    Filter
                                    <Badge v-if="activeFilterCount" variant="default" class="ml-1 h-5 w-5 p-0 justify-center text-[10px]">{{ activeFilterCount }}</Badge>
                                </Button>
                            </div>
                        </template>
                        <template #cell-full_name="{ row }">
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0">
                                    <img v-if="row.photo_url" :src="row.photo_url" :alt="row.full_name" class="h-8 w-8 rounded-full object-cover" />
                                    <div v-else class="h-8 w-8 rounded-full bg-muted flex items-center justify-center">
                                        <span class="text-muted-foreground font-medium text-xs">{{ row.full_name.charAt(0).toUpperCase() }}</span>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <span class="text-sm font-medium text-foreground">{{ row.full_name }}</span>
                                    <span v-if="row.nickname" class="block text-xs text-muted-foreground">({{ row.nickname }})</span>
                                    <span v-if="row.position" :class="getPositionBadgeClass(row.position.code)" class="inline-block mt-0.5 text-[10px] font-semibold px-1.5 py-0.5 rounded-full">
                                        {{ row.position.name }}
                                    </span>
                                </div>
                            </div>
                        </template>
                        <template #cell-phone="{ row }">
                            <span v-if="row.phone" class="text-sm">{{ row.phone }}</span>
                            <span v-else class="text-muted-foreground">-</span>
                        </template>
                        <template #actions="{ row }">
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="ghost" size="icon">
                                        <MoreHorizontal class="h-4 w-4" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuItem @click="handleDropdownAction('view', row)">
                                        <Eye class="h-4 w-4 mr-2" />
                                        Lihat
                                    </DropdownMenuItem>
                                    <DropdownMenuItem @click="handleDropdownAction('edit', row)">
                                        <Pencil class="h-4 w-4 mr-2" />
                                        Ubah
                                    </DropdownMenuItem>
                                    <DropdownMenuItem @click="handleDropdownAction('delete', row)" class="text-destructive">
                                        <Trash2 class="h-4 w-4 mr-2" />
                                        Hapus
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </template>
                    </DataTable>
                </div>
            </div>
        </div>

        <DeleteConfirmDialog
            :open="!!deleteTarget"
            title="Hapus Anggota"
            description="Apakah Anda yakin ingin menghapus anggota ini? Tindakan ini tidak dapat dibatalkan."
            @confirm="confirmDeleteMember"
            @cancel="deleteTarget = null"
        />

        <!-- Filter Sheet -->
        <Sheet :open="showFiltersSheet" @update:open="showFiltersSheet = $event">
            <SheetContent side="right" class="w-80">
                <SheetHeader>
                    <SheetTitle>Filter Anggota</SheetTitle>
                    <SheetDescription>Atur filter untuk menyaring data.</SheetDescription>
                </SheetHeader>
                <div class="flex-1 overflow-y-auto space-y-4 py-4">
                    <div class="space-y-1.5">
                        <Label class="text-xs text-muted-foreground">Status</Label>
                        <FilterDropdown v-model="status" :options="statusOptions" label="Semua" />
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs text-muted-foreground">Posisi / Jabatan</Label>
                        <FilterDropdown v-model="position" :options="positionOptions" label="Semua Posisi" />
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs text-muted-foreground">Gender</Label>
                        <FilterDropdown v-model="gender" :options="genderOptions" label="Semua" />
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs text-muted-foreground">Email</Label>
                        <FilterDropdown v-model="hasEmail" :options="yesNoOptions" label="Semua" />
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs text-muted-foreground">Telepon</Label>
                        <FilterDropdown v-model="hasPhone" :options="yesNoOptions" label="Semua" />
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs text-muted-foreground">BPJS Kesehatan</Label>
                        <FilterDropdown v-model="bpjsHealth" :options="bpjsOptions" label="Semua" />
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs text-muted-foreground">BPJS Ketenagakerjaan</Label>
                        <FilterDropdown v-model="bpjsEmployment" :options="bpjsOptions" label="Semua" />
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs text-muted-foreground">Akun User</Label>
                        <FilterDropdown v-model="hasUser" :options="yesNoOptions" label="Semua" />
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs text-muted-foreground">Periode Bergabung</Label>
                        <div class="flex gap-2">
                            <Input v-model="joinStart" type="date" class="text-xs" />
                            <Input v-model="joinEnd" type="date" class="text-xs" />
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="text-xs text-muted-foreground">Pekerjaan</Label>
                        <Input v-model="occupation" placeholder="Pekerjaan" class="text-xs" />
                    </div>
                </div>
                <SheetFooter class="gap-2 pt-4 border-t">
                    <Button variant="outline" size="sm" class="flex-1" @click="resetFilters">Reset</Button>
                    <Button size="sm" class="flex-1" @click="applyFilters; showFiltersSheet = false">Terapkan</Button>
                </SheetFooter>
            </SheetContent>
        </Sheet>
    </AuthenticatedLayout>
</template>
