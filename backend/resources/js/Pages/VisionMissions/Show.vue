<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { Pencil, Trash2 } from 'lucide-vue-next';

const props = defineProps({
    visionMission: Object,
});

const deleteVisionMission = () => {
    if (confirm('Apakah Anda yakin ingin menghapus visi & misi ini?')) {
        router.delete(route('vision-missions.destroy', props.visionMission.id));
    }
};
</script>

<template>
    <Head title="Detail Visi & Misi" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="route('vision-missions.index')" class="text-muted-foreground hover:text-foreground">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <h2 class="font-semibold text-xl text-foreground leading-tight">
                        Detail Visi & Misi
                    </h2>
                </div>
                <div class="flex gap-2">
                    <Link
                        v-if="hasPermission('manage_vision_missions')"
                        :href="route('vision-missions.edit', visionMission.id)"
                        class="inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary/90 active:bg-primary/80 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                    </Link>
                    <button
                        v-if="hasPermission('manage_vision_missions')"
                        @click="deleteVisionMission"
                        class="inline-flex items-center px-4 py-2 bg-destructive border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-destructive/90 active:bg-danger-900 focus:outline-none focus:ring-2 focus:ring-danger-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                </div>
            </div>
        </template>

        <div class="py-6 sm:py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <Card>
                    <CardContent class="p-6">
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-8">
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-muted-foreground">Periode</dt>
                                <dd class="mt-1 text-sm text-foreground">
                                    {{ visionMission.period_start }} - {{ visionMission.period_end || 'Sekarang' }}
                                </dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-muted-foreground">Status</dt>
                                <dd class="mt-1">
                                    <Badge :variant="visionMission.status === 'active' ? 'success' : 'secondary'">
                                        {{ visionMission.status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                                    </Badge>
                                </dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-muted-foreground">Dibuat Oleh</dt>
                                <dd class="mt-1 text-sm text-foreground">
                                    {{ visionMission.creator?.name }}
                                </dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-muted-foreground">Dibuat Pada</dt>
                                <dd class="mt-1 text-sm text-foreground">
                                    {{ new Date(visionMission.created_at).toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
                                </dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-muted-foreground">Visi</dt>
                                <dd class="mt-1 text-sm text-foreground whitespace-pre-wrap bg-muted p-4 rounded-md">
                                    {{ visionMission.vision }}
                                </dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-muted-foreground">Misi</dt>
                                <dd class="mt-1 text-sm text-foreground bg-muted p-4 rounded-md">
                                    <ol class="list-decimal list-inside space-y-1">
                                        <li v-for="(mission, index) in visionMission.missions" :key="index">
                                            {{ mission }}
                                        </li>
                                    </ol>
                                </dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-muted-foreground">Riwayat Perubahan</dt>
                                <dd class="mt-1 text-sm text-foreground">
                                    <div v-if="visionMission.histories && visionMission.histories.length > 0" class="bg-muted p-4 rounded-md space-y-3">
                                        <div v-for="h in visionMission.histories" :key="h.id" class="border-b pb-3 last:border-b-0">
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs font-semibold text-muted-foreground">
                                                    {{ new Date(h.created_at).toLocaleString('id-ID') }}
                                                </span>
                                                <Badge :variant="h.action === 'deleted' ? 'destructive' : 'default'" class="text-xs">
                                                    {{ h.action === 'deleted' ? 'Dihapus' : 'Diperbarui' }}
                                                </Badge>
                                            </div>
                                            <div class="mt-1 text-xs text-muted-foreground">
                                                Oleh: {{ h.user?.name || 'Sistem' }}
                                            </div>
                                            <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-3">
                                                <div v-if="h.old_data" class="bg-background border rounded p-2">
                                                    <div class="text-xs font-bold text-foreground mb-1">Sebelum</div>
                                                    <pre class="text-xs overflow-auto">{{ JSON.stringify(h.old_data, null, 2) }}</pre>
                                                </div>
                                                <div v-if="h.new_data" class="bg-background border rounded p-2">
                                                    <div class="text-xs font-bold text-foreground mb-1">Sesudah</div>
                                                    <pre class="text-xs overflow-auto">{{ JSON.stringify(h.new_data, null, 2) }}</pre>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="text-sm text-muted-foreground">
                                        Belum ada riwayat perubahan.
                                    </div>
                                </dd>
                            </div>
                        </dl>

                        <div class="mt-8 flex justify-end">
                            <Button variant="ghost" as-child>
                                <Link :href="route('vision-missions.index')">
                                    Kembali ke Daftar
                                </Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
