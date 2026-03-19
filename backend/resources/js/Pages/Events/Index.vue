<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SearchBar from '@/Components/SearchBar.vue';
import FilterDropdown from '@/Components/FilterDropdown.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import DeleteConfirmDialog from '@/Components/DeleteConfirmDialog.vue';
import { Plus, Search, Calendar, MapPin, MoreVertical, Eye, Pencil, Trash2, SlidersHorizontal, Clock, User } from 'lucide-vue-next';
import { useToast } from '@/composables/useToast';

const toast = useToast();

const props = defineProps({
    events: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const deleteTarget = ref(null);

const statusOptions = [
    { value: 'draft', label: 'Draft' },
    { value: 'published', label: 'Published' },
    { value: 'ongoing', label: 'Berlangsung' },
    { value: 'completed', label: 'Selesai' },
    { value: 'cancelled', label: 'Dibatalkan' },
];

watch([search, status], ([newSearch, newStatus]) => {
    router.get(route('events.index'), { search: newSearch, status: newStatus }, { preserveState: true, replace: true });
});

const formatDate = (date) => new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });

const formatTime = (date) => new Date(date).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

const getStatusBadge = (s) => {
    const map = { draft: 'secondary', published: 'default', ongoing: 'warning', completed: 'success', cancelled: 'destructive' };
    return map[s] || 'secondary';
};

const getStatusLabel = (s) => {
    const map = { draft: 'Draft', published: 'Published', ongoing: 'Berlangsung', completed: 'Selesai', cancelled: 'Dibatalkan' };
    return map[s] || s;
};

const confirmDelete = (event) => {
    deleteTarget.value = event;
};

const doDelete = () => {
    if (deleteTarget.value) {
        router.delete(`/events/${deleteTarget.value.id}`, {
            onError: (errors) => toast.error(Object.values(errors).flat().join(', ') || 'Gagal menghapus kegiatan.'),
            onFinish: () => (deleteTarget.value = null),
        });
    }
};
</script>

<template>
    <Head title="Kegiatan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold leading-tight text-foreground">Kegiatan</h2>
                <Button v-if="hasPermission('manage_events')" size="sm" as-child>
                    <Link :href="route('events.create')">
                        <Plus class="w-4 h-4 mr-1" />
                        <span class="hidden sm:inline">Buat Kegiatan</span>
                    </Link>
                </Button>
            </div>
        </template>

        <div class="py-4 sm:py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <!-- Mobile: Card-based list -->
                <div class="md:hidden">
                    <!-- Mobile Search & Filter -->
                    <div class="flex items-center gap-2 mb-3">
                        <div class="relative flex-1">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Cari kegiatan..."
                                class="w-full pl-9 pr-3 py-2.5 bg-card border rounded-xl text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary"
                            />
                        </div>
                        <div class="shrink-0 w-28">
                            <FilterDropdown v-model="status" :options="statusOptions" label="Status" />
                        </div>
                    </div>

                    <!-- Event Cards -->
                    <div class="space-y-3">
                        <div
                            v-for="event in events.data"
                            :key="event.id"
                            class="bg-card border rounded-xl overflow-hidden active:bg-muted/30 transition-colors"
                        >
                            <!-- Banner if exists -->
                            <Link :href="route('events.show', event)" v-if="event.banner_url" class="block">
                                <div class="aspect-[21/9] overflow-hidden">
                                    <img :src="event.banner_url" :alt="event.name" class="w-full h-full object-cover" />
                                </div>
                            </Link>

                            <div class="p-4">
                                <div class="flex items-start gap-3">
                                    <!-- Date badge -->
                                    <div class="shrink-0 w-12 h-12 bg-primary/10 rounded-xl flex flex-col items-center justify-center">
                                        <span class="text-primary font-bold text-sm leading-none">{{ new Date(event.start_date).getDate() }}</span>
                                        <span class="text-primary/70 text-[10px] font-semibold uppercase">{{ new Date(event.start_date).toLocaleDateString('id-ID', { month: 'short' }) }}</span>
                                    </div>

                                    <!-- Info -->
                                    <Link :href="route('events.show', event)" class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-foreground line-clamp-2">{{ event.name }}</p>
                                        <div class="flex items-center gap-3 mt-1.5 flex-wrap">
                                            <span class="text-xs text-muted-foreground flex items-center gap-1">
                                                <Clock class="w-3 h-3" />
                                                {{ formatTime(event.start_date) }}
                                            </span>
                                            <span v-if="event.location" class="text-xs text-muted-foreground flex items-center gap-1">
                                                <MapPin class="w-3 h-3" />
                                                <span class="truncate max-w-[120px]">{{ event.location }}</span>
                                            </span>
                                        </div>
                                    </Link>

                                    <!-- Status + Menu -->
                                    <div class="flex items-center gap-1 shrink-0">
                                        <Badge :variant="getStatusBadge(event.status)" class="text-[10px] px-1.5">
                                            {{ getStatusLabel(event.status) }}
                                        </Badge>
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <button class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-muted transition-colors">
                                                    <MoreVertical class="w-4 h-4 text-muted-foreground" />
                                                </button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuItem @click="router.visit(route('events.show', event))">
                                                    <Eye class="h-4 w-4 mr-2" />
                                                    Lihat Detail
                                                </DropdownMenuItem>
                                                <DropdownMenuItem v-if="hasPermission('manage_events')" @click="router.visit(route('events.edit', event))">
                                                    <Pencil class="h-4 w-4 mr-2" />
                                                    Ubah
                                                </DropdownMenuItem>
                                                <DropdownMenuItem v-if="hasPermission('manage_events')" @click="confirmDelete(event)" class="text-destructive">
                                                    <Trash2 class="h-4 w-4 mr-2" />
                                                    Hapus
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </div>

                                <!-- PIC -->
                                <div v-if="event.pic" class="mt-3 pt-3 border-t flex items-center gap-2">
                                    <User class="w-3 h-3 text-muted-foreground" />
                                    <span class="text-xs text-muted-foreground">PIC: <span class="font-medium text-foreground">{{ event.pic.full_name }}</span></span>
                                </div>
                            </div>
                        </div>

                        <!-- Empty -->
                        <div v-if="events.data.length === 0" class="bg-card border rounded-xl px-4 py-12 text-center">
                            <Calendar class="mx-auto h-10 w-10 text-muted-foreground/40 mb-3" />
                            <p class="text-sm text-muted-foreground">Tidak ada data kegiatan.</p>
                        </div>
                    </div>

                    <!-- Mobile Pagination -->
                    <div v-if="events.data.length > 0" class="mt-4 flex items-center justify-between">
                        <span class="text-xs text-muted-foreground">{{ events.from }}-{{ events.to }} / {{ events.total }}</span>
                        <div class="flex gap-2">
                            <Button v-if="events.prev_page_url" variant="outline" size="sm" class="h-9 text-xs" as-child>
                                <Link :href="events.prev_page_url">Prev</Link>
                            </Button>
                            <Button v-if="events.next_page_url" variant="outline" size="sm" class="h-9 text-xs" as-child>
                                <Link :href="events.next_page_url">Next</Link>
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Desktop: Table -->
                <div class="hidden md:block bg-card border rounded-xl overflow-hidden">
                    <div class="p-3 sm:p-4 border-b flex flex-col sm:flex-row gap-2">
                        <div class="flex-1 sm:flex-[2]">
                            <SearchBar v-model="search" placeholder="Cari kegiatan..." />
                        </div>
                        <div class="sm:w-40">
                            <FilterDropdown v-model="status" :options="statusOptions" label="Status" />
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-border">
                            <thead class="bg-muted/50">
                                <tr>
                                    <th class="px-4 py-2.5 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Kegiatan</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Waktu</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider hidden lg:table-cell">Lokasi</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider hidden lg:table-cell">PIC</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-2.5 text-right text-xs font-medium text-muted-foreground uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="event in events.data" :key="event.id" class="hover:bg-muted/30">
                                    <td class="px-4 py-3">
                                        <div class="text-sm font-medium text-foreground">{{ event.name }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-muted-foreground">{{ formatDate(event.start_date) }}</td>
                                    <td class="px-4 py-3 text-sm text-muted-foreground hidden lg:table-cell">{{ event.location || '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-muted-foreground hidden lg:table-cell">{{ event.pic ? event.pic.full_name : '-' }}</td>
                                    <td class="px-4 py-3">
                                        <Badge :variant="getStatusBadge(event.status)" class="text-xs">{{ getStatusLabel(event.status) }}</Badge>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex justify-end gap-1">
                                            <Button variant="ghost" size="sm" class="h-7 px-2 text-xs" as-child>
                                                <Link :href="route('events.show', event)">Detail</Link>
                                            </Button>
                                            <Button v-if="hasPermission('manage_events')" variant="ghost" size="sm" class="h-7 px-2 text-xs" as-child>
                                                <Link :href="route('events.edit', event)">Edit</Link>
                                            </Button>
                                            <Button v-if="hasPermission('manage_events')" variant="ghost" size="sm" class="h-7 px-2 text-xs text-destructive" @click="confirmDelete(event)">
                                                Hapus
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="events.data.length === 0">
                                    <td colspan="6" class="px-4 py-8 text-center text-sm text-muted-foreground">Tidak ada data kegiatan.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="events.data.length > 0" class="p-3 sm:p-4 border-t flex items-center justify-between text-sm">
                        <span class="text-xs text-muted-foreground">{{ events.from }}-{{ events.to }} / {{ events.total }}</span>
                        <div class="flex gap-2">
                            <Button v-if="events.prev_page_url" variant="outline" size="sm" class="h-7 text-xs" as-child><Link :href="events.prev_page_url">Prev</Link></Button>
                            <Button v-if="events.next_page_url" variant="outline" size="sm" class="h-7 text-xs" as-child><Link :href="events.next_page_url">Next</Link></Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation -->
        <DeleteConfirmDialog
            :open="!!deleteTarget"
            title="Hapus Kegiatan"
            :description="`Apakah Anda yakin ingin menghapus kegiatan ${deleteTarget?.name}? Tindakan ini tidak dapat dibatalkan.`"
            @confirm="doDelete"
            @cancel="deleteTarget = null"
        />
    </AuthenticatedLayout>
</template>
