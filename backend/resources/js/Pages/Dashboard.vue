<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import ChartWidget from '@/Components/ChartWidget.vue';
import UpcomingEvents from '@/Components/UpcomingEvents.vue';
import { Calendar, MapPin, ChevronRight, Megaphone, Wallet, Users, CalendarDays, ArrowUpRight, ArrowDownRight, Cake } from 'lucide-vue-next';

const props = defineProps({
    stats: Object,
    upcomingEvents: Array,
    recentAnnouncements: Array,
    financialSummary: Object,
    personalData: Object,
    birthdayMembers: Array,
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

const formatCurrencyShort = (value) => {
    if (value >= 1000000) return 'Rp' + (value / 1000000).toFixed(1).replace('.0', '') + 'jt';
    if (value >= 1000) return 'Rp' + (value / 1000).toFixed(0) + 'rb';
    return 'Rp' + value;
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

const formatBirthday = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long' });
};

const formatEventDate = (dateString) => {
    const date = new Date(dateString);
    return {
        day: date.getDate(),
        month: date.toLocaleDateString('id-ID', { month: 'short' }).toUpperCase(),
    };
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

                <!-- ========== MOBILE LAYOUT ========== -->
                <div class="sm:hidden space-y-5">

                    <!-- Stats Grid: 2x2 compact -->
                    <div class="grid grid-cols-2 gap-3 animate-fade-up stagger-1">
                        <div class="bg-card rounded-2xl border p-3.5">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-8 h-8 rounded-xl bg-primary/10 flex items-center justify-center">
                                    <Users class="w-4 h-4 text-primary" />
                                </div>
                                <span class="text-[10px] font-bold uppercase text-muted-foreground">Anggota</span>
                            </div>
                            <p class="text-2xl font-black text-foreground">{{ stats.totalMembers || 0 }}</p>
                            <p class="text-[10px] text-muted-foreground mt-0.5">{{ stats.activeMembers || 0 }} aktif</p>
                        </div>
                        <div class="bg-card rounded-2xl border p-3.5">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-8 h-8 rounded-xl bg-blue-500/10 flex items-center justify-center">
                                    <CalendarDays class="w-4 h-4 text-blue-500" />
                                </div>
                                <span class="text-[10px] font-bold uppercase text-muted-foreground">Event</span>
                            </div>
                            <p class="text-2xl font-black text-foreground">{{ stats.upcomingEvents || 0 }}</p>
                            <p class="text-[10px] text-muted-foreground mt-0.5">Mendatang</p>
                        </div>
                        <div v-if="stats.totalBalance !== undefined" class="bg-card rounded-2xl border p-3.5 col-span-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-xl bg-amber-500/10 flex items-center justify-center">
                                        <Wallet class="w-4 h-4 text-amber-600" />
                                    </div>
                                    <span class="text-[10px] font-bold uppercase text-muted-foreground">Saldo Kas</span>
                                </div>
                                <p class="text-xl font-black text-foreground">{{ formatCurrency(stats.totalBalance) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Quick Stats -->
                    <div v-if="stats.monthlyIncome !== undefined" class="grid grid-cols-2 gap-3 animate-fade-up stagger-2">
                        <div class="bg-green-500/5 border border-green-500/10 rounded-2xl p-3.5">
                            <div class="flex items-center gap-1.5 mb-1">
                                <ArrowUpRight class="w-3.5 h-3.5 text-green-600" />
                                <span class="text-[10px] font-bold uppercase text-green-700/70">Pemasukan</span>
                            </div>
                            <p class="text-lg font-black text-green-700">{{ formatCurrencyShort(stats.monthlyIncome) }}</p>
                            <p class="text-[10px] text-muted-foreground">Bulan ini</p>
                        </div>
                        <div class="bg-red-500/5 border border-red-500/10 rounded-2xl p-3.5">
                            <div class="flex items-center gap-1.5 mb-1">
                                <ArrowDownRight class="w-3.5 h-3.5 text-destructive" />
                                <span class="text-[10px] font-bold uppercase text-red-700/70">Pengeluaran</span>
                            </div>
                            <p class="text-lg font-black text-destructive">{{ formatCurrencyShort(stats.monthlyExpense) }}</p>
                            <p class="text-[10px] text-muted-foreground">Bulan ini</p>
                        </div>
                    </div>

                    <!-- Personal Data Card (for members) -->
                    <div v-if="personalData" class="bg-card rounded-2xl border overflow-hidden animate-fade-up stagger-3">
                        <div class="px-4 py-3 border-b">
                            <h3 class="text-sm font-bold text-foreground">Iuran Saya</h3>
                        </div>
                        <div class="flex divide-x divide-border">
                            <div class="flex-1 p-3.5 text-center">
                                <p class="text-lg font-black text-green-700">{{ formatCurrencyShort(personalData.totalContributions) }}</p>
                                <p class="text-[10px] text-muted-foreground font-semibold uppercase mt-0.5">Dibayar</p>
                            </div>
                            <div class="flex-1 p-3.5 text-center">
                                <p class="text-lg font-black text-amber-600">{{ personalData.pendingContributions }}</p>
                                <p class="text-[10px] text-muted-foreground font-semibold uppercase mt-0.5">Tertunda</p>
                            </div>
                        </div>
                    </div>

                    <!-- Birthdays This Week -->
                    <div v-if="birthdayMembers && birthdayMembers.length > 0" class="animate-fade-up stagger-3">
                        <div class="flex items-center justify-between mb-3 px-1">
                            <h3 class="text-sm font-bold text-foreground">Ulang Tahun Minggu Ini</h3>
                        </div>
                        <div class="bg-card rounded-2xl border overflow-hidden divide-y divide-border">
                            <div
                                v-for="member in birthdayMembers"
                                :key="member.id"
                                class="flex items-center gap-3 px-4 py-3"
                            >
                                <div class="w-10 h-10 rounded-full bg-pink-500/10 flex items-center justify-center shrink-0 relative">
                                    <img v-if="member.photo" :src="`/storage/${member.photo}`" class="w-10 h-10 rounded-full object-cover" />
                                    <Cake v-else class="w-4.5 h-4.5 text-pink-500" />
                                    <span v-if="member.is_today" class="absolute -top-0.5 -right-0.5 w-3 h-3 bg-pink-500 rounded-full border-2 border-card"></span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-foreground truncate">{{ member.full_name }}</p>
                                    <p class="text-[11px] text-muted-foreground mt-0.5">
                                        {{ formatBirthday(member.date_of_birth) }}
                                        <span v-if="member.is_today" class="text-pink-500 font-bold ml-1">Hari ini!</span>
                                    </p>
                                </div>
                                <Cake v-if="member.is_today" class="w-4 h-4 text-pink-500 shrink-0 animate-bounce" />
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming Events -->
                    <div v-if="upcomingEvents && upcomingEvents.length > 0" class="animate-fade-up stagger-3">
                        <div class="flex items-center justify-between mb-3 px-1">
                            <h3 class="text-sm font-bold text-foreground">Event Mendatang</h3>
                            <Link :href="route('events.index')" class="text-xs font-semibold text-primary flex items-center gap-0.5">
                                Semua <ChevronRight class="w-3.5 h-3.5" />
                            </Link>
                        </div>
                        <div class="space-y-2">
                            <Link
                                v-for="event in upcomingEvents.slice(0, 3)"
                                :key="event.id"
                                :href="route('events.show', event.id)"
                                class="flex items-center gap-3 bg-card rounded-2xl border p-3 active:scale-[0.98] transition-transform"
                            >
                                <div class="w-12 h-12 rounded-xl bg-primary/10 flex flex-col items-center justify-center shrink-0">
                                    <span class="text-primary font-black text-base leading-none">{{ formatEventDate(event.start_date).day }}</span>
                                    <span class="text-primary/60 text-[9px] font-bold">{{ formatEventDate(event.start_date).month }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-foreground truncate">{{ event.name }}</p>
                                    <p v-if="event.location" class="text-xs text-muted-foreground mt-0.5 flex items-center gap-1 truncate">
                                        <MapPin class="w-3 h-3 shrink-0" />
                                        {{ event.location }}
                                    </p>
                                    <p v-else class="text-xs text-muted-foreground mt-0.5">{{ formatDate(event.start_date) }}</p>
                                </div>
                                <ChevronRight class="w-4 h-4 text-muted-foreground/50 shrink-0" />
                            </Link>
                        </div>
                    </div>

                    <!-- Announcements -->
                    <div v-if="recentAnnouncements && recentAnnouncements.length > 0" class="animate-fade-up stagger-4">
                        <div class="flex items-center justify-between mb-3 px-1">
                            <h3 class="text-sm font-bold text-foreground">Pengumuman</h3>
                            <Link :href="route('announcements.index')" class="text-xs font-semibold text-primary flex items-center gap-0.5">
                                Semua <ChevronRight class="w-3.5 h-3.5" />
                            </Link>
                        </div>
                        <div class="bg-card rounded-2xl border overflow-hidden divide-y divide-border">
                            <Link
                                v-for="announcement in recentAnnouncements.slice(0, 3)"
                                :key="announcement.id"
                                :href="route('announcements.show', announcement.id)"
                                class="flex items-center gap-3 px-4 py-3 active:bg-muted/50 transition-colors"
                            >
                                <div class="w-9 h-9 rounded-xl bg-amber-500/10 flex items-center justify-center shrink-0">
                                    <Megaphone class="w-4 h-4 text-amber-600" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-foreground truncate">{{ announcement.title }}</p>
                                    <p class="text-[11px] text-muted-foreground mt-0.5">{{ formatDate(announcement.publish_date) }}</p>
                                </div>
                                <ChevronRight class="w-4 h-4 text-muted-foreground/50 shrink-0" />
                            </Link>
                        </div>
                    </div>

                    <!-- Chart (compact on mobile) -->
                    <div v-if="financialSummary" class="animate-fade-up stagger-5">
                        <ChartWidget
                            title="Keuangan 6 Bulan"
                            :data="chartData"
                            type="bar"
                            color="primary"
                            height="200px"
                        />
                    </div>
                </div>

                <!-- ========== DESKTOP LAYOUT ========== -->
                <div class="hidden sm:block">
                    <!-- Grid stats -->
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
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
                    <div v-if="stats.monthlyIncome !== undefined" class="grid grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                        <StatCard title="Pemasukan Bulan Ini" :value="formatCurrency(stats.monthlyIncome)" icon="cash" color="success" />
                        <StatCard title="Pengeluaran Bulan Ini" :value="formatCurrency(stats.monthlyExpense)" icon="cash" color="danger" />
                        <StatCard title="Iuran Tertunda" :value="stats.pendingContributions || 0" icon="document" color="warning" />
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
                                color="primary"
                                height="300px"
                            />

                            <!-- Upcoming Events -->
                            <UpcomingEvents :events="upcomingEvents" />
                        </div>

                        <!-- Sidebar (1/3) -->
                        <div class="space-y-6">
                            <!-- Announcements -->
                            <div class="bg-card rounded-lg shadow-sm border p-6">
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

                            <!-- Birthdays This Week -->
                            <div v-if="birthdayMembers && birthdayMembers.length > 0" class="bg-card rounded-lg shadow-sm border p-6">
                                <div class="flex items-center gap-2 mb-4">
                                    <Cake class="w-5 h-5 text-pink-500" />
                                    <h3 class="text-lg font-semibold text-foreground">Ulang Tahun Minggu Ini</h3>
                                </div>
                                <div class="space-y-3">
                                    <div
                                        v-for="member in birthdayMembers"
                                        :key="member.id"
                                        class="flex items-center gap-3 p-3 rounded-lg"
                                        :class="member.is_today ? 'bg-pink-500/10' : 'bg-muted'"
                                    >
                                        <div class="w-9 h-9 rounded-full bg-pink-500/10 flex items-center justify-center shrink-0 overflow-hidden">
                                            <img v-if="member.photo" :src="`/storage/${member.photo}`" class="w-9 h-9 rounded-full object-cover" />
                                            <Cake v-else class="w-4 h-4 text-pink-500" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-foreground truncate">{{ member.full_name }}</p>
                                            <p class="text-xs text-muted-foreground">
                                                {{ formatBirthday(member.date_of_birth) }}
                                                <span v-if="member.is_today" class="text-pink-500 font-bold ml-1">Hari ini!</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Personal Data (for members) -->
                            <div v-if="personalData" class="bg-card rounded-lg shadow-sm border p-6">
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
        </div>
    </AuthenticatedLayout>
</template>
