<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SearchBar from '@/Components/SearchBar.vue';
import FilterDropdown from '@/Components/FilterDropdown.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Plus, Heart, CalendarDays, Eye, ChevronLeft, ChevronRight, Globe,
    Target, TrendingUp, Inbox, MoreVertical, Pencil, Trash2,
} from 'lucide-vue-next';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';

const props = defineProps({
    donations: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');

const statusOptions = [
    { value: 'active', label: 'Aktif' },
    { value: 'completed', label: 'Selesai' },
    { value: 'cancelled', label: 'Dibatalkan' },
];

watch([search, status], ([newSearch, newStatus]) => {
    router.get(route('donations.index'), {
        search: newSearch,
        status: newStatus,
    }, {
        preserveState: true,
        replace: true,
    });
});

const getStatusConfig = (status) => {
    const map = {
        active: { label: 'Aktif', class: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' },
        completed: { label: 'Selesai', class: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' },
        cancelled: { label: 'Dibatalkan', class: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' },
    };
    return map[status] || { label: status, class: 'bg-muted text-foreground' };
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(amount);
};

const calculateProgress = (collected, target) => {
    if (!target || target <= 0) return 0;
    return Math.min(Math.round((collected / target) * 100), 100);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

const getProgressColor = (pct) => {
    if (pct >= 100) return 'bg-green-500';
    if (pct >= 50) return 'bg-primary';
    return 'bg-amber-500';
};

const deleteTarget = ref(null);

const confirmDelete = () => {
    if (deleteTarget.value) {
        router.delete(route('donations.destroy', deleteTarget.value), {
            onFinish: () => deleteTarget.value = null,
        });
    }
};
</script>

<template>
    <Head title="Manajemen Donasi" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold leading-tight text-foreground">Donasi</h2>
                <div class="flex gap-2">
                    <Button v-if="$page.props.auth.user.role !== 'anggota'" variant="outline" size="sm" as-child>
                        <Link :href="route('donations.report')">
                            <TrendingUp class="w-4 h-4 mr-1" />
                            <span class="hidden sm:inline">Laporan</span>
                        </Link>
                    </Button>
                    <Button v-if="$page.props.auth.user.role !== 'anggota'" size="sm" as-child>
                        <Link :href="route('donations.create')">
                            <Plus class="w-4 h-4 mr-1" />
                            <span class="hidden sm:inline">Buat Program</span>
                        </Link>
                    </Button>
                </div>
            </div>
        </template>

        <div class="py-4 sm:py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-4">

                <!-- Filters -->
                <div class="flex flex-col sm:flex-row gap-2">
                    <div class="flex-1">
                        <SearchBar v-model="search" placeholder="Cari program donasi..." />
                    </div>
                    <div class="sm:w-36">
                        <FilterDropdown v-model="status" :options="statusOptions" label="Status" />
                    </div>
                </div>

                <!-- Mobile List View -->
                <div class="sm:hidden space-y-2">
                    <div
                        v-for="donation in donations.data"
                        :key="'m-' + donation.id"
                        class="bg-card rounded-lg border overflow-hidden cursor-pointer active:bg-muted/50 transition-colors"
                        @click="router.visit(route('donations.show', donation))"
                    >
                        <div class="flex items-center gap-3 p-3">
                            <!-- Left color indicator -->
                            <div
                                class="w-1 self-stretch rounded-full shrink-0"
                                :class="donation.status === 'active' ? 'bg-green-500' : donation.status === 'completed' ? 'bg-blue-500' : 'bg-muted-foreground/30'"
                            />

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5 mb-0.5">
                                    <span
                                        :class="['px-1.5 py-px rounded-full text-[9px] font-semibold', getStatusConfig(donation.status).class]"
                                    >
                                        {{ getStatusConfig(donation.status).label }}
                                    </span>
                                    <span v-if="donation.is_public" class="text-[10px] text-muted-foreground flex items-center gap-0.5">
                                        <Globe class="w-2.5 h-2.5" />
                                        Publik
                                    </span>
                                </div>
                                <h3 class="text-sm font-semibold text-foreground truncate">{{ donation.program_name }}</h3>
                                <div class="flex items-center gap-2 mt-1">
                                    <div class="flex-1 bg-muted rounded-full h-1.5 overflow-hidden">
                                        <div
                                            :class="['h-full rounded-full', getProgressColor(calculateProgress(donation.collected_amount, donation.target_amount))]"
                                            :style="{ width: calculateProgress(donation.collected_amount, donation.target_amount) + '%' }"
                                        />
                                    </div>
                                    <span class="text-[10px] font-bold text-primary shrink-0">
                                        {{ calculateProgress(donation.collected_amount, donation.target_amount) }}%
                                    </span>
                                </div>
                                <div class="flex items-center justify-between mt-1">
                                    <span class="text-[11px] text-muted-foreground tabular-nums">{{ formatCurrency(donation.collected_amount) }} / {{ formatCurrency(donation.target_amount) }}</span>
                                </div>
                            </div>

                            <!-- Action menu -->
                            <DropdownMenu v-if="$page.props.auth.user.role !== 'anggota'">
                                <DropdownMenuTrigger as-child @click.stop>
                                    <Button variant="ghost" size="sm" class="h-7 w-7 p-0 shrink-0">
                                        <MoreVertical class="w-4 h-4" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuItem @click="router.visit(route('donations.edit', donation))">
                                        <Pencil class="w-4 h-4 mr-2" />
                                        Edit
                                    </DropdownMenuItem>
                                    <DropdownMenuItem class="text-destructive" @click="deleteTarget = donation">
                                        <Trash2 class="w-4 h-4 mr-2" />
                                        Hapus
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>
                </div>

                <!-- Desktop Grid Cards -->
                <div class="hidden sm:grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <div
                        v-for="donation in donations.data"
                        :key="'d-' + donation.id"
                        class="group bg-card rounded-xl border overflow-hidden hover:shadow-md hover:border-primary/20 transition-all duration-200 flex flex-col cursor-pointer"
                        @click="router.visit(route('donations.show', donation))"
                    >
                        <!-- Color accent -->
                        <div
                            class="h-1 w-full"
                            :class="donation.status === 'active' ? 'bg-green-500' : donation.status === 'completed' ? 'bg-blue-500' : 'bg-muted-foreground/30'"
                        />

                        <div class="p-4 flex-1 flex flex-col">
                            <!-- Header: Status + Actions -->
                            <div class="flex items-center justify-between gap-2 mb-2.5">
                                <div class="flex items-center gap-2">
                                    <span
                                        :class="['px-2 py-0.5 rounded-full text-[10px] font-semibold', getStatusConfig(donation.status).class]"
                                    >
                                        {{ getStatusConfig(donation.status).label }}
                                    </span>
                                    <span v-if="donation.is_public" class="text-[11px] text-muted-foreground flex items-center gap-1">
                                        <Globe class="w-3 h-3" />
                                        Publik
                                    </span>
                                </div>
                                <DropdownMenu v-if="$page.props.auth.user.role !== 'anggota'">
                                    <DropdownMenuTrigger as-child @click.stop>
                                        <Button variant="ghost" size="sm" class="h-7 w-7 p-0">
                                            <MoreVertical class="w-4 h-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem @click="router.visit(route('donations.edit', donation))">
                                            <Pencil class="w-4 h-4 mr-2" />
                                            Edit
                                        </DropdownMenuItem>
                                        <DropdownMenuItem class="text-destructive" @click="deleteTarget = donation">
                                            <Trash2 class="w-4 h-4 mr-2" />
                                            Hapus
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>

                            <!-- Title -->
                            <h3 class="text-sm font-semibold text-foreground mb-1.5 line-clamp-1 group-hover:text-primary transition-colors">
                                {{ donation.program_name }}
                            </h3>

                            <!-- Description -->
                            <p class="text-xs text-muted-foreground mb-3 line-clamp-2 flex-1">
                                {{ donation.description || 'Tidak ada deskripsi.' }}
                            </p>

                            <!-- Progress Section -->
                            <div class="bg-muted/50 rounded-lg p-3 border space-y-2">
                                <div class="flex justify-between items-baseline">
                                    <div>
                                        <p class="text-[10px] text-muted-foreground uppercase font-medium">Terkumpul</p>
                                        <p class="text-sm font-bold text-primary tabular-nums">{{ formatCurrency(donation.collected_amount) }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[10px] text-muted-foreground uppercase font-medium">Target</p>
                                        <p class="text-xs font-semibold text-foreground tabular-nums">{{ formatCurrency(donation.target_amount) }}</p>
                                    </div>
                                </div>
                                <div class="w-full bg-muted rounded-full h-1.5 overflow-hidden">
                                    <div
                                        :class="['h-full rounded-full transition-all duration-700 ease-out', getProgressColor(calculateProgress(donation.collected_amount, donation.target_amount))]"
                                        :style="{ width: calculateProgress(donation.collected_amount, donation.target_amount) + '%' }"
                                    />
                                </div>
                                <p class="text-right">
                                    <span class="text-[10px] font-bold text-primary">
                                        {{ calculateProgress(donation.collected_amount, donation.target_amount) }}% Tercapai
                                    </span>
                                </p>
                            </div>

                            <!-- Date -->
                            <div class="flex items-center gap-1.5 text-[11px] text-muted-foreground mt-3 pt-3 border-t">
                                <CalendarDays class="w-3.5 h-3.5 shrink-0" />
                                <span>{{ formatDate(donation.start_date) }}</span>
                                <template v-if="donation.end_date">
                                    <span>-</span>
                                    <span>{{ formatDate(donation.end_date) }}</span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="donations.data.length === 0" class="py-16 text-center">
                    <div class="w-14 h-14 rounded-full bg-muted flex items-center justify-center mx-auto mb-3">
                        <Inbox class="w-7 h-7 text-muted-foreground" />
                    </div>
                    <h3 class="text-sm font-semibold text-foreground mb-1">Tidak ada program donasi</h3>
                    <p class="text-xs text-muted-foreground">Mulai dengan membuat program donasi baru.</p>
                </div>

                <!-- Pagination -->
                <div v-if="donations.total > donations.per_page" class="flex items-center justify-between pt-2">
                    <p class="text-xs text-muted-foreground">
                        {{ donations.from }}-{{ donations.to }} dari {{ donations.total }}
                    </p>
                    <div class="flex gap-1.5">
                        <Button
                            v-if="donations.prev_page_url"
                            variant="outline"
                            size="sm"
                            as-child
                            class="h-8"
                        >
                            <Link :href="donations.prev_page_url">
                                <ChevronLeft class="w-4 h-4 mr-0.5" />
                                Sebelumnya
                            </Link>
                        </Button>
                        <Button
                            v-if="donations.next_page_url"
                            variant="outline"
                            size="sm"
                            as-child
                            class="h-8"
                        >
                            <Link :href="donations.next_page_url">
                                Selanjutnya
                                <ChevronRight class="w-4 h-4 ml-0.5" />
                            </Link>
                        </Button>
                    </div>
                </div>

            </div>
        </div>
        <!-- Delete Confirmation -->
        <AlertDialog :open="!!deleteTarget" @update:open="val => !val && (deleteTarget = null)">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Hapus Program Donasi</AlertDialogTitle>
                    <AlertDialogDescription>
                        Apakah Anda yakin ingin menghapus program <strong>{{ deleteTarget?.program_name }}</strong>? Tindakan ini tidak dapat dibatalkan.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Batal</AlertDialogCancel>
                    <AlertDialogAction variant="destructive" @click="confirmDelete">Hapus</AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AuthenticatedLayout>
</template>
