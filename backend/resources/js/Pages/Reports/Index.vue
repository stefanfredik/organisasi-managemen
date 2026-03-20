<script setup>
import { FileBarChart } from 'lucide-vue-next';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const reports = [
    {
        name: 'Laporan Keuangan',
        description: 'Laporan transaksi pemasukan dan pengeluaran',
        icon: 'cash',
        route: 'reports.financial',
        color: 'primary',
    },
    {
        name: 'Arus Kas',
        description: 'Analisis arus kas bulanan dan kategori',
        icon: 'chart',
        route: 'reports.cash-flow',
        color: 'green',
    },
    {
        name: 'Neraca Keuangan',
        description: 'Laporan aset, kewajiban, dan ekuitas',
        icon: 'document',
        route: 'reports.balance-sheet',
        color: 'blue',
    },
    {
        name: 'Laporan Iuran',
        description: 'Laporan pembayaran iuran anggota',
        icon: 'users',
        route: 'reports.contributions',
        color: 'yellow',
    },
    {
        name: 'Laporan Donasi',
        description: 'Laporan progress dan transaksi donasi',
        icon: 'gift',
        route: 'reports.donations',
        color: 'purple',
    },
];

const getIconPath = (icon) => {
    const icons = {
        cash: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        chart: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
        document: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        users: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
        gift: 'M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7',
    };
    return icons[icon] || icons.document;
};

const getColorClasses = (color) => {
    const colors = {
        primary: 'bg-primary/10 text-primary hover:bg-primary/20',
        green: 'bg-success/10 text-success-600 hover:bg-success/20',
        blue: 'bg-primary/10 text-primary hover:bg-primary/20',
        yellow: 'bg-warning-50 text-warning-600 hover:bg-warning-100',
        purple: 'bg-purple-50 text-purple-600 hover:bg-purple-100',
    };
    return colors[color] || colors.primary;
};
</script>

<template>
    <Head title="Laporan" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-foreground">
<div class="flex items-center gap-2.5">
<FileBarChart class="w-5 h-5" />
<span>Laporan</span>
</div>
</h2>
        </template>

        <div class="py-4">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                
                <!-- Header -->
                <div class="mb-4">
                    <p class="text-sm text-muted-foreground">Akses berbagai laporan keuangan dan operasional organisasi</p>
                </div>

                <!-- Reports Grid -->
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="report in reports"
                        :key="report.route"
                        :href="route(report.route)"
                        class="group relative bg-card rounded-lg shadow-sm border border p-4 hover:shadow-md transition-all duration-200"
                    >
                        <div class="flex items-center">
                            <div :class="[getColorClasses(report.color), 'p-2.5 rounded-lg transition-colors']">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getIconPath(report.icon)" />
                                </svg>
                            </div>
                            <div class="ml-3 flex-1 min-w-0">
                                <h4 class="text-sm font-semibold text-foreground group-hover:text-primary transition-colors">
                                    {{ report.name }}
                                </h4>
                                <p class="text-xs text-muted-foreground truncate">
                                    {{ report.description }}
                                </p>
                            </div>
                            <svg class="w-4 h-4 text-muted-foreground group-hover:text-primary transition-colors ml-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </Link>
                </div>

                <!-- Info Section -->
                <div class="mt-4 bg-primary/10 border border-primary-200 rounded-lg p-3">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-xs text-primary">
                            Semua laporan dapat difilter berdasarkan periode waktu dan diekspor ke PDF atau Excel.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
