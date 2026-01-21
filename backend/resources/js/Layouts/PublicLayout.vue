<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const mobileMenuOpen = ref(false);

const navigation = [
    { name: 'Beranda', href: route('home') },
    { name: 'Tentang', href: route('public.about') },
    { name: 'Visi & Misi', href: route('public.vision-mission') },
    { name: 'Struktur', href: route('public.structure') },
    { name: 'Kegiatan', href: route('public.events.index') },
    { name: 'Donasi', href: route('public.donations.index') },
    { name: 'Galeri', href: route('public.gallery.index') },
    { name: 'Kontak', href: route('public.contact') },
];
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8" aria-label="Top">
                <div class="flex w-full items-center justify-between border-b border-gray-200 py-6 lg:border-none">
                    <!-- Logo -->
                    <div class="flex items-center">
                        <Link href="/">
                            <span class="sr-only">Sistem Manajemen Organisasi</span>
                            <ApplicationLogo class="h-10 w-auto text-indigo-600" />
                        </Link>
                        <div class="ml-4 hidden lg:block">
                            <h1 class="text-xl font-bold text-gray-900">
                                Sistem Manajemen Organisasi
                            </h1>
                        </div>
                    </div>

                    <!-- Desktop Navigation -->
                    <div class="ml-10 hidden space-x-8 lg:flex">
                        <Link
                            v-for="item in navigation"
                            :key="item.name"
                            :href="item.href"
                            class="text-base font-medium text-gray-700 hover:text-indigo-600 transition-colors"
                        >
                            {{ item.name }}
                        </Link>
                    </div>

                    <!-- Login Button (Desktop) -->
                    <div class="hidden lg:flex lg:items-center lg:space-x-6">
                        <Link
                            href="/login"
                            class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors"
                        >
                            Login
                        </Link>
                    </div>

                    <!-- Mobile menu button -->
                    <div class="flex lg:hidden">
                        <button
                            type="button"
                            class="-m-2.5 inline-flex items-center justify-center rounded-md p-2.5 text-gray-700"
                            @click="mobileMenuOpen = !mobileMenuOpen"
                        >
                            <span class="sr-only">Toggle menu</span>
                            <svg
                                v-if="!mobileMenuOpen"
                                class="h-6 w-6"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"
                                />
                            </svg>
                            <svg
                                v-else
                                class="h-6 w-6"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Mobile Navigation -->
                <div v-if="mobileMenuOpen" class="lg:hidden py-4 space-y-2">
                    <Link
                        v-for="item in navigation"
                        :key="item.name"
                        :href="item.href"
                        class="block rounded-md px-3 py-2 text-base font-medium text-gray-700 hover:bg-gray-50 hover:text-indigo-600 transition-colors"
                        @click="mobileMenuOpen = false"
                    >
                        {{ item.name }}
                    </Link>
                    <Link
                        href="/login"
                        class="block rounded-md bg-indigo-600 px-3 py-2 text-base font-medium text-white hover:bg-indigo-700 transition-colors"
                        @click="mobileMenuOpen = false"
                    >
                        Login
                    </Link>
                </div>
            </nav>
        </header>

        <!-- Main Content -->
        <main>
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-gray-900 mt-16">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                    <!-- About Section -->
                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-400">
                            Tentang Kami
                        </h3>
                        <p class="mt-4 text-base text-gray-300">
                            Sistem Manajemen Organisasi berbasis web untuk mengelola organisasi secara terpusat, terstruktur, dan transparan.
                        </p>
                    </div>

                    <!-- Quick Links -->
                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-400">
                            Link Cepat
                        </h3>
                        <ul class="mt-4 space-y-2">
                            <li v-for="item in navigation.slice(0, 4)" :key="item.name">
                                <Link
                                    :href="item.href"
                                    class="text-base text-gray-300 hover:text-white transition-colors"
                                >
                                    {{ item.name }}
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <!-- Contact Info -->
                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-400">
                            Kontak
                        </h3>
                        <ul class="mt-4 space-y-2 text-base text-gray-300">
                            <li>Email: info@organisasi.com</li>
                            <li>Telepon: (021) 1234-5678</li>
                            <li>Alamat: Jakarta, Indonesia</li>
                        </ul>
                    </div>
                </div>

                <!-- Copyright -->
                <div class="mt-8 border-t border-gray-800 pt-8">
                    <p class="text-center text-base text-gray-400">
                        &copy; {{ new Date().getFullYear() }} Sistem Manajemen Organisasi. All rights reserved.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>
