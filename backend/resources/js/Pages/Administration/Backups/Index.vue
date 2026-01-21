<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    backups: Array,
});

const createBackup = () => {
    router.post(route('backups.create'), {}, {
        onBefore: () => confirm('Membuat backup mungkin membutuhkan waktu beberapa saat. Lanjutkan?'),
    });
};

const deleteBackup = (fileName) => {
    if (confirm('Apakah Anda yakin ingin menghapus file backup ini?')) {
        router.delete(route('backups.destroy', fileName));
    }
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head title="Backup & Restore System" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Backup & Restore</h2>
                    <p class="text-slate-500 text-sm font-medium mt-1">Amankan data organisasi Anda dengan mencadangkan database secara rutin.</p>
                </div>
                <button
                    @click="createBackup"
                    class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-indigo-100 transition-all active:scale-95 uppercase tracking-widest"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Buat Backup Baru
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                <!-- Info Alert -->
                <div class="bg-indigo-50 border border-indigo-100 rounded-[2rem] p-8 flex items-start gap-6 shadow-sm">
                    <div class="w-14 h-14 rounded-2xl bg-white flex items-center justify-center text-indigo-600 shadow-sm shrink-0">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-indigo-900 font-black uppercase tracking-widest text-sm mb-2">Informasi Cadangan</h3>
                        <p class="text-indigo-700 text-sm leading-relaxed max-w-3xl">
                            Backup ini hanya mencakup database sistem. File fisik (gambar, dokumen) disimpan secara terpisah dalam folder storage. 
                            Disarankan untuk melakukan backup secara berkala dan mengunduh file backup ke penyimpanan eksternal yang aman.
                        </p>
                    </div>
                </div>

                <!-- Backup List Table -->
                <div class="bg-white overflow-hidden shadow-xl shadow-slate-200/50 sm:rounded-[2rem] border border-slate-100">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50 border-b border-slate-100">
                                    <th class="px-8 py-5 text-[11px] font-black uppercase tracking-[0.2em] text-slate-400">File Backup</th>
                                    <th class="px-8 py-5 text-[11px] font-black uppercase tracking-[0.2em] text-slate-400">Ukuran</th>
                                    <th class="px-8 py-5 text-[11px] font-black uppercase tracking-[0.2em] text-slate-400">Dibuat Pada</th>
                                    <th class="px-8 py-5 text-[11px] font-black uppercase tracking-[0.2em] text-slate-400 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="backup in backups" :key="backup.file_name" class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="px-8 py-5">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center text-orange-600 shadow-sm border border-orange-100">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                                </svg>
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-900 truncate max-w-xs md:max-w-md">{{ backup.file_name }}</div>
                                                <div class="text-[10px] text-slate-400 font-black uppercase tracking-widest mt-0.5">ZIP Archive</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5">
                                        <span class="text-sm font-bold text-slate-600 bg-slate-100 px-3 py-1 rounded-lg">{{ backup.file_size }}</span>
                                    </td>
                                    <td class="px-8 py-5 text-sm font-medium text-slate-500">
                                        {{ formatDate(backup.last_modified) }}
                                    </td>
                                    <td class="px-8 py-5 text-right">
                                        <div class="flex justify-end gap-3 translate-x-2 opacity-0 group-hover:opacity-100 group-hover:translate-x-0 transition-all">
                                            <a 
                                                :href="route('backups.download', backup.file_name)"
                                                class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-xl transition-colors"
                                                title="Download Backup"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                </svg>
                                            </a>
                                            <button 
                                                @click="deleteBackup(backup.file_name)"
                                                class="p-2 text-rose-500 hover:bg-rose-50 rounded-xl transition-colors"
                                                title="Hapus Backup"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="backups.length === 0">
                                    <td colspan="4" class="px-8 py-24 text-center">
                                        <div class="flex flex-col items-center">
                                            <div class="w-24 h-24 bg-slate-50 rounded-[2rem] flex items-center justify-center text-slate-200 mb-6">
                                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                                                </svg>
                                            </div>
                                            <h3 class="text-xl font-bold text-slate-900 uppercase tracking-tight">Belum Ada Backup</h3>
                                            <p class="text-slate-500 text-sm mt-2 max-w-sm">Klik tombol "Buat Backup Baru" di atas untuk mencadangkan database Anda.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
