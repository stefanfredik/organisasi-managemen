<script setup>
import { ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import DeleteConfirmDialog from '@/Components/DeleteConfirmDialog.vue';
import {
    DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
    ArrowLeft, Pencil, Trash2, CalendarDays, UserCircle, Users, FileText, Download, Inbox, MoreVertical,
} from "lucide-vue-next";
import { useToast } from '@/composables/useToast';

const toast = useToast();

const props = defineProps({
    record: Object,
    participant_names: Array,
});

const formatDate = (dateString) => {
    if (!dateString) return "-";
    return new Date(dateString).toLocaleDateString("id-ID", {
        weekday: "long", day: "numeric", month: "long", year: "numeric",
    });
};

const formatDateShort = (dateString) => {
    if (!dateString) return "-";
    return new Date(dateString).toLocaleDateString("id-ID", {
        day: "numeric", month: "short", year: "numeric",
    });
};

const formatFileSize = (bytes) => {
    if (!bytes) return "N/A";
    const k = 1024;
    const sizes = ["Bytes", "KB", "MB", "GB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + " " + sizes[i];
};

const showDeleteDialog = ref(false);
const confirmDelete = () => {
    router.delete(route("meeting-minutes.destroy", props.record.id), {
        onError: (errors) => toast.error(Object.values(errors).flat().join(', ') || 'Gagal menghapus notulensi.'),
        onFinish: () => (showDeleteDialog.value = false),
    });
};
</script>

<template>
    <Head :title="`Notulensi: ${record.agenda}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <Button variant="ghost" size="icon" class="h-8 w-8 shrink-0" as-child>
                        <Link :href="route('meeting-minutes.index')">
                            <ArrowLeft class="w-4 h-4" />
                        </Link>
                    </Button>
                    <h2 class="text-lg font-semibold leading-tight text-foreground truncate">Notulensi Rapat</h2>
                </div>
                <DropdownMenu v-if="hasPermission('manage_meeting_minutes') && record?.id">
                    <DropdownMenuTrigger as-child>
                        <Button variant="ghost" size="icon" class="h-8 w-8 shrink-0">
                            <MoreVertical class="w-4 h-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuItem as-child>
                            <Link :href="route('meeting-minutes.edit', record.id)" class="flex items-center gap-2">
                                <Pencil class="w-4 h-4" /> Edit
                            </Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem class="text-destructive focus:text-destructive flex items-center gap-2" @click="showDeleteDialog = true">
                            <Trash2 class="w-4 h-4" /> Hapus
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </template>

        <div class="py-3 sm:py-6">
            <div class="max-w-4xl mx-auto px-3 sm:px-6 lg:px-8 space-y-3 sm:space-y-4">

                <!-- Main Info Card -->
                <div class="bg-card rounded-xl border overflow-hidden">
                    <div class="h-1 w-full bg-primary" />
                    <div class="p-4 sm:p-6">
                        <h1 class="text-base sm:text-xl font-bold text-foreground mb-3">{{ record.agenda }}</h1>

                        <div class="flex flex-wrap gap-x-4 gap-y-1.5 text-[11px] sm:text-xs text-muted-foreground">
                            <div class="flex items-center gap-1.5">
                                <CalendarDays class="w-3.5 h-3.5 text-primary" />
                                <span>{{ formatDate(record.meeting_date) }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <UserCircle class="w-3.5 h-3.5 text-primary" />
                                <span>{{ record.creator?.name || '-' }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <Users class="w-3.5 h-3.5 text-primary" />
                                <span>{{ participant_names?.length || 0 }} peserta</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes Content -->
                <div class="bg-card rounded-xl border overflow-hidden">
                    <div class="px-4 sm:px-6 py-3 border-b">
                        <h3 class="text-xs sm:text-sm font-semibold text-foreground">Hasil Rapat</h3>
                    </div>
                    <div class="p-4 sm:p-6">
                        <div v-if="record.notes" class="prose prose-sm sm:prose max-w-none dark:prose-invert" v-html="record.notes"></div>
                        <div v-else class="py-6 text-center">
                            <p class="text-sm text-muted-foreground italic">Tidak ada catatan hasil rapat.</p>
                        </div>
                    </div>
                </div>

                <!-- Attachments & Participants Row -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4">

                    <!-- Attachments -->
                    <div class="bg-card rounded-xl border overflow-hidden">
                        <div class="px-4 sm:px-6 py-3 border-b flex items-center justify-between">
                            <h3 class="text-xs sm:text-sm font-semibold text-foreground">Lampiran</h3>
                            <span class="text-[10px] sm:text-xs text-muted-foreground">{{ record.attachments?.length || 0 }} file</span>
                        </div>
                        <div class="p-3 sm:p-4">
                            <div v-if="record.attachments?.length > 0" class="space-y-1.5">
                                <a
                                    v-for="att in record.attachments"
                                    :key="att.id"
                                    :href="route('meeting-minutes.attachments.download', att.id)"
                                    class="flex items-center gap-2.5 p-2.5 bg-muted/50 rounded-lg hover:bg-muted transition-colors group"
                                >
                                    <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                                        <FileText class="w-4 h-4 text-primary" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs sm:text-sm font-medium text-foreground truncate">{{ att.original_name }}</p>
                                        <p class="text-[10px] sm:text-xs text-muted-foreground">{{ formatFileSize(att.size) }}</p>
                                    </div>
                                    <Download class="w-4 h-4 text-muted-foreground group-hover:text-primary shrink-0 transition-colors" />
                                </a>
                            </div>
                            <div v-else class="py-6 text-center">
                                <FileText class="w-8 h-8 text-muted-foreground/30 mx-auto mb-1.5" />
                                <p class="text-xs text-muted-foreground">Tidak ada lampiran.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Participants -->
                    <div class="bg-card rounded-xl border overflow-hidden">
                        <div class="px-4 sm:px-6 py-3 border-b flex items-center justify-between">
                            <h3 class="text-xs sm:text-sm font-semibold text-foreground">Peserta Rapat</h3>
                            <span class="text-[10px] sm:text-xs font-medium text-primary">{{ participant_names?.length || 0 }} orang</span>
                        </div>
                        <div class="p-3 sm:p-4">
                            <div v-if="participant_names?.length > 0" class="flex flex-wrap gap-1.5">
                                <Badge v-for="(name, i) in participant_names" :key="i" variant="secondary" class="text-xs">
                                    {{ name }}
                                </Badge>
                            </div>
                            <div v-else class="py-6 text-center">
                                <Users class="w-8 h-8 text-muted-foreground/30 mx-auto mb-1.5" />
                                <p class="text-xs text-muted-foreground">Tidak ada peserta.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation -->
        <DeleteConfirmDialog
            :open="showDeleteDialog"
            title="Hapus Notulensi Rapat"
            :description="`Apakah Anda yakin ingin menghapus notulensi ${record.agenda}? Tindakan ini tidak dapat dibatalkan.`"
            @confirm="confirmDelete"
            @cancel="showDeleteDialog = false"
        />
    </AuthenticatedLayout>
</template>
