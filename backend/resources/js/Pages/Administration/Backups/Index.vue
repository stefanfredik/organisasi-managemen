<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteConfirmDialog from '@/Components/DeleteConfirmDialog.vue';
import { Head, router } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import axios from 'axios';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow, } from "@/components/ui/table";
import {
    Database, Download, Trash2, ShieldAlert, Loader2, Play } from 'lucide-vue-next';

const toast = useToast();

const props = defineProps({
    backups: Array,
});

const isCreating = ref(false);
const statusMessage = ref('Memeriksa status...');
let pollInterval = null;

const checkStatus = async () => {
    try {
        const response = await axios.get(route('backups.status'));
        return response.data;
    } catch (error) {
        console.error('Error polling backup status', error);
        return null;
    }
};

const startPolling = () => {
    if (pollInterval) clearInterval(pollInterval);

    pollInterval = setInterval(async () => {
        const data = await checkStatus();
        if (!data) return;

        if (data.status === 'running') {
            isCreating.value = true;
            statusMessage.value = data.message || 'Mencadangkan...';
        } else if (data.status === 'completed' || data.status === 'failed') {
            isCreating.value = false;
            clearInterval(pollInterval);
            pollInterval = null;

            if (data.status === 'completed') {
                toast.success(data.message || 'Pencadangan selesai.');
            } else {
                toast.error(data.message || 'Pencadangan gagal.');
            }

            router.reload({ only: ['backups'] });
        } else {
            // idle - stop polling
            isCreating.value = false;
            statusMessage.value = '';
            clearInterval(pollInterval);
            pollInterval = null;
        }
    }, 2000);
};

onMounted(async () => {
    // Check initial status — only start polling if a backup is already running
    const data = await checkStatus();
    if (data && data.status === 'running') {
        isCreating.value = true;
        statusMessage.value = data.message || 'Mencadangkan...';
        startPolling();
    }
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
});

const createBackup = () => {
    if (isCreating.value) return;
    
    isCreating.value = true;
    statusMessage.value = 'Memulai inisialisasi modul pencadangan...';
    
    router.post(route('backups.create'), {}, {
        preserveScroll: true,
        onSuccess: () => {
            // Flash message sudah ditangani oleh FlashMessage global
            startPolling();
        },
        onError: () => {
            toast.error('Gagal memulai backup.');
            isCreating.value = false;
        }
    });
};

const deleteTargetFile = ref(null);

const confirmDeleteBackup = () => {
    if (deleteTargetFile.value) {
        router.delete(route('backups.destroy', deleteTargetFile.value), {
            preserveScroll: true,
            onError: (errors) => toast.error(Object.values(errors).flat().join(', ') || 'Gagal menghapus backup.'),
            onFinish: () => (deleteTargetFile.value = null),
        });
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
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <Database class="w-5 h-5 text-primary" />
                    <h2 class="text-lg font-semibold leading-tight text-foreground">
                        Backup & Restore
                    </h2>
                </div>
                <Button size="sm" :disabled="isCreating" @click="createBackup" class="hidden sm:flex min-w-[160px]">
                    <Loader2 v-if="isCreating" class="w-4 h-4 mr-2 animate-spin shrink-0" />
                    <Play v-else class="w-4 h-4 mr-2 shrink-0" />
                    <span class="truncate">{{ isCreating ? statusMessage : 'Buat Backup Baru' }}</span>
                </Button>
            </div>
        </template>

        <div class="py-3 sm:py-6">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-4">

                <!-- Mobile Header Actions -->
                <div class="sm:hidden mb-4">
                    <Button class="w-full" :disabled="isCreating" @click="createBackup">
                        <Loader2 v-if="isCreating" class="w-4 h-4 mr-2 animate-spin shrink-0" />
                        <Play v-else class="w-4 h-4 mr-2 shrink-0" />
                        <span class="truncate">{{ isCreating ? statusMessage : 'Buat Backup Sekarang' }}</span>
                    </Button>
                </div>

                <!-- Info Alert -->
                <div class="bg-primary/5 border border-primary/20 rounded-xl p-4 sm:p-5 flex items-start gap-4">
                    <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary shrink-0">
                        <ShieldAlert class="w-5 h-5" />
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-foreground mb-1">Informasi Pencadangan Data</h3>
                        <p class="text-[11px] sm:text-xs text-muted-foreground leading-relaxed">
                            Backup ini hanya mencakup <b>database sistem (SQL)</b>. File media fisik seperti gambar atau dokumen disimpan secara terpisah di dalam folder storage. 
                            Gunakan secara rutin dan segera unduh file cadangan Anda menggunakan tombol unduh untuk menjaganya tetap aman di penyimpanan luring.
                        </p>
                    </div>
                </div>

                <!-- Backup List Table -->
                <div class="bg-card rounded-xl border overflow-hidden">
                    <Table>
                        <TableHeader>
                            <TableRow class="bg-muted/50 hover:bg-muted/50">
                                <TableHead class="text-[11px] uppercase tracking-wide font-medium">File Backup</TableHead>
                                <TableHead class="text-[11px] uppercase tracking-wide font-medium">Ukuran</TableHead>
                                <TableHead class="text-[11px] uppercase tracking-wide font-medium">Tanggal Dibuat</TableHead>
                                <TableHead class="text-[11px] uppercase tracking-wide font-medium text-right">Aksi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="backup in backups" :key="backup.file_name" class="hover:bg-muted/30 transition-colors">
                                <TableCell>
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-green-50 dark:bg-green-900/30 flex items-center justify-center text-green-600 dark:text-green-400 shrink-0 border border-green-100 dark:border-green-800">
                                            <Database class="w-4 h-4" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-foreground truncate max-w-[150px] sm:max-w-sm">{{ backup.file_name }}</p>
                                            <p class="text-[10px] text-muted-foreground mt-0.5">ZIP Archive</p>
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge variant="secondary" class="font-mono text-[10px] px-1.5 py-0.5">
                                        {{ backup.file_size }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <span class="text-[11px] sm:text-xs text-muted-foreground font-medium">
                                        {{ formatDate(backup.last_modified) }}
                                    </span>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-1">
                                        <Button variant="ghost" size="icon" class="h-8 w-8 text-primary hover:text-primary hover:bg-primary/10" as-child>
                                            <a :href="route('backups.download', backup.file_name)">
                                                <Download class="w-4 h-4" />
                                            </a>
                                        </Button>
                                        <Button 
                                            variant="ghost" 
                                            size="icon" 
                                            class="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10"
                                            @click="deleteTargetFile = backup.file_name"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>

                            <!-- Empty State -->
                            <TableRow v-if="backups.length === 0">
                                <TableCell colspan="4" class="h-32 text-center">
                                    <div class="flex flex-col items-center justify-center text-muted-foreground">
                                        <Database class="w-8 h-8 mb-3 opacity-20" />
                                        <p class="text-sm font-medium text-foreground">Sistem Belum Memiliki Cadangan</p>
                                        <p class="text-xs mt-1">Silakan klik "Buat Backup Baru" untuk memulai pencadangan pertama.</p>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

            </div>
        </div>

        <!-- Delete Confirmation -->
        <DeleteConfirmDialog
            :open="!!deleteTargetFile"
            title="Hapus File Backup"
            :description="`Apakah Anda yakin ingin menghapus backup ${deleteTargetFile}? Tindakan ini tidak dapat dibatalkan dan file cadangan ini akan hilang permanen.`"
            @confirm="confirmDeleteBackup"
            @cancel="deleteTargetFile = null"
        />

    </AuthenticatedLayout>
</template>
