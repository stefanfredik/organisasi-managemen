<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetDescription,
} from '@/components/ui/sheet';
import { Menu, ArrowUp, Mail, Phone, MapPin, Heart } from 'lucide-vue-next';

const page = usePage();
const mobileMenuOpen = ref(false);
const isScrolled = ref(false);
const showBackToTop = ref(false);

const navigation = computed(() => {
    const items = [
        { name: 'Beranda', href: route('home') },
        { name: 'Tentang', href: route('public.about') },
        { name: 'Visi & Misi', href: route('public.vision-mission') },
        { name: 'Struktur', href: route('public.structure') },
        { name: 'Kegiatan', href: route('public.events.index') },
    ];

    if (page.props.appSettings.features.donations) {
        items.push({ name: 'Donasi', href: route('public.donations.index') });
    }

    if (page.props.appSettings.features.gallery) {
        items.push({ name: 'Galeri', href: route('public.gallery.index') });
    }

    items.push({ name: 'Kontak', href: route('public.contact') });

    return items;
});

const handleScroll = () => {
    isScrolled.value = window.scrollY > 20;
    showBackToTop.value = window.scrollY > 600;
};

const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll();
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <div class="min-h-screen bg-background">
        <!-- Header -->
        <header
            class="fixed top-0 left-0 right-0 z-50 transition-all duration-500"
            :class="isScrolled
                ? 'bg-card/80 backdrop-blur-xl border-b shadow-sm'
                : 'bg-transparent'"
        >
            <nav class="mx-auto max-w-7xl px-3 sm:px-4 md:px-6 lg:px-8" aria-label="Top">
                <div class="flex w-full items-center justify-between h-16 sm:h-18">
                    <!-- Logo -->
                    <div class="flex items-center min-w-0">
                        <Link href="/" class="shrink-0 group">
                            <span class="sr-only">{{ $page.props.appSettings.name }}</span>
                            <ApplicationLogo
                                class="h-8 sm:h-10 w-auto transition-transform duration-300 group-hover:scale-105"
                                :class="isScrolled ? 'text-primary' : 'text-primary'"
                            />
                        </Link>
                        <div class="ml-3 sm:ml-4 hidden sm:block min-w-0">
                            <h1
                                class="text-base sm:text-lg font-bold truncate transition-colors duration-300"
                                :class="isScrolled ? 'text-foreground' : 'text-foreground'"
                            >
                                {{ $page.props.appSettings.name }}
                            </h1>
                        </div>
                    </div>

                    <!-- Desktop Navigation -->
                    <div class="ml-6 hidden lg:flex items-center gap-0.5">
                        <Link
                            v-for="item in navigation"
                            :key="item.name"
                            :href="item.href"
                            class="relative px-3.5 py-2 text-sm font-medium transition-all duration-300 rounded-lg whitespace-nowrap hover:bg-primary/5"
                            :class="isScrolled
                                ? 'text-muted-foreground hover:text-primary'
                                : 'text-muted-foreground hover:text-primary'"
                        >
                            {{ item.name }}
                        </Link>
                    </div>

                    <!-- Login Button (Desktop) -->
                    <div class="hidden lg:flex lg:items-center lg:ml-4">
                        <Button as-child class="rounded-xl shadow-sm">
                            <Link href="/login">Login</Link>
                        </Button>
                    </div>

                    <!-- Mobile menu button -->
                    <Button
                        variant="ghost"
                        size="icon"
                        class="lg:hidden"
                        @click="mobileMenuOpen = true"
                    >
                        <Menu class="h-5 w-5" />
                        <span class="sr-only">Open menu</span>
                    </Button>
                </div>
            </nav>
        </header>

        <!-- Mobile Navigation Sheet -->
        <Sheet :open="mobileMenuOpen" @update:open="mobileMenuOpen = $event">
            <SheetContent side="right" class="w-[280px] max-w-[85vw] p-0 flex flex-col">
                <SheetHeader class="p-4 border-b">
                    <SheetTitle class="text-left flex items-center gap-3">
                        <ApplicationLogo class="h-7 w-7 shrink-0 text-primary" />
                        <span class="text-sm font-bold truncate">{{ $page.props.appSettings.name }}</span>
                    </SheetTitle>
                    <SheetDescription class="sr-only">Menu navigasi</SheetDescription>
                </SheetHeader>

                <div class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                    <Link
                        v-for="item in navigation"
                        :key="item.name"
                        :href="item.href"
                        class="flex items-center px-3 py-3 min-h-[44px] rounded-lg text-sm font-medium text-muted-foreground hover:bg-accent hover:text-accent-foreground transition-colors"
                        @click="mobileMenuOpen = false"
                    >
                        {{ item.name }}
                    </Link>
                </div>

                <div class="p-3 border-t">
                    <Button class="w-full" as-child>
                        <Link href="/login" @click="mobileMenuOpen = false">Login</Link>
                    </Button>
                </div>
            </SheetContent>
        </Sheet>

        <!-- Main Content -->
        <main>
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-foreground mt-16 sm:mt-24 relative overflow-hidden">
            <!-- Decorative top border -->
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-primary-400 to-primary"></div>

            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
                <div class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- About Section -->
                    <div class="lg:col-span-2">
                        <div class="flex items-center gap-3 mb-4">
                            <ApplicationLogo class="h-8 w-auto text-primary-400" />
                            <span class="text-lg font-bold text-background">{{ $page.props.appSettings.name }}</span>
                        </div>
                        <p class="text-sm sm:text-base text-muted leading-relaxed max-w-md">
                            {{ $page.props.appSettings.name }} berbasis web untuk mengelola organisasi secara terpusat, terstruktur, dan transparan.
                        </p>
                    </div>

                    <!-- Quick Links -->
                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground mb-4">
                            Link Cepat
                        </h3>
                        <ul class="space-y-2.5">
                            <li v-for="item in navigation.slice(0, 5)" :key="item.name">
                                <Link
                                    :href="item.href"
                                    class="text-sm text-muted hover:text-background transition-colors duration-200 inline-flex items-center gap-1.5 group"
                                >
                                    <span class="w-0 group-hover:w-2 h-0.5 bg-primary transition-all duration-200"></span>
                                    {{ item.name }}
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <!-- Contact Info -->
                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground mb-4">
                            Kontak
                        </h3>
                        <ul class="space-y-3 text-sm text-muted">
                            <li v-if="$page.props.appSettings.email" class="flex items-start gap-2.5">
                                <Mail class="w-4 h-4 text-primary-400 shrink-0 mt-0.5" />
                                <span class="break-all">{{ $page.props.appSettings.email }}</span>
                            </li>
                            <li v-if="$page.props.appSettings.phone" class="flex items-start gap-2.5">
                                <Phone class="w-4 h-4 text-primary-400 shrink-0 mt-0.5" />
                                <span>{{ $page.props.appSettings.phone }}</span>
                            </li>
                            <li v-if="$page.props.appSettings.address" class="flex items-start gap-2.5">
                                <MapPin class="w-4 h-4 text-primary-400 shrink-0 mt-0.5" />
                                <span>{{ $page.props.appSettings.address }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Copyright -->
                <Separator class="my-8 bg-muted-foreground/15" />
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-muted-foreground">
                    <p>
                        &copy; {{ new Date().getFullYear() }} {{ $page.props.appSettings.name }}. All rights reserved.
                    </p>
                    <p class="flex items-center gap-1 text-xs">
                        Dibuat dengan <Heart class="w-3 h-3 text-destructive inline" /> untuk komunitas
                    </p>
                </div>
            </div>
        </footer>

        <!-- Back to Top Button -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 translate-y-4"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-4"
        >
            <button
                v-if="showBackToTop"
                @click="scrollToTop"
                class="fixed bottom-6 right-6 z-40 w-12 h-12 rounded-full bg-primary text-white shadow-lg shadow-primary/25 flex items-center justify-center hover:bg-primary/90 hover:scale-110 active:scale-95 transition-all duration-200"
                aria-label="Kembali ke atas"
            >
                <ArrowUp class="w-5 h-5" />
            </button>
        </Transition>
    </div>
</template>
