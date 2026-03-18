<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent } from "@/components/ui/card";

const props = defineProps({
    announcement: Object,
});

const deleteAnnouncement = () => {
    if (confirm('Apakah Anda yakin ingin menghapus pengumuman ini?')) {
        router.delete(route('announcements.destroy', props.announcement.id));
    }
};
</script>

<template>
    <Head :title="`Detail Pengumuman - ${announcement?.title || ''}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="route('announcements.index')" class="text-muted-foreground hover:text-foreground">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-foreground">Detail Pengumuman</h2>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('announcements.edit', announcement.id)" class="inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary/90 active:bg-primary/80 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 transition ease-in-out duration-150"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg></Link>
                    <button @click="deleteAnnouncement" class="inline-flex items-center px-4 py-2 bg-destructive border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-destructive/90 active:bg-danger-900 focus:outline-none focus:ring-2 focus:ring-danger-500 focus:ring-offset-2 transition ease-in-out duration-150"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1 1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                </div>
            </div>
        </template>
        <div class="py-6 sm:py-8">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <Card>
                    <CardContent class="p-6 space-y-4">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-foreground">{{ announcement.title }}</h3>
                                <div class="mt-1 text-xs text-muted-foreground">
                                    Dibuat oleh: {{ announcement.creator?.name || '-' }} •
                                    Status: <span class="font-semibold">{{ announcement.status === 'published' ? 'Dipublikasikan' : 'Draft' }}</span> •
                                    Tanggal Publish: {{ announcement.publish_date || '-' }}
                                </div>
                            </div>
                            <Badge :variant="announcement.status === 'published' ? 'success' : 'secondary'">
                                {{ announcement.status === 'published' ? 'Dipublikasikan' : 'Draft' }}
                            </Badge>
                        </div>
                        <div class="border-t pt-4">
                            <div class="prose max-w-none" v-html="announcement.content || ''"></div>
                        </div>
                        <div class="border-t pt-4">
                            <div class="text-sm text-muted-foreground">
                                Audiens: <span v-if="announcement.target_audience === 'all'">Semua</span>
                                <span v-else>{{ (announcement.target_roles || []).join(', ') }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
