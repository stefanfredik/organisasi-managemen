<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import { Button } from '@/components/ui/button';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    users: Object,
    filters: Object,
    roles: Object,
    statuses: Object,
});

const search = ref(props.filters.search);
const role = ref(props.filters.role);
const status = ref(props.filters.status);
    
const columns = [
    { key: 'name', label: 'User' },
    { key: 'role', label: 'Role' },
    { key: 'status', label: 'Status' },
    { key: 'last_login_at', label: 'Login Terakhir', format: (val) => val ? new Date(val).toLocaleString('id-ID') : 'Belum pernah' },
];

watch([search, role, status], debounce(() => {
    router.get(route('users.index'), {
        search: search.value,
        role: role.value,
        status: status.value,
    }, {
        preserveState: true,
        replace: true,
    });
}, 300));

const deleteUser = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus user ini?')) {
        router.delete(route('users.destroy', id));
    }
};

const toggleStatus = (id) => {
    router.patch(route('users.toggle-status', id));
};

const resetPassword = (id) => {
    if (confirm('Apakah Anda yakin ingin mereset password user ini?')) {
        router.post(route('users.reset-password', id));
    }
};

const getRoleBadgeClass = (role) => {
    switch (role) {
        case 'admin': return 'bg-purple-100 text-purple-700 border-purple-200';
        case 'ketua': return 'bg-primary-100 text-primary border-primary-200';
        case 'bendahara': return 'bg-success/20 text-success-700 border-success-200';
        case 'sekretaris': return 'bg-orange-100 text-orange-700 border-orange-200';
        default: return 'bg-muted text-foreground border';
    }
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
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        <span class="hidden sm:inline">Tambah User</span>
                    </Link>
                </Button>
            </div>
        </template>

        <div class="py-4 sm:py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                <!-- Compact Filters -->
                <div class="bg-card p-3 sm:p-4 rounded-xl border flex flex-col sm:flex-row gap-2">
                    <div class="flex-1 relative">
                        <input v-model="search" type="text" placeholder="Cari nama atau email..."
                            class="w-full bg-muted border rounded-lg py-2 pl-9 text-sm focus:ring-2 focus:ring-ring focus:border-ring transition-all" />
                        <svg class="absolute left-3 top-2.5 w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <select v-model="role" class="bg-muted border rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-ring sm:w-auto">
                        <option value="">Semua Role</option>
                        <option v-for="(label, value) in roles" :key="value" :value="value">{{ label }}</option>
                    </select>
                    <select v-model="status" class="bg-muted border rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-ring sm:w-auto">
                        <option value="">Semua Status</option>
                        <option v-for="(label, value) in statuses" :key="value" :value="value">{{ label }}</option>
                    </select>
                </div>

                <!-- DataTable -->
                <DataTable 
                    :data="users" 
                    :columns="columns"
                    :searchable="false" 
                >
                    <template #cell-name="{ row: user }">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold uppercase ring-4 ring-white shadow-sm">
                                {{ user.name.charAt(0) }}
                            </div>
                            <div>
                                <div class="font-bold text-foreground">{{ user.name }}</div>
                                <div class="text-xs text-muted-foreground">{{ user.email }}</div>
                            </div>
                        </div>
                    </template>

                    <template #cell-role="{ row: user }">
                        <span 
                            class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest border"
                            :class="getRoleBadgeClass(user.role)"
                        >
                            {{ roles[user.role] }}
                        </span>
                    </template>

                    <template #cell-status="{ row: user }">
                        <button 
                            @click="toggleStatus(user.id)"
                            class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest border transition-all active:scale-95"
                            :class="user.status === 'active' 
                                ? 'bg-success/10 text-success-600 border-success-100 hover:bg-success/20' 
                                : 'bg-rose-50 text-rose-600 border-rose-100 hover:bg-rose-100'"
                        >
                            {{ statuses[user.status] }}
                        </button>
                    </template>

                    <template #actions="{ row: user }">
                        <div class="flex justify-end gap-2 text-right">
                             <button 
                                @click="resetPassword(user.id)"
                                title="Reset Password"
                                class="p-2 text-warning-500 hover:bg-warning-50 rounded-xl transition-colors"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                </svg>
                            </button>
                            <Link 
                                :href="route('users.edit', user.id)"
                                class="p-2 text-primary-500 hover:bg-primary/10 rounded-xl transition-colors"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </Link>
                            <button 
                                @click="deleteUser(user.id)"
                                class="p-2 text-rose-500 hover:bg-rose-50 rounded-xl transition-colors"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </template>
                </DataTable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
