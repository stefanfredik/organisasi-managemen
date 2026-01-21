<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import ChartWidget from '@/Components/ChartWidget.vue';
import UpcomingEvents from '@/Components/UpcomingEvents.vue';

const props = defineProps({
    stats: Object,
    recentActivities: Object,
    upcomingEvents: Array,
    recentAnnouncements: Array,
    financialSummary: Object,
    personalData: Object,
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
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                    <StatCard
                        title="Total Anggota"
                        :value="stats.totalMembers || 0"
                        icon="users"
                        color="indigo"
                    />
                    <StatCard
                        title="Anggota Aktif"
                        :value="stats.activeMembers || 0"
                        icon="users"
                        color="green"
                    />
                    <StatCard
                        title="Event Mendatang"
                        :value="stats.upcomingEvents || 0"
                        icon="calendar"
                        color="blue"
                    />
                    <StatCard
                        v-if="stats.totalBalance !== undefined"
                        title="Saldo Kas"
                        :value="formatCurrency(stats.totalBalance)"
                        icon="cash"
                        color="yellow"
                    />
                </div>

                <!-- Financial Stats (for admin, ketua, bendahara) -->
                <div v-if="stats.monthlyIncome !== undefined" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 mb-8">
                    <StatCard
                        title="Pemasukan Bulan Ini"
                        :value="formatCurrency(stats.monthlyIncome)"
                        icon="cash"
                        color="green"
                    />
                    <StatCard
                        title="Pengeluaran Bulan Ini"
                        :value="formatCurrency(stats.monthlyExpense)"
                        icon="cash"
                        color="red"
                    />
                    <StatCard
                        title="Iuran Tertunda"
                        :value="stats.pendingContributions || 0"
                        icon="document"
                        color="yellow"
                    />
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Content (2/3) -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Financial Chart -->
                        <ChartWidget
                            v-if="financialSummary"
                            title="Grafik Keuangan (6 Bulan Terakhir)"
                            :data="chartData"
                            type="bar"
                            color="indigo"
                            height="300px"
                        />

                        <!-- Upcoming Events -->
                        <UpcomingEvents :events="upcomingEvents" />

                        <!-- Recent Activities -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Aktivitas Terbaru</h3>
                            
                            <div v-if="recentActivities?.data && recentActivities.data.length > 0" class="space-y-3 mb-4">
                                <div
                                    v-for="activity in recentActivities.data"
                                    :key="activity.id"
                                    class="flex items-start text-sm"
                                >
                                    <div class="flex-shrink-0 w-2 h-2 bg-indigo-600 rounded-full mt-1.5 mr-3"></div>
                                    <div class="flex-1">
                                        <p class="text-gray-900">
                                            <span class="font-medium">{{ activity.user }}</span>
                                            {{ activity.description }}
                                        </p>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ activity.created_at }}</p>
                                    </div>
                                </div>
                            </div>
                            <p v-else class="text-sm text-gray-500 text-center py-4">Tidak ada aktivitas</p>
                            
                            <!-- Pagination -->
                            <div v-if="recentActivities?.data && recentActivities.data.length > 0" class="flex items-center justify-between border-t border-gray-200 pt-4">
                                <div class="text-sm text-gray-700">
                                    Menampilkan {{ recentActivities.from }} - {{ recentActivities.to }} dari {{ recentActivities.total }}
                                </div>
                                <div class="flex space-x-2">
                                    <Link
                                        v-if="recentActivities.prev_page_url"
                                        :href="recentActivities.prev_page_url"
                                        preserve-scroll
                                        class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50"
                                    >
                                        Sebelumnya
                                    </Link>
                                    <Link
                                        v-if="recentActivities.next_page_url"
                                        :href="recentActivities.next_page_url"
                                        preserve-scroll
                                        class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50"
                                    >
                                        Selanjutnya
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar (1/3) -->
                    <div class="space-y-6">
                        <!-- Announcements -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Pengumuman</h3>
                                <Link :href="route('announcements.index')" class="text-sm text-indigo-600 hover:text-indigo-700">
                                    Lihat Semua
                                </Link>
                            </div>
                            
                            <div v-if="recentAnnouncements && recentAnnouncements.length > 0" class="space-y-3">
                                <Link
                                    v-for="announcement in recentAnnouncements"
                                    :key="announcement.id"
                                    :href="route('announcements.show', announcement.id)"
                                    class="block p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
                                >
                                    <p class="text-sm font-medium text-gray-900 line-clamp-2">{{ announcement.title }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ formatDate(announcement.publish_date) }}</p>
                                </Link>
                            </div>
                            <p v-else class="text-sm text-gray-500 text-center py-4">Tidak ada pengumuman</p>
                        </div>

                        <!-- Personal Data (for members) -->
                        <div v-if="personalData" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Data Pribadi</h3>
                            
                            <div class="space-y-3">
                                <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                                    <span class="text-sm text-gray-700">Total Iuran Dibayar</span>
                                    <span class="text-sm font-semibold text-green-700">{{ formatCurrency(personalData.totalContributions) }}</span>
                                </div>
                                <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                                    <span class="text-sm text-gray-700">Iuran Tertunda</span>
                                    <span class="text-sm font-semibold text-yellow-700">{{ personalData.pendingContributions }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
