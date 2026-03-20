<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import { Link, usePage } from "@inertiajs/vue3";
import FlashMessage from "@/Components/FlashMessage.vue";
import Toaster from "@/Components/Toaster.vue";
import NotificationPanel from "@/Components/NotificationPanel.vue";
import { Button } from "@/components/ui/button";
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import { Separator } from "@/components/ui/separator";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetDescription,
} from "@/components/ui/sheet";
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from "@/components/ui/tooltip";
import {
    LayoutDashboard,
    Users,
    CalendarDays,
    Network,
    Image,
    Eye,
    Wallet,
    BarChart3,
    PiggyBank,
    Coins,
    Settings2,
    HandCoins,
    Megaphone,
    FileText,
    UserCog,
    Clock,
    Database,
    Settings,
    FileBarChart,
    Menu,
    ChevronsLeft,
    LogOut,
    User,
    ChevronDown,
    MoreHorizontal,
    X,
    Shield,
    Briefcase,
} from "lucide-vue-next";

const page = usePage();
const notifications = computed(() => page.props.notifications || []);
const mobileOpen = ref(false);
const moreMenuOpen = ref(false);
const isSidebarOpen = ref(true);

// Mobile: hide bottom nav on scroll down, show on scroll up
const showBottomNav = ref(true);
const lastScrollY = ref(0);

const handleScroll = () => {
    const currentScrollY = window.scrollY;
    if (currentScrollY < 50) {
        showBottomNav.value = true;
    } else if (currentScrollY > lastScrollY.value + 10) {
        showBottomNav.value = false;
        moreMenuOpen.value = false;
    } else if (currentScrollY < lastScrollY.value - 10) {
        showBottomNav.value = true;
    }
    lastScrollY.value = currentScrollY;
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll, { passive: true });
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});

const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
};

const hasPermission = (permission) => {
    if (page.props.auth.user.role === "admin") return true;
    return page.props.auth.user.permissions?.includes(permission);
};

const userName = computed(() => page.props.auth.user.name);
const userInitial = computed(() => userName.value.charAt(0).toUpperCase());
const userEmail = computed(() => page.props.auth.user.email);
const userRole = computed(() => page.props.auth.user.role);
const userPosition = computed(() => page.props.auth.user.position);
const isAdmin = computed(() => userRole.value === "admin");
const appName = computed(() => page.props.appSettings.name);
const appNameFirst = computed(() => appName.value.split(" ")[0]);
const appNameRest = computed(() => appName.value.split(" ").slice(1).join(" "));

// Navigation items config
const navGroups = computed(() => [
    {
        label: "Utama",
        items: [
            { icon: LayoutDashboard, label: "Dashboard", route: "dashboard", pattern: "dashboard", show: true },
            { icon: Users, label: "Anggota", route: "members.index", pattern: "members.*", show: hasPermission("view_members") },
            { icon: CalendarDays, label: "Kegiatan", route: "events.index", pattern: "events.*", show: hasPermission("view_events") },
            { icon: Network, label: "Struktur Organisasi", route: "organization-structures.index", pattern: "organization-structures.*", show: true },
        ],
    },
    {
        label: "Konten",
        items: [
            { icon: Image, label: "Album", route: "albums.index", pattern: "albums.*", show: true },
            { icon: Eye, label: "Visi & Misi", route: "vision-missions.index", pattern: "vision-missions.*", show: true },
        ],
    },
    {
        label: "Keuangan",
        items: [
            { icon: Wallet, label: "Kas (Dompet)", route: "wallets.index", pattern: "wallets.*", show: hasPermission("view_finance") },
            { icon: BarChart3, label: "Transaksi", route: "finances.index", pattern: "finances.*", show: hasPermission("view_finance") },
            { icon: PiggyBank, label: "Monitoring Iuran", route: "contributions.monitoring.index", pattern: "contributions.monitoring.*", show: hasPermission("view_contribution_monitoring") },
            {
                icon: Coins,
                label: userPosition.value === "anggota" ? "Iuran Saya" : "Pembayaran Iuran",
                route: "contributions.index",
                pattern: "contributions.index",
                activeCheck: () => route().current("contributions.index") || route().current("contributions.verification"),
                show: hasPermission("view_contributions"),
            },
            { icon: Settings2, label: "Manajemen Iuran", route: "contribution-types.index", pattern: "contribution-types.*", show: hasPermission("view_contribution_types") },
            { icon: HandCoins, label: "Donasi", route: "donations.index", pattern: "donations.*", show: route().has("donations.index") && hasPermission("view_donations") },
        ],
    },
    {
        label: "Administrasi",
        items: [
            { icon: Megaphone, label: "Pengumuman", route: "announcements.index", pattern: "announcements.*", show: true },
            { icon: FileText, label: "Notulensi Rapat", route: "meeting-minutes.index", pattern: "meeting-minutes.*", show: true },
            { icon: UserCog, label: "Manajemen User", route: "users.index", pattern: "users.*", show: isAdmin.value },
            { icon: Shield, label: "Role & Jabatan", route: "roles.index", activeCheck: () => route().current("roles.*") || route().current("positions.*"), show: isAdmin.value },
            { icon: Clock, label: "Log Aktivitas", route: "activity-logs.index", pattern: "activity-logs.*", show: isAdmin.value },
            { icon: Database, label: "Backup Data", route: "backups.index", pattern: "backups.*", show: isAdmin.value },
            { icon: Settings, label: "Pengaturan", route: "settings.index", pattern: "settings.*", show: isAdmin.value },
        ],
    },
    {
        label: "Laporan",
        items: [
            { icon: FileBarChart, label: "Laporan", route: "reports.index", pattern: "reports.*", show: hasPermission("view_reports") },
        ],
    },
]);

// Bottom nav: show max 4 primary items + "more" button
const bottomNavItems = computed(() => {
    const primary = [
        { icon: LayoutDashboard, label: "Home", route: "dashboard", pattern: "dashboard", show: true },
        { icon: Users, label: "Anggota", route: "members.index", pattern: "members.*", show: hasPermission("view_members") },
        { icon: CalendarDays, label: "Kegiatan", route: "events.index", pattern: "events.*", show: hasPermission("view_events") },
        { icon: Wallet, label: "Keuangan", route: "finances.index", pattern: "finances.*", show: hasPermission("view_finance") },
    ];
    return primary.filter(i => i.show).slice(0, 4);
});

// Items for "More" bottom sheet (everything not in bottom nav)
const moreNavItems = computed(() => {
    const bottomRoutes = new Set(bottomNavItems.value.map(i => i.route));
    const items = [];
    for (const group of navGroups.value) {
        const visible = group.items.filter(i => i.show && !bottomRoutes.has(i.route));
        if (visible.length) {
            items.push({ label: group.label, items: visible });
        }
    }
    return items;
});

const isActive = (item) => {
    if (item.activeCheck) return item.activeCheck();
    return route().current(item.pattern);
};

// Check if any "more" item is active (to highlight the more button)
const isMoreActive = computed(() => {
    const bottomRoutes = new Set(bottomNavItems.value.map(i => i.route));
    for (const group of navGroups.value) {
        for (const item of group.items) {
            if (item.show && !bottomRoutes.has(item.route) && isActive(item)) {
                return true;
            }
        }
    }
    return false;
});
</script>

<template>
    <TooltipProvider :delay-duration="0">
        <div class="min-h-screen bg-background flex">
            <FlashMessage />
            <Toaster />

            <!-- ========================================
                 SIDEBAR — Desktop (lg+)
                 ======================================== -->
            <aside
                :class="[
                    'fixed inset-y-0 left-0 z-50 hidden lg:flex flex-col border-r bg-card transition-all duration-300 overflow-hidden',
                    isSidebarOpen ? 'w-64' : 'w-[68px]',
                ]"
            >
                <!-- Sidebar Header -->
                <div class="h-16 flex items-center px-4 border-b shrink-0">
                    <Link :href="route('dashboard')" class="flex items-center gap-3 min-w-0">
                        <ApplicationLogo class="h-8 w-8 shrink-0 fill-current text-primary" />
                        <span
                            v-show="isSidebarOpen"
                            class="font-black text-xs uppercase tracking-tighter text-foreground truncate leading-tight"
                        >
                            {{ appNameFirst }}<br />
                            <span class="text-primary">{{ appNameRest }}</span>
                        </span>
                    </Link>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-6">
                    <div v-for="group in navGroups" :key="group.label">
                        <p
                            v-show="isSidebarOpen"
                            class="px-3 mb-2 text-[10px] font-bold uppercase tracking-widest text-muted-foreground"
                        >
                            {{ group.label }}
                        </p>
                        <div v-if="!isSidebarOpen && group !== navGroups[0]" class="my-2">
                            <Separator />
                        </div>
                        <div class="space-y-0.5">
                            <template v-for="item in group.items" :key="item.route">
                                <Tooltip v-if="item.show && !isSidebarOpen">
                                    <TooltipTrigger>
                                        <Link
                                            :href="route(item.route)"
                                            :class="[
                                                'flex items-center justify-center w-10 h-10 rounded-lg transition-all mx-auto',
                                                isActive(item)
                                                    ? 'bg-primary/10 text-primary'
                                                    : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground',
                                            ]"
                                        >
                                            <component :is="item.icon" class="h-5 w-5" />
                                        </Link>
                                    </TooltipTrigger>
                                    <TooltipContent side="right">
                                        {{ item.label }}
                                    </TooltipContent>
                                </Tooltip>
                                <Link
                                    v-else-if="item.show"
                                    :href="route(item.route)"
                                    :class="[
                                        'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all',
                                        isActive(item)
                                            ? 'bg-primary/10 text-primary'
                                            : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground',
                                    ]"
                                >
                                    <component :is="item.icon" class="h-5 w-5 shrink-0" />
                                    <span v-show="isSidebarOpen" class="truncate">{{ item.label }}</span>
                                </Link>
                            </template>
                        </div>
                    </div>
                </nav>

                <!-- Sidebar Toggle -->
                <div class="p-3 border-t">
                    <Button
                        variant="ghost"
                        size="icon"
                        @click="toggleSidebar"
                        class="w-full h-10"
                    >
                        <ChevronsLeft
                            :class="['h-5 w-5 transition-transform duration-300', !isSidebarOpen ? 'rotate-180' : '']"
                        />
                    </Button>
                </div>
            </aside>

            <!-- ========================================
                 MAIN CONTENT WRAPPER
                 ======================================== -->
            <div
                :class="[
                    'flex-1 flex flex-col transition-all duration-300 min-w-0 w-full',
                    isSidebarOpen ? 'lg:pl-64' : 'lg:pl-[68px]',
                ]"
            >
                <!-- Top Header Bar -->
                <header class="h-14 sm:h-16 border-b bg-card/95 backdrop-blur-xl sticky top-0 z-40 px-3 sm:px-4 md:px-6 lg:px-8 flex items-center justify-between gap-2">
                    <!-- Mobile: hamburger for full menu -->
                    <Button
                        variant="ghost"
                        size="icon"
                        class="lg:hidden -ml-1 tap-highlight touch-target"
                        @click="mobileOpen = true"
                    >
                        <Menu class="h-5 w-5" />
                        <span class="sr-only">Open menu</span>
                    </Button>

                    <!-- Mobile: App name centered -->
                    <div class="lg:hidden flex-1 flex items-center justify-center">
                        <Link :href="route('dashboard')" class="flex items-center gap-2">
                            <ApplicationLogo class="h-6 w-6 shrink-0 fill-current text-primary" />
                            <span class="font-black text-xs uppercase tracking-tight text-foreground">
                                {{ appNameFirst }}
                            </span>
                        </Link>
                    </div>

                    <!-- Desktop: Page Info -->
                    <div class="hidden lg:block text-xs font-semibold text-muted-foreground uppercase tracking-widest truncate">
                        {{ route().current() }}
                    </div>

                    <div class="flex items-center gap-1 sm:gap-3">
                        <!-- Notifications -->
                        <NotificationPanel :notifications="notifications" />

                        <!-- User Menu — avatar only on mobile, expanded on desktop -->
                        <DropdownMenu>
                            <DropdownMenuTrigger>
                                <Button variant="ghost" class="gap-2 px-1.5 sm:px-2 tap-highlight">
                                    <Avatar size="sm" class="bg-primary text-primary-foreground h-8 w-8">
                                        <AvatarFallback class="bg-primary text-primary-foreground font-bold text-xs">
                                            {{ userInitial }}
                                        </AvatarFallback>
                                    </Avatar>
                                    <span class="hidden sm:inline-block text-sm font-semibold truncate max-w-[120px]">
                                        {{ userName }}
                                    </span>
                                    <ChevronDown class="h-4 w-4 text-muted-foreground hidden sm:block" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-56">
                                <DropdownMenuLabel>
                                    <div class="flex flex-col space-y-1">
                                        <p class="text-sm font-medium">{{ userName }}</p>
                                        <p class="text-xs text-muted-foreground truncate">{{ userEmail }}</p>
                                    </div>
                                </DropdownMenuLabel>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem as-child>
                                    <Link :href="route('profile.edit')" class="flex items-center gap-2 cursor-pointer">
                                        <User class="h-4 w-4" />
                                        Profil
                                    </Link>
                                </DropdownMenuItem>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem as-child>
                                    <Link :href="route('logout')" method="post" as="button" class="flex items-center gap-2 cursor-pointer w-full text-destructive">
                                        <LogOut class="h-4 w-4" />
                                        Keluar
                                    </Link>
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </header>

                <!-- ========================================
                     MOBILE NAVIGATION — Full Menu Sheet (from hamburger)
                     ======================================== -->
                <Sheet :open="mobileOpen" @update:open="mobileOpen = $event">
                    <SheetContent side="left" class="w-[300px] max-w-[85vw] p-0 flex flex-col" :show-close="false">
                        <SheetHeader class="p-4 pb-0 sr-only">
                            <SheetTitle>Navigasi</SheetTitle>
                            <SheetDescription>Menu navigasi utama</SheetDescription>
                        </SheetHeader>

                        <!-- Mobile Logo -->
                        <div class="h-14 flex items-center px-4 border-b shrink-0">
                            <Link :href="route('dashboard')" class="flex items-center gap-3 min-w-0" @click="mobileOpen = false">
                                <ApplicationLogo class="h-7 w-7 shrink-0 fill-current text-primary" />
                                <span class="font-black text-[10px] uppercase tracking-tighter text-foreground leading-none truncate">
                                    {{ appNameFirst }}<br />
                                    <span class="text-primary">{{ appNameRest }}</span>
                                </span>
                            </Link>
                        </div>

                        <!-- Mobile User Info -->
                        <div class="px-4 py-3 border-b bg-muted/30">
                            <div class="flex items-center gap-3">
                                <Avatar size="sm" class="bg-primary text-primary-foreground h-10 w-10">
                                    <AvatarFallback class="bg-primary text-primary-foreground font-bold text-sm">
                                        {{ userInitial }}
                                    </AvatarFallback>
                                </Avatar>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-foreground truncate">{{ userName }}</p>
                                    <p class="text-xs text-muted-foreground truncate">{{ userEmail }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Mobile Nav Links -->
                        <div class="flex-1 overflow-y-auto py-4 px-3 space-y-5 overscroll-contain">
                            <div v-for="group in navGroups" :key="group.label">
                                <p class="px-3 mb-2 text-[10px] font-bold uppercase text-muted-foreground tracking-widest">
                                    {{ group.label }}
                                </p>
                                <div class="space-y-0.5">
                                    <Link
                                        v-for="item in group.items.filter(i => i.show)"
                                        :key="item.route"
                                        :href="route(item.route)"
                                        :class="[
                                            'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all tap-highlight touch-target',
                                            isActive(item)
                                                ? 'bg-primary/10 text-primary'
                                                : 'text-muted-foreground active:bg-accent/70',
                                        ]"
                                        @click="mobileOpen = false"
                                    >
                                        <component :is="item.icon" class="h-5 w-5 shrink-0" />
                                        <span class="truncate">{{ item.label }}</span>
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- Mobile Footer -->
                        <div class="px-3 py-3 border-t space-y-0.5 shrink-0 safe-bottom">
                            <Link
                                :href="route('profile.edit')"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-muted-foreground active:bg-accent/70 transition-all tap-highlight touch-target"
                                @click="mobileOpen = false"
                            >
                                <User class="h-5 w-5 shrink-0" />
                                Profil
                            </Link>
                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-destructive active:bg-destructive/10 transition-all tap-highlight touch-target w-full"
                                @click="mobileOpen = false"
                            >
                                <LogOut class="h-5 w-5 shrink-0" />
                                Keluar
                            </Link>
                        </div>
                    </SheetContent>
                </Sheet>

                <!-- ========================================
                     MOBILE "MORE" BOTTOM SHEET
                     ======================================== -->
                <Sheet :open="moreMenuOpen" @update:open="moreMenuOpen = $event">
                    <SheetContent side="bottom" class="rounded-t-3xl p-0 max-h-[75vh] flex flex-col" :show-close="false">
                        <SheetHeader class="sr-only">
                            <SheetTitle>Menu Lainnya</SheetTitle>
                            <SheetDescription>Menu navigasi tambahan</SheetDescription>
                        </SheetHeader>

                        <!-- Bottom sheet handle -->
                        <div class="bottom-sheet-handle" />

                        <!-- Header -->
                        <div class="flex items-center justify-between px-5 pb-3">
                            <h3 class="text-base font-bold text-foreground">Menu Lainnya</h3>
                            <button
                                @click="moreMenuOpen = false"
                                class="p-2 -mr-2 rounded-full text-muted-foreground active:bg-accent tap-highlight"
                            >
                                <X class="h-5 w-5" />
                            </button>
                        </div>

                        <!-- Grid menu items -->
                        <div class="flex-1 overflow-y-auto overscroll-contain px-5 pb-6">
                            <div v-for="group in moreNavItems" :key="group.label" class="mb-5 last:mb-0">
                                <p class="text-[10px] font-bold uppercase text-muted-foreground tracking-widest mb-3">
                                    {{ group.label }}
                                </p>
                                <div class="grid grid-cols-4 gap-2">
                                    <Link
                                        v-for="item in group.items"
                                        :key="item.route"
                                        :href="route(item.route)"
                                        :class="[
                                            'flex flex-col items-center justify-center gap-1.5 p-3 rounded-2xl transition-all tap-highlight active:scale-90',
                                            isActive(item)
                                                ? 'bg-primary/10 text-primary'
                                                : 'text-muted-foreground active:bg-accent/70',
                                        ]"
                                        @click="moreMenuOpen = false"
                                    >
                                        <component :is="item.icon" class="h-6 w-6" />
                                        <span class="text-[10px] font-semibold text-center leading-tight line-clamp-2">{{ item.label }}</span>
                                    </Link>
                                </div>
                            </div>

                            <!-- Quick actions at bottom -->
                            <div class="mt-4 pt-4 border-t space-y-1">
                                <Link
                                    :href="route('profile.edit')"
                                    class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium text-muted-foreground active:bg-accent/70 tap-highlight touch-target"
                                    @click="moreMenuOpen = false"
                                >
                                    <User class="h-5 w-5 shrink-0" />
                                    <span>Profil Saya</span>
                                </Link>
                                <Link
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                    class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium text-destructive active:bg-destructive/10 tap-highlight touch-target w-full"
                                    @click="moreMenuOpen = false"
                                >
                                    <LogOut class="h-5 w-5 shrink-0" />
                                    <span>Keluar</span>
                                </Link>
                            </div>
                        </div>
                    </SheetContent>
                </Sheet>

                <!-- Page Heading -->
                <div v-if="$slots.header" class="bg-card border-b px-3 sm:px-4 md:px-6 lg:px-8 py-4 sm:py-6">
                    <div class="mx-auto max-w-7xl">
                        <slot name="header" />
                    </div>
                </div>

                <!-- Page Content — add bottom padding on mobile for bottom nav -->
                <main class="flex-1 min-w-0 pb-bottom-nav">
                    <slot />
                </main>
            </div>

            <!-- ========================================
                 MOBILE BOTTOM NAVIGATION BAR (lg- only)
                 ======================================== -->
            <nav
                :class="[
                    'mobile-bottom-nav lg:hidden transition-transform duration-300 ease-out',
                    showBottomNav ? 'translate-y-0' : 'translate-y-full',
                ]"
            >
                <div class="flex items-stretch h-[var(--bottom-nav-height)]">
                    <!-- Primary nav items -->
                    <Link
                        v-for="item in bottomNavItems"
                        :key="item.route"
                        :href="route(item.route)"
                        :class="[
                            'mobile-bottom-nav-item',
                            isActive(item) ? 'active' : '',
                        ]"
                    >
                        <component :is="item.icon" class="nav-icon" />
                        <span class="nav-label">{{ item.label }}</span>
                        <div v-if="isActive(item)" class="nav-dot" />
                    </Link>

                    <!-- More button -->
                    <button
                        :class="[
                            'mobile-bottom-nav-item',
                            isMoreActive ? 'active' : '',
                        ]"
                        @click="moreMenuOpen = !moreMenuOpen"
                    >
                        <MoreHorizontal class="nav-icon" />
                        <span class="nav-label">Lainnya</span>
                        <div v-if="isMoreActive" class="nav-dot" />
                    </button>
                </div>
            </nav>
        </div>
    </TooltipProvider>
</template>
