<script setup>
import { ref, computed } from "vue";
import { Head, router, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Checkbox } from "@/components/ui/checkbox";
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from "@/components/ui/table";
import DeleteConfirmDialog from '@/Components/DeleteConfirmDialog.vue';
import {
    Shield, ShieldCheck, Users, Loader2, Check, Briefcase, Plus, Pencil, Trash2, Inbox
} from "lucide-vue-next";
import { useToast } from '@/composables/useToast';

const toast = useToast();

const props = defineProps({
    roles: Object,
    positions: Object,
    userCounts: Object,
    rolePermissions: Object,
    positionsList: Array,
});

const activeTab = ref('permissions');

// Permission groups for organized display
const permissionGroups = [
    {
        label: "Dashboard",
        permissions: [
            { key: "view_dashboard", label: "Lihat Dashboard" },
        ],
    },
    {
        label: "Anggota",
        permissions: [
            { key: "manage_members", label: "Kelola Anggota (CRUD)" },
            { key: "view_members", label: "Lihat Daftar Anggota" },
        ],
    },
    {
        label: "Keuangan",
        permissions: [
            { key: "manage_finance", label: "Kelola Keuangan" },
            { key: "view_finance", label: "Lihat Data Keuangan" },
        ],
    },
    {
        label: "Kegiatan",
        permissions: [
            { key: "manage_events", label: "Kelola Kegiatan" },
            { key: "view_events", label: "Lihat Daftar Kegiatan" },
        ],
    },
    {
        label: "Iuran",
        permissions: [
            { key: "manage_contributions", label: "Kelola Iuran" },
            { key: "view_contributions", label: "Lihat Data Iuran" },
            { key: "view_contribution_monitoring", label: "Lihat Monitoring Iuran" },
            { key: "manage_contribution_types", label: "Kelola Jenis Iuran" },
            { key: "view_contribution_types", label: "Lihat Jenis Iuran" },
        ],
    },
    {
        label: "Arisan",
        permissions: [
            { key: "manage_arisans", label: "Kelola Arisan" },
            { key: "view_arisans", label: "Lihat Data Arisan" },
        ],
    },
    {
        label: "Donasi",
        permissions: [
            { key: "view_donations", label: "Lihat Data Donasi" },
        ],
    },
    {
        label: "Pengumuman",
        permissions: [
            { key: "manage_announcements", label: "Kelola Pengumuman" },
            { key: "view_announcements", label: "Lihat Pengumuman" },
        ],
    },
    {
        label: "Notulensi Rapat",
        permissions: [
            { key: "manage_meeting_minutes", label: "Kelola Notulensi" },
            { key: "view_meeting_minutes", label: "Lihat Notulensi" },
        ],
    },
    {
        label: "Visi & Misi",
        permissions: [
            { key: "manage_vision_missions", label: "Kelola Visi & Misi" },
            { key: "view_vision_missions", label: "Lihat Visi & Misi" },
        ],
    },
    {
        label: "Struktur Organisasi",
        permissions: [
            { key: "manage_organization_structures", label: "Kelola Struktur Org" },
            { key: "view_organization_structures", label: "Lihat Struktur Org" },
        ],
    },
    {
        label: "Album Foto",
        permissions: [
            { key: "manage_albums", label: "Kelola Album Foto" },
            { key: "view_albums", label: "Lihat Album Foto" },
        ],
    },
    {
        label: "Laporan",
        permissions: [
            { key: "view_reports", label: "Lihat Laporan" },
        ],
    },
    {
        label: "Sistem",
        permissions: [
            { key: "manage_settings", label: "Kelola Pengaturan" },
        ],
    },
];

// Position-based roles (ketua, bendahara, sekretaris, anggota)
const editableRoles = computed(() => {
    return Object.entries(props.positions)
        .map(([key, label]) => ({ key, label }));
});

// Selected role
const selectedRole = ref(editableRoles.value[0]?.key || "ketua");

// Local copy of permissions (reactive)
const localPermissions = ref(
    JSON.parse(JSON.stringify(props.rolePermissions))
);

const hasPermission = (role, permKey) => {
    return (localPermissions.value[role] || []).includes(permKey);
};

const togglePermission = (role, permKey) => {
    if (!localPermissions.value[role]) {
        localPermissions.value[role] = [];
    }
    const perms = localPermissions.value[role];
    const idx = perms.indexOf(permKey);
    if (idx >= 0) {
        perms.splice(idx, 1);
    } else {
        perms.push(permKey);
    }
};

// Count permissions for a role
const permCount = (role) => {
    return (localPermissions.value[role] || []).length;
};

const totalPermissions = computed(() => {
    return permissionGroups.reduce((sum, g) => sum + g.permissions.length, 0);
});

// Toggle all permissions in a group
const isGroupFullyChecked = (role, group) => {
    return group.permissions.every(p => hasPermission(role, p.key));
};

const isGroupPartiallyChecked = (role, group) => {
    const checked = group.permissions.filter(p => hasPermission(role, p.key)).length;
    return checked > 0 && checked < group.permissions.length;
};

const toggleGroup = (role, group) => {
    const allChecked = isGroupFullyChecked(role, group);
    for (const perm of group.permissions) {
        const has = hasPermission(role, perm.key);
        if (allChecked && has) {
            togglePermission(role, perm.key);
        } else if (!allChecked && !has) {
            togglePermission(role, perm.key);
        }
    }
};

// Save Permissions
const saving = ref(false);
const save = () => {
    saving.value = true;
    router.post(route("roles.update"), {
        permissions: localPermissions.value,
    }, {
        preserveScroll: true,
        onFinish: () => { saving.value = false; },
    });
};

// Role badge colors
const getRoleColor = (role) => {
    switch (role) {
        case "ketua": return "bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 border-blue-200 dark:border-blue-800";
        case "bendahara": return "bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 border-green-200 dark:border-green-800";
        case "sekretaris": return "bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400 border-orange-200 dark:border-orange-800";
        case "anggota": return "bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-400 border-gray-200 dark:border-gray-800";
        default: return "bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 border-purple-200 dark:border-purple-800";
    }
};

const getRoleAccent = (role) => {
    switch (role) {
        case "ketua": return "bg-blue-500";
        case "bendahara": return "bg-green-500";
        case "sekretaris": return "bg-orange-500";
        case "anggota": return "bg-gray-500";
        default: return "bg-purple-500";
    }
};

// Position Management
const deleteTarget = ref(null);
const confirmDelete = () => {
    if (deleteTarget.value) {
        router.delete(route("positions.destroy", deleteTarget.value.id), {
            preserveScroll: true,
            onError: (errors) => {
                if(errors.message) {
                    toast.error(errors.message);
                } else {
                    toast.error('Gagal menghapus posisi.');
                }
            },
            onFinish: () => (deleteTarget.value = null),
        });
    }
};
</script>

<template>
    <Head title="Manajemen Role & Jabatan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <Shield class="w-5 h-5 text-primary" />
                    <h2 class="text-lg font-semibold leading-tight text-foreground">Role & Jabatan</h2>
                </div>
                <!-- Only show save button if editing permissions -->
                <Button v-show="activeTab === 'permissions'" size="sm" :disabled="saving" @click="save">
                    <Loader2 v-if="saving" class="w-4 h-4 mr-1 animate-spin" />
                    <Check v-else class="w-4 h-4 mr-1" />
                    <span class="hidden sm:inline">{{ saving ? 'Menyimpan...' : 'Simpan Perubahan' }}</span>
                    <span class="sm:hidden">Simpan</span>
                </Button>
            </div>
        </template>

        <div class="py-3 sm:py-6">
            <div class="max-w-6xl mx-auto px-3 sm:px-6 lg:px-8 space-y-4">
                
                <!-- Admin info -->
                <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-xl p-3 sm:p-4 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center shrink-0">
                        <ShieldCheck class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs sm:text-sm font-semibold text-purple-700 dark:text-purple-300">Administrator</p>
                        <p class="text-[10px] sm:text-xs text-purple-600 dark:text-purple-400">Pengecualian khusus: Admin memiliki akses mutlak ke seluruh fitur sistem dan bypass izin otoritas.</p>
                    </div>
                </div>

                <!-- Unified Vertical Tabs Container -->
                <div class="flex flex-col md:flex-row gap-6 mt-4 md:mt-6">
                    <!-- Sidebar Tabs -->
                    <div class="w-full md:w-64 shrink-0 space-y-2">
                        <button 
                            @click="activeTab = 'permissions'" 
                            :class="activeTab === 'permissions' ? 'bg-primary text-primary-foreground border-primary shadow-sm' : 'bg-card text-foreground hover:bg-muted/50 border-border'" 
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-xl border font-semibold transition-all text-sm"
                        >
                            <Shield class="w-4 h-4" />
                            Manajemen Permission
                        </button>
                        <button 
                            @click="activeTab = 'positions'" 
                            :class="activeTab === 'positions' ? 'bg-primary text-primary-foreground border-primary shadow-sm' : 'bg-card text-foreground hover:bg-muted/50 border-border'" 
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-xl border font-semibold transition-all text-sm"
                        >
                            <Briefcase class="w-4 h-4" />
                            Daftar Jabatan
                        </button>
                    </div>

                    <!-- Content Area -->
                    <div class="flex-1 min-w-0">
                        
                        <!-- TAB: PERMISSIONS -->
                        <div v-show="activeTab === 'permissions'" class="space-y-4 animate-in fade-in duration-300">
                            <!-- Role Selector Cards -->
                            <div class="flex gap-2 overflow-x-auto pb-1 -mx-3 px-3 sm:mx-0 sm:px-0">
                                <button
                                    v-for="r in editableRoles"
                                    :key="r.key"
                                    @click="selectedRole = r.key"
                                    class="flex-shrink-0 rounded-xl border p-3 sm:p-4 transition-all min-w-[130px] sm:min-w-[150px] text-left"
                                    :class="selectedRole === r.key
                                        ? 'bg-card border-primary ring-2 ring-primary/20'
                                        : 'bg-card hover:bg-muted/50'"
                                >
                                    <div class="flex items-center gap-2 mb-2">
                                        <div :class="['w-2 h-2 rounded-full', getRoleAccent(r.key)]" />
                                        <span class="text-xs sm:text-sm font-semibold text-foreground">{{ r.label }}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] sm:text-xs text-muted-foreground">
                                            <Users class="w-3 h-3 inline mr-0.5" />
                                            {{ userCounts?.[r.key] || 0 }} user
                                        </span>
                                        <span class="text-[10px] sm:text-xs text-muted-foreground">
                                            {{ permCount(r.key) }}/{{ totalPermissions }}
                                        </span>
                                    </div>
                                </button>
                            </div>

                            <!-- Permission Matrix -->
                            <div class="bg-card rounded-xl border overflow-hidden">
                                <div class="h-1 w-full" :class="getRoleAccent(selectedRole)" />

                                <div class="p-4 sm:p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <div>
                                            <h3 class="text-sm sm:text-base font-semibold text-foreground">
                                                Izin — {{ positions[selectedRole] }}
                                            </h3>
                                            <p class="text-[10px] sm:text-xs text-muted-foreground mt-0.5">
                                                {{ permCount(selectedRole) }} dari {{ totalPermissions }} permission aktif
                                            </p>
                                        </div>
                                        <Badge :class="getRoleColor(selectedRole)" class="text-[10px] sm:text-xs uppercase font-semibold border hidden sm:block">
                                            {{ positions[selectedRole] }}
                                        </Badge>
                                    </div>

                                    <div class="space-y-1">
                                        <div v-for="group in permissionGroups" :key="group.label" class="border rounded-lg overflow-hidden">
                                            <!-- Group Header -->
                                            <button
                                                type="button"
                                                class="w-full flex items-center gap-3 px-3 sm:px-4 py-2.5 bg-muted/40 hover:bg-muted/60 transition-colors text-left"
                                                @click="toggleGroup(selectedRole, group)"
                                            >
                                                <Checkbox
                                                    :checked="isGroupFullyChecked(selectedRole, group)"
                                                    :indeterminate="isGroupPartiallyChecked(selectedRole, group)"
                                                    class="pointer-events-none"
                                                />
                                                <span class="text-xs sm:text-sm font-semibold text-foreground flex-1">{{ group.label }}</span>
                                                <span class="text-[10px] sm:text-xs text-muted-foreground">
                                                    {{ group.permissions.filter(p => hasPermission(selectedRole, p.key)).length }}/{{ group.permissions.length }}
                                                </span>
                                            </button>

                                            <!-- Permission Items -->
                                            <div class="divide-y divide-border">
                                                <label
                                                    v-for="perm in group.permissions"
                                                    :key="perm.key"
                                                    class="flex items-center gap-3 px-3 sm:px-4 py-2 sm:py-2.5 hover:bg-muted/30 transition-colors cursor-pointer pl-6 sm:pl-8"
                                                >
                                                    <Checkbox
                                                        :checked="hasPermission(selectedRole, perm.key)"
                                                        @update:checked="togglePermission(selectedRole, perm.key)"
                                                    />
                                                    <div class="flex-1 min-w-0">
                                                        <span class="text-xs sm:text-sm text-foreground">{{ perm.label }}</span>
                                                    </div>
                                                    <span
                                                        v-if="perm.key.startsWith('manage_')"
                                                        class="text-[9px] sm:text-[10px] px-1.5 py-0.5 rounded bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400 font-medium shrink-0"
                                                    >
                                                        CRUD
                                                    </span>
                                                    <span
                                                        v-else
                                                        class="text-[9px] sm:text-[10px] px-1.5 py-0.5 rounded bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 font-medium shrink-0"
                                                    >
                                                        VIEW
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Hint -->
                                    <p class="text-[10px] sm:text-xs text-muted-foreground mt-4 text-center italic">
                                        Permission "Kelola" (manage) secara otomatis mencakup permission "Lihat" (view) terkait.
                                    </p>
                                </div>
                            </div>

                            <!-- Mobile Save Button -->
                            <div class="sm:hidden pb-2">
                                <Button class="w-full" :disabled="saving" @click="save">
                                    <Loader2 v-if="saving" class="w-4 h-4 mr-1 animate-spin" />
                                    <Check v-else class="w-4 h-4 mr-1" />
                                    {{ saving ? 'Menyimpan...' : 'Simpan Perubahan' }}
                                </Button>
                            </div>
                        </div>

                        <!-- TAB: POSITIONS -->
                        <div v-show="activeTab === 'positions'" class="space-y-4 animate-in fade-in duration-300">
                            
                            <!-- Header with Add Position button -->
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <h3 class="text-base font-semibold text-foreground">Daftar Jabatan / Posisi</h3>
                                    <p class="text-[11px] text-muted-foreground mt-0.5">Kelola nama-nama posisi yang dapat diduduki oleh anggota.</p>
                                </div>
                                <Button size="sm" as-child>
                                    <Link :href="route('positions.create')">
                                        <Plus class="w-4 h-4 sm:mr-1" />
                                        <span class="hidden sm:inline">Tambah Posisi</span>
                                    </Link>
                                </Button>
                            </div>

                            <!-- Desktop: Table -->
                            <div class="bg-card rounded-xl border overflow-hidden">
                                <Table>
                                    <TableHeader>
                                        <TableRow class="bg-muted/50">
                                            <TableHead class="text-[11px] uppercase tracking-wide font-medium">Nama Posisi</TableHead>
                                            <TableHead class="text-[11px] uppercase tracking-wide font-medium">Kode Internal</TableHead>
                                            <TableHead class="text-[11px] uppercase tracking-wide font-medium text-center">Jumlah Anggota</TableHead>
                                            <TableHead class="text-[11px] uppercase tracking-wide font-medium text-right">Aksi</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow v-for="pos in positionsList" :key="pos.id" class="hover:bg-muted/30 transition-colors">
                                            <TableCell>
                                                <p class="text-sm font-semibold text-foreground">{{ pos.name }}</p>
                                            </TableCell>
                                            <TableCell>
                                                <code class="text-[10px] bg-muted/60 px-1.5 py-0.5 rounded border">{{ pos.code }}</code>
                                            </TableCell>
                                            <TableCell class="text-center">
                                                <Badge variant="secondary" class="font-mono text-xs">{{ pos.members_count }}</Badge>
                                            </TableCell>
                                            <TableCell class="text-right">
                                                <div class="flex justify-end gap-1">
                                                    <Button variant="ghost" size="icon" class="h-8 w-8" as-child>
                                                        <Link :href="route('positions.edit', pos.id)">
                                                            <Pencil class="w-4 h-4" />
                                                        </Link>
                                                    </Button>
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        class="h-8 w-8 text-destructive hover:text-destructive"
                                                        @click="deleteTarget = pos"
                                                    >
                                                        <Trash2 class="w-4 h-4" />
                                                    </Button>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                        <TableRow v-if="positionsList.length === 0">
                                            <TableCell colspan="4" class="h-24 text-center">
                                                <div class="flex flex-col items-center gap-2 text-muted-foreground">
                                                    <Inbox class="w-8 h-8 opacity-50" />
                                                    <span class="text-sm">Belum ada posisi yang terdaftar.</span>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Position Delete Confirmation -->
        <DeleteConfirmDialog
            :open="!!deleteTarget"
            title="Hapus Posisi"
            :description="`Apakah Anda yakin ingin menghapus posisi '${deleteTarget?.name}'? Tindakan ini tidak dapat dibatalkan.`"
            @confirm="confirmDelete"
            @cancel="deleteTarget = null"
        />
    </AuthenticatedLayout>
</template>
