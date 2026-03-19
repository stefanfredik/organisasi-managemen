<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';

import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';

import { ArrowLeft, LayoutDashboard, Grid3x3, Clock, Download, Loader2 } from 'lucide-vue-next';

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
        case 'paid': return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400';
        case 'pending': return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400';
        case 'partial': return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400';
        default: return 'bg-muted text-muted-foreground';
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

const navItems = [
    { route: 'contributions.monitoring.dashboard', label: 'Dashboard', icon: LayoutDashboard },
    { route: 'contributions.monitoring.matrix', label: 'Matrix', icon: Grid3x3 },
    { route: 'contributions.monitoring.history', label: 'Riwayat', icon: Clock },
];

const onYearChange = (value) => {
    form.value.year = value;
};
</script>

<template>
    <Head :title="`Matrix - ${type.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('contributions.monitoring.index')" class="shrink-0 p-1.5 rounded-lg text-muted-foreground hover:text-foreground hover:bg-muted transition-colors">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div class="min-w-0">
                    <h2 class="text-lg font-semibold leading-tight text-foreground truncate">{{ type.name }}</h2>
                </div>
            </div>
        </template>

        <div class="py-4 sm:py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-4">

                <!-- Navigation Tabs (compact) -->
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <div class="inline-flex items-center gap-1 bg-muted p-1 rounded-lg">
                        <Link
                            v-for="item in navItems"
                            :key="item.route"
                            :href="route(item.route, type.id)"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md transition-colors"
                            :class="route().current(item.route)
                                ? 'bg-card text-foreground shadow-sm'
                                : 'text-muted-foreground hover:text-foreground'"
                        >
                            <component :is="item.icon" class="w-3.5 h-3.5" />
                            {{ item.label }}
                        </Link>
                    </div>

                    <!-- Inline filter -->
                    <div class="flex items-center gap-2">
                        <Select :model-value="String(form.year)" @update:model-value="onYearChange">
                            <SelectTrigger class="w-[100px] h-8 text-xs">
                                <SelectValue placeholder="Tahun" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="y in years" :key="y" :value="String(y)">{{ y }}</SelectItem>
                            </SelectContent>
                        </Select>
                        <Button size="sm" variant="outline" class="h-8 text-xs" @click="applyFilter" :disabled="isLoading">
                            <Loader2 v-if="isLoading" class="w-3 h-3 mr-1 animate-spin" />
                            Tampil
                        </Button>
                        <Button size="sm" variant="outline" class="h-8 text-xs gap-1" @click="exportExcel">
                            <Download class="w-3.5 h-3.5" />
                            <span class="hidden sm:inline">Export</span>
                        </Button>
                    </div>
                </div>

                <!-- Matrix Table -->
                <div v-if="matrixData && matrixData.periods && matrixData.periods.length > 0" class="bg-card rounded-xl border overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-center text-sm">
                            <thead class="bg-muted/50 text-xs uppercase font-medium text-muted-foreground">
                                <tr>
                                    <th class="px-3 py-2.5 text-left border-b sticky left-0 bg-muted/50 z-10 w-40 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.06)]">Anggota</th>
                                    <th v-for="period in matrixData.periods" :key="period.key" class="px-1.5 py-2.5 border-b min-w-[50px]">{{ period.label }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="member in matrixData.members" :key="member.id" class="hover:bg-muted/30 transition-colors">
                                    <td class="px-3 py-2 text-left bg-card font-semibold text-foreground text-xs sticky left-0 z-10 border-r shadow-[2px_0_5px_-2px_rgba(0,0,0,0.06)]">
                                        {{ member.name }}
                                        <div class="text-[10px] text-muted-foreground font-normal">{{ member.member_code }}</div>
                                    </td>
                                    <td v-for="period in matrixData.periods" :key="period.key" class="px-1 py-2 bg-card border-l">
                                        <span
                                            class="inline-flex items-center justify-center w-full px-1.5 py-0.5 rounded text-[10px] font-bold"
                                            :class="getStatusClass(member.status_by_period[period.key])"
                                        >
                                            {{ getStatusLabel(member.status_by_period[period.key]) }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div v-else class="flex flex-col items-center justify-center py-16 text-center bg-card rounded-xl border">
                    <div class="w-12 h-12 rounded-xl bg-muted flex items-center justify-center mb-3">
                        <Grid3x3 class="w-6 h-6 text-muted-foreground" />
                    </div>
                    <p class="text-sm text-muted-foreground">Pilih tahun untuk melihat data.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
