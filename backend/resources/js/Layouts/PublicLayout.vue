<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
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
import { Menu, ArrowUp, Mail, Phone, MapPin } from 'lucide-vue-next';

const page = usePage();
const mobileMenuOpen = ref(false);
const isScrolled = ref(false);
const showBackToTop = ref(false);

const features = computed(() => page.props.appSettings.features || {});
const social = computed(() => page.props.appSettings.social || {});
const portal = computed(() => page.props.appSettings.portal || {});

const hasSocialLinks = computed(() => {
    const s = social.value;
    return s.facebook || s.instagram || s.twitter || s.youtube || s.tiktok || s.whatsapp || s.telegram;
});

const isHomePage = computed(() => {
    return page.url === '/' || page.url === '';
});

const navigation = computed(() => {
    const home = route('home');
    const items = [
        { name: 'Beranda', href: isHomePage.value ? '#beranda' : home, anchor: 'beranda' },
        { name: 'Tentang', href: isHomePage.value ? '#tentang' : home + '#tentang', anchor: 'tentang' },
        { name: 'Visi & Misi', href: isHomePage.value ? '#visi-misi' : home + '#visi-misi', anchor: 'visi-misi' },
        { name: 'Struktur', href: isHomePage.value ? '#struktur' : home + '#struktur', anchor: 'struktur' },
    ];

    if (features.value.events) {
        items.push({ name: 'Kegiatan', href: isHomePage.value ? '#kegiatan' : home + '#kegiatan', anchor: 'kegiatan' });
    }

    if (features.value.donations) {
        items.push({ name: 'Donasi', href: isHomePage.value ? '#donasi' : home + '#donasi', anchor: 'donasi' });
    }

    if (features.value.gallery) {
        items.push({ name: 'Galeri', href: isHomePage.value ? '#galeri' : home + '#galeri', anchor: 'galeri' });
    }

    items.push({ name: 'Kontak', href: isHomePage.value ? '#kontak' : home + '#kontak', anchor: 'kontak' });

    return items;
});

const scrollToSection = (item, e) => {
    if (isHomePage.value && item.anchor) {
        e.preventDefault();
        const el = document.getElementById(item.anchor);
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        mobileMenuOpen.value = false;
    }
};

// Track active section on scroll
const activeSection = ref('beranda');
const updateActiveSection = () => {
    const sections = navigation.value.map(n => n.anchor).filter(Boolean);
    for (let i = sections.length - 1; i >= 0; i--) {
        const el = document.getElementById(sections[i]);
        if (el) {
            const rect = el.getBoundingClientRect();
            if (rect.top <= 100) {
                activeSection.value = sections[i];
                return;
            }
        }
    }
    activeSection.value = 'beranda';
};

const handleScroll = () => {
    isScrolled.value = window.scrollY > 20;
    showBackToTop.value = window.scrollY > 600;
    if (isHomePage.value) updateActiveSection();
};

const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll();

    // Handle hash on page load (from other pages linking to /#section)
    if (window.location.hash) {
        setTimeout(() => {
            const el = document.getElementById(window.location.hash.slice(1));
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 300);
    }
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <div class="min-h-screen bg-background">
        <!-- Header -->
        <header
            class="fixed top-0 left-0 right-0 z-50 transition-all duration-300"
            :class="isScrolled
                ? 'bg-card/90 backdrop-blur-xl border-b shadow-sm'
                : 'bg-transparent'"
        >
            <nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex w-full items-center justify-between h-14 sm:h-16">
                    <!-- Logo -->
                    <Link href="/" class="flex items-center gap-2.5 shrink-0">
                        <ApplicationLogo class="h-7 sm:h-8 w-auto text-primary" />
                        <span
                            class="text-sm sm:text-base font-bold truncate max-w-[160px] sm:max-w-none transition-colors"
                            :class="isScrolled ? 'text-foreground' : 'text-white'"
                        >
                            {{ $page.props.appSettings.name }}
                        </span>
                    </Link>

                    <!-- Desktop Navigation -->
                    <div class="hidden lg:flex items-center gap-0.5 ml-8">
                        <a
                            v-for="item in navigation"
                            :key="item.name"
                            :href="item.href"
                            @click="scrollToSection(item, $event)"
                            class="px-3 py-1.5 text-[13px] font-medium rounded-lg transition-colors whitespace-nowrap cursor-pointer"
                            :class="isHomePage && activeSection === item.anchor
                                ? (isScrolled ? 'text-primary bg-primary/5' : 'text-white bg-white/10')
                                : (isScrolled ? 'text-muted-foreground hover:text-primary hover:bg-primary/5' : 'text-white/80 hover:text-white hover:bg-white/10')"
                        >
                            {{ item.name }}
                        </a>
                    </div>

                    <!-- Login/Dashboard Button -->
                    <div class="hidden lg:block ml-4">
                        <Button as-child size="sm" class="rounded-lg h-8 text-xs">
                            <Link v-if="$page.props.auth.user" href="/dashboard">Dashboard</Link>
                            <Link v-else href="/login">Login</Link>
                        </Button>
                    </div>

                    <!-- Mobile menu -->
                    <Button variant="ghost" size="icon" class="lg:hidden h-9 w-9 transition-colors" :class="isScrolled ? 'text-foreground' : 'text-white'" @click="mobileMenuOpen = true">
                        <Menu class="h-5 w-5" />
                    </Button>
                </div>
            </nav>
        </header>

        <!-- Mobile Navigation Sheet -->
        <Sheet :open="mobileMenuOpen" @update:open="mobileMenuOpen = $event">
            <SheetContent side="right" class="w-[260px] max-w-[80vw] p-0 flex flex-col">
                <SheetHeader class="p-3 border-b">
                    <SheetTitle class="text-left flex items-center gap-2.5">
                        <ApplicationLogo class="h-6 w-6 shrink-0 text-primary" />
                        <span class="text-sm font-bold truncate">{{ $page.props.appSettings.name }}</span>
                    </SheetTitle>
                    <SheetDescription class="sr-only">Menu navigasi</SheetDescription>
                </SheetHeader>

                <div class="flex-1 overflow-y-auto py-2 px-2 space-y-0.5">
                    <a
                        v-for="item in navigation"
                        :key="item.name"
                        :href="item.href"
                        @click="scrollToSection(item, $event)"
                        class="flex items-center px-3 py-2.5 min-h-[40px] rounded-lg text-sm font-medium transition-colors cursor-pointer"
                        :class="isHomePage && activeSection === item.anchor
                            ? 'text-primary bg-primary/5'
                            : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground'"
                    >
                        {{ item.name }}
                    </a>
                </div>

                <div class="p-3 border-t">
                    <Button class="w-full h-9 text-sm" as-child>
                        <Link v-if="$page.props.auth.user" href="/dashboard" @click="mobileMenuOpen = false">Dashboard</Link>
                        <Link v-else href="/login" @click="mobileMenuOpen = false">Login</Link>
                    </Button>
                </div>
            </SheetContent>
        </Sheet>

        <!-- Main Content -->
        <main>
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-foreground mt-12 sm:mt-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10 sm:py-12">
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- About -->
                    <div class="lg:col-span-2">
                        <div class="flex items-center gap-2.5 mb-3">
                            <ApplicationLogo class="h-7 w-auto text-primary-400" />
                            <span class="text-base font-bold text-background">{{ $page.props.appSettings.name }}</span>
                        </div>
                        <p class="text-sm text-muted leading-relaxed max-w-sm">
                            {{ $page.props.appSettings.description || `${$page.props.appSettings.name} — sistem manajemen organisasi yang transparan dan terstruktur.` }}
                        </p>

                        <!-- Social Media -->
                        <div v-if="hasSocialLinks" class="flex gap-2 mt-4">
                            <a v-if="social.facebook" :href="social.facebook" target="_blank" rel="noopener" class="w-8 h-8 rounded-full bg-muted-foreground/10 flex items-center justify-center text-muted hover:bg-[#1877F2] hover:text-white transition-all">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </a>
                            <a v-if="social.instagram" :href="social.instagram" target="_blank" rel="noopener" class="w-8 h-8 rounded-full bg-muted-foreground/10 flex items-center justify-center text-muted hover:bg-[#E4405F] hover:text-white transition-all">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.791-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.209-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                            </a>
                            <a v-if="social.twitter" :href="social.twitter" target="_blank" rel="noopener" class="w-8 h-8 rounded-full bg-muted-foreground/10 flex items-center justify-center text-muted hover:bg-black hover:text-white transition-all">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                            </a>
                            <a v-if="social.youtube" :href="social.youtube" target="_blank" rel="noopener" class="w-8 h-8 rounded-full bg-muted-foreground/10 flex items-center justify-center text-muted hover:bg-[#FF0000] hover:text-white transition-all">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                            </a>
                            <a v-if="social.tiktok" :href="social.tiktok" target="_blank" rel="noopener" class="w-8 h-8 rounded-full bg-muted-foreground/10 flex items-center justify-center text-muted hover:bg-black hover:text-white transition-all">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                            </a>
                            <a v-if="social.whatsapp" :href="'https://wa.me/' + social.whatsapp" target="_blank" rel="noopener" class="w-8 h-8 rounded-full bg-muted-foreground/10 flex items-center justify-center text-muted hover:bg-[#25D366] hover:text-white transition-all">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            </a>
                            <a v-if="social.telegram" :href="social.telegram" target="_blank" rel="noopener" class="w-8 h-8 rounded-full bg-muted-foreground/10 flex items-center justify-center text-muted hover:bg-[#0088cc] hover:text-white transition-all">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.479.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                            </a>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-3">Link Cepat</h3>
                        <ul class="space-y-2">
                            <li v-for="item in navigation.slice(0, 5)" :key="item.name">
                                <a :href="item.href" @click="scrollToSection(item, $event)" class="text-sm text-muted hover:text-background transition-colors cursor-pointer">
                                    {{ item.name }}
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Contact -->
                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-3">Kontak</h3>
                        <ul class="space-y-2.5 text-sm text-muted">
                            <li v-if="$page.props.appSettings.email" class="flex items-start gap-2">
                                <Mail class="w-3.5 h-3.5 text-primary-400 shrink-0 mt-0.5" />
                                <span class="break-all">{{ $page.props.appSettings.email }}</span>
                            </li>
                            <li v-if="$page.props.appSettings.phone" class="flex items-start gap-2">
                                <Phone class="w-3.5 h-3.5 text-primary-400 shrink-0 mt-0.5" />
                                <span>{{ $page.props.appSettings.phone }}</span>
                            </li>
                            <li v-if="$page.props.appSettings.address" class="flex items-start gap-2">
                                <MapPin class="w-3.5 h-3.5 text-primary-400 shrink-0 mt-0.5" />
                                <span>{{ $page.props.appSettings.address }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Portal Footer Text -->
                <p v-if="portal.footer_text" class="mt-6 pt-6 border-t border-muted-foreground/10 text-sm text-muted leading-relaxed">
                    {{ portal.footer_text }}
                </p>

                <!-- Copyright -->
                <div class="mt-6 pt-6 border-t border-muted-foreground/10 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-muted-foreground">
                    <p>&copy; {{ new Date().getFullYear() }} {{ $page.props.appSettings.name }}</p>
                </div>
            </div>
        </footer>

        <!-- Back to Top -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 translate-y-4"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-4"
        >
            <button
                v-if="showBackToTop"
                @click="scrollToTop"
                class="fixed bottom-5 right-5 z-40 w-10 h-10 rounded-full bg-primary text-white shadow-md flex items-center justify-center hover:bg-primary/90 active:scale-95 transition-all"
                aria-label="Kembali ke atas"
            >
                <ArrowUp class="w-4 h-4" />
            </button>
        </Transition>
    </div>
</template>
