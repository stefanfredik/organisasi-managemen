<script setup>
import { ref, watch, computed } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import SearchBar from "@/Components/SearchBar.vue";
import FilterDropdown from "@/Components/FilterDropdown.vue";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
    Sheet, SheetContent, SheetHeader, SheetTitle, SheetDescription,
} from "@/components/ui/sheet";
import {
    Plus, ImageIcon, SlidersHorizontal, X, Search, Eye, Pencil,
} from "lucide-vue-next";

const props = defineProps({
    albums: Object,
    filters: Object,
});

const search = ref(props.filters.search || "");
const category = ref(props.filters.category || "");
const status = ref(props.filters.status || "");
const filterOpen = ref(false);

const categoryOptions = [
    { value: "event", label: "Event" },
    { value: "routine", label: "Kegiatan Rutin" },
    { value: "official", label: "Dokumentasi Resmi" },
    { value: "other", label: "Lainnya" },
];

const statusOptions = [
    { value: "public", label: "Publik" },
    { value: "private", label: "Privat" },
];

const activeFilterCount = computed(() => {
    let count = 0;
    if (category.value) count++;
    if (status.value) count++;
    return count;
});

watch([search, category, status], ([newSearch, newCategory, newStatus]) => {
    router.get(route("albums.index"), { search: newSearch, category: newCategory, status: newStatus }, { preserveState: true, replace: true });
});

const applyFilter = () => {
    router.get(
        route("albums.index"),
        { search: search.value, category: category.value, status: status.value },
        { preserveState: true, replace: true },
    );
    filterOpen.value = false;
};

const resetFilters = () => {
    category.value = "";
    status.value = "";
    applyFilter();
};

const clearCategory = () => { category.value = ""; };
const clearStatus = () => { status.value = ""; };

const getCategoryVariant = (cat) => {
    const map = { event: 'default', routine: 'success', official: 'info', other: 'secondary' };
    return map[cat] || 'secondary';
};

const getCategoryLabel = (cat) => {
    const labels = { event: "Event", routine: "Kegiatan Rutin", official: "Dokumentasi Resmi", other: "Lainnya" };
    return labels[cat] || cat;
};

const getCoverImage = (album) => {
    if (album.cover_image) return `/storage/${album.cover_image}`;
    if (album.photos && album.photos.length > 0) return `/storage/${album.photos[0].file_path}`;
    return null;
};
</script>

<template>
    <Head title="Manajemen Album" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold leading-tight text-foreground">Album</h2>
                <Button v-if="hasPermission('manage_albums')" size="sm" as-child class="hidden md:inline-flex">
                    <Link :href="route('albums.create')">
                        <Plus class="w-4 h-4 mr-1" />
                        Buat Album
                    </Link>
                </Button>
            </div>
        </template>

        <div class="py-4 sm:py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <!-- ========== MOBILE LAYOUT ========== -->
                <div class="md:hidden space-y-4">
                    <!-- Search + Filter -->
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none" />
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Cari album..."
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
                    <div v-if="category || status" class="flex flex-wrap gap-2">
                        <button v-if="category" @click="clearCategory" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-primary/10 text-primary text-xs font-medium">
                            {{ getCategoryLabel(category) }}
                            <X class="w-3 h-3" />
                        </button>
                        <button v-if="status" @click="clearStatus" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-primary/10 text-primary text-xs font-medium">
                            {{ status === 'public' ? 'Publik' : 'Privat' }}
                            <X class="w-3 h-3" />
                        </button>
                    </div>

                    <!-- Album count -->
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-muted-foreground">{{ albums.total || 0 }} album</span>
                    </div>

                    <!-- Mobile Album Cards -->
                    <div v-if="albums.data.length > 0" class="grid grid-cols-2 gap-3">
                        <Link
                            v-for="album in albums.data"
                            :key="album.id"
                            :href="route('albums.show', album)"
                            class="block rounded-xl border bg-card overflow-hidden hover:shadow-md transition-shadow"
                        >
                            <div class="relative aspect-[4/3] bg-muted">
                                <img
                                    v-if="getCoverImage(album)"
                                    :src="getCoverImage(album)"
                                    :alt="album.name"
                                    class="w-full h-full object-cover"
                                    @error="$event.target.style.display = 'none'"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center">
                                    <ImageIcon class="w-8 h-8 text-muted-foreground/30" />
                                </div>
                                <div class="absolute top-1.5 right-1.5">
                                    <Badge :variant="album.status === 'public' ? 'success' : 'warning'" class="text-[10px] px-1.5 py-0">
                                        {{ album.status === "public" ? "Publik" : "Privat" }}
                                    </Badge>
                                </div>
                            </div>
                            <div class="p-2.5">
                                <h3 class="text-xs font-semibold text-foreground truncate">{{ album.name }}</h3>
                                <div class="flex items-center gap-1 mt-1 text-[10px] text-muted-foreground">
                                    <ImageIcon class="w-3 h-3" />
                                    {{ album.photos_count || 0 }} foto
                                </div>
                                <Badge :variant="getCategoryVariant(album.category)" class="mt-1.5 text-[10px] px-1.5 py-0">
                                    {{ getCategoryLabel(album.category) }}
                                </Badge>
                            </div>
                        </Link>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="flex flex-col items-center justify-center py-16 text-center">
                        <div class="w-16 h-16 rounded-2xl bg-muted flex items-center justify-center mb-4">
                            <ImageIcon class="w-8 h-8 text-muted-foreground" />
                        </div>
                        <p class="text-sm font-medium text-foreground mb-1">Belum ada album</p>
                        <p class="text-xs text-muted-foreground">Mulai dengan membuat album baru.</p>
                    </div>

                    <!-- Mobile Pagination -->
                    <div v-if="albums.data.length > 0" class="flex items-center justify-between pt-2">
                        <span class="text-xs text-muted-foreground">
                            {{ albums.from }}–{{ albums.to }} dari {{ albums.total }}
                        </span>
                        <div class="flex gap-2">
                            <Button v-if="albums.prev_page_url" variant="outline" size="sm" as-child>
                                <Link :href="albums.prev_page_url">Prev</Link>
                            </Button>
                            <Button v-if="albums.next_page_url" variant="outline" size="sm" as-child>
                                <Link :href="albums.next_page_url">Next</Link>
                            </Button>
                        </div>
                    </div>

                    <!-- Mobile FAB -->
                    <Link
                        v-if="hasPermission('manage_albums')"
                        :href="route('albums.create')"
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
                                    <SearchBar v-model="search" placeholder="Cari album..." />
                                </div>
                                <div class="w-40">
                                    <FilterDropdown v-model="category" :options="categoryOptions" label="Kategori" />
                                </div>
                                <div class="w-36">
                                    <FilterDropdown v-model="status" :options="statusOptions" label="Status" />
                                </div>
                            </div>

                            <!-- Active filter chips -->
                            <div v-if="category || status" class="flex flex-wrap gap-2 mt-3">
                                <button v-if="category" @click="clearCategory" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-primary/10 text-primary text-xs font-medium hover:bg-primary/20 transition-colors">
                                    Kategori: {{ getCategoryLabel(category) }}
                                    <X class="w-3 h-3" />
                                </button>
                                <button v-if="status" @click="clearStatus" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-primary/10 text-primary text-xs font-medium hover:bg-primary/20 transition-colors">
                                    Status: {{ status === 'public' ? 'Publik' : 'Privat' }}
                                    <X class="w-3 h-3" />
                                </button>
                            </div>
                        </div>

                        <!-- Album Grid -->
                        <div class="p-4">
                            <div v-if="albums.data.length > 0" class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                <div
                                    v-for="album in albums.data"
                                    :key="album.id"
                                    class="group rounded-xl border overflow-hidden bg-card hover:shadow-lg transition-all duration-200"
                                >
                                    <Link :href="route('albums.show', album)" class="block">
                                        <div class="relative aspect-[4/3] bg-muted overflow-hidden">
                                            <img
                                                v-if="getCoverImage(album)"
                                                :src="getCoverImage(album)"
                                                :alt="album.name"
                                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                                @error="$event.target.style.display = 'none'"
                                            />
                                            <div v-else class="w-full h-full flex items-center justify-center">
                                                <ImageIcon class="w-12 h-12 text-muted-foreground/20" />
                                            </div>
                                            <!-- Overlay badges -->
                                            <div class="absolute top-2 left-2 flex flex-wrap gap-1.5">
                                                <Badge :variant="getCategoryVariant(album.category)" class="text-[10px]">
                                                    {{ getCategoryLabel(album.category) }}
                                                </Badge>
                                            </div>
                                            <div class="absolute top-2 right-2">
                                                <Badge :variant="album.status === 'public' ? 'success' : 'warning'" class="text-[10px]">
                                                    {{ album.status === "public" ? "Publik" : "Privat" }}
                                                </Badge>
                                            </div>
                                            <!-- Photo count overlay -->
                                            <div class="absolute bottom-2 right-2 bg-black/60 backdrop-blur-sm rounded-md px-2 py-0.5 flex items-center gap-1">
                                                <ImageIcon class="w-3 h-3 text-white" />
                                                <span class="text-[10px] text-white font-medium">{{ album.photos_count || 0 }}</span>
                                            </div>
                                        </div>
                                    </Link>

                                    <div class="p-3">
                                        <Link :href="route('albums.show', album)">
                                            <h3 class="text-sm font-semibold text-foreground truncate hover:text-primary transition-colors">
                                                {{ album.name }}
                                            </h3>
                                        </Link>
                                        <p v-if="album.description" class="mt-0.5 text-xs text-muted-foreground line-clamp-2">
                                            {{ album.description }}
                                        </p>
                                        <div v-if="album.event" class="mt-1.5 text-[11px] text-muted-foreground truncate">
                                            Event: <span class="font-medium">{{ album.event.name }}</span>
                                        </div>
                                        <!-- Actions -->
                                        <div v-if="hasPermission('manage_albums')" class="flex items-center gap-1 mt-2 pt-2 border-t">
                                            <Link
                                                :href="route('albums.show', album)"
                                                class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[11px] text-muted-foreground hover:text-primary hover:bg-primary/5 transition-colors"
                                            >
                                                <Eye class="w-3 h-3" />
                                                Detail
                                            </Link>
                                            <Link
                                                :href="route('albums.edit', album)"
                                                class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[11px] text-muted-foreground hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-500/10 transition-colors"
                                            >
                                                <Pencil class="w-3 h-3" />
                                                Edit
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Empty State -->
                            <div v-else class="flex flex-col items-center justify-center py-16 text-center">
                                <div class="w-16 h-16 rounded-2xl bg-muted flex items-center justify-center mb-4">
                                    <ImageIcon class="w-8 h-8 text-muted-foreground" />
                                </div>
                                <p class="text-sm font-medium text-foreground mb-1">Belum ada album</p>
                                <p class="text-xs text-muted-foreground mb-4">Mulai dengan membuat album baru.</p>
                                <Button v-if="hasPermission('manage_albums')" size="sm" as-child>
                                    <Link :href="route('albums.create')">
                                        <Plus class="w-4 h-4 mr-1" />
                                        Buat Album
                                    </Link>
                                </Button>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="albums.data.length > 0" class="p-4 border-t">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">
                                    Menampilkan {{ albums.from }} sampai {{ albums.to }} dari {{ albums.total }} album
                                </span>
                                <div class="flex gap-2">
                                    <Button v-if="albums.prev_page_url" variant="outline" size="sm" as-child>
                                        <Link :href="albums.prev_page_url">Sebelumnya</Link>
                                    </Button>
                                    <Button v-if="albums.next_page_url" variant="outline" size="sm" as-child>
                                        <Link :href="albums.next_page_url">Selanjutnya</Link>
                                    </Button>
                                </div>
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
                    <SheetDescription>Filter data album</SheetDescription>
                </SheetHeader>
                <div class="py-4 space-y-4">
                    <div>
                        <label class="text-sm font-medium text-foreground mb-1.5 block">Kategori</label>
                        <select
                            v-model="category"
                            class="block w-full border-input bg-background focus:border-ring focus:ring-ring rounded-md shadow-sm text-sm h-10 px-3"
                        >
                            <option value="">Semua Kategori</option>
                            <option value="event">Event</option>
                            <option value="routine">Kegiatan Rutin</option>
                            <option value="official">Dokumentasi Resmi</option>
                            <option value="other">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-foreground mb-1.5 block">Status</label>
                        <select
                            v-model="status"
                            class="block w-full border-input bg-background focus:border-ring focus:ring-ring rounded-md shadow-sm text-sm h-10 px-3"
                        >
                            <option value="">Semua Status</option>
                            <option value="public">Publik</option>
                            <option value="private">Privat</option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <Button variant="outline" class="flex-1" @click="resetFilters">Reset</Button>
                    <Button class="flex-1" @click="applyFilter">Terapkan</Button>
                </div>
            </SheetContent>
        </Sheet>
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
