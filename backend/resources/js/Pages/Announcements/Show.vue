<script setup>
import { ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Button } from "@/components/ui/button";
import DeleteConfirmDialog from '@/Components/DeleteConfirmDialog.vue';
import {
    DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
    ArrowLeft, Pencil, Trash2, CalendarDays, UserCircle, Users, Globe, MoreVertical,
} from "lucide-vue-next";
import { useToast } from '@/composables/useToast';

const toast = useToast();

const props = defineProps({
    announcement: Object,
});

const getStatusConfig = (s) => {
    return s === "published"
        ? { label: "Dipublikasikan", class: "bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400" }
        : { label: "Draft", class: "bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400" };
};

const getAudienceLabel = (a) => {
    if (!a || a.target_audience === "all") return "Semua";
    return (a.target_roles || []).map(r => r.charAt(0).toUpperCase() + r.slice(1)).join(", ");
};

const formatDate = (date) => {
    if (!date) return "-";
    return new Date(date).toLocaleDateString("id-ID", { day: "numeric", month: "long", year: "numeric" });
};

const showDeleteDialog = ref(false);
const confirmDelete = () => {
    router.delete(route("announcements.destroy", props.announcement.id), {
        onError: (errors) => toast.error(Object.values(errors).flat().join(', ') || 'Gagal menghapus pengumuman.'),
        onFinish: () => (showDeleteDialog.value = false),
    });
};
</script>

<template>
    <Head :title="announcement.title" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <Button variant="ghost" size="icon" class="h-8 w-8 shrink-0" as-child>
                        <Link :href="route('announcements.index')">
                            <ArrowLeft class="w-4 h-4" />
                        </Link>
                    </Button>
                    <h2 class="text-lg font-semibold leading-tight text-foreground truncate">{{ announcement.title }}</h2>
                </div>
                <DropdownMenu v-if="hasPermission('manage_announcements')">
                    <DropdownMenuTrigger as-child>
                        <Button variant="ghost" size="icon" class="h-8 w-8 shrink-0">
                            <MoreVertical class="w-4 h-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuItem as-child>
                            <Link :href="route('announcements.edit', announcement.id)" class="flex items-center gap-2">
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
            <div class="max-w-4xl mx-auto px-3 sm:px-6 lg:px-8">
                <div class="bg-card rounded-xl border overflow-hidden">
                    <!-- Color accent -->
                    <div
                        class="h-1 w-full"
                        :class="announcement.status === 'published' ? 'bg-green-500' : 'bg-yellow-500'"
                    />

                    <div class="p-4 sm:p-6">
                        <!-- Header -->
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <h1 class="text-base sm:text-xl font-bold text-foreground">{{ announcement.title }}</h1>
                            <span :class="['px-2 py-0.5 rounded-full text-[10px] font-semibold shrink-0', getStatusConfig(announcement.status).class]">
                                {{ getStatusConfig(announcement.status).label }}
                            </span>
                        </div>

                        <!-- Meta -->
                        <div class="flex flex-wrap gap-x-4 gap-y-1.5 text-[11px] sm:text-xs text-muted-foreground mb-4 pb-4 border-b">
                            <div class="flex items-center gap-1.5">
                                <UserCircle class="w-3.5 h-3.5 text-primary" />
                                <span>{{ announcement.creator?.name || '-' }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <CalendarDays class="w-3.5 h-3.5 text-primary" />
                                <span>{{ formatDate(announcement.publish_date) }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <Users class="w-3.5 h-3.5 text-primary" />
                                <span>{{ getAudienceLabel(announcement) }}</span>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="prose prose-sm sm:prose max-w-none dark:prose-invert" v-html="announcement.content || '<p class=\'text-muted-foreground italic\'>Tidak ada konten.</p>'"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation -->
        <DeleteConfirmDialog
            :open="showDeleteDialog"
            title="Hapus Pengumuman"
            :description="`Apakah Anda yakin ingin menghapus pengumuman ${announcement.title}? Tindakan ini tidak dapat dibatalkan.`"
            @confirm="confirmDelete"
            @cancel="showDeleteDialog = false"
        />
    </AuthenticatedLayout>
</template>
