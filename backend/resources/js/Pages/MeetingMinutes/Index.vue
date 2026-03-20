<script setup>
import { ref, watch } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import SearchBar from "@/Components/SearchBar.vue";
import { Button } from "@/components/ui/button";
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow, } from "@/components/ui/table";
import DeleteConfirmDialog from '@/Components/DeleteConfirmDialog.vue';
import {
    Sheet, SheetContent, SheetHeader, SheetTitle, SheetDescription, } from "@/components/ui/sheet";
import {
    Plus, Eye, Pencil, Trash2, Inbox, CalendarDays, Users, MoreVertical, ChevronLeft, ChevronRight, FileText } from 'lucide-vue-next';
import { useToast } from '@/composables/useToast';

const toast = useToast();

const props = defineProps({
    minutes: Object,
    filters: Object,
});

const search = ref(props.filters?.search || "");

watch(search, (s) => {
    router.get(route("meeting-minutes.index"), { search: s }, { preserveState: true, replace: true });
});

const formatDate = (date) => {
    if (!date) return "-";
    return new Date(date).toLocaleDateString("id-ID", { day: "numeric", month: "short", year: "numeric" });
};

const deleteTarget = ref(null);
const confirmDelete = () => {
    if (deleteTarget.value) {
        router.delete(route("meeting-minutes.destroy", deleteTarget.value.id), {
            preserveScroll: true,
            onError: (errors) => toast.error(Object.values(errors).flat().join(', ') || 'Gagal menghapus notulensi.'),
            onFinish: () => (deleteTarget.value = null),
        });
    }
};

const detailItem = ref(null);
const showDetailSheet = ref(false);
const openDetail = (m) => { detailItem.value = m; showDetailSheet.value = true; };
const closeDetail = () => { showDetailSheet.value = false; detailItem.value = null; };
</script>

<template>
    <Head title="Notulensi Rapat" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold leading-tight text-foreground">
<div class="flex items-center gap-2.5">
<FileText class="w-5 h-5" />
<span>Notulensi Rapat</span>
</div>
</h2>
                <Button v-if="hasPermission('manage_meeting_minutes')" size="sm" as-child>
                    <Link :href="route('meeting-minutes.create')">
                        <Plus class="w-4 h-4 mr-1" />
                        <span class="hidden sm:inline">Tambah</span>
                    </Link>
                </Button>
            </div>
        </template>

        <div class="py-3 sm:py-6">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-3 sm:space-y-4">

                <!-- Search -->
                <SearchBar v-model="search" placeholder="Cari agenda rapat..." />

                <!-- Mobile: Compact List -->
                <div class="sm:hidden space-y-2">
                    <div
                        v-for="m in minutes.data"
                        :key="'m-' + m.id"
                        class="bg-card rounded-lg border overflow-hidden cursor-pointer active:bg-muted/50 transition-colors"
                        @click="router.visit(route('meeting-minutes.show', m.id))"
                    >
                        <div class="flex items-center gap-3 p-3">
                            <div class="w-1 self-stretch rounded-full shrink-0 bg-primary" />
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm font-semibold text-foreground truncate">{{ m.agenda }}</h3>
                                <div class="flex items-center gap-1.5 text-[10px] text-muted-foreground mt-0.5">
                                    <CalendarDays class="w-2.5 h-2.5" />
                                    <span>{{ formatDate(m.meeting_date) }}</span>
                                    <span class="text-border">·</span>
                                    <Users class="w-2.5 h-2.5" />
                                    <span>{{ Array.isArray(m.participants) ? m.participants.length : 0 }} peserta</span>
                                    <span class="text-border">·</span>
                                    <span>{{ m.creator?.name || '-' }}</span>
                                </div>
                            </div>
                            <Button variant="ghost" size="icon" class="h-7 w-7 shrink-0" @click.stop="openDetail(m)">
                                <MoreVertical class="w-4 h-4 text-muted-foreground" />
                            </Button>
                        </div>
                    </div>

                    <div v-if="(minutes?.data?.length || 0) === 0" class="py-12 text-center">
                        <div class="w-12 h-12 rounded-full bg-muted flex items-center justify-center mx-auto mb-2">
                            <Inbox class="w-6 h-6 text-muted-foreground" />
                        </div>
                        <p class="text-xs text-muted-foreground">Tidak ada notulensi rapat.</p>
                    </div>

                    <div v-if="minutes.total > minutes.per_page" class="flex items-center justify-between pt-1">
                        <p class="text-[10px] text-muted-foreground">{{ minutes.from }}-{{ minutes.to }} dari {{ minutes.total }}</p>
                        <div class="flex gap-1">
                            <Button v-if="minutes.prev_page_url" variant="outline" size="sm" class="h-6 text-[10px] px-2" as-child>
                                <Link :href="minutes.prev_page_url">Sebelumnya</Link>
                            </Button>
                            <Button v-if="minutes.next_page_url" variant="outline" size="sm" class="h-6 text-[10px] px-2" as-child>
                                <Link :href="minutes.next_page_url">Selanjutnya</Link>
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Desktop: Table -->
                <div class="hidden sm:block bg-card rounded-xl border overflow-hidden">
                    <Table>
                        <TableHeader>
                            <TableRow class="bg-muted/50">
                                <TableHead class="text-[11px] uppercase tracking-wide font-medium">Agenda</TableHead>
                                <TableHead class="text-[11px] uppercase tracking-wide font-medium">Tanggal Rapat</TableHead>
                                <TableHead class="text-[11px] uppercase tracking-wide font-medium">Peserta</TableHead>
                                <TableHead class="text-[11px] uppercase tracking-wide font-medium">Dibuat Oleh</TableHead>
                                <TableHead class="text-[11px] uppercase tracking-wide font-medium text-right">Aksi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="m in minutes.data" :key="m.id" class="hover:bg-muted/30 transition-colors">
                                <TableCell>
                                    <p class="text-sm font-medium text-foreground truncate max-w-xs">{{ m.agenda }}</p>
                                </TableCell>
                                <TableCell class="text-sm text-muted-foreground whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5">
                                        <CalendarDays class="w-3.5 h-3.5" />
                                        {{ formatDate(m.meeting_date) }}
                                    </span>
                                </TableCell>
                                <TableCell>
                                    <span v-if="Array.isArray(m.participants) && m.participants.length > 0" class="text-sm text-muted-foreground inline-flex items-center gap-1.5">
                                        <Users class="w-3.5 h-3.5" />
                                        {{ m.participants.length }} orang
                                    </span>
                                    <span v-else class="text-sm text-muted-foreground">-</span>
                                </TableCell>
                                <TableCell class="text-sm text-muted-foreground">{{ m.creator?.name || '-' }}</TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-1">
                                        <Button variant="ghost" size="icon" class="h-8 w-8" as-child>
                                            <Link :href="route('meeting-minutes.show', m.id)">
                                                <Eye class="w-4 h-4" />
                                            </Link>
                                        </Button>
                                        <Button v-if="hasPermission('manage_meeting_minutes')" variant="ghost" size="icon" class="h-8 w-8" as-child>
                                            <Link :href="route('meeting-minutes.edit', m.id)">
                                                <Pencil class="w-4 h-4" />
                                            </Link>
                                        </Button>
                                        <Button
                                            v-if="hasPermission('manage_meeting_minutes')"
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8 text-destructive hover:text-destructive"
                                            @click="deleteTarget = m"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="(minutes?.data?.length || 0) === 0">
                                <TableCell colspan="5" class="h-24 text-center">
                                    <div class="flex flex-col items-center gap-2 text-muted-foreground">
                                        <Inbox class="w-8 h-8" />
                                        <span class="text-sm">Tidak ada notulensi rapat.</span>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <div v-if="minutes.total > minutes.per_page" class="flex items-center justify-between px-4 py-3 border-t bg-muted/30">
                        <p class="text-xs text-muted-foreground">{{ minutes.from }}-{{ minutes.to }} dari {{ minutes.total }}</p>
                        <div class="flex gap-1.5">
                            <Button v-if="minutes.prev_page_url" variant="outline" size="sm" class="h-8" as-child>
                                <Link :href="minutes.prev_page_url"><ChevronLeft class="w-4 h-4 mr-0.5" /> Sebelumnya</Link>
                            </Button>
                            <Button v-if="minutes.next_page_url" variant="outline" size="sm" class="h-8" as-child>
                                <Link :href="minutes.next_page_url">Selanjutnya <ChevronRight class="w-4 h-4 ml-0.5" /></Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation -->
        <DeleteConfirmDialog
            :open="!!deleteTarget"
            title="Hapus Notulensi Rapat"
            :description="`Apakah Anda yakin ingin menghapus notulensi ${deleteTarget?.agenda}? Tindakan ini tidak dapat dibatalkan.`"
            @confirm="confirmDelete"
            @cancel="deleteTarget = null"
        />

        <!-- Mobile: Detail Sheet -->
        <Sheet :open="showDetailSheet" @update:open="val => { if (!val) closeDetail(); }">
            <SheetContent side="bottom" class="rounded-t-2xl max-h-[80vh] overflow-y-auto px-4 pb-6 pt-3">
                <SheetHeader class="text-left pb-2.5 border-b mb-3">
                    <SheetTitle class="text-sm">Detail Notulensi</SheetTitle>
                    <SheetDescription class="sr-only">Detail notulensi rapat</SheetDescription>
                </SheetHeader>

                <template v-if="detailItem">
                    <h3 class="text-sm font-bold text-foreground mb-2">{{ detailItem.agenda }}</h3>

                    <div class="grid grid-cols-3 gap-2 mb-3">
                        <div class="bg-muted/50 rounded-lg p-2.5">
                            <p class="text-[10px] text-muted-foreground uppercase font-medium">Tanggal</p>
                            <p class="text-xs font-medium text-foreground mt-0.5">{{ formatDate(detailItem.meeting_date) }}</p>
                        </div>
                        <div class="bg-muted/50 rounded-lg p-2.5">
                            <p class="text-[10px] text-muted-foreground uppercase font-medium">Peserta</p>
                            <p class="text-xs font-medium text-foreground mt-0.5">{{ Array.isArray(detailItem.participants) ? detailItem.participants.length : 0 }} orang</p>
                        </div>
                        <div class="bg-muted/50 rounded-lg p-2.5">
                            <p class="text-[10px] text-muted-foreground uppercase font-medium">Oleh</p>
                            <p class="text-xs font-medium text-foreground mt-0.5 truncate">{{ detailItem.creator?.name || '-' }}</p>
                        </div>
                    </div>

                    <div class="space-y-2 border-t pt-3">
                        <Button variant="outline" size="sm" class="w-full justify-start" as-child>
                            <Link :href="route('meeting-minutes.show', detailItem.id)" @click="closeDetail()">
                                <Eye class="w-4 h-4 mr-2" /> Lihat Detail
                            </Link>
                        </Button>
                        <Button v-if="hasPermission('manage_meeting_minutes')" variant="outline" size="sm" class="w-full justify-start" as-child>
                            <Link :href="route('meeting-minutes.edit', detailItem.id)" @click="closeDetail()">
                                <Pencil class="w-4 h-4 mr-2" /> Edit Notulensi
                            </Link>
                        </Button>
                        <Button
                            v-if="hasPermission('manage_meeting_minutes')"
                            variant="outline"
                            size="sm"
                            class="w-full justify-start text-destructive hover:text-destructive"
                            @click="closeDetail(); deleteTarget = detailItem;"
                        >
                            <Trash2 class="w-4 h-4 mr-2" /> Hapus Notulensi
                        </Button>
                    </div>
                </template>
            </SheetContent>
        </Sheet>
    </AuthenticatedLayout>
</template>
