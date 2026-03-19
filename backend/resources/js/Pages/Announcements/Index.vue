<script setup>
import { ref, watch, computed } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import SearchBar from "@/Components/SearchBar.vue";
import FilterDropdown from "@/Components/FilterDropdown.vue";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from "@/components/ui/table";
import DeleteConfirmDialog from '@/Components/DeleteConfirmDialog.vue';
import {
    Plus, Eye, Pencil, Trash2, Megaphone, CalendarDays, Users, Inbox,
    ChevronLeft, ChevronRight, MoreVertical,
} from "lucide-vue-next";
import {
    Sheet, SheetContent, SheetHeader, SheetTitle, SheetDescription,
} from "@/components/ui/sheet";
import { useToast } from '@/composables/useToast';

const toast = useToast();

const props = defineProps({
    announcements: Object,
    filters: Object,
    roleOptions: Array,
});

const search = ref(props.filters?.search || "");
const status = ref(props.filters?.status || "");
const audience = ref(props.filters?.audience || "all");

const statusOptions = [
    { value: "draft", label: "Draft" },
    { value: "published", label: "Dipublikasikan" },
];

watch([search, status, audience], ([s, st, aud]) => {
    router.get(
        route("announcements.index"),
        { search: s, status: st, audience: aud },
        { preserveState: true, replace: true }
    );
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
    return new Date(date).toLocaleDateString("id-ID", { day: "numeric", month: "short", year: "numeric" });
};

const deleteTarget = ref(null);
const confirmDelete = () => {
    if (deleteTarget.value) {
        router.delete(route("announcements.destroy", deleteTarget.value.id), {
            preserveScroll: true,
            onError: (errors) => toast.error(Object.values(errors).flat().join(', ') || 'Gagal menghapus pengumuman.'),
            onFinish: () => (deleteTarget.value = null),
        });
    }
};

const detailItem = ref(null);
const showDetailSheet = ref(false);
const openDetail = (a) => { detailItem.value = a; showDetailSheet.value = true; };
const closeDetail = () => { showDetailSheet.value = false; detailItem.value = null; };
</script>

<template>
    <Head title="Pengumuman" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold leading-tight text-foreground">Pengumuman</h2>
                <Button v-if="hasPermission('manage_announcements')" size="sm" as-child>
                    <Link :href="route('announcements.create')">
                        <Plus class="w-4 h-4 mr-1" />
                        <span class="hidden sm:inline">Tambah</span>
                    </Link>
                </Button>
            </div>
        </template>

        <div class="py-3 sm:py-6">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-3 sm:space-y-4">

                <!-- Filters -->
                <div class="flex flex-col sm:flex-row gap-2">
                    <div class="flex-1">
                        <SearchBar v-model="search" placeholder="Cari pengumuman..." />
                    </div>
                    <div class="flex gap-2">
                        <div class="flex-1 sm:w-36">
                            <FilterDropdown v-model="status" :options="statusOptions" label="Status" />
                        </div>
                        <div class="flex-1 sm:w-36">
                            <FilterDropdown v-model="audience" :options="props.roleOptions" label="Audiens" />
                        </div>
                    </div>
                </div>

                <!-- Mobile: Compact List -->
                <div class="sm:hidden space-y-2">
                    <div
                        v-for="a in announcements.data"
                        :key="'m-' + a.id"
                        class="bg-card rounded-lg border overflow-hidden cursor-pointer active:bg-muted/50 transition-colors"
                        @click="router.visit(route('announcements.show', a.id))"
                    >
                        <div class="flex items-center gap-3 p-3">
                            <div
                                class="w-1 self-stretch rounded-full shrink-0"
                                :class="a.status === 'published' ? 'bg-green-500' : 'bg-yellow-500'"
                            />
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5 mb-0.5">
                                    <span :class="['px-1.5 py-px rounded-full text-[9px] font-semibold', getStatusConfig(a.status).class]">
                                        {{ getStatusConfig(a.status).label }}
                                    </span>
                                </div>
                                <h3 class="text-sm font-semibold text-foreground truncate">{{ a.title }}</h3>
                                <div class="flex items-center gap-1.5 text-[10px] text-muted-foreground mt-0.5">
                                    <span>{{ formatDate(a.publish_date) }}</span>
                                    <span class="text-border">·</span>
                                    <span>{{ a.creator?.name || '-' }}</span>
                                    <span class="text-border">·</span>
                                    <span>{{ getAudienceLabel(a) }}</span>
                                </div>
                            </div>
                            <Button variant="ghost" size="icon" class="h-7 w-7 shrink-0" @click.stop="openDetail(a)">
                                <MoreVertical class="w-4 h-4 text-muted-foreground" />
                            </Button>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="(announcements?.data?.length || 0) === 0" class="py-12 text-center">
                        <div class="w-12 h-12 rounded-full bg-muted flex items-center justify-center mx-auto mb-2">
                            <Inbox class="w-6 h-6 text-muted-foreground" />
                        </div>
                        <p class="text-xs text-muted-foreground">Tidak ada pengumuman.</p>
                    </div>

                    <!-- Mobile Pagination -->
                    <div v-if="announcements.total > announcements.per_page" class="flex items-center justify-between pt-1">
                        <p class="text-[10px] text-muted-foreground">{{ announcements.from }}-{{ announcements.to }} dari {{ announcements.total }}</p>
                        <div class="flex gap-1">
                            <Button v-if="announcements.prev_page_url" variant="outline" size="sm" class="h-6 text-[10px] px-2" as-child>
                                <Link :href="announcements.prev_page_url">Sebelumnya</Link>
                            </Button>
                            <Button v-if="announcements.next_page_url" variant="outline" size="sm" class="h-6 text-[10px] px-2" as-child>
                                <Link :href="announcements.next_page_url">Selanjutnya</Link>
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Desktop: Table -->
                <div class="hidden sm:block bg-card rounded-xl border overflow-hidden">
                    <Table>
                        <TableHeader>
                            <TableRow class="bg-muted/50">
                                <TableHead class="text-[11px] uppercase tracking-wide font-medium">Judul</TableHead>
                                <TableHead class="text-[11px] uppercase tracking-wide font-medium">Status</TableHead>
                                <TableHead class="text-[11px] uppercase tracking-wide font-medium">Tanggal Publish</TableHead>
                                <TableHead class="text-[11px] uppercase tracking-wide font-medium">Audiens</TableHead>
                                <TableHead class="text-[11px] uppercase tracking-wide font-medium">Dibuat Oleh</TableHead>
                                <TableHead class="text-[11px] uppercase tracking-wide font-medium text-right">Aksi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="a in announcements.data" :key="a.id" class="hover:bg-muted/30 transition-colors">
                                <TableCell>
                                    <p class="text-sm font-medium text-foreground truncate max-w-xs">{{ a.title }}</p>
                                </TableCell>
                                <TableCell>
                                    <span :class="['inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold', getStatusConfig(a.status).class]">
                                        {{ getStatusConfig(a.status).label }}
                                    </span>
                                </TableCell>
                                <TableCell class="text-sm text-muted-foreground">{{ formatDate(a.publish_date) }}</TableCell>
                                <TableCell class="text-sm text-muted-foreground">{{ getAudienceLabel(a) }}</TableCell>
                                <TableCell class="text-sm text-muted-foreground">{{ a.creator?.name || '-' }}</TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-1">
                                        <Button variant="ghost" size="icon" class="h-8 w-8" as-child>
                                            <Link :href="route('announcements.show', a.id)">
                                                <Eye class="w-4 h-4" />
                                            </Link>
                                        </Button>
                                        <Button v-if="hasPermission('manage_announcements')" variant="ghost" size="icon" class="h-8 w-8" as-child>
                                            <Link :href="route('announcements.edit', a.id)">
                                                <Pencil class="w-4 h-4" />
                                            </Link>
                                        </Button>
                                        <Button
                                            v-if="hasPermission('manage_announcements')"
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8 text-destructive hover:text-destructive"
                                            @click="deleteTarget = a"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="(announcements?.data?.length || 0) === 0">
                                <TableCell colspan="6" class="h-24 text-center">
                                    <div class="flex flex-col items-center gap-2 text-muted-foreground">
                                        <Inbox class="w-8 h-8" />
                                        <span class="text-sm">Tidak ada pengumuman.</span>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Desktop Pagination -->
                    <div v-if="announcements.total > announcements.per_page" class="flex items-center justify-between px-4 py-3 border-t bg-muted/30">
                        <p class="text-xs text-muted-foreground">
                            {{ announcements.from }}-{{ announcements.to }} dari {{ announcements.total }}
                        </p>
                        <div class="flex gap-1.5">
                            <Button v-if="announcements.prev_page_url" variant="outline" size="sm" class="h-8" as-child>
                                <Link :href="announcements.prev_page_url">
                                    <ChevronLeft class="w-4 h-4 mr-0.5" /> Sebelumnya
                                </Link>
                            </Button>
                            <Button v-if="announcements.next_page_url" variant="outline" size="sm" class="h-8" as-child>
                                <Link :href="announcements.next_page_url">
                                    Selanjutnya <ChevronRight class="w-4 h-4 ml-0.5" />
                                </Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation -->
        <DeleteConfirmDialog
            :open="!!deleteTarget"
            title="Hapus Pengumuman"
            :description="`Apakah Anda yakin ingin menghapus pengumuman ${deleteTarget?.title}? Tindakan ini tidak dapat dibatalkan.`"
            @confirm="confirmDelete"
            @cancel="deleteTarget = null"
        />

        <!-- Mobile: Detail Sheet -->
        <Sheet :open="showDetailSheet" @update:open="val => { if (!val) closeDetail(); }">
            <SheetContent side="bottom" class="rounded-t-2xl max-h-[80vh] overflow-y-auto px-4 pb-6 pt-3">
                <SheetHeader class="text-left pb-2.5 border-b mb-3">
                    <SheetTitle class="text-sm">Detail Pengumuman</SheetTitle>
                    <SheetDescription class="sr-only">Detail pengumuman</SheetDescription>
                </SheetHeader>

                <template v-if="detailItem">
                    <!-- Title + Status -->
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <h3 class="text-sm font-bold text-foreground">{{ detailItem.title }}</h3>
                        <span :class="['px-1.5 py-0.5 rounded-full text-[10px] font-semibold shrink-0', getStatusConfig(detailItem.status).class]">
                            {{ getStatusConfig(detailItem.status).label }}
                        </span>
                    </div>

                    <!-- Info grid -->
                    <div class="grid grid-cols-3 gap-2 mb-3">
                        <div class="bg-muted/50 rounded-lg p-2.5">
                            <p class="text-[10px] text-muted-foreground uppercase font-medium">Tanggal</p>
                            <p class="text-xs font-medium text-foreground mt-0.5">{{ formatDate(detailItem.publish_date) }}</p>
                        </div>
                        <div class="bg-muted/50 rounded-lg p-2.5">
                            <p class="text-[10px] text-muted-foreground uppercase font-medium">Oleh</p>
                            <p class="text-xs font-medium text-foreground mt-0.5">{{ detailItem.creator?.name || '-' }}</p>
                        </div>
                        <div class="bg-muted/50 rounded-lg p-2.5">
                            <p class="text-[10px] text-muted-foreground uppercase font-medium">Audiens</p>
                            <p class="text-xs font-medium text-foreground mt-0.5">{{ getAudienceLabel(detailItem) }}</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="space-y-2 border-t pt-3">
                        <Button variant="outline" size="sm" class="w-full justify-start" as-child>
                            <Link :href="route('announcements.show', detailItem.id)" @click="closeDetail()">
                                <Eye class="w-4 h-4 mr-2" /> Lihat Detail
                            </Link>
                        </Button>
                        <Button v-if="hasPermission('manage_announcements')" variant="outline" size="sm" class="w-full justify-start" as-child>
                            <Link :href="route('announcements.edit', detailItem.id)" @click="closeDetail()">
                                <Pencil class="w-4 h-4 mr-2" /> Edit Pengumuman
                            </Link>
                        </Button>
                        <Button
                            v-if="hasPermission('manage_announcements')"
                            variant="outline"
                            size="sm"
                            class="w-full justify-start text-destructive hover:text-destructive"
                            @click="closeDetail(); deleteTarget = detailItem;"
                        >
                            <Trash2 class="w-4 h-4 mr-2" /> Hapus Pengumuman
                        </Button>
                    </div>
                </template>
            </SheetContent>
        </Sheet>
    </AuthenticatedLayout>
</template>
