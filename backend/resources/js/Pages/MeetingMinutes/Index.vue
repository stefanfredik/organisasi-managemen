<script setup>
import { ref, watch } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import SearchBar from "@/Components/SearchBar.vue";
import { Button } from "@/components/ui/button";
import { Card } from "@/components/ui/card";
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from "@/components/ui/table";
import { Plus, Eye, Pencil, Trash2 } from "lucide-vue-next";

const props = defineProps({
    minutes: Object,
    filters: Object,
});

const search = ref(props.filters?.search || "");

watch(search, (s) => {
    router.get(route("meeting-minutes.index"), { search: s }, { preserveState: true, replace: true });
});
</script>

<template>
    <Head title="Notulensi Rapat" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold leading-tight text-foreground">Notulensi Rapat</h2>
                <Button v-if="hasPermission('manage_meeting_minutes')" size="sm" as-child>
                    <Link :href="route('meeting-minutes.create')">
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
                        <div class="mb-4">
                            <SearchBar v-model="search" placeholder="Cari agenda rapat..." />
                        </div>

                        <div class="hidden md:block">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Agenda</TableHead>
                                        <TableHead>Tanggal Rapat</TableHead>
                                        <TableHead>Peserta</TableHead>
                                        <TableHead>Dibuat Oleh</TableHead>
                                        <TableHead class="text-right">Aksi</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="m in minutes.data" :key="m.id">
                                        <TableCell class="font-medium">{{ m.agenda }}</TableCell>
                                        <TableCell>{{ m.meeting_date || '-' }}</TableCell>
                                        <TableCell>
                                            <span v-if="Array.isArray(m.participants) && m.participants.length > 0">{{ m.participants.length }} peserta</span>
                                            <span v-else class="text-muted-foreground">-</span>
                                        </TableCell>
                                        <TableCell>{{ m.creator?.name || '-' }}</TableCell>
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
                                                    as-child
                                                >
                                                    <Link :href="route('meeting-minutes.destroy', m.id)" method="delete" as="button" preserve-scroll>
                                                        <Trash2 class="w-4 h-4" />
                                                    </Link>
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-if="(minutes?.data?.length || 0) === 0">
                                        <TableCell colspan="5" class="h-24 text-center text-muted-foreground">
                                            Tidak ada notulensi rapat.
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>

                        <!-- Mobile -->
                        <div class="md:hidden divide-y">
                            <div v-for="m in minutes.data" :key="m.id" class="p-4 space-y-2">
                                <p class="text-sm font-medium text-foreground">{{ m.agenda }}</p>
                                <div class="text-xs text-muted-foreground space-y-1">
                                    <p>Tanggal: {{ m.meeting_date || '-' }}</p>
                                    <p>Oleh: {{ m.creator?.name || '-' }}</p>
                                </div>
                                <div class="flex justify-end gap-2 pt-2 border-t">
                                    <Button variant="ghost" size="sm" as-child>
                                        <Link :href="route('meeting-minutes.show', m.id)">Detail</Link>
                                    </Button>
                                    <Button v-if="hasPermission('manage_meeting_minutes')" variant="ghost" size="sm" as-child>
                                        <Link :href="route('meeting-minutes.edit', m.id)">Edit</Link>
                                    </Button>
                                </div>
                            </div>
                            <div v-if="(minutes?.data?.length || 0) === 0" class="px-4 py-16 text-center text-muted-foreground">
                                Tidak ada notulensi rapat.
                            </div>
                        </div>
                    </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
