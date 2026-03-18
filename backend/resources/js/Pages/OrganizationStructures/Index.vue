<script setup>
import { ref, watch } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import SearchBar from "@/Components/SearchBar.vue";
import FilterDropdown from "@/Components/FilterDropdown.vue";
import StructureChart from "@/Components/StructureChart.vue";
import { Button } from "@/components/ui/button";

const props = defineProps({
    structures: Array,
    filters: Object,
    members: Array,
    canManage: Boolean,
});

const search = ref(props.filters?.search || "");
const status = ref(props.filters?.status || "");
const collapsed = ref({});

const statusOptions = [
    { value: "active", label: "Aktif" },
    { value: "inactive", label: "Tidak Aktif" },
];

const viewMode = ref("visual");

watch([search, status], ([newSearch, newStatus]) => {
    router.get(
        route("organization-structures.index"),
        { search: newSearch, status: newStatus },
        { preserveState: true, replace: true },
    );
});

const getStatusBadge = (isActive) => {
    return isActive ? "bg-success/20 text-success-foreground" : "bg-muted text-foreground";
};

const getStatusLabel = (isActive) => {
    return isActive ? "Aktif" : "Tidak Aktif";
};

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

const tree = buildTree(props.structures || []);

const toggle = (id) => {
    collapsed.value[id] = !collapsed.value[id];
};

const flattenVisible = (nodes, arr = [], level = 0) => {
    nodes.forEach((n) => {
        arr.push({ ...n, _level: level });
        if (!collapsed.value[n.id] && n.children?.length) {
            flattenVisible(n.children, arr, level + 1);
        }
    });
    return arr;
};

const visibleRows = () => flattenVisible(tree);
</script>

<template>
    <Head title="Struktur Organisasi" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold leading-tight text-foreground">Struktur Organisasi</h2>
                <Button v-if="hasPermission('manage_organization_structures')" size="sm" as-child>
                    <Link :href="route('organization-structures.create')">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span class="hidden sm:inline">Tambah Posisi</span>
                    </Link>
                </Button>
            </div>
        </template>

        <div class="py-4 sm:py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-card border rounded-xl overflow-hidden">
                    <div class="p-3 sm:p-4 border-b">
                        <div class="flex flex-col sm:flex-row gap-2">
                            <div class="flex-1">
                                <SearchBar v-model="search" placeholder="Cari posisi atau anggota..." />
                            </div>
                            <div class="flex gap-2">
                                <FilterDropdown v-model="status" :options="statusOptions" label="Status" />
                                <div class="inline-flex rounded-lg border" role="group">
                                    <button type="button" @click="viewMode = 'visual'"
                                        :class="['px-3 py-1.5 text-xs font-medium rounded-l-lg', viewMode === 'visual' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted']">
                                        Visual
                                    </button>
                                    <button type="button" @click="viewMode = 'table'"
                                        :class="['px-3 py-1.5 text-xs font-medium rounded-r-lg -ml-px', viewMode === 'table' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted']">
                                        Tabel
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-if="viewMode === 'visual'" class="mb-8">
                            <StructureChart :items="structures" :members="members" :can-manage="hasPermission('manage_organization_structures')" />
                        </div>

                        <!-- Hierarchical List (indented by level) -->
                        <div v-if="viewMode === 'table'" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-border">
                                <thead class="bg-muted">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Posisi</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Anggota</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Periode</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Induk</th>
                                        <th scope="col" class="relative px-6 py-3">
                                            <span class="sr-only">Aksi</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-card divide-y divide-border">
                                    <tr v-for="item in visibleRows()" :key="item.id" class="hover:bg-muted">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                                            <div :style="{ paddingLeft: `${(item._level || 0) * 16}px` }" class="flex items-center">
                                                <button
                                                    v-if="item.children && item.children.length"
                                                    @click="toggle(item.id)"
                                                    class="mr-2 text-muted-foreground hover:text-foreground"
                                                    :aria-label="collapsed[item.id] ? 'Perluas' : 'Ciutkan'"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path v-if="collapsed[item.id]" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4l8 8-8 8M4 12h16" />
                                                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m7 7l-7-7 7-7" />
                                                    </svg>
                                                </button>
                                                <span class="inline-block w-2 h-2 rounded-full mr-2" :class="item.parent_id ? 'bg-muted-foreground' : 'bg-primary/60'"></span>
                                                <span class="font-medium">{{ item.position_name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                                            {{ item.member?.full_name || '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                                            {{ item.period_start }} - {{ item.period_end || 'Sekarang' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStatusBadge(item.is_active)">
                                                {{ getStatusLabel(item.is_active) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                                            {{ item.parent?.position_name || '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end space-x-2">
                                                <Link v-if="hasPermission('manage_organization_structures')" :href="route('organization-structures.edit', item.id)" class="text-primary hover:text-primary/80" title="Edit">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                                </Link>
                                                <button
                                                    v-if="hasPermission('manage_organization_structures')"
                                                    @click="() => {
                                                        if (confirm('Hapus posisi ini?')) {
                                                            router.delete(route('organization-structures.destroy', item.id), {
                                                                preserveScroll: true
                                                            });
                                                        }
                                                    }"
                                                    class="text-destructive hover:text-destructive/80"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1 1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="(structures?.length || 0) === 0">
                                        <td colspan="6" class="px-6 py-4 text-center text-sm text-muted-foreground">Tidak ada data struktur organisasi.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
