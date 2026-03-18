<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { ArrowLeft, Pencil, Calendar, User, FileText, Download, Users, Trash2 } from "lucide-vue-next";

const props = defineProps({
    record: Object,
    participant_names: Array,
});

const deleteMeetingMinute = () => {
    if (confirm('Apakah Anda yakin ingin menghapus notulensi rapat ini?')) {
        router.delete(route('meeting-minutes.destroy', props.record.id));
    }
};

const formatDate = (dateString) => {
    if (!dateString) return "-";
    const date = new Date(dateString);
    return date.toLocaleDateString("id-ID", {
        weekday: "long", day: "numeric", month: "long", year: "numeric",
    });
};

const formatFileSize = (bytes) => {
    if (!bytes) return "N/A";
    const k = 1024;
    const sizes = ["Bytes", "KB", "MB", "GB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + " " + sizes[i];
};
</script>

<template>
    <Head :title="`Notulensi: ${props.record.agenda}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Button variant="ghost" size="icon" as-child>
                        <Link :href="route('meeting-minutes.index')">
                            <ArrowLeft class="w-5 h-5" />
                        </Link>
                    </Button>
                    <h2 class="text-xl font-semibold text-foreground">Notulensi Rapat</h2>
                </div>
                <div class="flex gap-2">
                    <Link
                        v-if="hasPermission('manage_meeting_minutes') && props.record?.id"
                        :href="route('meeting-minutes.edit', props.record.id)"
                        class="inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary/90 active:bg-primary/80 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                    </Link>
                    <button
                        v-if="hasPermission('manage_meeting_minutes') && props.record?.id"
                        @click="deleteMeetingMinute"
                        class="inline-flex items-center px-4 py-2 bg-destructive border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-destructive/90 active:bg-danger-900 focus:outline-none focus:ring-2 focus:ring-danger-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h1 class="text-2xl font-bold text-foreground mb-2">{{ props.record.agenda }}</h1>
                                <div class="flex items-center text-sm text-muted-foreground">
                                    <Calendar class="w-4 h-4 mr-1.5" />
                                    {{ formatDate(props.record.meeting_date) }}
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center pt-4 border-t">
                            <User class="w-5 h-5 text-muted-foreground mr-2" />
                            <div>
                                <p class="text-xs text-muted-foreground">Dibuat Oleh</p>
                                <p class="text-sm font-medium text-foreground">{{ props.record.creator?.name || "-" }}</p>
                                <p class="text-xs text-muted-foreground">Pada {{ formatDate(props.record.created_at) }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-6">
                        <div v-if="props.record.notes" class="prose max-w-none text-foreground" v-html="props.record.notes"></div>
                        <p v-else class="text-muted-foreground text-sm italic text-center py-8">Tidak ada catatan hasil rapat</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-lg">Lampiran</CardTitle>
                            <span class="text-sm text-muted-foreground">{{ props.record.attachments?.length || 0 }}</span>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div v-if="props.record.attachments?.length > 0" class="space-y-2">
                            <a
                                v-for="att in props.record.attachments"
                                :key="att.id"
                                :href="route('meeting-minutes.attachments.download', att.id)"
                                class="flex items-center justify-between p-3 bg-muted rounded-lg hover:bg-muted/80 transition-colors group"
                            >
                                <div class="flex items-center min-w-0 flex-1">
                                    <FileText class="w-5 h-5 text-muted-foreground mr-3 shrink-0" />
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-foreground truncate">{{ att.original_name }}</p>
                                        <p class="text-xs text-muted-foreground">{{ formatFileSize(att.size) }}</p>
                                    </div>
                                </div>
                                <Download class="w-5 h-5 text-muted-foreground group-hover:text-primary ml-3 shrink-0" />
                            </a>
                        </div>
                        <div v-else class="text-center py-8">
                            <FileText class="mx-auto h-12 w-12 text-muted-foreground/30" />
                            <p class="mt-2 text-sm text-muted-foreground">Tidak ada lampiran</p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-lg">Peserta Rapat</CardTitle>
                            <span class="text-sm font-medium text-primary">{{ props.participant_names?.length || 0 }} Orang</span>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div v-if="props.participant_names?.length > 0" class="flex flex-wrap gap-2">
                            <Badge v-for="(n, i) in props.participant_names" :key="i" variant="secondary">{{ n }}</Badge>
                        </div>
                        <p v-else class="text-sm text-muted-foreground italic text-center py-4">Tidak ada peserta</p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
