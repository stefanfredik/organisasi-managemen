<script setup>
import { ref, watch } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import SearchBar from "@/Components/SearchBar.vue";
import FilterDropdown from "@/Components/FilterDropdown.vue";
import Pagination from "@/Components/Pagination.vue";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Card } from "@/components/ui/card";
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from "@/components/ui/table";
import { Plus, Eye, Pencil } from "lucide-vue-next";

const props = defineProps({
    visionMissions: Object,
    filters: Object,
});

const search = ref(props.filters.search || "");
const status = ref(props.filters.status || "");

const statusOptions = [
    { value: "active", label: "Aktif" },
    { value: "inactive", label: "Tidak Aktif" },
];

watch([search, status], ([newSearch, newStatus]) => {
    router.get(
        route("vision-missions.index"),
        { search: newSearch, status: newStatus },
        { preserveState: true, replace: true },
    );
});
</script>

<template>
    <Head title="Manajemen Visi & Misi" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold leading-tight text-foreground">Visi & Misi</h2>
                <Button v-if="hasPermission('manage_vision_missions')" size="sm" as-child>
                    <Link :href="route('vision-missions.create')">
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
                                <SearchBar v-model="search" placeholder="Cari visi..." />
                            </div>
                            <FilterDropdown v-model="status" :options="statusOptions" label="Status" />
                        </div>

                        <!-- Desktop Table -->
                        <div class="hidden md:block">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Periode</TableHead>
                                        <TableHead>Visi</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Dibuat Oleh</TableHead>
                                        <TableHead class="text-right">Aksi</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="item in visionMissions.data" :key="item.id">
                                        <TableCell class="whitespace-nowrap">
                                            {{ item.period_start }} - {{ item.period_end || 'Sekarang' }}
                                        </TableCell>
                                        <TableCell>
                                            <div class="line-clamp-2">{{ item.vision }}</div>
                                        </TableCell>
                                        <TableCell>
                                            <Badge :variant="item.status === 'active' ? 'success' : 'secondary'">
                                                {{ item.status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>{{ item.creator?.name }}</TableCell>
                                        <TableCell class="text-right">
                                            <div class="flex justify-end gap-2">
                                                <Button variant="ghost" size="sm" as-child>
                                                    <Link :href="route('vision-missions.show', item.id)">
                                                        <Eye class="w-4 h-4" />
                                                    </Link>
                                                </Button>
                                                <Button
                                                    v-if="hasPermission('manage_vision_missions')"
                                                    variant="ghost"
                                                    size="sm"
                                                    as-child
                                                >
                                                    <Link :href="route('vision-missions.edit', item.id)">
                                                        <Pencil class="w-4 h-4" />
                                                    </Link>
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-if="visionMissions.data.length === 0">
                                        <TableCell colspan="5" class="h-24 text-center text-muted-foreground">
                                            Tidak ada data visi & misi.
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>

                        <!-- Mobile Card View -->
                        <div class="md:hidden divide-y">
                            <div v-for="item in visionMissions.data" :key="item.id" class="p-4 space-y-2">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-sm font-medium text-foreground">
                                            {{ item.period_start }} - {{ item.period_end || 'Sekarang' }}
                                        </p>
                                        <p class="text-sm text-muted-foreground line-clamp-2 mt-1">{{ item.vision }}</p>
                                    </div>
                                    <Badge :variant="item.status === 'active' ? 'success' : 'secondary'" class="shrink-0">
                                        {{ item.status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                                    </Badge>
                                </div>
                                <div class="flex justify-between items-center pt-2 border-t">
                                    <span class="text-xs text-muted-foreground">{{ item.creator?.name }}</span>
                                    <div class="flex gap-2">
                                        <Button variant="ghost" size="sm" as-child>
                                            <Link :href="route('vision-missions.show', item.id)">Detail</Link>
                                        </Button>
                                        <Button
                                            v-if="hasPermission('manage_vision_missions')"
                                            variant="ghost"
                                            size="sm"
                                            as-child
                                        >
                                            <Link :href="route('vision-missions.edit', item.id)">Edit</Link>
                                        </Button>
                                    </div>
                                </div>
                            </div>
                            <div v-if="visionMissions.data.length === 0" class="px-4 py-16 text-center text-muted-foreground">
                                Tidak ada data visi & misi.
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="visionMissions.links && visionMissions.links.length > 3" class="mt-4 pt-4 border-t">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <p class="text-sm text-muted-foreground">
                                    Menampilkan
                                    <span class="font-medium">{{ visionMissions.from }}</span>
                                    sampai
                                    <span class="font-medium">{{ visionMissions.to }}</span>
                                    dari
                                    <span class="font-medium">{{ visionMissions.total }}</span>
                                    hasil
                                </p>
                                <Pagination :links="visionMissions.links" />
                            </div>
                        </div>
                    </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
