<script setup>
import { ref, computed } from "vue";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import NavLink from "@/Components/NavLink.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import { Link, usePage } from "@inertiajs/vue3";
import FlashMessage from "@/Components/FlashMessage.vue";
import NotificationPanel from "@/Components/NotificationPanel.vue";

const page = usePage();
const notifications = computed(() => page.props.notifications || []);
const showingNavigationDropdown = ref(false);
const isSidebarOpen = ref(true);

const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
};

const hasPermission = (permission) => {
    if (page.props.auth.user.role === 'admin') return true;
    return page.props.auth.user.permissions?.includes(permission);
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex">
        <FlashMessage />
        <!-- Sidebar Desktop -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 bg-white border-r border-gray-200 transition-all duration-300 z-50 overflow-hidden hidden lg:block',
                isSidebarOpen ? 'w-64' : 'w-20',
            ]"
        >
            <div class="h-full flex flex-col">
                <!-- Sidebar Header -->
                <div
                    class="h-16 flex items-center px-6 border-b border-gray-100 shrink-0"
                >
                    <Link
                        :href="route('dashboard')"
                        class="flex items-center gap-3"
                    >
                        <ApplicationLogo
                            class="h-8 w-auto fill-current text-indigo-600"
                        />
                        <span
                            v-show="isSidebarOpen"
                            class="font-black text-xs uppercase tracking-tighter text-gray-900 truncate"
                        >
                            {{ $page.props.appSettings.name.split(' ')[0] }}<br />
                            <span class="text-indigo-600">{{ $page.props.appSettings.name.split(' ').slice(1).join(' ') }}</span>
                        </span>
                    </Link>
                </div>

                <!-- Navigation Sidebar -->
                <nav class="flex-1 overflow-y-auto px-3 py-6 space-y-8">
                    <!-- Kelompok Utama -->
                    <div>
                        <div
                            v-show="isSidebarOpen"
                            class="px-3 mb-2 text-[10px] font-black uppercase tracking-widest text-gray-400"
                        >
                            Utama
                        </div>
                        <div class="space-y-1">
                            <Link
                                :href="route('dashboard')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all font-bold text-sm',
                                    route().current('dashboard')
                                        ? 'bg-indigo-50 text-indigo-700 shadow-sm'
                                        : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900',
                                ]"
                            >
                                <svg
                                    class="w-5 h-5 shrink-0"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                                    />
                                </svg>
                                <span v-show="isSidebarOpen">Dashboard</span>
                            </Link>
                            <Link
                                v-if="hasPermission('view_members')"
                                :href="route('members.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all font-bold text-sm',
                                    route().current('members.*')
                                        ? 'bg-indigo-50 text-indigo-700 shadow-sm'
                                        : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900',
                                ]"
                            >
                                <svg
                                    class="w-5 h-5 shrink-0"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                                    />
                                </svg>
                                <span v-show="isSidebarOpen">Anggota</span>
                            </Link>
                            <Link
                                v-if="hasPermission('view_events')"
                                :href="route('events.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all font-bold text-sm',
                                    route().current('events.*')
                                        ? 'bg-indigo-50 text-indigo-700 shadow-sm'
                                        : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900',
                                ]"
                            >
                                <svg
                                    class="w-5 h-5 shrink-0"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                    />
                                </svg>
                                <span v-show="isSidebarOpen">Kegiatan</span>
                            </Link>
                            <Link
                                :href="route('organization-structures.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all font-bold text-sm',
                                    route().current('organization-structures.*')
                                        ? 'bg-indigo-50 text-indigo-700 shadow-sm'
                                        : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900',
                                ]"
                            >
                                <svg
                                    class="w-5 h-5 shrink-0"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h10M4 18h7"
                                    />
                                </svg>
                                <span v-show="isSidebarOpen">Struktur Organisasi</span>
                            </Link>
                        </div>
                    </div>

                    <!-- Kelompok Konten -->
                    <div>
                        <div
                            v-show="isSidebarOpen"
                            class="px-3 mb-2 text-[10px] font-black uppercase tracking-widest text-gray-400"
                        >
                            Konten
                        </div>
                        <div class="space-y-1">
                            <Link
                                :href="route('albums.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all font-bold text-sm',
                                    route().current('albums.*')
                                        ? 'bg-indigo-50 text-indigo-700 shadow-sm'
                                        : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900',
                                ]"
                            >
                                <svg
                                    class="w-5 h-5 shrink-0"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                    />
                                </svg>
                                <span v-show="isSidebarOpen">Album</span>
                            </Link>
                        </div>
                    </div>

                    <!-- Kelompok Keuangan -->
                    <div>
                        <div
                            v-show="isSidebarOpen"
                            class="px-3 mb-2 text-[10px] font-black uppercase tracking-widest text-gray-400"
                        >
                            Keuangan
                        </div>
                        <div class="space-y-1">
                            <Link
                                v-if="hasPermission('view_finance')"
                                :href="route('wallets.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all font-bold text-sm',
                                    route().current('wallets.*')
                                        ? 'bg-indigo-50 text-indigo-700 shadow-sm'
                                        : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900',
                                ]"
                            >
                                <svg
                                    class="w-5 h-5 shrink-0"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                                    />
                                </svg>
                                <span v-show="isSidebarOpen">Kas (Dompet)</span>
                            </Link>
                            <Link
                                v-if="hasPermission('view_finance')"
                                :href="route('finances.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all font-bold text-sm',
                                    route().current('finances.*')
                                        ? 'bg-indigo-50 text-indigo-700 shadow-sm'
                                        : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900',
                                ]"
                            >
                                <svg
                                    class="w-5 h-5 shrink-0"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                    />
                                </svg>
                                <span v-show="isSidebarOpen">Transaksi</span>
                            </Link>
                            <Link
                                v-if="hasPermission('view_contributions')"
                                :href="route('contributions.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all font-bold text-sm',
                                    route().current('contributions.*')
                                        ? 'bg-indigo-50 text-indigo-700 shadow-sm'
                                        : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900',
                                ]"
                            >
                                <svg
                                    class="w-5 h-5 shrink-0"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"
                                    />
                                </svg>
                                <span v-show="isSidebarOpen">{{
                                    $page.props.auth.user.role === "anggota"
                                        ? "Iuran Saya"
                                        : "Iuran Anggota"
                                }}</span>
                            </Link>
                            <Link
                                v-if="hasPermission('view_contribution_types')"
                                :href="route('contribution-types.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all font-bold text-sm',
                                    route().current('contribution-types.*')
                                        ? 'bg-indigo-50 text-indigo-700 shadow-sm'
                                        : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900',
                                ]"
                            >
                                <svg
                                    class="w-5 h-5 shrink-0"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                    />
                                </svg>
                                <span v-show="isSidebarOpen">Jenis Iuran</span>
                            </Link>
                            <Link
                                v-if="route().has('donations.index') && hasPermission('view_donations')"
                                :href="route('donations.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all font-bold text-sm',
                                    route().current('donations.*')
                                        ? 'bg-indigo-50 text-indigo-700 shadow-sm'
                                        : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900',
                                ]"
                            >
                                <svg
                                    class="w-5 h-5 shrink-0"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                                <span v-show="isSidebarOpen">Donasi</span>
                            </Link>
                        </div>
                    </div>
                        <!-- Kelompok Administrasi -->
                        <div>
                        <div
                            v-show="isSidebarOpen"
                            class="px-3 mb-2 text-[10px] font-black uppercase tracking-widest text-gray-400"
                        >
                            Administrasi
                        </div>
                        <div class="space-y-1">
                            <Link
                                :href="route('announcements.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all font-bold text-sm',
                                    route().current('announcements.*')
                                        ? 'bg-indigo-50 text-indigo-700 shadow-sm'
                                        : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900',
                                ]"
                            >
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                                <span v-show="isSidebarOpen">Pengumuman</span>
                            </Link>
                            <Link
                                :href="route('meeting-minutes.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all font-bold text-sm',
                                    route().current('meeting-minutes.*')
                                        ? 'bg-indigo-50 text-indigo-700 shadow-sm'
                                        : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900',
                                ]"
                            >
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                <span v-show="isSidebarOpen">Notulensi Rapat</span>
                            </Link>
                            <Link
                                v-if="$page.props.auth.user.role === 'admin'"
                                :href="route('users.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all font-bold text-sm',
                                    route().current('users.*')
                                        ? 'bg-indigo-50 text-indigo-700 shadow-sm'
                                        : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900',
                                ]"
                            >
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                <span v-show="isSidebarOpen">Manajemen User</span>
                            </Link>
                            <Link
                                v-if="$page.props.auth.user.role === 'admin'"
                                :href="route('activity-logs.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all font-bold text-sm',
                                    route().current('activity-logs.*')
                                        ? 'bg-indigo-50 text-indigo-700 shadow-sm'
                                        : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900',
                                ]"
                            >
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span v-show="isSidebarOpen">Log Aktivitas</span>
                            </Link>
                            <Link
                                v-if="$page.props.auth.user.role === 'admin'"
                                :href="route('backups.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all font-bold text-sm',
                                    route().current('backups.*')
                                        ? 'bg-indigo-50 text-indigo-700 shadow-sm'
                                        : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900',
                                ]"
                            >
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" /></svg>
                                <span v-show="isSidebarOpen">Backup Data</span>
                            </Link>
                            <Link
                                v-if="$page.props.auth.user.role === 'admin'"
                                :href="route('settings.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all font-bold text-sm',
                                    route().current('settings.*')
                                        ? 'bg-indigo-50 text-indigo-700 shadow-sm'
                                        : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900',
                                ]"
                            >
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                <span v-show="isSidebarOpen">Pengaturan</span>
                            </Link>
                        </div>
                    </div>

                    <!-- Kelompok Laporan -->
                    <div>
                        <div
                            v-show="isSidebarOpen"
                            class="px-3 mb-2 text-[10px] font-black uppercase tracking-widest text-gray-400"
                        >
                            Laporan
                        </div>
                        <div class="space-y-1">
                            <Link
                                v-if="hasPermission('view_reports')"
                                :href="route('reports.index')"
                                :class="[
                                    'flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all font-bold text-sm',
                                    route().current('reports.*')
                                        ? 'bg-indigo-50 text-indigo-700 shadow-sm'
                                        : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900',
                                ]"
                            >
                                <svg
                                    class="w-5 h-5 shrink-0"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                    />
                                </svg>
                                <span v-show="isSidebarOpen">Laporan</span>
                            </Link>
                        </div>
                    </div>
                </nav>

                <!-- Sidebar Footer (Toggle) -->
                <div class="p-4 border-t border-gray-100 hidden lg:block">
                    <button
                        @click="toggleSidebar"
                        class="w-full h-10 flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:bg-gray-50 rounded-xl transition-all"
                    >
                        <svg
                            :class="[
                                'w-6 h-6 transition-transform duration-300',
                                !isSidebarOpen ? 'rotate-180' : '',
                            ]"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M11 19l-7-7 7-7m8 14l-7-7 7-7"
                            />
                        </svg>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div
            :class="[
                'flex-1 flex flex-col transition-all duration-300 min-w-0',
                isSidebarOpen ? 'lg:pl-64' : 'lg:pl-20',
            ]"
        >
            <!-- Top Header -->
            <nav
                class="h-16 bg-white border-b border-gray-200 sticky top-0 z-40 px-4 sm:px-6 lg:px-8 flex items-center justify-between"
            >
                <!-- Mobile Toggle -->
                <button
                    @click="
                        showingNavigationDropdown = !showingNavigationDropdown
                    "
                    class="lg:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none transition"
                >
                    <svg
                        class="h-6 w-6"
                        stroke="currentColor"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />
                    </svg>
                </button>

                <!-- Page Info -->
                <div
                    class="hidden sm:block text-xs font-bold text-gray-400 uppercase tracking-widest"
                >
                    {{ route().current() }}
                </div>

                <div class="flex items-center gap-4">
                    <!-- Notifications -->
                    <NotificationPanel :notifications="notifications" />

                    <!-- User Menu -->
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button
                                type="button"
                                class="flex items-center gap-2 group transition"
                            >
                                <div
                                    class="h-8 w-8 rounded-full bg-indigo-600 flex items-center justify-center text-white font-black text-xs shadow-md shadow-indigo-100"
                                >
                                    {{
                                        $page.props.auth.user.name
                                            .charAt(0)
                                            .toUpperCase()
                                    }}
                                </div>
                                <span
                                    class="hidden sm:inline-block text-sm font-bold text-gray-700 group-hover:text-indigo-600"
                                >
                                    {{ $page.props.auth.user.name }}
                                </span>
                                <svg
                                    class="h-4 w-4 text-gray-400 group-hover:text-indigo-600"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </button>
                        </template>

                        <template #content>
                            <DropdownLink :href="route('profile.edit')"
                                >Profil</DropdownLink
                            >
                            <div class="border-t border-gray-100"></div>
                            <DropdownLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                                >Keluar</DropdownLink
                            >
                        </template>
                    </Dropdown>
                </div>
            </nav>

            <!-- Responsive Mobile Menu Overlay -->
            <transition name="fade">
                <div
                    v-show="showingNavigationDropdown"
                    class="lg:hidden fixed inset-0 bg-black/50 z-[60]"
                    @click="showingNavigationDropdown = false"
                ></div>
            </transition>
            <transition name="slide">
                <div
                    v-show="showingNavigationDropdown"
                    class="lg:hidden fixed inset-y-0 left-0 w-72 bg-white z-[70] shadow-2xl flex flex-col"
                >
                    <div
                        class="h-16 flex items-center px-6 border-b border-gray-100 shrink-0 justify-between"
                    >
                        <div class="flex items-center gap-3">
                            <ApplicationLogo
                                class="h-8 w-auto fill-current text-indigo-600"
                            />
                            <span
                                class="font-black text-[10px] uppercase tracking-tighter text-gray-900 leading-none"
                            >
                                Organisasi<br /><span class="text-indigo-600"
                                    >Manajemen</span
                                >
                            </span>
                        </div>
                        <button
                            @click="showingNavigationDropdown = false"
                            class="p-2 text-gray-400"
                        >
                            <svg
                                class="w-6 h-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                    <!-- Reuse navigation items for mobile -->
                    <div class="flex-1 overflow-y-auto px-4 py-6 space-y-6">
                        <div>
                            <div
                                class="px-3 mb-2 text-[10px] font-black uppercase text-gray-400 tracking-widest"
                            >
                                Utama
                            </div>
                            <ResponsiveNavLink
                                :href="route('dashboard')"
                                :active="route().current('dashboard')"
                                >Dashboard</ResponsiveNavLink
                            >
                            <ResponsiveNavLink
                                v-if="hasPermission('view_members')"
                                :href="route('members.index')"
                                :active="route().current('members.*')"
                                >Anggota</ResponsiveNavLink
                            >
                            <ResponsiveNavLink
                                v-if="hasPermission('view_events')"
                                :href="route('events.index')"
                                :active="route().current('events.*')"
                                >Kegiatan</ResponsiveNavLink
                            >
                            <ResponsiveNavLink
                                :href="route('organization-structures.index')"
                                :active="route().current('organization-structures.*')"
                                >Struktur Organisasi</ResponsiveNavLink
                            >
                        </div>
                        <div>
                            <div
                                class="px-3 mb-2 text-[10px] font-black uppercase text-gray-400 tracking-widest"
                            >
                                Konten
                            </div>
                            <ResponsiveNavLink
                                :href="route('albums.index')"
                                :active="route().current('albums.*')"
                                >Album</ResponsiveNavLink
                            >
                        </div>
                        <div>
                            <div
                                class="px-3 mb-2 text-[10px] font-black uppercase text-gray-400 tracking-widest"
                            >
                                Keuangan
                            </div>
                            <ResponsiveNavLink
                                v-if="hasPermission('view_finance')"
                                :href="route('wallets.index')"
                                :active="route().current('wallets.*')"
                                >Kas (Dompet)</ResponsiveNavLink
                            >
                            <ResponsiveNavLink
                                v-if="hasPermission('view_finance')"
                                :href="route('finances.index')"
                                :active="route().current('finances.*')"
                                >Transaksi</ResponsiveNavLink
                            >
                            <ResponsiveNavLink
                                v-if="hasPermission('view_contributions')"
                                :href="route('contributions.index')"
                                :active="route().current('contributions.*')"
                                >{{
                                    $page.props.auth.user.role === "anggota"
                                        ? "Iuran Saya"
                                        : "Iuran Anggota"
                                }}</ResponsiveNavLink
                            >
                            <ResponsiveNavLink
                                v-if="hasPermission('view_contribution_types')"
                                :href="route('contribution-types.index')"
                                :active="
                                    route().current('contribution-types.*')
                                "
                                >Jenis Iuran</ResponsiveNavLink
                            >
                            <ResponsiveNavLink
                                v-if="route().has('donations.index') && hasPermission('view_donations')"
                                :href="route('donations.index')"
                                :active="route().current('donations.*')"
                                >Donasi</ResponsiveNavLink
                            >
                        </div>
                        
                        <!-- Kelompok Administrasi Mobile -->
                        <div>
                            <div class="px-3 mb-2 text-[10px] font-black uppercase text-gray-400 tracking-widest">
                                Administrasi
                            </div>
                            <ResponsiveNavLink
                                :href="route('announcements.index')"
                                :active="route().current('announcements.*')"
                            >
                                Pengumuman
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('meeting-minutes.index')"
                                :active="route().current('meeting-minutes.*')"
                            >
                                Notulensi Rapat
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                v-if="$page.props.auth.user.role === 'admin'"
                                :href="route('users.index')"
                                :active="route().current('users.*')"
                            >
                                Manajemen User
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                v-if="$page.props.auth.user.role === 'admin'"
                                :href="route('activity-logs.index')"
                                :active="route().current('activity-logs.*')"
                            >
                                Log Aktivitas
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                v-if="$page.props.auth.user.role === 'admin'"
                                :href="route('backups.index')"
                                :active="route().current('backups.*')"
                            >
                                Backup Data
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                v-if="$page.props.auth.user.role === 'admin'"
                                :href="route('settings.index')"
                                :active="route().current('settings.*')"
                            >
                                Pengaturan
                            </ResponsiveNavLink>
                        </div>

                        <!-- Kelompok Laporan Mobile -->
                        <div>
                            <div class="px-3 mb-2 text-[10px] font-black uppercase text-gray-400 tracking-widest">
                                Laporan
                            </div>
                            <ResponsiveNavLink
                                v-if="hasPermission('view_reports')"
                                :href="route('reports.index')"
                                :active="route().current('reports.*')"
                            >
                                Laporan
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </transition>

            <!-- Page Heading -->
            <header
                class="bg-white px-4 sm:px-6 lg:px-8 py-6"
                v-if="$slots.header"
            >
                <div class="mx-auto max-w-7xl">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1">
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.slide-enter-active,
.slide-leave-active {
    transition: transform 0.3s;
}
.slide-enter-from {
    transform: translateX(-100%);
}
.slide-leave-to {
    transform: translateX(-100%);
}
</style>
