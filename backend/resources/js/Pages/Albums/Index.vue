<script setup>
import { ref, watch } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import SearchBar from "@/Components/SearchBar.vue";
import FilterDropdown from "@/Components/FilterDropdown.vue";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Card } from "@/components/ui/card";
import { Plus, ImageIcon } from "lucide-vue-next";

const props = defineProps({
    albums: Object,
    filters: Object,
});

const search = ref(props.filters.search || "");
const category = ref(props.filters.category || "");
const status = ref(props.filters.status || "");

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

watch([search, category, status], ([newSearch, newCategory, newStatus]) => {
    router.get(route("albums.index"), { search: newSearch, category: newCategory, status: newStatus }, { preserveState: true, replace: true });
});

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
    return "/images/album-placeholder.png";
};
</script>

<template>
    <Head title="Manajemen Album" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold leading-tight text-foreground">Album</h2>
                <Button v-if="hasPermission('manage_albums')" size="sm" as-child>
                    <Link :href="route('albums.create')">
                        <Plus class="w-4 h-4 mr-1" />
                        <span class="hidden sm:inline">Buat Album</span>
                    </Link>
                </Button>
            </div>
        </template>

        <div class="py-4 sm:py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <Card>
                    <!-- Compact Filters -->
                    <div class="p-3 sm:p-4 border-b">
                        <div class="flex flex-col sm:flex-row gap-2">
                            <div class="flex-1">
                                <SearchBar v-model="search" placeholder="Cari album..." />
                            </div>
                            <div class="flex gap-2">
                                <FilterDropdown v-model="category" :options="categoryOptions" label="Kategori" />
                                <FilterDropdown v-model="status" :options="statusOptions" label="Status" />
                            </div>
                        </div>
                    </div>

                    <!-- Album Grid -->
                    <div class="p-3 sm:p-4">
                        <div v-if="albums.data.length > 0" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div
                                v-for="album in albums.data"
                                :key="album.id"
                                class="border rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-200 bg-card"
                            >
                                <Link :href="route('albums.show', album)" class="block">
                                    <div class="relative h-48 bg-muted">
                                        <img
                                            :src="getCoverImage(album)"
                                            :alt="album.name"
                                            class="w-full h-full object-cover"
                                            @error="$event.target.src = '/images/album-placeholder.png'"
                                        />
                                        <div class="absolute top-2 right-2 flex gap-2">
                                            <Badge :variant="getCategoryVariant(album.category)">
                                                {{ getCategoryLabel(album.category) }}
                                            </Badge>
                                            <Badge :variant="album.status === 'public' ? 'success' : 'warning'">
                                                {{ album.status === "public" ? "Publik" : "Privat" }}
                                            </Badge>
                                        </div>
                                    </div>
                                </Link>

                                <div class="p-4">
                                    <Link :href="route('albums.show', album)">
                                        <h3 class="text-lg font-semibold text-foreground hover:text-primary">
                                            {{ album.name }}
                                        </h3>
                                    </Link>
                                    <p v-if="album.description" class="mt-1 text-sm text-muted-foreground line-clamp-2">
                                        {{ album.description }}
                                    </p>
                                    <div class="mt-2 flex items-center text-sm text-muted-foreground">
                                        <ImageIcon class="w-4 h-4 mr-1" />
                                        {{ album.photos_count || 0 }} foto
                                    </div>
                                    <div v-if="album.event" class="mt-1 text-sm text-muted-foreground">
                                        <span class="font-medium">Event:</span> {{ album.event.name }}
                                    </div>
                                </div>

                                <div class="px-4 py-3 bg-muted/30 border-t flex justify-end gap-2">
                                    <Button variant="ghost" size="sm" as-child>
                                        <Link :href="route('albums.show', album)">Detail</Link>
                                    </Button>
                                    <Button v-if="hasPermission('manage_albums')" variant="ghost" size="sm" as-child>
                                        <Link :href="route('albums.edit', album)">Edit</Link>
                                    </Button>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="text-center py-12">
                            <ImageIcon class="mx-auto h-12 w-12 text-muted-foreground/30" />
                            <h3 class="mt-2 text-sm font-medium text-foreground">Tidak ada album</h3>
                            <p class="mt-1 text-sm text-muted-foreground">Mulai dengan membuat album baru.</p>
                            <div class="mt-6">
                                <Button v-if="hasPermission('manage_albums')" as-child>
                                    <Link :href="route('albums.create')">
                                        <Plus class="w-4 h-4 mr-1" />
                                        Buat Album
                                    </Link>
                                </Button>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="albums.data.length > 0" class="p-6 border-t">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">
                                Menampilkan {{ albums.from }} sampai {{ albums.to }} dari {{ albums.total }} hasil
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
                </Card>
            </div>
        </div>
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
