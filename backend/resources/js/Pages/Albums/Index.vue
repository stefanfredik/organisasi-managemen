<script setup>
import { ref, watch } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import SearchBar from "@/Components/SearchBar.vue";
import FilterDropdown from "@/Components/FilterDropdown.vue";

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
    router.get(
        route("albums.index"),
        {
            search: newSearch,
            category: newCategory,
            status: newStatus,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
});

const getCategoryBadge = (category) => {
    const badges = {
        event: "bg-blue-100 text-blue-800",
        routine: "bg-green-100 text-green-800",
        official: "bg-purple-100 text-purple-800",
        other: "bg-gray-100 text-gray-800",
    };
    return badges[category] || "bg-gray-100 text-gray-800";
};

const getCategoryLabel = (category) => {
    const labels = {
        event: "Event",
        routine: "Kegiatan Rutin",
        official: "Dokumentasi Resmi",
        other: "Lainnya",
    };
    return labels[category] || category;
};

const getStatusBadge = (status) => {
    return status === "public"
        ? "bg-green-100 text-green-800"
        : "bg-orange-100 text-orange-800";
};

const getCoverImage = (album) => {
    if (album.cover_image) {
        return `/storage/${album.cover_image}`;
    }
    // Use first photo as cover if no cover image
    if (album.photos && album.photos.length > 0) {
        return `/storage/${album.photos[0].file_path}`;
    }
    // Default placeholder
    return "/images/album-placeholder.png";
};
</script>

<template>
    <Head title="Manajemen Album" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Manajemen Album
                </h2>
                <Link
                    :href="route('albums.create')"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    <svg
                        class="w-4 h-4 mr-1"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 4v16m8-8H4"
                        />
                    </svg>
                    Buat Album
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <!-- Filters -->
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex gap-4">
                            <div class="w-1/2">
                                <SearchBar
                                    v-model="search"
                                    placeholder="Cari nama album..."
                                />
                            </div>
                            <div class="w-1/4">
                                <FilterDropdown
                                    v-model="category"
                                    :options="categoryOptions"
                                    label="Semua Kategori"
                                />
                            </div>
                            <div class="w-1/4">
                                <FilterDropdown
                                    v-model="status"
                                    :options="statusOptions"
                                    label="Semua Status"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Album Grid -->
                    <div class="p-6">
                        <div
                            v-if="albums.data.length > 0"
                            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
                        >
                            <div
                                v-for="album in albums.data"
                                :key="album.id"
                                class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-200"
                            >
                                <!-- Cover Image -->
                                <Link
                                    :href="route('albums.show', album)"
                                    class="block"
                                >
                                    <div class="relative h-48 bg-gray-200">
                                        <img
                                            :src="getCoverImage(album)"
                                            :alt="album.name"
                                            class="w-full h-full object-cover"
                                            @error="
                                                $event.target.src =
                                                    '/images/album-placeholder.png'
                                            "
                                        />
                                        <div
                                            class="absolute top-2 right-2 flex gap-2"
                                        >
                                            <span
                                                :class="
                                                    getCategoryBadge(
                                                        album.category,
                                                    )
                                                "
                                                class="px-2 py-1 text-xs rounded-full font-semibold"
                                            >
                                                {{
                                                    getCategoryLabel(
                                                        album.category,
                                                    )
                                                }}
                                            </span>
                                            <span
                                                :class="
                                                    getStatusBadge(album.status)
                                                "
                                                class="px-2 py-1 text-xs rounded-full font-semibold"
                                            >
                                                {{
                                                    album.status === "public"
                                                        ? "Publik"
                                                        : "Privat"
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </Link>

                                <!-- Album Info -->
                                <div class="p-4">
                                    <Link :href="route('albums.show', album)">
                                        <h3
                                            class="text-lg font-semibold text-gray-900 hover:text-indigo-600"
                                        >
                                            {{ album.name }}
                                        </h3>
                                    </Link>
                                    <p
                                        v-if="album.description"
                                        class="mt-1 text-sm text-gray-600 line-clamp-2"
                                    >
                                        {{ album.description }}
                                    </p>
                                    <div
                                        class="mt-2 flex items-center text-sm text-gray-500"
                                    >
                                        <svg
                                            class="w-4 h-4 mr-1"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                            />
                                        </svg>
                                        {{ album.photos_count || 0 }} foto
                                    </div>
                                    <div
                                        v-if="album.event"
                                        class="mt-1 text-sm text-gray-500"
                                    >
                                        <span class="font-medium">Event:</span>
                                        {{ album.event.name }}
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div
                                    class="px-4 py-3 bg-gray-50 border-t border-gray-200 flex justify-end gap-2"
                                >
                                    <Link
                                        :href="route('albums.show', album)"
                                        class="text-sm text-indigo-600 hover:text-indigo-900 font-medium"
                                    >
                                        Detail
                                    </Link>
                                    <Link
                                        :href="route('albums.edit', album)"
                                        class="text-sm text-orange-600 hover:text-orange-900 font-medium"
                                    >
                                        Edit
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="text-center py-12">
                            <svg
                                class="mx-auto h-12 w-12 text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">
                                Tidak ada album
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Mulai dengan membuat album baru.
                            </p>
                            <div class="mt-6">
                                <Link
                                    :href="route('albums.create')"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
                                >
                                    <svg
                                        class="w-4 h-4 mr-1"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 4v16m8-8H4"
                                        />
                                    </svg>
                                    Buat Album
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="albums.data.length > 0"
                        class="p-6 border-t border-gray-200"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-700">
                                Menampilkan {{ albums.from }} sampai
                                {{ albums.to }} dari {{ albums.total }} hasil
                            </span>
                            <div class="flex gap-2">
                                <Link
                                    v-if="albums.prev_page_url"
                                    :href="albums.prev_page_url"
                                    class="px-3 py-1 border rounded hover:bg-gray-100"
                                >
                                    Sebelumnya
                                </Link>
                                <Link
                                    v-if="albums.next_page_url"
                                    :href="albums.next_page_url"
                                    class="px-3 py-1 border rounded hover:bg-gray-100"
                                >
                                    Selanjutnya
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
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
