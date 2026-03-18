<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import ChartWidget from '@/Components/ChartWidget.vue';
import UpcomingEvents from '@/Components/UpcomingEvents.vue';

const props = defineProps({
    stats: Object,
    upcomingEvents: Array,
    recentAnnouncements: Array,
    financialSummary: Object,
    personalData: Object,
});

const page = usePage();
const userName = computed(() => page.props.auth.user.name.split(' ')[0]);

const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Selamat Pagi';
    if (hour < 15) return 'Selamat Siang';
    if (hour < 18) return 'Selamat Sore';
    return 'Selamat Malam';
});

const chartData = computed(() => {
    if (!props.financialSummary?.monthlyData) return [];
    return props.financialSummary.monthlyData.map(item => ({
        label: item.month,
        value: item.net,
    }));
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <!-- Mobile: Personalized greeting -->
            <div class="lg:hidden">
                <p class="text-sm text-muted-foreground">{{ greeting }} 👋</p>
                <h2 class="text-xl font-bold text-foreground">{{ userName }}</h2>
            </div>
            <!-- Desktop: Standard header -->
            <h2 class="hidden lg:block text-xl font-semibold leading-tight text-foreground">
                Dashboard
            </h2>
        </template>

        <div class="py-4 sm:py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <!-- Mobile: Horizontal scrollable stats -->
                <div class="sm:hidden mobile-scroll-x mb-6">
                    <div class="w-[70vw] shrink-0 animate-fade-up stagger-1">
                        <StatCard
                            title="Total Anggota"
                            :value="stats.totalMembers || 0"
                            icon="users"
                            color="primary"
                        />
                    </div>
                    <div class="w-[70vw] shrink-0 animate-fade-up stagger-2">
                        <StatCard
                            title="Anggota Aktif"
                            :value="stats.activeMembers || 0"
                            icon="users"
                            color="success"
                        />
                    </div>
                    <div class="w-[70vw] shrink-0 animate-fade-up stagger-3">
                        <StatCard
                            title="Event Mendatang"
                            :value="stats.upcomingEvents || 0"
                            icon="calendar"
                            color="info"
                        />
                    </div>
                    <div v-if="stats.totalBalance !== undefined" class="w-[70vw] shrink-0 animate-fade-up stagger-4">
                        <StatCard
                            title="Saldo Kas"
                            :value="formatCurrency(stats.totalBalance)"
                            icon="cash"
                            color="warning"
                        />
                    </div>
                </div>

                <!-- Desktop: Grid stats -->
                <div class="hidden sm:grid grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <StatCard
                        title="Total Anggota"
                        :value="stats.totalMembers || 0"
                        icon="users"
                        color="primary"
                    />
                    <StatCard
                        title="Anggota Aktif"
                        :value="stats.activeMembers || 0"
                        icon="users"
                        color="success"
                    />
                    <StatCard
                        title="Event Mendatang"
                        :value="stats.upcomingEvents || 0"
                        icon="calendar"
                        color="info"
                    />
                    <StatCard
                        v-if="stats.totalBalance !== undefined"
                        title="Saldo Kas"
                        :value="formatCurrency(stats.totalBalance)"
                        icon="cash"
                        color="warning"
                    />
                </div>

                <!-- Financial Stats (for admin, ketua, bendahara) -->
                <div v-if="stats.monthlyIncome !== undefined">
                    <!-- Mobile: horizontal scroll -->
                    <div class="sm:hidden mobile-scroll-x mb-6">
                        <div class="w-[70vw] shrink-0">
                            <StatCard title="Pemasukan" :value="formatCurrency(stats.monthlyIncome)" icon="cash" color="success" />
                        </div>
                        <div class="w-[70vw] shrink-0">
                            <StatCard title="Pengeluaran" :value="formatCurrency(stats.monthlyExpense)" icon="cash" color="danger" />
                        </div>
                        <div class="w-[70vw] shrink-0">
                            <StatCard title="Iuran Tertunda" :value="stats.pendingContributions || 0" icon="document" color="warning" />
                        </div>
                    </div>
                    <!-- Desktop: grid -->
                    <div class="hidden sm:grid grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                        <StatCard title="Pemasukan Bulan Ini" :value="formatCurrency(stats.monthlyIncome)" icon="cash" color="success" />
                        <StatCard title="Pengeluaran Bulan Ini" :value="formatCurrency(stats.monthlyExpense)" icon="cash" color="danger" />
                        <StatCard title="Iuran Tertunda" :value="stats.pendingContributions || 0" icon="document" color="warning" />
                    </div>
                </div>

                <!-- Mobile: Announcements as horizontal cards (contextual, important first) -->
                <div v-if="recentAnnouncements && recentAnnouncements.length > 0" class="sm:hidden mb-6">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-bold text-foreground">Pengumuman</h3>
                        <Link :href="route('announcements.index')" class="text-xs font-semibold text-primary">
                            Lihat Semua
                        </Link>
                    </div>
                    <div class="mobile-scroll-x">
                        <Link
                            v-for="announcement in recentAnnouncements"
                            :key="announcement.id"
                            :href="route('announcements.show', announcement.id)"
                            class="w-[75vw] shrink-0 mobile-card"
                        >
                            <p class="text-sm font-semibold text-foreground line-clamp-2">{{ announcement.title }}</p>
                            <p class="text-xs text-muted-foreground mt-1.5">{{ formatDate(announcement.publish_date) }}</p>
                        </Link>
                    </div>
                </div>

                <!-- Mobile: Personal Data card (contextual for members) -->
                <div v-if="personalData" class="sm:hidden mb-6 animate-fade-up">
                    <div class="bg-card rounded-2xl border p-4 space-y-3">
                        <h3 class="text-sm font-bold text-foreground">Iuran Saya</h3>
                        <div class="flex gap-3">
                            <div class="flex-1 p-3 bg-success/10 rounded-xl text-center">
                                <p class="text-lg font-bold text-success-700">{{ formatCurrency(personalData.totalContributions) }}</p>
                                <p class="text-[10px] text-muted-foreground font-semibold uppercase mt-0.5">Dibayar</p>
                            </div>
                            <div class="flex-1 p-3 bg-warning-50 rounded-xl text-center">
                                <p class="text-lg font-bold text-warning-700">{{ personalData.pendingContributions }}</p>
                                <p class="text-[10px] text-muted-foreground font-semibold uppercase mt-0.5">Tertunda</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
                    <!-- Main Content (2/3) -->
                    <div class="lg:col-span-2 space-y-4 sm:space-y-6">
                        <!-- Financial Chart -->
                        <ChartWidget
                            v-if="financialSummary"
                            title="Grafik Keuangan (6 Bulan Terakhir)"
                            :data="chartData"
                            type="bar"
                            color="primary"
                            height="300px"
                        />

                        <!-- Upcoming Events -->
                        <UpcomingEvents :events="upcomingEvents" />
                    </div>

                    <!-- Sidebar (1/3) — hidden on mobile since shown above -->
                    <div class="space-y-6">
                        <!-- Announcements — desktop only (shown horizontally on mobile above) -->
                        <div class="hidden sm:block bg-card rounded-lg shadow-sm border p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-foreground">Pengumuman</h3>
                                <Link :href="route('announcements.index')" class="text-sm text-primary hover:text-primary">
                                    Lihat Semua
                                </Link>
                            </div>

                            <div v-if="recentAnnouncements && recentAnnouncements.length > 0" class="space-y-3">
                                <Link
                                    v-for="announcement in recentAnnouncements"
                                    :key="announcement.id"
                                    :href="route('announcements.show', announcement.id)"
                                    class="block p-3 bg-muted rounded-lg hover:bg-muted transition-colors"
                                >
                                    <p class="text-sm font-medium text-foreground line-clamp-2">{{ announcement.title }}</p>
                                    <p class="text-xs text-muted-foreground mt-1">{{ formatDate(announcement.publish_date) }}</p>
                                </Link>
                            </div>
                            <p v-else class="text-sm text-muted-foreground text-center py-4">Tidak ada pengumuman</p>
                        </div>

                        <!-- Personal Data (for members) — desktop only (shown above on mobile) -->
                        <div v-if="personalData" class="hidden sm:block bg-card rounded-lg shadow-sm border p-6">
                            <h3 class="text-lg font-semibold text-foreground mb-4">Data Pribadi</h3>

                            <div class="space-y-3">
                                <div class="flex items-center justify-between p-3 bg-success/10 rounded-lg">
                                    <span class="text-sm text-foreground">Total Iuran Dibayar</span>
                                    <span class="text-sm font-semibold text-success-700">{{ formatCurrency(personalData.totalContributions) }}</span>
                                </div>
                                <div class="flex items-center justify-between p-3 bg-warning-50 rounded-lg">
                                    <span class="text-sm text-foreground">Iuran Tertunda</span>
                                    <span class="text-sm font-semibold text-warning-700">{{ personalData.pendingContributions }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
