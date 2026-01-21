<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';
import DataTable from '@/Components/DataTable.vue';

const props = defineProps({
    logs: Object,
    filters: Object,
});

const columns = [
    { key: 'created_at', label: 'Waktu', format: (val) => new Date(val).toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) },
    { key: 'user', label: 'User' },
    { key: 'action', label: 'Aktivitas' },
    { key: 'description', label: 'Deskripsi' },
    { key: 'ip_address', label: 'IP Address', cellClass: 'font-mono text-xs text-slate-400' }
];

const search = ref(props.filters.search);

watch(search, debounce((value) => {
    router.get(route('activity-logs.index'), {
        search: value,
    }, {
        preserveState: true,
        replace: true,
    });
}, 300));

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getActivityColor = (activity) => {
    activity = activity.toLowerCase();
    if (activity.includes('create') || activity.includes('tambah')) return 'text-emerald-600 bg-emerald-50 border-emerald-100';
    if (activity.includes('update') || activity.includes('ubah') || activity.includes('edit')) return 'text-amber-600 bg-amber-50 border-amber-100';
    if (activity.includes('delete') || activity.includes('hapus')) return 'text-rose-600 bg-rose-50 border-rose-100';
    if (activity.includes('login')) return 'text-indigo-600 bg-indigo-50 border-indigo-100';
    return 'text-slate-600 bg-slate-50 border-slate-100';
};
</script>

<template>
    <Head title="Log Aktivitas Sistem" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Log Aktivitas</h2>
                <p class="text-slate-500 text-sm font-medium mt-1">Riwayat seluruh aktivitas yang terjadi dalam sistem.</p>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Filters -->
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
                    <div class="relative max-w-md">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari aktivitas, deskripsi, atau user..."
                            class="w-full bg-slate-50 border-slate-200 rounded-2xl py-3 pl-11 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all font-medium"
                        >
                        <svg class="absolute left-4 top-3.5 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <!-- DataTable -->
                <DataTable
                    :data="logs"
                    :columns="columns"
                    :searchable="false"
                    :actions="false"
                >
                    <template #cell-user="{ row: log }">
                        <div class="flex items-center gap-3" v-if="log.user">
                            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-black text-slate-500 uppercase">
                                {{ log.user.name.charAt(0) }}
                            </div>
                            <div>
                                <div class="text-sm font-bold text-slate-900 leading-none mb-1">{{ log.user.name }}</div>
                                <div class="text-[10px] text-slate-400 font-black uppercase tracking-widest">{{ log.user.role }}</div>
                            </div>
                        </div>
                        <span v-else class="text-slate-400 italic text-sm">System</span>
                    </template>

                    <template #cell-action="{ row: log }">
                        <span
                            class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest border"
                            :class="getActivityColor(log.action)"
                        >
                            {{ log.action }}
                        </span>
                    </template>

                    <template #cell-description="{ row: log }">
                        <p class="text-sm text-slate-600 font-medium max-w-xs truncate" :title="log.description">
                            {{ log.description }}
                        </p>
                    </template>
                </DataTable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
