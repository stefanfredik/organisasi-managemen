<script setup>
import { ref, watch } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import SearchBar from "@/Components/SearchBar.vue";
import FilterDropdown from "@/Components/FilterDropdown.vue";
import StructureChart from "@/Components/StructureChart.vue";

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
    return isActive ? "bg-green-100 text-green-800" : "bg-gray-100 text-gray-800";
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
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Struktur Organisasi</h2>
                <Link
                    v-if="hasPermission('manage_organization_structures')"
                    :href="route('organization-structures.create')"
                    class="inline-flex items-center justify-center rounded-xl border border-transparent bg-indigo-600 px-4 py-3 text-xs font-bold uppercase tracking-widest text-white transition duration-200 ease-in-out hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 shadow-md shadow-indigo-100"
                >
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Posisi
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <!-- Filters -->
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0 md:space-x-4 mb-6">
                            <div class="flex-1 max-w-md">
                                <SearchBar v-model="search" placeholder="Cari posisi atau anggota..." />
                            </div>
                            <div class="flex items-center space-x-2">
                                <FilterDropdown v-model="status" :options="statusOptions" label="Status" />
                                <div class="inline-flex rounded-md shadow-sm" role="group">
                                    <button
                                        type="button"
                                        @click="viewMode = 'visual'"
                                        :class="[
                                            'px-3 py-2 text-xs font-semibold bg-white border border-gray-200 rounded-l-md',
                                            viewMode === 'visual' ? 'text-indigo-600' : 'text-gray-600',
                                        ]"
                                    >
                                        Visual
                                    </button>
                                    <button
                                        type="button"
                                        @click="viewMode = 'table'"
                                        :class="[
                                            'px-3 py-2 text-xs font-semibold bg-white border border-gray-200 rounded-r-md -ml-px',
                                            viewMode === 'table' ? 'text-indigo-600' : 'text-gray-600',
                                        ]"
                                    >
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
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Posisi</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Anggota</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Periode</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Induk</th>
                                        <th scope="col" class="relative px-6 py-3">
                                            <span class="sr-only">Aksi</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="item in visibleRows()" :key="item.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div :style="{ paddingLeft: `${(item._level || 0) * 16}px` }" class="flex items-center">
                                                <button
                                                    v-if="item.children && item.children.length"
                                                    @click="toggle(item.id)"
                                                    class="mr-2 text-gray-500 hover:text-gray-700"
                                                    :aria-label="collapsed[item.id] ? 'Perluas' : 'Ciutkan'"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path v-if="collapsed[item.id]" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4l8 8-8 8M4 12h16" />
                                                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m7 7l-7-7 7-7" />
                                                    </svg>
                                                </button>
                                                <span class="inline-block w-2 h-2 rounded-full mr-2" :class="item.parent_id ? 'bg-gray-300' : 'bg-indigo-400'"></span>
                                                <span class="font-medium">{{ item.position_name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ item.member?.full_name || '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ item.period_start }} - {{ item.period_end || 'Sekarang' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStatusBadge(item.is_active)">
                                                {{ getStatusLabel(item.is_active) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ item.parent?.position_name || '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end space-x-2">
                                                <Link v-if="hasPermission('manage_organization_structures')" :href="route('organization-structures.edit', item.id)" class="text-indigo-600 hover:text-indigo-900" title="Edit">
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
                                                    class="text-red-600 hover:text-red-900"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1 1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="(structures?.length || 0) === 0">
                                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada data struktur organisasi.</td>
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
