<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SearchBar from '@/Components/SearchBar.vue';
import FilterDropdown from '@/Components/FilterDropdown.vue';

const props = defineProps({
    members: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');

const statusOptions = [
    { value: 'active', label: 'Aktif' },
    { value: 'inactive', label: 'Tidak Aktif' },
];

// Watch for filter changes and update URL
watch([search, status], ([newSearch, newStatus]) => {
    router.get('/members', {
        search: newSearch,
        status: newStatus,
    }, {
        preserveState: true,
        replace: true,
    });
});

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
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div class="flex-1 flex gap-4">
                                <div class="flex-1 max-w-md">
                                    <SearchBar
                                        v-model="search"
                                        placeholder="Cari nama, email, telepon, atau kode anggota..."
                                    />
                                </div>
                                <FilterDropdown
                                    v-model="status"
                                    :options="statusOptions"
                                    label="Semua Status"
                                />
                            </div>
                            <Link
                                v-if="hasPermission('manage_members')"
                                href="/members/create"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Tambah Anggota
                            </Link>
                        </div>
                    </div>

                    <!-- Members Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Foto
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Kode
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nama
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Email
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Telepon
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tanggal Bergabung
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ member.email || '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ member.phone || '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ new Date(member.join_date).toLocaleDateString('id-ID') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                            :class="getStatusBadgeClass(member.status)"
                                        >
                                            {{ member.status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link
                                            :href="`/members/${member.id}`"
                                            class="text-indigo-600 hover:text-indigo-900 mr-3"
                                        >
                                            Lihat
                                        </Link>
                                        <Link
                                            v-if="hasPermission('manage_members')"
                                            :href="`/members/${member.id}/edit`"
                                            class="text-yellow-600 hover:text-yellow-900 mr-3"
                                        >
                                            Edit
                                        </Link>
                                        <button
                                            v-if="hasPermission('manage_members')"
                                            type="button"
                                            class="text-red-600 hover:text-red-900"
                                            @click="deleteMember(member)"
                                        >
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="members.links.length > 3" class="px-6 py-4 border-t border-gray-200">
                        <div class="flex flex-wrap gap-1">
                            <Link
                                v-for="(link, index) in members.links"
                                :key="index"
                                :href="link.url"
                                :class="[
                                    'px-3 py-2 text-sm rounded',
                                    link.active
                                        ? 'bg-indigo-600 text-white'
                                        : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300',
                                    !link.url ? 'opacity-50 cursor-not-allowed' : '',
                                ]"
                                :disabled="!link.url"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
