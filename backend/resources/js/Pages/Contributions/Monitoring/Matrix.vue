<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';

const props = defineProps({
    type: Object,
    years: Array,
    filters: Object,
    matrixData: Object, 
});

const form = ref({
    year: props.filters.year || new Date().getFullYear(),
});

const isLoading = ref(false);

const applyFilter = () => {
    isLoading.value = true;
    router.get(route('contributions.monitoring.matrix', props.type.id), form.value, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => isLoading.value = false,
    });
};

const exportExcel = () => {
    const url = route('contributions.monitoring.matrix.export', {
        contributionType: props.type.id,
        year: form.value.year,
    });
    window.open(url, '_blank');
};

const getStatusClass = (status) => {
    switch (status) {
        case 'paid': return 'bg-success/20 text-success-700';
        case 'pending': return 'bg-warning-100 text-warning-700';
        case 'partial': return 'bg-primary/20 text-primary';
        default: return 'bg-destructive/10 text-danger-300';
    }
};

const getStatusLabel = (status) => {
    switch (status) {
        case 'paid': return 'Lunas';
        case 'pending': return 'Proses';
        case 'partial': return 'Sebagian';
        default: return '-';
    }
};
</script>

<template>
    <Head :title="`Matrix - ${type.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                 <Link :href="route('contributions.monitoring.index')" class="mr-3 text-muted-foreground hover:text-foreground">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-foreground">
                    Matrix: {{ type.name }}
                </h2>
            </div>
        </template>

        <div class="py-4">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row gap-4">
                
                <!-- Sidebar Navigation -->
                <div class="w-full md:w-56 shrink-0">
                    <div class="bg-card rounded-lg shadow-sm border border overflow-hidden sticky top-4">
                        <div class="px-3 py-2 bg-muted border-b border">
                            <h3 class="font-bold text-foreground text-sm">Menu</h3>
                        </div>
                        <div class="p-1.5 space-y-0.5">
                            <Link :href="route('contributions.monitoring.dashboard', type.id)" 
                                class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors"
                                :class="route().current('contributions.monitoring.dashboard') ? 'bg-primary/10 text-primary' : 'text-muted-foreground hover:bg-muted'"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                                Dashboard
                            </Link>
                            <Link :href="route('contributions.monitoring.matrix', type.id)" 
                                class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors"
                                :class="route().current('contributions.monitoring.matrix') ? 'bg-primary/10 text-primary' : 'text-muted-foreground hover:bg-muted'"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7-8v8m14-8v8M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                Matrix
                            </Link>
                            <Link :href="route('contributions.monitoring.history', type.id)" 
                                class="flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors"
                                :class="route().current('contributions.monitoring.history') ? 'bg-primary/10 text-primary' : 'text-muted-foreground hover:bg-muted'"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Riwayat
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="flex-1 space-y-4">
                    <!-- Filters -->
                    <div class="bg-card p-3 rounded-lg shadow-sm border border">
                        <div class="flex flex-wrap items-end gap-3">
                            <div>
                                <label class="block text-xs font-medium text-muted-foreground mb-1">Tahun</label>
                                <select v-model="form.year" class="text-sm rounded-md border-input font-medium text-foreground focus:ring-ring focus:border-ring">
                                    <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                                </select>
                            </div>
                            <button @click="applyFilter" :disabled="isLoading" class="px-4 py-2 text-sm bg-primary hover:bg-primary/90 text-white font-medium rounded-md transition-colors disabled:opacity-50 flex items-center">
                                <span v-if="isLoading" class="mr-1.5">
                                    <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                                {{ isLoading ? '...' : 'Tampil' }}
                            </button>
                            <button @click="exportExcel" class="px-3 py-2 text-sm bg-success-600 hover:bg-success-700 text-white font-medium rounded-md transition-colors flex items-center" title="Export Excel">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Matrix Table -->
                    <div v-if="matrixData && matrixData.periods && matrixData.periods.length > 0" class="bg-card rounded-lg shadow-sm border border overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-center text-sm">
                                <thead class="bg-muted text-xs uppercase font-bold text-muted-foreground">
                                    <tr>
                                        <th class="px-3 py-2.5 text-left border-b border sticky left-0 bg-muted z-10 w-40 shadow-sm">Anggota</th>
                                        <th v-for="period in matrixData.periods" :key="period.key" class="px-1.5 py-2.5 border-b border min-w-[50px]">{{ period.label }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border">
                                    <tr v-for="member in matrixData.members" :key="member.id" class="hover:bg-muted transition-colors">
                                        <td class="px-3 py-2 text-left bg-card font-bold text-foreground text-xs sticky left-0 z-10 border-r border shadow-sm">
                                            {{ member.name }}
                                            <div class="text-[10px] text-muted-foreground font-normal">{{ member.member_code }}</div>
                                        </td>
                                        <td v-for="period in matrixData.periods" :key="period.key" class="px-1 py-2 bg-card border-l border">
                                            <div 
                                                class="inline-flex items-center justify-center w-full h-7 rounded text-[10px] font-bold"
                                                :class="getStatusClass(member.status_by_period[period.key])"
                                            >
                                                {{ getStatusLabel(member.status_by_period[period.key]) }}
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div v-else class="text-center py-8">
                        <p class="text-sm text-muted-foreground">Pilih tahun untuk melihat data.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
