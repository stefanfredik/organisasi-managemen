<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/components/ui/button';
import {
    ArrowLeft, Target, TrendingUp, Heart, Users, Inbox,
} from 'lucide-vue-next';

const props = defineProps({
    donations: Array,
    stats: Object,
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
};

const getStatusConfig = (status) => {
    const map = {
        active: { label: 'Aktif', class: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' },
        completed: { label: 'Selesai', class: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' },
        cancelled: { label: 'Dibatalkan', class: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' },
    };
    return map[status] || { label: status, class: 'bg-muted text-foreground' };
};

const getProgressColor = (pct) => {
    if (pct >= 100) return 'bg-green-500';
    if (pct >= 50) return 'bg-primary';
    return 'bg-amber-500';
};

const statCards = [
    { key: 'total_target', label: 'Total Target', icon: Target, format: 'currency' },
    { key: 'total_collected', label: 'Total Terkumpul', icon: TrendingUp, format: 'currency', primary: true },
    { key: 'active_programs', label: 'Program Aktif', icon: Heart, suffix: 'Program' },
    { key: 'total_donators', label: 'Total Donatur', icon: Users, suffix: 'Orang' },
];
</script>

<template>
    <Head title="Laporan Donasi" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2.5">
                <Button variant="ghost" size="icon" class="h-8 w-8 shrink-0" as-child>
                    <Link :href="route('donations.index')">
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                </Button>
                <h2 class="text-lg font-semibold leading-tight text-foreground">Laporan Donasi</h2>
            </div>
        </template>

        <div class="py-4 sm:py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-4">

                <!-- Stats Grid -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                    <div v-for="card in statCards" :key="card.key" class="bg-card rounded-xl border p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-7 h-7 rounded-lg bg-primary/10 flex items-center justify-center">
                                <component :is="card.icon" class="w-3.5 h-3.5 text-primary" />
                            </div>
                            <p class="text-[10px] text-muted-foreground uppercase font-medium tracking-wide">{{ card.label }}</p>
                        </div>
                        <p class="text-lg font-bold tabular-nums" :class="card.primary ? 'text-primary' : 'text-foreground'">
                            <template v-if="card.format === 'currency'">{{ formatCurrency(stats[card.key]) }}</template>
                            <template v-else>{{ stats[card.key] }} {{ card.suffix }}</template>
                        </p>
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-card rounded-xl border overflow-hidden">
                    <div class="px-5 py-4 border-b">
                        <h3 class="text-sm font-semibold text-foreground">Breakdown per Program</h3>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="sm:hidden divide-y">
                        <div v-for="d in donations" :key="d.id" class="px-4 py-3 space-y-1.5">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-sm font-semibold text-foreground truncate">{{ d.program_name }}</p>
                                <span :class="['px-1.5 py-0.5 rounded-full text-[10px] font-semibold shrink-0', getStatusConfig(d.status).class]">
                                    {{ getStatusConfig(d.status).label }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-muted-foreground">{{ formatCurrency(d.collected_amount) }} / {{ formatCurrency(d.target_amount) }}</span>
                                <span class="font-bold text-primary">{{ d.progress }}%</span>
                            </div>
                            <div class="w-full bg-muted rounded-full h-1.5 overflow-hidden">
                                <div :class="['h-full rounded-full', getProgressColor(d.progress)]" :style="{ width: d.progress + '%' }" />
                            </div>
                            <p class="text-[11px] text-muted-foreground">{{ d.transactions_count }} donatur</p>
                        </div>
                        <div v-if="donations.length === 0" class="py-12 text-center">
                            <Inbox class="w-7 h-7 text-muted-foreground mx-auto mb-2" />
                            <p class="text-sm text-muted-foreground">Belum ada data.</p>
                        </div>
                    </div>

                    <!-- Desktop Table -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b bg-muted/50 text-[11px] text-muted-foreground uppercase tracking-wide">
                                    <th class="px-4 py-2.5 text-left font-medium">Nama Program</th>
                                    <th class="px-4 py-2.5 text-center font-medium">Status</th>
                                    <th class="px-4 py-2.5 text-right font-medium">Target</th>
                                    <th class="px-4 py-2.5 text-right font-medium">Terkumpul</th>
                                    <th class="px-4 py-2.5 text-right font-medium">Progres</th>
                                    <th class="px-4 py-2.5 text-center font-medium">Donatur</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="d in donations" :key="d.id" class="hover:bg-muted/30 transition-colors">
                                    <td class="px-4 py-3 font-medium text-foreground">{{ d.program_name }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span :class="['px-1.5 py-0.5 rounded-full text-[10px] font-semibold', getStatusConfig(d.status).class]">
                                            {{ getStatusConfig(d.status).label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right text-muted-foreground tabular-nums">{{ formatCurrency(d.target_amount) }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-primary tabular-nums">{{ formatCurrency(d.collected_amount) }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <div class="w-16 bg-muted rounded-full h-1.5 overflow-hidden">
                                                <div :class="['h-full rounded-full', getProgressColor(d.progress)]" :style="{ width: d.progress + '%' }" />
                                            </div>
                                            <span class="font-semibold text-xs tabular-nums">{{ d.progress }}%</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center text-muted-foreground">{{ d.transactions_count }}</td>
                                </tr>
                                <tr v-if="donations.length === 0">
                                    <td colspan="6" class="py-12 text-center text-muted-foreground">
                                        <Inbox class="w-7 h-7 mx-auto mb-2" />
                                        Belum ada data donasi.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
