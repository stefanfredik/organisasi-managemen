<script setup>
import { ref, watch } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import SearchBar from "@/Components/SearchBar.vue";
import FilterDropdown from "@/Components/FilterDropdown.vue";

const props = defineProps({
    visionMissions: Object,
    filters: Object,
});

const search = ref(props.filters.search || "");
const status = ref(props.filters.status || "");

const statusOptions = [
    { value: "active", label: "Aktif" },
    { value: "inactive", label: "Tidak Aktif" },
];

watch([search, status], ([newSearch, newStatus]) => {
    router.get(
        route("vision-missions.index"),
        {
            search: newSearch,
            status: newStatus,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
});

const getStatusBadge = (status) => {
    return status === "active"
        ? "bg-green-100 text-green-800"
        : "bg-gray-100 text-gray-800";
};

const getStatusLabel = (status) => {
    return status === "active" ? "Aktif" : "Tidak Aktif";
};
</script>

<template>
    <Head title="Manajemen Visi & Misi" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Manajemen Visi & Misi
                </h2>
                <Link
                    v-if="hasPermission('manage_vision_missions')"
                    :href="route('vision-missions.create')"
                    class="inline-flex items-center justify-center rounded-xl border border-transparent bg-indigo-600 px-4 py-3 text-xs font-bold uppercase tracking-widest text-white transition duration-200 ease-in-out hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 shadow-md shadow-indigo-100"
                >
                    <svg
                        class="w-4 h-4 mr-1"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 4v16m8-8H4"
                        />
                    </svg>
                    Tambah Visi & Misi
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <!-- Filters -->
                        <div
                            class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0 md:space-x-4 mb-6"
                        >
                            <div class="flex-1 max-w-md">
                                <SearchBar
                                    v-model="search"
                                    placeholder="Cari visi..."
                                />
                            </div>
                            <div class="flex items-center space-x-2">
                                <FilterDropdown
                                    v-model="status"
                                    :options="statusOptions"
                                    label="Status"
                                />
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            Periode
                                        </th>
                                        <th
                                            scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            Visi
                                        </th>
                                        <th
                                            scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            Status
                                        </th>
                                        <th
                                            scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            Dibuat Oleh
                                        </th>
                                        <th
                                            scope="col"
                                            class="relative px-6 py-3"
                                        >
                                            <span class="sr-only">Aksi</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="bg-white divide-y divide-gray-200"
                                >
                                    <tr
                                        v-for="item in visionMissions.data"
                                        :key="item.id"
                                        class="hover:bg-gray-50"
                                    >
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                        >
                                            {{ item.period_start }} - {{ item.period_end || 'Sekarang' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            <div class="line-clamp-2">
                                                {{ item.vision }}
                                            </div>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap"
                                        >
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                :class="getStatusBadge(item.status)"
                                            >
                                                {{
                                                    getStatusLabel(item.status)
                                                }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                        >
                                            {{ item.creator?.name }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
                                        >
                                            <div
                                                class="flex justify-end space-x-2"
                                            >
                                                <Link
                                                    :href="route('vision-missions.show', item.id)"
                                                    class="text-blue-600 hover:text-blue-900"
                                                >
                                                    Detail
                                                </Link>
                                                <Link
                                                    v-if="hasPermission('manage_vision_missions')"
                                                    :href="route('vision-missions.edit', item.id)"
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    Edit
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="visionMissions.data.length === 0">
                                        <td
                                            colspan="5"
                                            class="px-6 py-4 text-center text-sm text-gray-500"
                                        >
                                            Tidak ada data visi & misi.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4" v-if="visionMissions.links">
                             <div class="flex items-center justify-between">
                                <div class="flex-1 flex justify-between sm:hidden">
                                </div>
                                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm text-gray-700">
                                            Menampilkan
                                            <span class="font-medium">{{ visionMissions.from }}</span>
                                            sampai
                                            <span class="font-medium">{{ visionMissions.to }}</span>
                                            dari
                                            <span class="font-medium">{{ visionMissions.total }}</span>
                                            hasil
                                        </p>
                                    </div>
                                    <div>
                                         <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                            <Link
                                                v-for="(link, key) in visionMissions.links"
                                                :key="key"
                                                :href="link.url ?? '#'"
                                                v-html="link.label"
                                                class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                                                :class="{
                                                    'z-10 bg-indigo-50 border-indigo-500 text-indigo-600': link.active,
                                                    'bg-white border-gray-300 text-gray-500 hover:bg-gray-50': !link.active,
                                                    'cursor-not-allowed opacity-50': !link.url
                                                }"
                                                :preserve-scroll="true"
                                            />
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
