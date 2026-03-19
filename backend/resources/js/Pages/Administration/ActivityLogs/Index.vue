<script setup>
import { ref, watch } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import SearchBar from "@/Components/SearchBar.vue";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from "@/components/ui/table";
import {
    Sheet, SheetContent, SheetHeader, SheetTitle, SheetDescription,
} from "@/components/ui/sheet";
import {
    Inbox, Clock, UserCircle, Globe, FileText, MoreVertical,
    ChevronLeft, ChevronRight,
} from "lucide-vue-next";
import debounce from "lodash/debounce";

const props = defineProps({
    logs: Object,
    filters: Object,
});

const search = ref(props.filters?.search || "");

watch(search, debounce((value) => {
    router.get(route("activity-logs.index"), { search: value }, {
        preserveState: true,
        replace: true,
    });
}, 300));

const formatDate = (val) => {
    if (!val) return "-";
    return new Date(val).toLocaleDateString("id-ID", {
        day: "numeric", month: "short", year: "numeric",
    });
};

const formatTime = (val) => {
    if (!val) return "";
    return new Date(val).toLocaleTimeString("id-ID", {
        hour: "2-digit", minute: "2-digit",
    });
};

const formatDateTime = (val) => {
    if (!val) return "-";
    return new Date(val).toLocaleString("id-ID", {
        day: "numeric", month: "short", year: "numeric",
        hour: "2-digit", minute: "2-digit",
    });
};

const getActivityConfig = (action) => {
    const a = (action || "").toLowerCase();
    if (a.includes("create") || a.includes("tambah") || a.includes("store"))
        return { label: action, color: "bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400", dot: "bg-green-500" };
    if (a.includes("update") || a.includes("ubah") || a.includes("edit"))
        return { label: action, color: "bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400", dot: "bg-yellow-500" };
    if (a.includes("delete") || a.includes("hapus") || a.includes("destroy"))
        return { label: action, color: "bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400", dot: "bg-red-500" };
    if (a.includes("login"))
        return { label: action, color: "bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400", dot: "bg-blue-500" };
    if (a.includes("logout"))
        return { label: action, color: "bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-400", dot: "bg-gray-500" };
    if (a.includes("reset") || a.includes("password"))
        return { label: action, color: "bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400", dot: "bg-orange-500" };
    return { label: action, color: "bg-muted text-muted-foreground", dot: "bg-muted-foreground" };
};

// Mobile detail sheet
const detailItem = ref(null);
const showDetailSheet = ref(false);
const openDetail = (log) => { detailItem.value = log; showDetailSheet.value = true; };
const closeDetail = () => { showDetailSheet.value = false; detailItem.value = null; };
</script>

<template>
    <Head title="Log Aktivitas" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-semibold leading-tight text-foreground">Log Aktivitas</h2>
        </template>

        <div class="py-3 sm:py-6">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-3 sm:space-y-4">

                <!-- Search -->
                <SearchBar v-model="search" placeholder="Cari aktivitas, deskripsi, atau user..." />

                <!-- Mobile: Timeline List -->
                <div class="sm:hidden space-y-2">
                    <div
                        v-for="log in logs.data"
                        :key="'m-' + log.id"
                        class="bg-card rounded-lg border overflow-hidden cursor-pointer active:bg-muted/50 transition-colors"
                        @click="openDetail(log)"
                    >
                        <div class="flex items-start gap-3 p-3">
                            <!-- Activity dot -->
                            <div class="mt-1.5">
                                <div :class="['w-2 h-2 rounded-full shrink-0', getActivityConfig(log.action).dot]" />
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5 mb-0.5">
                                    <span :class="['px-1.5 py-px rounded text-[9px] font-semibold uppercase', getActivityConfig(log.action).color]">
                                        {{ log.action }}
                                    </span>
                                </div>
                                <p class="text-xs text-foreground truncate">{{ log.description || '-' }}</p>
                                <div class="flex items-center gap-1.5 text-[10px] text-muted-foreground mt-0.5">
                                    <span>{{ log.user?.name || 'System' }}</span>
                                    <span class="text-border">&middot;</span>
                                    <span>{{ formatDate(log.created_at) }} {{ formatTime(log.created_at) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty -->
                    <div v-if="(logs?.data?.length || 0) === 0" class="py-12 text-center">
                        <div class="w-12 h-12 rounded-full bg-muted flex items-center justify-center mx-auto mb-2">
                            <Inbox class="w-6 h-6 text-muted-foreground" />
                        </div>
                        <p class="text-xs text-muted-foreground">Tidak ada log aktivitas.</p>
                    </div>

                    <!-- Mobile Pagination -->
                    <div v-if="logs.total > logs.per_page" class="flex items-center justify-between pt-1">
                        <p class="text-[10px] text-muted-foreground">{{ logs.from }}-{{ logs.to }} dari {{ logs.total }}</p>
                        <div class="flex gap-1">
                            <Button v-if="logs.prev_page_url" variant="outline" size="sm" class="h-6 text-[10px] px-2" as-child>
                                <Link :href="logs.prev_page_url">Sebelumnya</Link>
                            </Button>
                            <Button v-if="logs.next_page_url" variant="outline" size="sm" class="h-6 text-[10px] px-2" as-child>
                                <Link :href="logs.next_page_url">Selanjutnya</Link>
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Desktop: Table -->
                <div class="hidden sm:block bg-card rounded-xl border overflow-hidden">
                    <Table>
                        <TableHeader>
                            <TableRow class="bg-muted/50">
                                <TableHead class="text-[11px] uppercase tracking-wide font-medium">Waktu</TableHead>
                                <TableHead class="text-[11px] uppercase tracking-wide font-medium">User</TableHead>
                                <TableHead class="text-[11px] uppercase tracking-wide font-medium">Aktivitas</TableHead>
                                <TableHead class="text-[11px] uppercase tracking-wide font-medium">Deskripsi</TableHead>
                                <TableHead class="text-[11px] uppercase tracking-wide font-medium">IP Address</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="log in logs.data" :key="log.id" class="hover:bg-muted/30 transition-colors">
                                <TableCell class="whitespace-nowrap">
                                    <div>
                                        <p class="text-sm text-foreground">{{ formatDate(log.created_at) }}</p>
                                        <p class="text-xs text-muted-foreground">{{ formatTime(log.created_at) }}</p>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div v-if="log.user" class="flex items-center gap-2.5">
                                        <div class="h-7 w-7 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                                            <span class="text-primary font-semibold text-[10px]">{{ log.user.name.charAt(0).toUpperCase() }}</span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-foreground leading-tight">{{ log.user.name }}</p>
                                            <p class="text-[10px] text-muted-foreground uppercase">{{ log.user.role }}</p>
                                        </div>
                                    </div>
                                    <span v-else class="text-sm text-muted-foreground italic">System</span>
                                </TableCell>
                                <TableCell>
                                    <span :class="['inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold uppercase', getActivityConfig(log.action).color]">
                                        {{ log.action }}
                                    </span>
                                </TableCell>
                                <TableCell>
                                    <p class="text-sm text-muted-foreground max-w-xs truncate" :title="log.description">
                                        {{ log.description || '-' }}
                                    </p>
                                </TableCell>
                                <TableCell>
                                    <span class="text-xs font-mono text-muted-foreground">{{ log.ip_address || '-' }}</span>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="(logs?.data?.length || 0) === 0">
                                <TableCell colspan="5" class="h-24 text-center">
                                    <div class="flex flex-col items-center gap-2 text-muted-foreground">
                                        <Inbox class="w-8 h-8" />
                                        <span class="text-sm">Tidak ada log aktivitas.</span>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Desktop Pagination -->
                    <div v-if="logs.total > logs.per_page" class="flex items-center justify-between px-4 py-3 border-t bg-muted/30">
                        <p class="text-xs text-muted-foreground">{{ logs.from }}-{{ logs.to }} dari {{ logs.total }}</p>
                        <div class="flex gap-1.5">
                            <Button v-if="logs.prev_page_url" variant="outline" size="sm" class="h-8" as-child>
                                <Link :href="logs.prev_page_url"><ChevronLeft class="w-4 h-4 mr-0.5" /> Sebelumnya</Link>
                            </Button>
                            <Button v-if="logs.next_page_url" variant="outline" size="sm" class="h-8" as-child>
                                <Link :href="logs.next_page_url">Selanjutnya <ChevronRight class="w-4 h-4 ml-0.5" /></Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile: Detail Sheet -->
        <Sheet :open="showDetailSheet" @update:open="val => { if (!val) closeDetail(); }">
            <SheetContent side="bottom" class="rounded-t-2xl max-h-[80vh] overflow-y-auto px-4 pb-6 pt-3">
                <SheetHeader class="text-left pb-2.5 border-b mb-3">
                    <SheetTitle class="text-sm">Detail Log</SheetTitle>
                    <SheetDescription class="sr-only">Detail log aktivitas</SheetDescription>
                </SheetHeader>

                <template v-if="detailItem">
                    <!-- Activity badge -->
                    <div class="flex items-center gap-2 mb-3">
                        <span :class="['px-2 py-0.5 rounded text-[10px] font-semibold uppercase', getActivityConfig(detailItem.action).color]">
                            {{ detailItem.action }}
                        </span>
                    </div>

                    <!-- Description -->
                    <p class="text-sm text-foreground mb-3">{{ detailItem.description || 'Tidak ada deskripsi.' }}</p>

                    <!-- Info grid -->
                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <div class="bg-muted/50 rounded-lg p-2.5">
                            <p class="text-[10px] text-muted-foreground uppercase font-medium flex items-center gap-1">
                                <UserCircle class="w-3 h-3" /> User
                            </p>
                            <p class="text-xs font-medium text-foreground mt-0.5">{{ detailItem.user?.name || 'System' }}</p>
                            <p v-if="detailItem.user" class="text-[10px] text-muted-foreground uppercase">{{ detailItem.user.role }}</p>
                        </div>
                        <div class="bg-muted/50 rounded-lg p-2.5">
                            <p class="text-[10px] text-muted-foreground uppercase font-medium flex items-center gap-1">
                                <Clock class="w-3 h-3" /> Waktu
                            </p>
                            <p class="text-xs font-medium text-foreground mt-0.5">{{ formatDate(detailItem.created_at) }}</p>
                            <p class="text-[10px] text-muted-foreground">{{ formatTime(detailItem.created_at) }}</p>
                        </div>
                        <div class="bg-muted/50 rounded-lg p-2.5">
                            <p class="text-[10px] text-muted-foreground uppercase font-medium flex items-center gap-1">
                                <Globe class="w-3 h-3" /> IP Address
                            </p>
                            <p class="text-xs font-mono font-medium text-foreground mt-0.5">{{ detailItem.ip_address || '-' }}</p>
                        </div>
                        <div class="bg-muted/50 rounded-lg p-2.5">
                            <p class="text-[10px] text-muted-foreground uppercase font-medium flex items-center gap-1">
                                <FileText class="w-3 h-3" /> Model
                            </p>
                            <p class="text-xs font-medium text-foreground mt-0.5 truncate">{{ detailItem.model_type ? detailItem.model_type.split('\\').pop() : '-' }}</p>
                        </div>
                    </div>
                </template>
            </SheetContent>
        </Sheet>
    </AuthenticatedLayout>
</template>
