<script setup>
import { ref, watch } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import SearchBar from "@/Components/SearchBar.vue";
import FilterDropdown from "@/Components/FilterDropdown.vue";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Card } from "@/components/ui/card";
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from "@/components/ui/table";
import { Plus, Eye, Pencil, Trash2 } from "lucide-vue-next";

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

        <div class="py-4 sm:py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <Card>
                    <div class="p-3 sm:p-4">
                        <div class="flex flex-col sm:flex-row gap-2 mb-4">
                            <div class="flex-1">
                                <SearchBar v-model="search" placeholder="Cari pengumuman..." />
                            </div>
                            <div class="flex gap-2">
                                <FilterDropdown v-model="status" :options="statusOptions" label="Status" />
                                <FilterDropdown v-model="audience" :options="props.roleOptions" label="Audiens" />
                            </div>
                        </div>

                        <!-- Desktop Table -->
                        <div class="hidden md:block">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Judul</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Tanggal Publish</TableHead>
                                        <TableHead>Audiens</TableHead>
                                        <TableHead>Dibuat Oleh</TableHead>
                                        <TableHead class="text-right">Aksi</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="a in announcements.data" :key="a.id">
                                        <TableCell class="font-medium">{{ a.title }}</TableCell>
                                        <TableCell>
                                            <Badge :variant="a.status === 'published' ? 'success' : 'secondary'">
                                                {{ a.status === 'published' ? 'Dipublikasikan' : 'Draft' }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>{{ a.publish_date || '-' }}</TableCell>
                                        <TableCell>
                                            <span v-if="a.target_audience === 'all'">Semua</span>
                                            <span v-else>{{ (a.target_roles || []).join(', ') }}</span>
                                        </TableCell>
                                        <TableCell>{{ a.creator?.name || '-' }}</TableCell>
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
                                                    as-child
                                                >
                                                    <Link
                                                        :href="route('announcements.destroy', a.id)"
                                                        method="delete"
                                                        as="button"
                                                        preserve-scroll
                                                        :confirm="'Hapus pengumuman ini?'"
                                                    >
                                                        <Trash2 class="w-4 h-4" />
                                                    </Link>
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-if="(announcements?.data?.length || 0) === 0">
                                        <TableCell colspan="6" class="h-24 text-center text-muted-foreground">
                                            Tidak ada pengumuman.
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>

                        <!-- Mobile Card View -->
                        <div class="md:hidden divide-y">
                            <div v-for="a in announcements.data" :key="a.id" class="p-4 space-y-2">
                                <div class="flex justify-between items-start">
                                    <p class="text-sm font-medium text-foreground">{{ a.title }}</p>
                                    <Badge :variant="a.status === 'published' ? 'success' : 'secondary'" class="shrink-0 ml-2">
                                        {{ a.status === 'published' ? 'Dipublikasikan' : 'Draft' }}
                                    </Badge>
                                </div>
                                <div class="text-xs text-muted-foreground space-y-1">
                                    <p>Tanggal: {{ a.publish_date || '-' }}</p>
                                    <p>Oleh: {{ a.creator?.name || '-' }}</p>
                                </div>
                                <div class="flex justify-end gap-2 pt-2 border-t">
                                    <Button variant="ghost" size="sm" as-child>
                                        <Link :href="route('announcements.show', a.id)">Detail</Link>
                                    </Button>
                                    <Button v-if="hasPermission('manage_announcements')" variant="ghost" size="sm" as-child>
                                        <Link :href="route('announcements.edit', a.id)">Edit</Link>
                                    </Button>
                                </div>
                            </div>
                            <div v-if="(announcements?.data?.length || 0) === 0" class="px-4 py-16 text-center text-muted-foreground">
                                Tidak ada pengumuman.
                            </div>
                        </div>
                    </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
