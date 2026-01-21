<script setup>
import { ref, watch } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import SearchBar from "@/Components/SearchBar.vue";

const props = defineProps({
    minutes: Object,
    filters: Object,
});

const search = ref(props.filters?.search || "");

watch(search, (s) => {
    router.get(route("meeting-minutes.index"), { search: s }, { preserveState: true, replace: true });
});
</script>

<template>
    <Head title="Notulensi Rapat" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Notulensi Rapat</h2>
                <Link
                    :href="route('meeting-minutes.create')"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Notulensi
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0 md:space-x-4 mb-6">
                            <div class="flex-1 max-w-md">
                                <SearchBar v-model="search" placeholder="Cari agenda rapat..." />
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Agenda</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Rapat</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Peserta</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dibuat Oleh</th>
                                        <th class="relative px-6 py-3"><span class="sr-only">Aksi</span></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="m in minutes.data" :key="m.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ m.agenda }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ m.meeting_date || '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span v-if="Array.isArray(m.participants) && m.participants.length > 0">{{ m.participants.length }} peserta</span>
                                            <span v-else>-</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ m.creator?.name || '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end space-x-2">
                                                <Link :href="route('meeting-minutes.show', m.id)" class="text-gray-600 hover:text-gray-900">Lihat</Link>
                                                <Link :href="route('meeting-minutes.edit', m.id)" class="text-indigo-600 hover:text-indigo-900">Edit</Link>
                                                <Link
                                                    :href="route('meeting-minutes.destroy', m.id)"
                                                    method="delete"
                                                    as="button"
                                                    class="text-red-600 hover:text-red-900"
                                                    preserve-scroll
                                                >
                                                    Hapus
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="(minutes?.data?.length || 0) === 0">
                                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada notulensi rapat.</td>
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
