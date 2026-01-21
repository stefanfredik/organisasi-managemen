<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    logs: Object,
    filters: Object,
});

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

                <!-- Table -->
                <div class="bg-white overflow-hidden shadow-xl shadow-slate-200/50 sm:rounded-[2rem] border border-slate-100">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50 border-b border-slate-100">
                                    <th class="px-8 py-5 text-[11px] font-black uppercase tracking-[0.2em] text-slate-400">Waktu</th>
                                    <th class="px-8 py-5 text-[11px] font-black uppercase tracking-[0.2em] text-slate-400">User</th>
                                    <th class="px-8 py-5 text-[11px] font-black uppercase tracking-[0.2em] text-slate-400">Aktivitas</th>
                                    <th class="px-8 py-5 text-[11px] font-black uppercase tracking-[0.2em] text-slate-400">Deskripsi</th>
                                    <th class="px-8 py-5 text-[11px] font-black uppercase tracking-[0.2em] text-slate-400">IP Address</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="log in logs.data" :key="log.id" class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-8 py-5 whitespace-nowrap text-sm font-bold text-slate-900">
                                        {{ formatDate(log.created_at) }}
                                    </td>
                                    <td class="px-8 py-5">
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
                                    </td>
                                    <td class="px-8 py-5">
                                        <span 
                                            class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest border"
                                            :class="getActivityColor(log.action)"
                                        >
                                            {{ log.action }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-5">
                                        <p class="text-sm text-slate-600 font-medium max-w-xs truncate" :title="log.description">
                                            {{ log.description }}
                                        </p>
                                    </td>
                                    <td class="px-8 py-5 text-xs font-mono text-slate-400">
                                        {{ log.ip_address || '-' }}
                                    </td>
                                </tr>
                                <tr v-if="logs.data.length === 0">
                                    <td colspan="5" class="px-8 py-20 text-center text-slate-400 italic">
                                        Belum ada riwayat aktivitas.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="logs.links.length > 3" class="px-8 py-6 bg-slate-50/50 border-t border-slate-100 flex justify-center">
                        <div class="flex gap-2">
                            <Link 
                                v-for="link in logs.links" 
                                :key="link.label"
                                :href="link.url || '#'"
                                class="px-4 py-2 rounded-xl text-xs font-bold transition-all border"
                                :class="link.active 
                                    ? 'bg-indigo-600 text-white border-indigo-600 shadow-lg shadow-indigo-100' 
                                    : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
