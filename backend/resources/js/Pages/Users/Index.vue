<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SearchBar from '@/Components/SearchBar.vue';
import FilterDropdown from '@/Components/FilterDropdown.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import DeleteConfirmDialog from '@/Components/DeleteConfirmDialog.vue';
import {
    AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent,
    AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import {
    DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Sheet, SheetContent, SheetHeader, SheetTitle, SheetDescription,
} from '@/components/ui/sheet';
import {
    Plus, Eye, Pencil, Trash2, MoreVertical, Inbox, KeyRound, ShieldCheck, ShieldOff,
    ChevronLeft, ChevronRight, Mail,
} from 'lucide-vue-next';
import debounce from 'lodash/debounce';
import { useToast } from '@/composables/useToast';

const toast = useToast();

const props = defineProps({
    users: Object,
    filters: Object,
    roles: Object,
    statuses: Object,
});

const search = ref(props.filters?.search || '');
const role = ref(props.filters?.role || '');
const status = ref(props.filters?.status || '');

const roleOptions = Object.entries(props.roles || {}).map(([value, label]) => ({ value, label }));
const statusOptions = Object.entries(props.statuses || {}).map(([value, label]) => ({ value, label }));

watch([search, role, status], debounce(() => {
    router.get(route('users.index'), {
        search: search.value,
        role: role.value,
        status: status.value,
    }, { preserveState: true, replace: true });
}, 300));

// Delete
const deleteTarget = ref(null);
const confirmDelete = () => {
    if (deleteTarget.value) {
        router.delete(route('users.destroy', deleteTarget.value.id), {
            preserveScroll: true,
            onError: (errors) => toast.error(Object.values(errors).flat().join(', ') || 'Gagal menghapus user.'),
            onFinish: () => (deleteTarget.value = null),
        });
    }
};

// Toggle status
const toggleStatus = (user) => {
    router.patch(route('users.toggle-status', user.id), {}, { preserveScroll: true });
};

// Reset password
const resetTarget = ref(null);
const confirmResetPassword = () => {
    if (resetTarget.value) {
        router.post(route('users.reset-password', resetTarget.value.id), {}, {
            preserveScroll: true,
            onFinish: () => (resetTarget.value = null),
        });
    }
};

// Mobile detail sheet
const detailItem = ref(null);
const showDetailSheet = ref(false);
const openDetail = (u) => { detailItem.value = u; showDetailSheet.value = true; };
const closeDetail = () => { showDetailSheet.value = false; detailItem.value = null; };

const getRoleBadgeClass = (r) => {
    switch (r) {
        case 'admin': return 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400';
        case 'ketua': return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400';
        case 'bendahara': return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400';
        case 'sekretaris': return 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400';
        default: return 'bg-muted text-muted-foreground';
    }
};

const getStatusBadge = (s) => {
    return s === 'active'
        ? { label: 'Aktif', class: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' }
        : { label: 'Nonaktif', class: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' };
};

const formatLastLogin = (val) => {
    if (!val) return 'Belum pernah';
    return new Date(val).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <Head title="Manajemen User" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold leading-tight text-foreground">Manajemen User</h2>
                <Button size="sm" as-child>
                    <Link :href="route('users.create')">
                        <Plus class="w-4 h-4 mr-1" />
                        <span class="hidden sm:inline">Tambah User</span>
                    </Link>
                </Button>
            </div>
        </template>

        <div class="py-3 sm:py-6">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-3 sm:space-y-4">

                <!-- Filters -->
                <div class="flex flex-col sm:flex-row gap-2">
                    <div class="flex-1">
                        <SearchBar v-model="search" placeholder="Cari nama atau email..." />
                    </div>
                    <div class="flex gap-2">
                        <div class="flex-1 sm:w-36">
                            <FilterDropdown v-model="role" :options="roleOptions" label="Role" />
                        </div>
                        <div class="flex-1 sm:w-36">
                            <FilterDropdown v-model="status" :options="statusOptions" label="Status" />
                        </div>
                    </div>
                </div>

                <!-- Mobile: Contact List -->
                <div class="sm:hidden">
                    <div class="bg-card border rounded-xl overflow-hidden divide-y divide-border">
                        <div
                            v-for="user in users.data"
                            :key="'m-' + user.id"
                            class="flex items-center gap-3 px-3 py-3 active:bg-muted/50 transition-colors"
                        >
                            <!-- Avatar -->
                            <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                                <span class="text-primary font-semibold text-sm">{{ user.name.charAt(0).toUpperCase() }}</span>
                            </div>

                            <!-- Info -->
                            <div class="flex-1 min-w-0" @click="openDetail(user)">
                                <p class="text-sm font-semibold text-foreground truncate">{{ user.name }}</p>
                                <div class="flex items-center gap-1.5 text-[10px] text-muted-foreground mt-0.5">
                                    <Mail class="w-2.5 h-2.5" />
                                    <span class="truncate">{{ user.email }}</span>
                                </div>
                            </div>

                            <!-- Role Badge -->
                            <span :class="['px-1.5 py-0.5 rounded-full text-[9px] font-semibold shrink-0 uppercase', getRoleBadgeClass(user.role)]">
                                {{ roles[user.role] }}
                            </span>

                            <!-- Hamburger -->
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <button class="shrink-0 w-8 h-8 flex items-center justify-center rounded-full hover:bg-muted transition-colors" @click.stop>
                                        <MoreVertical class="w-4 h-4 text-muted-foreground" />
                                    </button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuItem as-child>
                                        <Link :href="route('users.edit', user.id)" class="flex items-center gap-2">
                                            <Pencil class="w-4 h-4" /> Edit
                                        </Link>
                                    </DropdownMenuItem>
                                    <DropdownMenuItem @click="toggleStatus(user)" class="flex items-center gap-2">
                                        <ShieldCheck v-if="user.status !== 'active'" class="w-4 h-4" />
                                        <ShieldOff v-else class="w-4 h-4" />
                                        {{ user.status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </DropdownMenuItem>
                                    <DropdownMenuItem @click="resetTarget = user" class="flex items-center gap-2">
                                        <KeyRound class="w-4 h-4" /> Reset Password
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem @click="deleteTarget = user" class="text-destructive focus:text-destructive flex items-center gap-2">
                                        <Trash2 class="w-4 h-4" /> Hapus
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>

                        <!-- Empty -->
                        <div v-if="(users?.data?.length || 0) === 0" class="py-12 text-center">
                            <div class="w-12 h-12 rounded-full bg-muted flex items-center justify-center mx-auto mb-2">
                                <Inbox class="w-6 h-6 text-muted-foreground" />
                            </div>
                            <p class="text-xs text-muted-foreground">Tidak ada user ditemukan.</p>
                        </div>
                    </div>

                    <!-- Mobile Pagination -->
                    <div v-if="users.total > users.per_page" class="flex items-center justify-between pt-2">
                        <p class="text-[10px] text-muted-foreground">{{ users.from }}-{{ users.to }} dari {{ users.total }}</p>
                        <div class="flex gap-1">
                            <Button v-if="users.prev_page_url" variant="outline" size="sm" class="h-6 text-[10px] px-2" as-child>
                                <Link :href="users.prev_page_url">Sebelumnya</Link>
                            </Button>
                            <Button v-if="users.next_page_url" variant="outline" size="sm" class="h-6 text-[10px] px-2" as-child>
                                <Link :href="users.next_page_url">Selanjutnya</Link>
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Desktop: Table -->
                <div class="hidden sm:block bg-card rounded-xl border overflow-hidden">
                    <Table>
                        <TableHeader>
                            <TableRow class="bg-muted/50">
                                <TableHead class="text-[11px] uppercase tracking-wide font-medium">User</TableHead>
                                <TableHead class="text-[11px] uppercase tracking-wide font-medium">Role</TableHead>
                                <TableHead class="text-[11px] uppercase tracking-wide font-medium">Status</TableHead>
                                <TableHead class="text-[11px] uppercase tracking-wide font-medium">Login Terakhir</TableHead>
                                <TableHead class="text-[11px] uppercase tracking-wide font-medium text-right">Aksi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="user in users.data" :key="user.id" class="hover:bg-muted/30 transition-colors">
                                <TableCell>
                                    <div class="flex items-center gap-3">
                                        <div class="h-9 w-9 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                                            <span class="text-primary font-semibold text-sm">{{ user.name.charAt(0).toUpperCase() }}</span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-foreground">{{ user.name }}</p>
                                            <p class="text-xs text-muted-foreground">{{ user.email }}</p>
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <span :class="['px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase', getRoleBadgeClass(user.role)]">
                                        {{ roles[user.role] }}
                                    </span>
                                </TableCell>
                                <TableCell>
                                    <button
                                        @click="toggleStatus(user)"
                                        :class="['px-2 py-0.5 rounded-full text-[10px] font-semibold transition-colors', getStatusBadge(user.status).class]"
                                    >
                                        {{ getStatusBadge(user.status).label }}
                                    </button>
                                </TableCell>
                                <TableCell class="text-sm text-muted-foreground whitespace-nowrap">
                                    {{ formatLastLogin(user.last_login_at) }}
                                </TableCell>
                                <TableCell class="text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" size="icon" class="h-8 w-8">
                                                <MoreVertical class="w-4 h-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem as-child>
                                                <Link :href="route('users.edit', user.id)" class="flex items-center gap-2">
                                                    <Pencil class="w-4 h-4" /> Edit
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem @click="toggleStatus(user)" class="flex items-center gap-2">
                                                <ShieldCheck v-if="user.status !== 'active'" class="w-4 h-4" />
                                                <ShieldOff v-else class="w-4 h-4" />
                                                {{ user.status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </DropdownMenuItem>
                                            <DropdownMenuItem @click="resetTarget = user" class="flex items-center gap-2">
                                                <KeyRound class="w-4 h-4" /> Reset Password
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem @click="deleteTarget = user" class="text-destructive focus:text-destructive flex items-center gap-2">
                                                <Trash2 class="w-4 h-4" /> Hapus
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="(users?.data?.length || 0) === 0">
                                <TableCell colspan="5" class="h-24 text-center">
                                    <div class="flex flex-col items-center gap-2 text-muted-foreground">
                                        <Inbox class="w-8 h-8" />
                                        <span class="text-sm">Tidak ada user ditemukan.</span>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Desktop Pagination -->
                    <div v-if="users.total > users.per_page" class="flex items-center justify-between px-4 py-3 border-t bg-muted/30">
                        <p class="text-xs text-muted-foreground">{{ users.from }}-{{ users.to }} dari {{ users.total }}</p>
                        <div class="flex gap-1.5">
                            <Button v-if="users.prev_page_url" variant="outline" size="sm" class="h-8" as-child>
                                <Link :href="users.prev_page_url"><ChevronLeft class="w-4 h-4 mr-0.5" /> Sebelumnya</Link>
                            </Button>
                            <Button v-if="users.next_page_url" variant="outline" size="sm" class="h-8" as-child>
                                <Link :href="users.next_page_url">Selanjutnya <ChevronRight class="w-4 h-4 ml-0.5" /></Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation -->
        <DeleteConfirmDialog
            :open="!!deleteTarget"
            title="Hapus User"
            :description="`Apakah Anda yakin ingin menghapus user ${deleteTarget?.name}? Tindakan ini tidak dapat dibatalkan.`"
            @confirm="confirmDelete"
            @cancel="deleteTarget = null"
        />

        <!-- Reset Password Confirmation -->
        <AlertDialog :open="!!resetTarget" @update:open="val => !val && (resetTarget = null)">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Reset Password</AlertDialogTitle>
                    <AlertDialogDescription>
                        Apakah Anda yakin ingin mereset password user <strong>{{ resetTarget?.name }}</strong>?
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Batal</AlertDialogCancel>
                    <AlertDialogAction @click="confirmResetPassword">Reset Password</AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

        <!-- Mobile: Detail Sheet -->
        <Sheet :open="showDetailSheet" @update:open="val => { if (!val) closeDetail(); }">
            <SheetContent side="bottom" class="rounded-t-2xl max-h-[80vh] overflow-y-auto px-4 pb-6 pt-3">
                <SheetHeader class="text-left pb-2.5 border-b mb-3">
                    <SheetTitle class="text-sm">Detail User</SheetTitle>
                    <SheetDescription class="sr-only">Detail informasi user</SheetDescription>
                </SheetHeader>

                <template v-if="detailItem">
                    <!-- User info -->
                    <div class="flex items-center gap-3 mb-3">
                        <div class="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                            <span class="text-primary font-semibold text-lg">{{ detailItem.name.charAt(0).toUpperCase() }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-foreground truncate">{{ detailItem.name }}</p>
                            <p class="text-xs text-muted-foreground truncate">{{ detailItem.email }}</p>
                        </div>
                    </div>

                    <!-- Info grid -->
                    <div class="grid grid-cols-3 gap-2 mb-3">
                        <div class="bg-muted/50 rounded-lg p-2.5">
                            <p class="text-[10px] text-muted-foreground uppercase font-medium">Role</p>
                            <span :class="['inline-block mt-0.5 px-1.5 py-0.5 rounded-full text-[9px] font-semibold uppercase', getRoleBadgeClass(detailItem.role)]">
                                {{ roles[detailItem.role] }}
                            </span>
                        </div>
                        <div class="bg-muted/50 rounded-lg p-2.5">
                            <p class="text-[10px] text-muted-foreground uppercase font-medium">Status</p>
                            <span :class="['inline-block mt-0.5 px-1.5 py-0.5 rounded-full text-[9px] font-semibold', getStatusBadge(detailItem.status).class]">
                                {{ getStatusBadge(detailItem.status).label }}
                            </span>
                        </div>
                        <div class="bg-muted/50 rounded-lg p-2.5">
                            <p class="text-[10px] text-muted-foreground uppercase font-medium">Login</p>
                            <p class="text-[10px] font-medium text-foreground mt-0.5">{{ formatLastLogin(detailItem.last_login_at) }}</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="space-y-2 border-t pt-3">
                        <Button variant="outline" size="sm" class="w-full justify-start" as-child>
                            <Link :href="route('users.edit', detailItem.id)" @click="closeDetail()">
                                <Pencil class="w-4 h-4 mr-2" /> Edit User
                            </Link>
                        </Button>
                        <Button variant="outline" size="sm" class="w-full justify-start" @click="closeDetail(); toggleStatus(detailItem)">
                            <ShieldCheck v-if="detailItem.status !== 'active'" class="w-4 h-4 mr-2" />
                            <ShieldOff v-else class="w-4 h-4 mr-2" />
                            {{ detailItem.status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                        </Button>
                        <Button variant="outline" size="sm" class="w-full justify-start" @click="closeDetail(); resetTarget = detailItem">
                            <KeyRound class="w-4 h-4 mr-2" /> Reset Password
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            class="w-full justify-start text-destructive hover:text-destructive"
                            @click="closeDetail(); deleteTarget = detailItem;"
                        >
                            <Trash2 class="w-4 h-4 mr-2" /> Hapus User
                        </Button>
                    </div>
                </template>
            </SheetContent>
        </Sheet>
    </AuthenticatedLayout>
</template>
