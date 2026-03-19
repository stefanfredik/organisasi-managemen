<script setup>
import { ref, watch, computed } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import SearchBar from "@/Components/SearchBar.vue";
import FilterDropdown from "@/Components/FilterDropdown.vue";
import Pagination from "@/Components/Pagination.vue";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
    Sheet, SheetContent, SheetHeader, SheetTitle, SheetDescription,
} from "@/components/ui/sheet";
import {
    DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
    Plus, Eye, Pencil, Trash2, MoreVertical, Search, SlidersHorizontal,
    X, Lightbulb, Target, Calendar, User,
} from "lucide-vue-next";
import DeleteConfirmDialog from '@/Components/DeleteConfirmDialog.vue';
import { useToast } from '@/composables/useToast';

const toast = useToast();

const props = defineProps({
    visionMissions: Object,
    filters: Object,
});

const search = ref(props.filters.search || "");
const status = ref(props.filters.status || "");
const filterOpen = ref(false);

const statusOptions = [
    { value: "active", label: "Aktif" },
    { value: "inactive", label: "Tidak Aktif" },
];

const activeFilterCount = computed(() => status.value ? 1 : 0);

watch([search, status], ([newSearch, newStatus]) => {
    router.get(
        route("vision-missions.index"),
        { search: newSearch, status: newStatus },
        { preserveState: true, replace: true },
    );
});

const applyFilter = () => {
    router.get(
        route("vision-missions.index"),
        { search: search.value, status: status.value },
        { preserveState: true, replace: true },
    );
    filterOpen.value = false;
};

const resetFilters = () => {
    status.value = "";
    applyFilter();
};

const clearStatus = () => {
    status.value = "";
};

const deleteTarget = ref(null);
const confirmDelete = (id) => {
    deleteTarget.value = id;
};
const executeDelete = () => {
    if (deleteTarget.value) {
        router.delete(route("vision-missions.destroy", deleteTarget.value), {
            preserveScroll: true,
            onError: (errors) => toast.error(Object.values(errors).flat().join(', ') || 'Gagal menghapus data.'),
            onFinish: () => (deleteTarget.value = null),
        });
    }
};
</script>

<template>
    <Head title="Manajemen Visi & Misi" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold leading-tight text-foreground">Visi & Misi</h2>
                <Button v-if="hasPermission('manage_vision_missions')" size="sm" as-child class="hidden md:inline-flex">
                    <Link :href="route('vision-missions.create')">
                        <Plus class="w-4 h-4 mr-1" />
                        Tambah
                    </Link>
                </Button>
            </div>
        </template>

        <div class="py-4 sm:py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- ========== MOBILE LAYOUT ========== -->
                <div class="md:hidden space-y-4">
                    <!-- Search + Filter -->
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none" />
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Cari visi..."
                                class="w-full h-10 pl-9 pr-4 rounded-xl border bg-card text-sm focus:outline-none focus:ring-2 focus:ring-ring"
                            />
                        </div>
                        <button
                            @click="filterOpen = true"
                            class="relative h-10 w-10 rounded-xl border bg-card flex items-center justify-center shrink-0"
                        >
                            <SlidersHorizontal class="w-4 h-4 text-muted-foreground" />
                            <span v-if="activeFilterCount > 0" class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-primary text-primary-foreground text-[10px] font-bold flex items-center justify-center">
                                {{ activeFilterCount }}
                            </span>
                        </button>
                    </div>

                    <!-- Active filter chips -->
                    <div v-if="status" class="flex flex-wrap gap-2">
                        <button @click="clearStatus" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-primary/10 text-primary text-xs font-medium">
                            {{ status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                            <X class="w-3 h-3" />
                        </button>
                    </div>

                    <!-- Count -->
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-muted-foreground">{{ visionMissions.total || 0 }} data</span>
                    </div>

                    <!-- Mobile Cards -->
                    <div v-if="visionMissions.data.length > 0" class="space-y-3">
                        <Link
                            v-for="item in visionMissions.data"
                            :key="item.id"
                            :href="route('vision-missions.show', item.id)"
                            class="block rounded-xl border bg-card overflow-hidden hover:shadow-md transition-shadow"
                        >
                            <div class="p-4">
                                <!-- Header -->
                                <div class="flex items-start justify-between gap-3 mb-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                                            <Lightbulb class="w-4 h-4 text-primary" />
                                        </div>
                                        <div>
                                            <p class="text-xs font-semibold text-foreground">
                                                Periode {{ item.period_start }} - {{ item.period_end || 'Sekarang' }}
                                            </p>
                                            <p class="text-[10px] text-muted-foreground">{{ item.creator?.name }}</p>
                                        </div>
                                    </div>
                                    <Badge :variant="item.status === 'active' ? 'success' : 'secondary'" class="text-[10px] shrink-0">
                                        {{ item.status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                                    </Badge>
                                </div>

                                <!-- Visi preview -->
                                <div class="mb-2">
                                    <p class="text-[10px] font-medium text-muted-foreground uppercase tracking-wider mb-0.5">Visi</p>
                                    <p class="text-xs text-foreground line-clamp-2">{{ item.vision }}</p>
                                </div>

                                <!-- Misi count -->
                                <div class="flex items-center gap-1 text-[10px] text-muted-foreground">
                                    <Target class="w-3 h-3" />
                                    {{ item.missions?.length || 0 }} butir misi
                                </div>
                            </div>
                        </Link>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="flex flex-col items-center justify-center py-16 text-center">
                        <div class="w-16 h-16 rounded-2xl bg-muted flex items-center justify-center mb-4">
                            <Lightbulb class="w-8 h-8 text-muted-foreground" />
                        </div>
                        <p class="text-sm font-medium text-foreground mb-1">Belum ada data</p>
                        <p class="text-xs text-muted-foreground">Tambahkan visi & misi organisasi.</p>
                    </div>

                    <!-- Mobile FAB -->
                    <Link
                        v-if="hasPermission('manage_vision_missions')"
                        :href="route('vision-missions.create')"
                        class="fixed bottom-20 right-4 z-40 w-14 h-14 rounded-full bg-primary text-primary-foreground shadow-lg flex items-center justify-center active:scale-95 transition-transform md:hidden"
                    >
                        <Plus class="w-6 h-6" />
                    </Link>
                </div>

                <!-- ========== DESKTOP LAYOUT ========== -->
                <div class="hidden md:block">
                    <div class="bg-card border rounded-xl overflow-hidden">
                        <!-- Filters -->
                        <div class="p-4 border-b">
                            <div class="flex items-center gap-3">
                                <div class="flex-1">
                                    <SearchBar v-model="search" placeholder="Cari visi..." />
                                </div>
                                <div class="w-40">
                                    <FilterDropdown v-model="status" :options="statusOptions" label="Status" />
                                </div>
                            </div>
                            <div v-if="status" class="flex flex-wrap gap-2 mt-3">
                                <button @click="clearStatus" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-primary/10 text-primary text-xs font-medium hover:bg-primary/20 transition-colors">
                                    Status: {{ status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                                    <X class="w-3 h-3" />
                                </button>
                            </div>
                        </div>

                        <!-- Cards Grid -->
                        <div class="p-4">
                            <div v-if="visionMissions.data.length > 0" class="space-y-3">
                                <div
                                    v-for="item in visionMissions.data"
                                    :key="item.id"
                                    class="rounded-xl border bg-card overflow-hidden hover:shadow-md transition-shadow"
                                >
                                    <div class="p-5">
                                        <div class="flex items-start justify-between gap-4">
                                            <!-- Left: Info -->
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-3 mb-3">
                                                    <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center shrink-0">
                                                        <Lightbulb class="w-5 h-5 text-primary" />
                                                    </div>
                                                    <div>
                                                        <div class="flex items-center gap-2">
                                                            <h3 class="text-sm font-semibold text-foreground">
                                                                Periode {{ item.period_start }} - {{ item.period_end || 'Sekarang' }}
                                                            </h3>
                                                            <Badge :variant="item.status === 'active' ? 'success' : 'secondary'">
                                                                {{ item.status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                                                            </Badge>
                                                        </div>
                                                        <div class="flex items-center gap-3 mt-0.5 text-xs text-muted-foreground">
                                                            <span class="flex items-center gap-1">
                                                                <User class="w-3 h-3" />
                                                                {{ item.creator?.name }}
                                                            </span>
                                                            <span class="flex items-center gap-1">
                                                                <Target class="w-3 h-3" />
                                                                {{ item.missions?.length || 0 }} misi
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Visi -->
                                                <div class="ml-[52px]">
                                                    <p class="text-[11px] font-medium text-muted-foreground uppercase tracking-wider mb-1">Visi</p>
                                                    <p class="text-sm text-foreground line-clamp-2">{{ item.vision }}</p>
                                                </div>

                                                <!-- Misi preview -->
                                                <div v-if="item.missions && item.missions.length > 0" class="ml-[52px] mt-3">
                                                    <p class="text-[11px] font-medium text-muted-foreground uppercase tracking-wider mb-1">Misi</p>
                                                    <ol class="text-xs text-muted-foreground space-y-0.5 list-decimal list-inside">
                                                        <li v-for="(m, i) in item.missions.slice(0, 3)" :key="i" class="truncate">{{ m }}</li>
                                                        <li v-if="item.missions.length > 3" class="text-primary text-[11px]">
                                                            +{{ item.missions.length - 3 }} lainnya...
                                                        </li>
                                                    </ol>
                                                </div>
                                            </div>

                                            <!-- Right: Actions -->
                                            <div class="flex items-center gap-1 shrink-0">
                                                <Link
                                                    :href="route('vision-missions.show', item.id)"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-medium text-muted-foreground hover:text-primary hover:bg-primary/5 transition-colors"
                                                >
                                                    <Eye class="w-3.5 h-3.5" />
                                                    Detail
                                                </Link>
                                                <template v-if="hasPermission('manage_vision_missions')">
                                                    <Link
                                                        :href="route('vision-missions.edit', item.id)"
                                                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-medium text-muted-foreground hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-500/10 transition-colors"
                                                    >
                                                        <Pencil class="w-3.5 h-3.5" />
                                                        Edit
                                                    </Link>
                                                    <button
                                                        @click="confirmDelete(item.id)"
                                                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-medium text-muted-foreground hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors"
                                                    >
                                                        <Trash2 class="w-3.5 h-3.5" />
                                                        Hapus
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Empty State -->
                            <div v-else class="flex flex-col items-center justify-center py-16 text-center">
                                <div class="w-16 h-16 rounded-2xl bg-muted flex items-center justify-center mb-4">
                                    <Lightbulb class="w-8 h-8 text-muted-foreground" />
                                </div>
                                <p class="text-sm font-medium text-foreground mb-1">Belum ada data visi & misi</p>
                                <p class="text-xs text-muted-foreground mb-4">Tambahkan visi & misi organisasi.</p>
                                <Button v-if="hasPermission('manage_vision_missions')" size="sm" as-child>
                                    <Link :href="route('vision-missions.create')">
                                        <Plus class="w-4 h-4 mr-1" />
                                        Tambah Visi & Misi
                                    </Link>
                                </Button>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="visionMissions.links && visionMissions.links.length > 3" class="p-4 border-t">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <p class="text-sm text-muted-foreground">
                                    Menampilkan {{ visionMissions.from }} sampai {{ visionMissions.to }} dari {{ visionMissions.total }} hasil
                                </p>
                                <Pagination :links="visionMissions.links" />
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Mobile Filter Sheet -->
        <Sheet v-model:open="filterOpen">
            <SheetContent side="bottom" class="rounded-t-2xl">
                <SheetHeader>
                    <SheetTitle>Filter</SheetTitle>
                    <SheetDescription>Filter data visi & misi</SheetDescription>
                </SheetHeader>
                <div class="py-4 space-y-4">
                    <div>
                        <label class="text-sm font-medium text-foreground mb-1.5 block">Status</label>
                        <select
                            v-model="status"
                            class="block w-full border-input bg-background focus:border-ring focus:ring-ring rounded-md shadow-sm text-sm h-10 px-3"
                        >
                            <option value="">Semua Status</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <Button variant="outline" class="flex-1" @click="resetFilters">Reset</Button>
                    <Button class="flex-1" @click="applyFilter">Terapkan</Button>
                </div>
            </SheetContent>
        </Sheet>
        <DeleteConfirmDialog :open="!!deleteTarget" title="Hapus Visi & Misi" description="Apakah Anda yakin ingin menghapus visi & misi ini? Tindakan ini tidak dapat dibatalkan." @confirm="executeDelete" @cancel="deleteTarget = null" />
    </AuthenticatedLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
