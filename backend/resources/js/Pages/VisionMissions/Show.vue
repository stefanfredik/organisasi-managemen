<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
    DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
    ArrowLeft, Pencil, Trash2, MoreVertical, Lightbulb, Target,
    Calendar, User, Clock, History,
} from "lucide-vue-next";

const props = defineProps({
    visionMission: Object,
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("id-ID", {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};

const formatDateTime = (date) => {
    return new Date(date).toLocaleString("id-ID", {
        day: "numeric",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const deleteVisionMission = () => {
    if (confirm("Apakah Anda yakin ingin menghapus visi & misi ini?")) {
        router.delete(route("vision-missions.destroy", props.visionMission.id));
    }
};
</script>

<template>
    <Head title="Detail Visi & Misi" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <Link :href="route('vision-missions.index')" class="shrink-0 p-1.5 rounded-lg text-muted-foreground hover:text-foreground hover:bg-muted transition-colors">
                        <ArrowLeft class="w-5 h-5" />
                    </Link>
                    <h2 class="text-lg font-semibold leading-tight text-foreground truncate">Detail Visi & Misi</h2>
                </div>
                <DropdownMenu v-if="hasPermission('manage_vision_missions')">
                    <DropdownMenuTrigger as-child>
                        <Button variant="outline" size="sm">
                            <MoreVertical class="w-4 h-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuItem as-child>
                            <Link :href="route('vision-missions.edit', visionMission.id)" class="flex items-center gap-2">
                                <Pencil class="w-4 h-4" />
                                Edit
                            </Link>
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem @click="deleteVisionMission" class="text-destructive flex items-center gap-2">
                            <Trash2 class="w-4 h-4" />
                            Hapus
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </template>

        <div class="py-4 sm:py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">

                <!-- Meta Info Card -->
                <div class="bg-card border rounded-xl overflow-hidden">
                    <div class="p-4 sm:p-6">
                        <div class="flex flex-wrap items-center gap-2 mb-4">
                            <Badge :variant="visionMission.status === 'active' ? 'success' : 'secondary'">
                                {{ visionMission.status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                            </Badge>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                                    <Calendar class="w-4 h-4 text-primary" />
                                </div>
                                <div>
                                    <p class="text-[11px] text-muted-foreground">Periode</p>
                                    <p class="text-sm font-medium text-foreground">{{ visionMission.period_start }} - {{ visionMission.period_end || 'Sekarang' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-blue-500/10 flex items-center justify-center shrink-0">
                                    <User class="w-4 h-4 text-blue-500" />
                                </div>
                                <div>
                                    <p class="text-[11px] text-muted-foreground">Dibuat Oleh</p>
                                    <p class="text-sm font-medium text-foreground">{{ visionMission.creator?.name }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-amber-500/10 flex items-center justify-center shrink-0">
                                    <Clock class="w-4 h-4 text-amber-500" />
                                </div>
                                <div>
                                    <p class="text-[11px] text-muted-foreground">Tanggal Dibuat</p>
                                    <p class="text-sm font-medium text-foreground">{{ formatDate(visionMission.created_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Visi Card -->
                <div class="bg-card border rounded-xl overflow-hidden">
                    <div class="flex items-center gap-2 px-4 sm:px-6 py-3 border-b bg-muted/30">
                        <Lightbulb class="w-4 h-4 text-primary" />
                        <h3 class="text-sm font-semibold text-foreground">Visi</h3>
                    </div>
                    <div class="p-4 sm:p-6">
                        <p class="text-sm text-foreground leading-relaxed whitespace-pre-wrap">{{ visionMission.vision }}</p>
                    </div>
                </div>

                <!-- Misi Card -->
                <div class="bg-card border rounded-xl overflow-hidden">
                    <div class="flex items-center gap-2 px-4 sm:px-6 py-3 border-b bg-muted/30">
                        <Target class="w-4 h-4 text-primary" />
                        <h3 class="text-sm font-semibold text-foreground">Misi</h3>
                        <Badge variant="secondary" class="ml-auto text-[10px]">{{ visionMission.missions?.length || 0 }} butir</Badge>
                    </div>
                    <div class="p-4 sm:p-6">
                        <ol class="space-y-3">
                            <li
                                v-for="(mission, index) in visionMission.missions"
                                :key="index"
                                class="flex gap-3"
                            >
                                <span class="shrink-0 w-7 h-7 rounded-lg bg-primary/10 text-primary text-xs font-bold flex items-center justify-center">
                                    {{ index + 1 }}
                                </span>
                                <p class="text-sm text-foreground pt-1 leading-relaxed">{{ mission }}</p>
                            </li>
                        </ol>
                    </div>
                </div>

                <!-- Riwayat Card -->
                <div class="bg-card border rounded-xl overflow-hidden">
                    <div class="flex items-center gap-2 px-4 sm:px-6 py-3 border-b bg-muted/30">
                        <History class="w-4 h-4 text-muted-foreground" />
                        <h3 class="text-sm font-semibold text-foreground">Riwayat Perubahan</h3>
                    </div>
                    <div class="p-4 sm:p-6">
                        <div v-if="visionMission.histories && visionMission.histories.length > 0" class="space-y-4">
                            <div
                                v-for="h in visionMission.histories"
                                :key="h.id"
                                class="relative pl-6 pb-4 last:pb-0 border-l-2 border-border last:border-l-transparent"
                            >
                                <!-- Timeline dot -->
                                <div class="absolute -left-[5px] top-0.5 w-2 h-2 rounded-full" :class="h.action === 'deleted' ? 'bg-destructive' : 'bg-primary'"></div>

                                <div class="flex items-center gap-2 mb-1">
                                    <Badge :variant="h.action === 'deleted' ? 'destructive' : 'default'" class="text-[10px]">
                                        {{ h.action === 'deleted' ? 'Dihapus' : 'Diperbarui' }}
                                    </Badge>
                                    <span class="text-[11px] text-muted-foreground">{{ formatDateTime(h.created_at) }}</span>
                                </div>
                                <p class="text-xs text-muted-foreground">Oleh: {{ h.user?.name || 'Sistem' }}</p>

                                <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-2">
                                    <div v-if="h.old_data" class="bg-muted/50 border rounded-lg p-3">
                                        <p class="text-[10px] font-semibold text-muted-foreground uppercase tracking-wider mb-1">Sebelum</p>
                                        <pre class="text-[11px] text-foreground overflow-auto whitespace-pre-wrap">{{ JSON.stringify(h.old_data, null, 2) }}</pre>
                                    </div>
                                    <div v-if="h.new_data" class="bg-muted/50 border rounded-lg p-3">
                                        <p class="text-[10px] font-semibold text-muted-foreground uppercase tracking-wider mb-1">Sesudah</p>
                                        <pre class="text-[11px] text-foreground overflow-auto whitespace-pre-wrap">{{ JSON.stringify(h.new_data, null, 2) }}</pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="flex flex-col items-center py-8 text-center">
                            <History class="w-8 h-8 text-muted-foreground/30 mb-2" />
                            <p class="text-xs text-muted-foreground">Belum ada riwayat perubahan.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
