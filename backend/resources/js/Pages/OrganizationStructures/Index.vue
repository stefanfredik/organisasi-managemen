<script setup>
import { ref, watch } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import SearchBar from "@/Components/SearchBar.vue";
import FilterDropdown from "@/Components/FilterDropdown.vue";

const props = defineProps({
    structures: Array,
    filters: Object,
});

const search = ref(props.filters?.search || "");
const status = ref(props.filters?.status || "");

const statusOptions = [
    { value: "active", label: "Aktif" },
    { value: "inactive", label: "Tidak Aktif" },
];

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
</script>

<template>
    <Head title="Struktur Organisasi" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Struktur Organisasi</h2>
                <Link
                    :href="route('organization-structures.create')"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
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
                            </div>
                        </div>

                        <!-- Hierarchical List (indented by level) -->
                        <div class="overflow-x-auto">
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
                                    <tr v-for="item in structures" :key="item.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div :style="{ paddingLeft: `${(item.level || 0) * 16}px` }" class="flex items-center">
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
                                                <Link :href="route('organization-structures.edit', item.id)" class="text-indigo-600 hover:text-indigo-900">Edit</Link>
                                                <Link
                                                    :href="route('organization-structures.destroy', item.id)"
                                                    method="delete"
                                                    as="button"
                                                    class="text-red-600 hover:text-red-900"
                                                    preserve-scroll
                                                    :confirm="'Hapus posisi ini?'"
                                                >
                                                    Hapus
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="structures.length === 0">
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

