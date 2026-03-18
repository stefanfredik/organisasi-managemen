<script setup>
import { ref, watch, computed } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import SearchBar from "@/Components/SearchBar.vue";
import FilterDropdown from "@/Components/FilterDropdown.vue";
import StructureChart from "@/Components/StructureChart.vue";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
    Sheet, SheetContent, SheetHeader, SheetTitle, SheetDescription,
} from "@/components/ui/sheet";
import {
    DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
    Plus, MoreVertical, Pencil, Trash2, SlidersHorizontal, X,
    Search, ChevronRight, ChevronDown, Network, Table2, Users,
} from "lucide-vue-next";

const props = defineProps({
    structures: Array,
    filters: Object,
    members: Array,
    canManage: Boolean,
});

const search = ref(props.filters?.search || "");
const status = ref(props.filters?.status || "");
const viewMode = ref("visual");
const filterOpen = ref(false);
const mobileViewMode = ref("list"); // 'list' | 'visual'

const statusOptions = [
    { value: "active", label: "Aktif" },
    { value: "inactive", label: "Tidak Aktif" },
];

// Filter count for badge
const activeFilterCount = computed(() => {
    let count = 0;
    if (status.value) count++;
    return count;
});

// Apply filter
const applyFilter = () => {
    router.get(
        route("organization-structures.index"),
        { search: search.value, status: status.value },
        { preserveState: true, replace: true },
    );
    filterOpen.value = false;
};

const resetFilters = () => {
    status.value = "";
    applyFilter();
};

const clearStatus = () => {
    status.value = "";
    applyFilter();
};

// Watch search for debounced filter
watch(search, (newSearch) => {
    router.get(
        route("organization-structures.index"),
        { search: newSearch, status: status.value },
        { preserveState: true, replace: true },
    );
});

// Build tree for display
const buildTree = (items) => {
    const byId = new Map();
    items.forEach((i) => byId.set(i.id, { ...i, children: [] }));
    const roots = [];
    byId.forEach((node) => {
        if (node.parent_id && byId.has(node.parent_id)) {
            byId.get(node.parent_id).children.push(node);
        } else {
            roots.push(node);
        }
    });
    const sortChildren = (n) => {
        n.children.sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0));
        n.children.forEach(sortChildren);
    };
    roots.sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0));
    roots.forEach(sortChildren);
    return roots;
};

const tree = computed(() => buildTree(props.structures || []));

// Collapse state for mobile list
const collapsed = ref({});
const toggleCollapse = (id) => {
    collapsed.value[id] = !collapsed.value[id];
};

// Flatten tree for mobile list
const flattenForList = (nodes, arr = [], level = 0) => {
    nodes.forEach((n) => {
        arr.push({ ...n, _level: level });
        if (!collapsed.value[n.id] && n.children?.length) {
            flattenForList(n.children, arr, level + 1);
        }
    });
    return arr;
};

const flatList = computed(() => flattenForList(tree.value));

// Flatten for table (desktop)
const flattenForTable = (nodes, arr = [], level = 0) => {
    nodes.forEach((n) => {
        arr.push({ ...n, _level: level });
        if (!collapsed.value[n.id] && n.children?.length) {
            flattenForTable(n.children, arr, level + 1);
        }
    });
    return arr;
};
const tableRows = computed(() => flattenForTable(tree.value));

const getStatusBadge = (isActive) =>
    isActive ? "bg-success/20 text-success-foreground" : "bg-muted text-muted-foreground";
const getStatusLabel = (isActive) => (isActive ? "Aktif" : "Tidak Aktif");

const avatarSrc = (item) => {
    if (item.photo_path) return `/storage/${item.photo_path}`;
    if (item.member?.photo_url) return item.member.photo_url;
    return null;
};

const avatarInitial = (item) =>
    (item.member?.full_name || item.position_name || "?").charAt(0).toUpperCase();

const levelColors = [
    'border-l-primary',
    'border-l-blue-500',
    'border-l-green-500',
    'border-l-amber-500',
    'border-l-purple-500',
    'border-l-pink-500',
];

const getLevelColor = (level) => levelColors[level % levelColors.length];

const confirmDelete = (id) => {
    if (confirm('Hapus posisi ini?')) {
        router.delete(route('organization-structures.destroy', id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Struktur Organisasi" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold leading-tight text-foreground">Struktur Organisasi</h2>
                <!-- Desktop add button -->
                <Button v-if="hasPermission('manage_organization_structures')" size="sm" as-child class="hidden md:inline-flex">
                    <Link :href="route('organization-structures.create')">
                        <Plus class="w-4 h-4 mr-1" />
                        Tambah Posisi
                    </Link>
                </Button>
            </div>
        </template>

        <div class="py-4 sm:py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- ========== MOBILE LAYOUT ========== -->
                <div class="md:hidden space-y-4">

                    <!-- Search + Filter -->
                    <div class="flex items-center gap-2 animate-fade-up stagger-1">
                        <div class="relative flex-1">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none" />
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Cari posisi atau anggota..."
                                class="w-full h-10 pl-9 pr-4 rounded-xl border bg-card text-sm focus:outline-none focus:ring-2 focus:ring-ring"
                            />
                        </div>
                        <button
                            @click="filterOpen = true"
                            class="relative h-10 w-10 rounded-xl border bg-card flex items-center justify-center shrink-0"
                        >
                            <SlidersHorizontal class="w-4 h-4 text-muted-foreground" />
                            <span v-if="activeFilterCount > 0" class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-primary text-primary-foreground text-[10px] font-bold flex items-center justify-center">
                                {{ activeFilterCount }}
                            </span>
                        </button>
                    </div>

                    <!-- Active filter chips -->
                    <div v-if="status" class="flex flex-wrap gap-2 animate-fade-up stagger-1">
                        <button
                            @click="clearStatus"
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-primary/10 text-primary text-xs font-medium"
                        >
                            {{ status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                            <X class="w-3 h-3" />
                        </button>
                    </div>

                    <!-- View mode toggle -->
                    <div class="flex items-center gap-2 animate-fade-up stagger-1">
                        <div class="inline-flex rounded-xl border bg-card overflow-hidden" role="group">
                            <button
                                @click="mobileViewMode = 'list'"
                                :class="['flex items-center gap-1.5 px-3 py-2 text-xs font-medium transition-colors', mobileViewMode === 'list' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted']"
                            >
                                <Table2 class="w-3.5 h-3.5" />
                                Daftar
                            </button>
                            <button
                                @click="mobileViewMode = 'visual'"
                                :class="['flex items-center gap-1.5 px-3 py-2 text-xs font-medium transition-colors', mobileViewMode === 'visual' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted']"
                            >
                                <Network class="w-3.5 h-3.5" />
                                Bagan
                            </button>
                        </div>
                        <span class="text-xs text-muted-foreground ml-auto">
                            {{ structures?.length || 0 }} posisi
                        </span>
                    </div>

                    <!-- Mobile: Card List View -->
                    <div v-if="mobileViewMode === 'list'" class="space-y-2">
                        <template v-if="flatList.length > 0">
                            <div
                                v-for="(item, idx) in flatList"
                                :key="item.id"
                                class="animate-fade-up"
                                :class="[`stagger-${Math.min(idx + 1, 6)}`]"
                            >
                                <div
                                    class="bg-card rounded-xl border border-l-[3px] overflow-hidden"
                                    :class="getLevelColor(item._level)"
                                    :style="{ marginLeft: `${item._level * 16}px` }"
                                >
                                    <div class="flex items-center gap-3 p-3">
                                        <!-- Avatar -->
                                        <div class="shrink-0">
                                            <img
                                                v-if="avatarSrc(item)"
                                                class="h-10 w-10 rounded-full object-cover ring-2 ring-background"
                                                :src="avatarSrc(item)"
                                                alt=""
                                            />
                                            <div
                                                v-else
                                                class="h-10 w-10 rounded-full bg-primary text-primary-foreground flex items-center justify-center font-bold text-sm"
                                            >
                                                {{ avatarInitial(item) }}
                                            </div>
                                        </div>

                                        <!-- Content -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <p class="text-sm font-semibold text-foreground truncate">{{ item.position_name }}</p>
                                            </div>
                                            <p class="text-xs text-muted-foreground truncate">{{ item.member?.full_name || 'Belum ditentukan' }}</p>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full" :class="getStatusBadge(item.is_active)">
                                                    {{ getStatusLabel(item.is_active) }}
                                                </span>
                                                <span class="text-[10px] text-muted-foreground">{{ item.period_start }}–{{ item.period_end || 'Sekarang' }}</span>
                                            </div>
                                        </div>

                                        <!-- Expand/Collapse -->
                                        <button
                                            v-if="item.children && item.children.length"
                                            @click="toggleCollapse(item.id)"
                                            class="p-1.5 rounded-lg hover:bg-muted text-muted-foreground shrink-0"
                                        >
                                            <ChevronDown v-if="!collapsed[item.id]" class="w-4 h-4" />
                                            <ChevronRight v-else class="w-4 h-4" />
                                        </button>

                                        <!-- Actions -->
                                        <DropdownMenu v-if="hasPermission('manage_organization_structures')">
                                            <DropdownMenuTrigger class="p-1.5 rounded-lg hover:bg-muted text-muted-foreground shrink-0">
                                                <MoreVertical class="w-4 h-4" />
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuItem as-child>
                                                    <Link :href="route('organization-structures.edit', item.id)" class="flex items-center gap-2">
                                                        <Pencil class="w-4 h-4" /> Edit
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem @click="confirmDelete(item.id)" class="text-destructive flex items-center gap-2">
                                                    <Trash2 class="w-4 h-4" /> Hapus
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Empty state -->
                        <div v-else class="flex flex-col items-center justify-center py-16 text-center">
                            <div class="w-16 h-16 rounded-2xl bg-muted flex items-center justify-center mb-4">
                                <Network class="w-8 h-8 text-muted-foreground" />
                            </div>
                            <p class="text-sm font-medium text-foreground mb-1">Belum ada data</p>
                            <p class="text-xs text-muted-foreground">Tambahkan posisi untuk membangun struktur organisasi.</p>
                        </div>
                    </div>

                    <!-- Mobile: Visual Chart View -->
                    <div v-if="mobileViewMode === 'visual'" class="bg-card rounded-xl border overflow-hidden">
                        <StructureChart
                            :items="structures"
                            :members="members"
                            :can-manage="hasPermission('manage_organization_structures')"
                        />
                    </div>

                    <!-- Mobile FAB -->
                    <Link
                        v-if="hasPermission('manage_organization_structures')"
                        :href="route('organization-structures.create')"
                        class="fixed bottom-20 right-4 z-40 w-14 h-14 rounded-full bg-primary text-primary-foreground shadow-lg flex items-center justify-center active:scale-95 transition-transform md:hidden"
                    >
                        <Plus class="w-6 h-6" />
                    </Link>
                </div>

                <!-- ========== DESKTOP LAYOUT ========== -->
                <div class="hidden md:block">
                    <div class="bg-card border rounded-xl overflow-hidden">
                        <!-- Filter Bar -->
                        <div class="p-4 border-b">
                            <div class="flex items-center gap-3">
                                <div class="flex-1">
                                    <SearchBar v-model="search" placeholder="Cari posisi atau anggota..." />
                                </div>
                                <div class="w-40">
                                    <FilterDropdown v-model="status" :options="statusOptions" label="Status" />
                                </div>
                                <div class="inline-flex rounded-lg border overflow-hidden" role="group">
                                    <button
                                        @click="viewMode = 'visual'"
                                        :class="['flex items-center gap-1.5 px-3 py-2 text-xs font-medium transition-colors', viewMode === 'visual' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted']"
                                    >
                                        <Network class="w-3.5 h-3.5" />
                                        Bagan
                                    </button>
                                    <button
                                        @click="viewMode = 'table'"
                                        :class="['flex items-center gap-1.5 px-3 py-2 text-xs font-medium transition-colors', viewMode === 'table' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted']"
                                    >
                                        <Table2 class="w-3.5 h-3.5" />
                                        Tabel
                                    </button>
                                </div>
                            </div>

                            <!-- Active filter chips -->
                            <div v-if="status" class="flex flex-wrap gap-2 mt-3">
                                <button
                                    @click="clearStatus"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-primary/10 text-primary text-xs font-medium hover:bg-primary/20 transition-colors"
                                >
                                    Status: {{ status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                                    <X class="w-3 h-3" />
                                </button>
                            </div>
                        </div>

                        <!-- Desktop: Visual Chart -->
                        <div v-if="viewMode === 'visual'">
                            <StructureChart
                                :items="structures"
                                :members="members"
                                :can-manage="hasPermission('manage_organization_structures')"
                            />
                        </div>

                        <!-- Desktop: Table View -->
                        <div v-if="viewMode === 'table'" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-border">
                                <thead class="bg-muted/50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Posisi</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Anggota</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Periode</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Induk</th>
                                        <th v-if="hasPermission('manage_organization_structures')" scope="col" class="relative px-6 py-3">
                                            <span class="sr-only">Aksi</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-card divide-y divide-border">
                                    <tr v-for="item in tableRows" :key="item.id" class="hover:bg-muted/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                                            <div :style="{ paddingLeft: `${(item._level || 0) * 20}px` }" class="flex items-center gap-2">
                                                <button
                                                    v-if="item.children && item.children.length"
                                                    @click="toggleCollapse(item.id)"
                                                    class="text-muted-foreground hover:text-foreground shrink-0"
                                                >
                                                    <ChevronDown v-if="!collapsed[item.id]" class="w-4 h-4" />
                                                    <ChevronRight v-else class="w-4 h-4" />
                                                </button>
                                                <span v-else class="w-4"></span>
                                                <div class="shrink-0">
                                                    <img
                                                        v-if="avatarSrc(item)"
                                                        class="h-8 w-8 rounded-full object-cover"
                                                        :src="avatarSrc(item)"
                                                        alt=""
                                                    />
                                                    <div v-else class="h-8 w-8 rounded-full bg-primary text-primary-foreground flex items-center justify-center font-bold text-xs">
                                                        {{ avatarInitial(item) }}
                                                    </div>
                                                </div>
                                                <span class="font-medium">{{ item.position_name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                                            {{ item.member?.full_name || '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                                            {{ item.period_start }}–{{ item.period_end || 'Sekarang' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStatusBadge(item.is_active)">
                                                {{ getStatusLabel(item.is_active) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                                            {{ item.parent?.position_name || '-' }}
                                        </td>
                                        <td v-if="hasPermission('manage_organization_structures')" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end gap-1">
                                                <Link
                                                    :href="route('organization-structures.edit', item.id)"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-medium text-muted-foreground hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-500/10 transition-colors"
                                                    title="Edit"
                                                >
                                                    <Pencil class="w-3.5 h-3.5" />
                                                    Edit
                                                </Link>
                                                <button
                                                    @click="confirmDelete(item.id)"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-medium text-muted-foreground hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors"
                                                    title="Hapus"
                                                >
                                                    <Trash2 class="w-3.5 h-3.5" />
                                                    Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="tableRows.length === 0">
                                        <td :colspan="hasPermission('manage_organization_structures') ? 6 : 5" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center">
                                                <Network class="w-8 h-8 text-muted-foreground mb-2" />
                                                <p class="text-sm text-muted-foreground">Tidak ada data struktur organisasi.</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Mobile Filter Sheet -->
        <Sheet v-model:open="filterOpen">
            <SheetContent side="bottom" class="rounded-t-2xl">
                <SheetHeader>
                    <SheetTitle>Filter</SheetTitle>
                    <SheetDescription>Filter data struktur organisasi</SheetDescription>
                </SheetHeader>
                <div class="py-4 space-y-4">
                    <div>
                        <label class="text-sm font-medium text-foreground mb-1.5 block">Status</label>
                        <select
                            v-model="status"
                            class="block w-full border-input bg-background focus:border-ring focus:ring-ring rounded-md shadow-sm text-sm h-10 px-3"
                        >
                            <option value="">Semua Status</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <Button variant="outline" class="flex-1" @click="resetFilters">Reset</Button>
                    <Button class="flex-1" @click="applyFilter">Terapkan</Button>
                </div>
            </SheetContent>
        </Sheet>
    </AuthenticatedLayout>
</template>
