<script setup>
import ActionLink from '@/Components/ActionLink.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import OrgTreeNode from '@/Components/OrgTreeNode.vue';
import NetworkBackground from '@/Components/NetworkBackground.vue';
import { useScrollReveal } from '@/composables/useScrollReveal';
import {
    Megaphone, ChevronRight, ArrowRight, Plus, Heart, CreditCard,
    BookOpen, Shield, Eye, Users, Lightbulb, Target,
    ClipboardList, Calendar, MapPin, Mail, Phone, Send,
    UserX, ImageOff, ChevronDown,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';

import { ref, computed } from 'vue';

useScrollReveal();

const page = usePage();
const app = page.props.appSettings;
const portal = app.portal || {};
const social = app.social || {};
const features = computed(() => app.features || {});

const props = defineProps({
    upcomingEvents: Array,
    latestAnnouncements: Array,
    activeDonations: Array,
    latestPhotos: Array,
    featuredAlbums: Array,
    memberCount: Number,
    visionMission: Object,
    structures: Array,
});



// Org tree
const tree = computed(() => {
    const rawItems = props.structures || [];
    if (!Array.isArray(rawItems) || rawItems.length === 0) return [];
    const idMap = {};
    const items = [];
    rawItems.forEach(item => {
        if (!item || !item.id) return;
        const n = { ...item, children: [], id: String(item.id), parent_id: item.parent_id ? String(item.parent_id) : null };
        idMap[n.id] = n;
        items.push(n);
    });
    const rootNodes = [];
    items.forEach(node => {
        if (node.parent_id && idMap[node.parent_id] && node.parent_id !== node.id) {
            idMap[node.parent_id].children.push(node);
        } else {
            rootNodes.push(node);
        }
    });
    const sortRec = (nodes) => {
        nodes.sort((a, b) => (Number(a.sort_order) || 0) - (Number(b.sort_order) || 0));
        nodes.forEach(n => { if (n.children.length > 0) sortRec(n.children); });
    };
    sortRec(rootNodes);
    return rootNodes;
});

const values = [
    { icon: Shield, title: 'Integritas', description: 'Menjalankan setiap tugas dengan kejujuran dan standar etika yang tinggi.' },
    { icon: Eye, title: 'Transparansi', description: 'Terbuka dalam pengelolaan dana dan informasi kepada seluruh anggota.' },
    { icon: Users, title: 'Kolaborasi', description: 'Mengutamakan kerjasama tim untuk mencapai hasil yang maksimal.' },
    { icon: Lightbulb, title: 'Inovasi', description: 'Terus berkembang dan beradaptasi dengan teknologi dan cara baru.' },
];

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};

const formatCurrency = (value) => {
    const currency = app.financial?.currency || 'Rp';
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value).replace('Rp', currency);
};

const donationProgress = (donation) => {
    if (!donation.target_amount || donation.target_amount === 0) return 0;
    return Math.min(100, Math.round((donation.current_amount / donation.target_amount) * 100));
};
</script>

<template>
    <Head title="Beranda" />

    <PublicLayout>
        <!-- ==================== BERANDA (Hero) ==================== -->
        <section id="beranda" class="scroll-mt-16 relative bg-black overflow-hidden h-screen min-h-[480px]">
            <div class="absolute inset-0">
                <!-- Static dark gradient background -->
                <div class="absolute inset-0 bg-gradient-to-br from-gray-950 via-primary-950/80 to-black"></div>

                <!-- Decorative floating orbs -->
                <div class="absolute top-1/4 left-10 w-72 h-72 bg-primary/20 rounded-full blur-[120px] animate-float"></div>
                <div class="absolute bottom-1/4 right-10 w-96 h-96 bg-primary-400/15 rounded-full blur-[150px] animate-float-delayed"></div>

                <!-- Network Background Overlay -->
                <div class="absolute inset-0 z-[5]">
                    <NetworkBackground
                        color="rgba(255, 255, 255, 0.5)"
                        :count="60"
                        :distance="180"
                        :speed="0.4"
                        :opacity="0.3"
                        class="w-full h-full"
                    />
                </div>
            </div>

            <div class="relative z-10 h-full max-w-7xl mx-auto px-5 sm:px-6 lg:px-8 flex flex-col justify-center items-center text-center">
                <div class="max-w-3xl">
                    <!-- Logo Organisasi -->
                    <Transition appear enter-active-class="transition duration-1000 delay-100" enter-from-class="opacity-0 scale-75" enter-to-class="opacity-100 scale-100">
                        <div class="flex justify-center mb-6">
                            <div class="logo-container relative">
                                <!-- Circuit line SVG -->
                                <svg class="circuit-svg" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <!-- Top traces -->
                                    <path class="circuit-path circuit-delay-0" d="M100 72 L100 52 L120 52 L120 38" stroke="rgba(255,255,255,0.6)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle class="circuit-dot circuit-delay-0" cx="120" cy="36" r="2.5" fill="rgba(255,255,255,0.9)"/>

                                    <path class="circuit-path circuit-delay-1" d="M100 72 L100 52 L80 52 L80 34" stroke="rgba(255,255,255,0.5)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle class="circuit-dot circuit-delay-1" cx="80" cy="32" r="2" fill="rgba(255,255,255,0.75)"/>

                                    <!-- Right traces -->
                                    <path class="circuit-path circuit-delay-2" d="M128 100 L148 100 L148 80 L162 80" stroke="rgba(255,255,255,0.6)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle class="circuit-dot circuit-delay-2" cx="164" cy="80" r="2.5" fill="rgba(255,255,255,0.9)"/>

                                    <path class="circuit-path circuit-delay-3" d="M128 100 L148 100 L148 118 L166 118" stroke="rgba(255,255,255,0.4)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle class="circuit-dot circuit-delay-3" cx="168" cy="118" r="2" fill="rgba(255,255,255,0.65)"/>

                                    <!-- Bottom traces -->
                                    <path class="circuit-path circuit-delay-4" d="M100 128 L100 148 L118 148 L118 162" stroke="rgba(255,255,255,0.55)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle class="circuit-dot circuit-delay-4" cx="118" cy="164" r="2.5" fill="rgba(255,255,255,0.85)"/>

                                    <path class="circuit-path circuit-delay-5" d="M100 128 L100 148 L82 148 L82 166" stroke="rgba(255,255,255,0.4)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle class="circuit-dot circuit-delay-5" cx="82" cy="168" r="2" fill="rgba(255,255,255,0.7)"/>

                                    <!-- Left traces -->
                                    <path class="circuit-path circuit-delay-6" d="M72 100 L52 100 L52 82 L36 82" stroke="rgba(255,255,255,0.6)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle class="circuit-dot circuit-delay-6" cx="34" cy="82" r="2.5" fill="rgba(255,255,255,0.9)"/>

                                    <path class="circuit-path circuit-delay-7" d="M72 100 L52 100 L52 120 L34 120" stroke="rgba(255,255,255,0.4)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle class="circuit-dot circuit-delay-7" cx="32" cy="120" r="2" fill="rgba(255,255,255,0.65)"/>
                                </svg>

                                <!-- Soft glow -->
                                <div class="logo-glow"></div>

                                <!-- Logo itself -->
                                <div class="logo-inner logo-float">
                                    <img
                                        v-if="app.logo"
                                        :src="app.logo"
                                        :alt="app.name"
                                        class="h-20 sm:h-24 w-auto object-contain"
                                    />
                                    <ApplicationLogo v-else class="h-16 sm:h-20 w-auto text-white" />
                                </div>
                            </div>
                        </div>
                    </Transition>

                    <Transition appear enter-active-class="transition duration-700 delay-300" enter-from-class="opacity-0 translate-y-8" enter-to-class="opacity-100 translate-y-0">
                        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-[1.05] text-white break-words drop-shadow-lg">
                            {{ app.name }}
                        </h1>
                    </Transition>
                    <Transition appear enter-active-class="transition duration-700 delay-500" enter-from-class="opacity-0 translate-y-8" enter-to-class="opacity-100 translate-y-0">
                        <p class="mt-5 text-lg sm:text-xl text-white/80 leading-relaxed max-w-xl mx-auto">
                            {{ portal.welcome_text || 'Selamat Datang di Portal Resmi Organisasi Kami' }}
                        </p>
                    </Transition>
                    <Transition appear enter-active-class="transition duration-700 delay-700" enter-from-class="opacity-0 translate-y-8" enter-to-class="opacity-100 translate-y-0">
                        <div class="mt-10 flex flex-wrap justify-center gap-4">
                            <ActionLink :href="$page.props.auth.user ? route('dashboard') : route('login')" size="md">
                                {{ $page.props.auth.user ? 'Ke Dashboard' : 'Mulai Sekarang' }}
                            </ActionLink>
                            <a href="#tentang" class="inline-flex items-center justify-center rounded-xl text-sm font-semibold ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-white/20 bg-white/10 backdrop-blur-md text-white hover:bg-white/20 hover:border-white/40 h-11 px-6">
                                Tentang Kami
                            </a>
                        </div>
                    </Transition>
                </div>
            </div>



            <!-- Scroll indicator -->
            <div class="absolute bottom-8 left-1/2 -translate-x-1/2 z-20 flex flex-col items-center gap-3">
                <span class="text-white/70 text-[11px] uppercase tracking-[0.2em] font-semibold">Scroll</span>
                <!-- Mouse shape -->
                <div class="scroll-mouse">
                    <div class="scroll-wheel"></div>
                </div>
                <!-- Arrows -->
                <div class="flex flex-col items-center gap-0.5 -mt-1">
                    <ChevronDown class="w-4 h-4 text-white/80 animate-[scroll-arrow_1.4s_ease-in-out_infinite]" />
                    <ChevronDown class="w-4 h-4 text-white/50 animate-[scroll-arrow_1.4s_ease-in-out_0.2s_infinite]" />
                </div>
            </div>
        </section>

        <!-- Wave divider -->
        <div class="relative -mt-1 z-10">
            <svg viewBox="0 0 1440 60" fill="none" class="w-full h-8 sm:h-12 text-card" preserveAspectRatio="none"><path d="M0 60V20C360 -5 720 50 1080 20C1260 5 1380 15 1440 20V60H0Z" fill="currentColor"/></svg>
        </div>

        <!-- ==================== TENTANG ==================== -->
        <section id="tentang" class="snap-start scroll-mt-16 relative bg-card py-10 overflow-hidden min-h-screen flex items-center">
            <!-- Decorative orb -->
            <div class="absolute -top-20 -right-20 w-80 h-80 bg-primary/5 rounded-full blur-[100px]"></div>
            <div class="absolute -bottom-20 -left-20 w-60 h-60 bg-primary/5 rounded-full blur-[80px]"></div>

            <div class="w-full mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-14 items-center">

                    <!-- Left: Text Block -->
                    <div>
                        <div class="flex items-center gap-3 mb-3" data-reveal="left">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary/20 to-primary/5 flex items-center justify-center text-primary ring-1 ring-primary/10">
                                <BookOpen class="w-4 h-4" />
                            </div>
                            <p class="text-primary font-bold text-xs uppercase tracking-wider">Tentang Kami</p>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-foreground tracking-tight mb-3" data-reveal data-reveal-delay="100">
                            {{ app.name }}
                        </h2>
                        <p v-if="app.motto" class="text-sm italic text-primary/80 font-medium mb-3 border-l-4 border-primary/30 pl-3" data-reveal data-reveal-delay="150">
                            "{{ app.motto }}"
                        </p>
                        <p class="text-sm text-muted-foreground leading-relaxed mb-4" data-reveal data-reveal-delay="200">
                            {{ portal.about_text || 'Organisasi ini didirikan dengan semangat kebersamaan dan keinginan untuk memberikan kontribusi nyata bagi masyarakat. Berawal dari komunitas kecil, kini kami telah tumbuh menjadi sebuah wadah profesional yang mengelola berbagai bidang kegiatan.' }}
                        </p>
                        <div v-if="app.founded_date" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-primary/10 to-primary/5 rounded-xl border border-primary/10" data-reveal data-reveal-delay="300">
                            <span class="text-xs text-muted-foreground">Berdiri sejak</span>
                            <span class="text-xs font-black text-primary">{{ app.founded_date }}</span>
                        </div>
                    </div>

                    <!-- Right: Values Grid 2x2 -->
                    <div class="grid grid-cols-2 gap-3">
                        <div
                            v-for="(value, idx) in values"
                            :key="value.title"
                            data-reveal
                            :data-reveal-delay="idx * 80"
                            class="group relative bg-muted/40 rounded-2xl p-4 border hover:border-primary/30 hover:bg-primary/5 hover:-translate-y-1 transition-all duration-300 overflow-hidden"
                        >
                            <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-bl from-primary/10 to-transparent rounded-full -translate-y-1/2 translate-x-1/2 group-hover:scale-150 transition-transform duration-500"></div>
                            <div class="relative z-10">
                                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center text-primary mb-2.5 group-hover:scale-110 transition-transform duration-300 ring-1 ring-primary/10">
                                    <component :is="value.icon" class="w-4 h-4" />
                                </div>
                                <h4 class="text-sm font-bold text-foreground mb-1 group-hover:text-primary transition-colors duration-300">{{ value.title }}</h4>
                                <p class="text-xs text-muted-foreground leading-relaxed">{{ value.description }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Wave divider -->
        <div class="relative -mt-1 z-10">
            <svg viewBox="0 0 1440 60" fill="none" class="w-full h-8 sm:h-12 text-muted" preserveAspectRatio="none"><path d="M0 60V30C240 0 480 50 720 30C960 10 1200 45 1440 25V60H0Z" fill="currentColor"/></svg>
        </div>

        <!-- ==================== VISI & MISI ==================== -->
        <section id="visi-misi" class="scroll-mt-16 relative bg-muted py-14 sm:py-20 overflow-hidden">
            <!-- Decorative -->
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-primary/[0.03] rounded-full blur-[80px]"></div>

            <div class="w-full mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center mb-12" data-reveal>
                    <p class="text-primary font-bold text-sm uppercase tracking-wider mb-2">Arah & Landasan</p>
                    <h2 class="text-3xl sm:text-4xl font-black text-foreground tracking-tight">Visi & Misi</h2>
                    <div class="w-16 h-1 bg-gradient-to-r from-primary to-primary/30 rounded-full mx-auto mt-3"></div>
                </div>

                <div v-if="visionMission" class="grid grid-cols-1 lg:grid-cols-2 gap-8 max-w-5xl mx-auto">
                    <!-- Vision -->
                    <div data-reveal="left" class="relative bg-card/80 backdrop-blur-sm border-2 border-primary/20 rounded-3xl p-8 shadow-lg shadow-primary/5 hover:shadow-xl hover:shadow-primary/10 hover:border-primary/40 transition-all duration-500">
                        <div class="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-primary/[0.02] rounded-3xl"></div>
                        <div class="absolute top-4 right-4 w-20 h-20 bg-primary/5 rounded-full blur-2xl"></div>
                        <div class="relative">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="p-3 bg-gradient-to-br from-primary/20 to-primary/5 rounded-2xl ring-1 ring-primary/10"><Eye class="w-6 h-6 text-primary" /></div>
                                <h3 class="text-xl font-black text-foreground">Visi Kami</h3>
                            </div>
                            <blockquote class="text-lg italic text-foreground/90 leading-relaxed border-l-4 border-primary/40 pl-5">
                                "{{ visionMission.vision }}"
                            </blockquote>
                        </div>
                    </div>
                    <!-- Mission -->
                    <div data-reveal="right" class="relative bg-card/80 backdrop-blur-sm border-2 border-emerald-500/20 rounded-3xl p-8 shadow-lg shadow-emerald-500/5 hover:shadow-xl hover:shadow-emerald-500/10 hover:border-emerald-500/40 transition-all duration-500">
                        <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 via-transparent to-emerald-500/[0.02] rounded-3xl"></div>
                        <div class="absolute top-4 right-4 w-20 h-20 bg-emerald-500/5 rounded-full blur-2xl"></div>
                        <div class="relative">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="p-3 bg-gradient-to-br from-emerald-500/20 to-emerald-500/5 rounded-2xl ring-1 ring-emerald-500/10"><ClipboardList class="w-6 h-6 text-emerald-600" /></div>
                                <h3 class="text-xl font-black text-foreground">Misi Kami</h3>
                            </div>
                            <ul class="space-y-4">
                                <li v-for="(mission, index) in visionMission.missions" :key="index" class="flex gap-3 items-start">
                                    <span class="flex-shrink-0 w-8 h-8 rounded-xl bg-gradient-to-br from-emerald-500/15 to-emerald-500/5 text-emerald-600 flex items-center justify-center font-black text-xs ring-1 ring-emerald-500/20">{{ index + 1 }}</span>
                                    <p class="text-base text-foreground pt-1">{{ mission }}</p>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-12 text-muted-foreground" data-reveal="fade">
                    Data visi dan misi belum tersedia.
                </div>
            </div>
        </section>

        <!-- Wave divider -->
        <div class="relative -mt-1 z-10">
            <svg viewBox="0 0 1440 60" fill="none" class="w-full h-8 sm:h-12 text-card" preserveAspectRatio="none"><path d="M0 60V20C360 -5 720 50 1080 20C1260 5 1380 15 1440 20V60H0Z" fill="currentColor"/></svg>
        </div>

        <!-- ==================== STRUKTUR ==================== -->
        <section id="struktur" class="scroll-mt-16 relative bg-card py-14 sm:py-20 overflow-hidden">
            <div class="absolute top-0 right-0 w-80 h-80 bg-primary/[0.03] rounded-full blur-[100px]"></div>
            <div class="w-full mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center mb-10" data-reveal>
                    <p class="text-primary font-bold text-sm uppercase tracking-wider mb-2">Kepengurusan</p>
                    <h2 class="text-3xl sm:text-4xl font-black text-foreground tracking-tight">Struktur Organisasi</h2>
                    <div class="w-16 h-1 bg-gradient-to-r from-primary to-primary/30 rounded-full mx-auto mt-3"></div>
                </div>

                <div class="w-full overflow-x-auto">
                    <div class="flex justify-center min-w-max pb-8">
                        <ul v-if="tree.length > 0" class="tree-container" data-reveal="fade">
                            <OrgTreeNode v-for="root in tree" :key="root.id" :node="root" />
                        </ul>
                        <div v-else data-reveal="scale" class="bg-muted rounded-2xl p-10 border text-center max-w-md mx-auto">
                            <UserX class="w-10 h-10 text-muted-foreground mx-auto mb-3" />
                            <h3 class="text-lg font-bold text-foreground mb-1">Data Belum Tersedia</h3>
                            <p class="text-sm text-muted-foreground">Informasi struktur organisasi sedang dalam tahap pemutakhiran.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Wave divider -->
        <div class="relative -mt-1 z-10">
            <svg viewBox="0 0 1440 60" fill="none" class="w-full h-8 sm:h-12 text-muted" preserveAspectRatio="none"><path d="M0 60V30C240 0 480 50 720 30C960 10 1200 45 1440 25V60H0Z" fill="currentColor"/></svg>
        </div>

        <!-- ==================== KEGIATAN ==================== -->
        <section v-if="features.events" id="kegiatan" class="scroll-mt-16 relative bg-muted py-14 sm:py-20 overflow-hidden">
            <div class="absolute -bottom-20 -right-20 w-80 h-80 bg-primary/[0.03] rounded-full blur-[100px]"></div>
            <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="flex items-end justify-between gap-4 mb-10" data-reveal>
                    <div>
                        <p class="text-primary text-xs font-bold tracking-widest uppercase mb-2">Kegiatan</p>
                        <h2 class="text-3xl sm:text-4xl font-black text-foreground tracking-tight">Agenda Mendatang</h2>
                        <div class="w-16 h-1 bg-gradient-to-r from-primary to-primary/30 rounded-full mt-3"></div>
                    </div>
                    <Link :href="route('public.events.index')" class="text-primary text-sm font-semibold hover:text-primary/80 transition-colors flex items-center gap-1.5 shrink-0 group">
                        Lihat Semua <ArrowRight class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                    </Link>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div
                        v-for="(event, idx) in upcomingEvents"
                        :key="event.id"
                        data-reveal
                        :data-reveal-delay="idx * 80"
                        class="bg-card rounded-2xl p-5 border-l-4 border-l-primary border border-border shadow-sm hover:shadow-xl hover:shadow-primary/5 hover:-translate-y-1 transition-all duration-300 group"
                    >
                        <div class="flex items-center gap-4 mb-3">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary/15 to-primary/5 flex flex-col items-center justify-center text-primary shrink-0 ring-1 ring-primary/10">
                                <span class="text-xl font-black leading-none">{{ new Date(event.start_date).getDate() }}</span>
                                <span class="text-[10px] font-bold uppercase">{{ new Date(event.start_date).toLocaleDateString('id-ID', { month: 'short' }) }}</span>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-foreground leading-snug group-hover:text-primary transition-colors truncate">{{ event.title }}</h3>
                                <p class="text-xs text-muted-foreground mt-0.5">{{ event.location || '-' }}</p>
                            </div>
                        </div>
                        <p v-if="event.description" class="text-sm text-muted-foreground line-clamp-2 leading-relaxed">{{ event.description }}</p>
                    </div>
                </div>

                <div v-if="!upcomingEvents || upcomingEvents.length === 0" class="text-center py-12 bg-card rounded-2xl border border-dashed" data-reveal="fade">
                    <Calendar class="w-10 h-10 text-muted-foreground mx-auto mb-3" />
                    <p class="text-muted-foreground text-sm">Belum ada kegiatan mendatang.</p>
                </div>
            </div>
        </section>

        <!-- Wave divider -->
        <div class="relative -mt-1 z-10">
            <svg viewBox="0 0 1440 60" fill="none" class="w-full h-8 sm:h-12 text-card" preserveAspectRatio="none"><path d="M0 60V20C360 -5 720 50 1080 20C1260 5 1380 15 1440 20V60H0Z" fill="currentColor"/></svg>
        </div>

        <!-- ==================== PENGUMUMAN ==================== -->
        <section id="pengumuman" class="scroll-mt-16 relative bg-card py-14 sm:py-20 overflow-hidden">
            <div class="absolute top-0 left-0 w-80 h-80 bg-primary/[0.03] rounded-full blur-[100px]"></div>
            <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="flex items-end justify-between gap-4 mb-10" data-reveal>
                    <div>
                        <p class="text-primary text-xs font-bold tracking-widest uppercase mb-2">Pengumuman</p>
                        <h2 class="text-3xl sm:text-4xl font-black text-foreground tracking-tight">Kabar Terbaru</h2>
                        <div class="w-16 h-1 bg-gradient-to-r from-primary to-primary/30 rounded-full mt-3"></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="(announcement, idx) in latestAnnouncements"
                        :key="announcement.id"
                        data-reveal
                        :data-reveal-delay="idx * 80"
                        class="bg-card rounded-2xl border shadow-sm hover:shadow-xl hover:shadow-primary/5 hover:-translate-y-1 transition-all duration-300 flex flex-col group overflow-hidden"
                    >
                        <!-- Gradient top accent -->
                        <div class="h-1 bg-gradient-to-r from-primary via-primary/60 to-transparent"></div>
                        <div class="p-6 flex flex-col flex-1">
                            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center text-primary mb-4 group-hover:scale-110 transition-transform duration-300 ring-1 ring-primary/10">
                                <Megaphone class="w-4.5 h-4.5" />
                            </div>
                            <h3 class="text-base font-bold text-foreground mb-1.5 leading-snug group-hover:text-primary transition-colors line-clamp-2">{{ announcement.title }}</h3>
                            <p class="text-muted-foreground text-xs mb-3">{{ formatDate(announcement.created_at) }}</p>
                            <div class="text-muted-foreground line-clamp-2 text-sm leading-relaxed mb-4" v-html="announcement.content"></div>
                            <div class="mt-auto pt-3 border-t">
                                <Link :href="route('announcements.show', announcement.id)" class="text-primary text-xs font-semibold hover:text-primary/80 transition-colors flex items-center gap-1 group/link">
                                    Baca Detail <ChevronRight class="w-3.5 h-3.5 group-hover/link:translate-x-0.5 transition-transform" />
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="latestAnnouncements.length === 0" class="text-center py-12 bg-card rounded-2xl border border-dashed" data-reveal="fade">
                    <p class="text-muted-foreground text-sm">Belum ada pengumuman terbaru.</p>
                </div>
            </div>
        </section>

        <!-- Wave divider -->
        <div class="relative -mt-1 z-10">
            <svg viewBox="0 0 1440 60" fill="none" class="w-full h-8 sm:h-12 text-muted" preserveAspectRatio="none"><path d="M0 60V30C240 0 480 50 720 30C960 10 1200 45 1440 25V60H0Z" fill="currentColor"/></svg>
        </div>

        <!-- ==================== DONASI ==================== -->
        <section v-if="features.donations" id="donasi" class="scroll-mt-16 relative bg-muted py-14 sm:py-20 overflow-hidden">
            <div class="absolute top-1/2 right-0 w-80 h-80 bg-emerald-500/[0.03] rounded-full blur-[100px]"></div>
            <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="flex items-end justify-between gap-4 mb-10" data-reveal>
                    <div>
                        <p class="text-emerald-600 text-xs font-bold tracking-widest uppercase mb-2">Donasi</p>
                        <h2 class="text-3xl sm:text-4xl font-black text-foreground tracking-tight">Program Donasi</h2>
                        <div class="w-16 h-1 bg-gradient-to-r from-emerald-500 to-emerald-500/30 rounded-full mt-3"></div>
                    </div>
                    <Link :href="route('public.donations.index')" class="text-emerald-600 text-sm font-semibold hover:text-emerald-500 transition-colors flex items-center gap-1.5 shrink-0 group">
                        Lihat Semua <ArrowRight class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                    </Link>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div
                        v-for="(donation, idx) in activeDonations"
                        :key="donation.id"
                        data-reveal
                        :data-reveal-delay="idx * 80"
                        class="bg-card rounded-2xl p-6 border shadow-sm hover:shadow-xl hover:shadow-emerald-500/5 hover:-translate-y-1 transition-all duration-300 group"
                    >
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-emerald-500/15 to-emerald-500/5 flex items-center justify-center text-emerald-600 shrink-0 ring-1 ring-emerald-500/10 group-hover:scale-110 transition-transform duration-300">
                                <Heart class="w-5 h-5" />
                            </div>
                            <h3 class="text-base font-bold text-foreground leading-snug group-hover:text-emerald-600 transition-colors line-clamp-1">{{ donation.title }}</h3>
                        </div>
                        <p v-if="donation.description" class="text-sm text-muted-foreground line-clamp-2 mb-5">{{ donation.description }}</p>
                        <div v-if="donation.target_amount" class="space-y-2.5">
                            <div class="w-full h-2.5 bg-muted rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-emerald-500 to-emerald-400 rounded-full transition-all duration-700 relative" :style="{ width: donationProgress(donation) + '%' }">
                                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/25 to-transparent animate-shimmer"></div>
                                </div>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-muted-foreground">{{ formatCurrency(donation.current_amount || 0) }}</span>
                                <span class="font-bold text-foreground">{{ formatCurrency(donation.target_amount) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="!activeDonations || activeDonations.length === 0" class="text-center py-12 bg-card rounded-2xl border border-dashed" data-reveal="fade">
                    <Heart class="w-10 h-10 text-muted-foreground mx-auto mb-3" />
                    <p class="text-muted-foreground text-sm">Belum ada program donasi aktif.</p>
                </div>
            </div>
        </section>

        <!-- Wave divider -->
        <div class="relative -mt-1 z-10">
            <svg viewBox="0 0 1440 60" fill="none" class="w-full h-8 sm:h-12 text-card" preserveAspectRatio="none"><path d="M0 60V20C360 -5 720 50 1080 20C1260 5 1380 15 1440 20V60H0Z" fill="currentColor"/></svg>
        </div>

        <!-- ==================== GALERI ==================== -->
        <section v-if="features.gallery && featuredAlbums?.length" id="galeri" class="scroll-mt-16 relative bg-card py-14 sm:py-20 overflow-hidden">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[300px] bg-primary/[0.03] rounded-full blur-[100px]"></div>
            <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center mb-10" data-reveal>
                    <p class="text-primary text-xs font-bold tracking-widest uppercase mb-2">Galeri</p>
                    <h2 class="text-3xl sm:text-4xl font-black text-foreground tracking-tight">Momen Berharga</h2>
                    <div class="w-16 h-1 bg-gradient-to-r from-primary to-primary/30 rounded-full mx-auto mt-3"></div>
                </div>

                <div class="flex gap-5 sm:gap-6 overflow-x-auto pb-4 snap-x no-scrollbar" data-reveal="fade">
                    <div
                        v-for="album in featuredAlbums"
                        :key="album.id"
                        class="snap-center shrink-0 w-[280px] sm:w-[340px] h-[360px] sm:h-[420px] group relative rounded-3xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-500"
                    >
                        <img
                            :src="album.cover_image_url || 'https://images.unsplash.com/photo-1542038784456-1ea8e935640e?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80'"
                            class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                        >
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/30 to-transparent"></div>
                        <div class="absolute bottom-0 left-0 right-0 p-6">
                            <span class="text-white/60 text-xs font-semibold uppercase tracking-wider">{{ album.category }} &middot; {{ album.photos_count }} Foto</span>
                            <h3 class="text-lg font-black text-white mt-1.5 leading-tight">{{ album.name }}</h3>
                            <Link :href="route('public.gallery.show', album.slug)" class="mt-4 inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white hover:bg-primary hover:border-primary hover:scale-110 transition-all duration-300">
                                <Plus class="w-4.5 h-4.5" />
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Wave divider -->
        <div class="relative -mt-1 z-10">
            <svg viewBox="0 0 1440 60" fill="none" class="w-full h-8 sm:h-12 text-muted" preserveAspectRatio="none"><path d="M0 60V30C240 0 480 50 720 30C960 10 1200 45 1440 25V60H0Z" fill="currentColor"/></svg>
        </div>

        <!-- ==================== KONTAK ==================== -->
        <section id="kontak" class="scroll-mt-16 relative bg-muted py-14 sm:py-20 overflow-hidden">
            <div class="absolute -bottom-20 left-1/3 w-80 h-80 bg-primary/[0.03] rounded-full blur-[100px]"></div>
            <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center mb-12" data-reveal>
                    <p class="text-primary font-bold text-sm uppercase tracking-wider mb-2">Hubungi Kami</p>
                    <h2 class="text-3xl sm:text-4xl font-black text-foreground tracking-tight">Kontak</h2>
                    <div class="w-16 h-1 bg-gradient-to-r from-primary to-primary/30 rounded-full mx-auto mt-3"></div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 max-w-5xl mx-auto">
                    <!-- Contact Info -->
                    <div class="space-y-4">
                        <div data-reveal="left" class="group flex gap-4 p-5 bg-card border-l-4 border-l-primary border border-border rounded-2xl shadow-sm hover:shadow-lg hover:shadow-primary/5 transition-all duration-300">
                            <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-primary/15 to-primary/5 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300 ring-1 ring-primary/10">
                                <MapPin class="w-5 h-5 text-primary" />
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-foreground">Alamat</h4>
                                <p class="text-sm text-muted-foreground mt-1">{{ [app.address, app.city, app.province, app.postal_code].filter(Boolean).join(', ') || '-' }}</p>
                            </div>
                        </div>

                        <div data-reveal="left" data-reveal-delay="100" class="group flex gap-4 p-5 bg-card border-l-4 border-l-primary border border-border rounded-2xl shadow-sm hover:shadow-lg hover:shadow-primary/5 transition-all duration-300">
                            <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-primary/15 to-primary/5 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300 ring-1 ring-primary/10">
                                <Mail class="w-5 h-5 text-primary" />
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-foreground">Email</h4>
                                <p class="text-sm text-muted-foreground mt-1">{{ portal.contact_email || app.email || '-' }}</p>
                            </div>
                        </div>

                        <div data-reveal="left" data-reveal-delay="200" class="group flex gap-4 p-5 bg-card border-l-4 border-l-primary border border-border rounded-2xl shadow-sm hover:shadow-lg hover:shadow-primary/5 transition-all duration-300">
                            <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-primary/15 to-primary/5 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300 ring-1 ring-primary/10">
                                <Phone class="w-5 h-5 text-primary" />
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-foreground">Telepon</h4>
                                <p class="text-sm text-muted-foreground mt-1">{{ app.phone || '-' }}</p>
                            </div>
                        </div>

                        <!-- Social Media -->
                        <div v-if="social.facebook || social.instagram || social.twitter || social.youtube || social.whatsapp || social.telegram" data-reveal="left" data-reveal-delay="300" class="pt-3">
                            <h4 class="text-sm font-bold text-muted-foreground mb-3 uppercase tracking-wider">Ikuti Kami</h4>
                            <div class="flex gap-3 flex-wrap">
                                <a v-if="social.facebook" :href="social.facebook" target="_blank" class="w-11 h-11 bg-muted rounded-xl flex items-center justify-center text-muted-foreground hover:bg-[#1877F2] hover:text-white hover:scale-110 hover:shadow-lg hover:shadow-[#1877F2]/20 transition-all duration-300">
                                    <svg class="w-4.5 h-4.5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                </a>
                                <a v-if="social.instagram" :href="social.instagram" target="_blank" class="w-11 h-11 bg-muted rounded-xl flex items-center justify-center text-muted-foreground hover:bg-[#E4405F] hover:text-white hover:scale-110 hover:shadow-lg hover:shadow-[#E4405F]/20 transition-all duration-300">
                                    <svg class="w-4.5 h-4.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.791-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.209-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                </a>
                                <a v-if="social.whatsapp" :href="'https://wa.me/' + social.whatsapp" target="_blank" class="w-11 h-11 bg-muted rounded-xl flex items-center justify-center text-muted-foreground hover:bg-[#25D366] hover:text-white hover:scale-110 hover:shadow-lg hover:shadow-[#25D366]/20 transition-all duration-300">
                                    <svg class="w-4.5 h-4.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                </a>
                                <a v-if="social.telegram" :href="social.telegram" target="_blank" class="w-11 h-11 bg-muted rounded-xl flex items-center justify-center text-muted-foreground hover:bg-[#0088cc] hover:text-white hover:scale-110 hover:shadow-lg hover:shadow-[#0088cc]/20 transition-all duration-300">
                                    <svg class="w-4.5 h-4.5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.479.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Google Maps -->
                    <div data-reveal="right">
                        <div v-if="portal.google_maps_embed" class="rounded-3xl overflow-hidden border shadow-lg h-full min-h-[300px]" v-html="portal.google_maps_embed"></div>
                        <div v-else class="bg-card rounded-3xl border p-8 flex flex-col items-center justify-center text-center h-full min-h-[300px] shadow-sm">
                            <div class="w-16 h-16 bg-gradient-to-br from-primary/10 to-primary/5 rounded-3xl flex items-center justify-center mb-4">
                                <MapPin class="w-8 h-8 text-muted-foreground" />
                            </div>
                            <p class="text-sm text-muted-foreground">Peta lokasi belum tersedia.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ==================== CTA ==================== -->
        <section class="relative py-20 sm:py-28 bg-gradient-to-br from-primary-900 via-primary-800 to-primary-950 overflow-hidden" data-reveal="scale">
            <!-- Decorative orbs -->
            <div class="absolute top-0 left-1/4 w-96 h-96 bg-primary-500/10 rounded-full blur-[150px]"></div>
            <div class="absolute bottom-0 right-1/4 w-80 h-80 bg-primary-400/10 rounded-full blur-[120px]"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] border border-white/5 rounded-full"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[350px] h-[350px] border border-white/5 rounded-full"></div>

            <div class="max-w-3xl mx-auto text-center px-4 sm:px-6 relative z-10">
                <div class="inline-flex items-center gap-2 mb-6 px-4 py-2 rounded-full bg-white/10 border border-white/10 backdrop-blur-sm">
                    <Heart class="w-4 h-4 text-primary-200" />
                    <span class="text-primary-100 text-xs font-semibold">Dukung Kami</span>
                </div>
                <h2 class="text-3xl sm:text-5xl font-black text-white tracking-tight mb-4 drop-shadow-lg">Bangun Bersama Masa Depan</h2>
                <p class="text-lg text-primary-100/80 mb-10 leading-relaxed max-w-xl mx-auto">
                    Dukungan Anda melalui iuran dan donasi menjadi energi utama pergerakan organisasi kami.
                </p>
                <div class="flex flex-wrap justify-center gap-4">
                    <ActionLink v-if="features.donations" :href="route('public.donations.index')" variant="secondary" size="md">Donasi Program</ActionLink>
                    <ActionLink :href="$page.props.auth.user ? route('dashboard') : route('login')" size="md">
                        {{ $page.props.auth.user ? 'Ke Dashboard' : 'Bayar Iuran' }}
                    </ActionLink>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>

<style>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

/* CSS Tree Connector Lines */
.tree-container {
    display: flex;
    justify-content: center;
    padding: 0;
    margin: 0;
    list-style: none;
}
.tree-container li::before {
    content: '';
    position: absolute;
    top: 0;
    left: 50%;
    width: 2px;
    height: 32px;
    background-color: #E2E8F0;
    transform: translateX(-50%);
}
.tree-container > li::before {
    display: none;
}
.tree-container li > .relative > ul::before {
    content: '';
    position: absolute;
    top: 0;
    left: 128px;
    right: 128px;
    height: 2px;
    background-color: #E2E8F0;
}
.tree-container li > .relative > ul:has(> li:only-child)::before {
    display: none;
}

@keyframes kenburns {
    0% { transform: scale(1); }
    100% { transform: scale(1.1); }
}
.animate-kenburns {
    animation: kenburns 12s linear forwards;
}

@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}
.animate-shimmer {
    animation: shimmer 2s ease-in-out infinite;
}

/* ========== Logo Hero Animations — Circuit Effect ========== */
.logo-container {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 200px;
    height: 200px;
}
@media (min-width: 640px) {
    .logo-container { width: 200px; height: 200px; }
}

/* Circuit SVG overlay */
.circuit-svg {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    z-index: 5;
}

/* Animate each path with stroke-dashoffset flowing effect */
.circuit-path {
    stroke-dasharray: 80;
    stroke-dashoffset: 80;
    animation: circuit-flow 2.8s ease-in-out infinite;
}
.circuit-dot {
    opacity: 0;
    animation: circuit-dot-blink 2.8s ease-in-out infinite;
}

.circuit-delay-0 { animation-delay: 0s; }
.circuit-delay-1 { animation-delay: 0.35s; }
.circuit-delay-2 { animation-delay: 0.7s; }
.circuit-delay-3 { animation-delay: 1.05s; }
.circuit-delay-4 { animation-delay: 1.4s; }
.circuit-delay-5 { animation-delay: 1.75s; }
.circuit-delay-6 { animation-delay: 2.1s; }
.circuit-delay-7 { animation-delay: 2.45s; }

@keyframes circuit-flow {
    0%   { stroke-dashoffset: 80; opacity: 0; }
    10%  { opacity: 1; }
    60%  { stroke-dashoffset: 0; opacity: 1; }
    80%  { stroke-dashoffset: 0; opacity: 0.5; }
    100% { stroke-dashoffset: 0; opacity: 0; }
}

@keyframes circuit-dot-blink {
    0%   { opacity: 0; }
    55%  { opacity: 0; }
    65%  { opacity: 1; }
    85%  { opacity: 0.7; }
    100% { opacity: 0; }
}

/* Logo inner circle */
.logo-inner {
    position: relative;
    z-index: 10;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 118px;
    height: 118px;
    border-radius: 9999px;
    background: rgba(255,255,255,0.07);
    border: 1px solid rgba(255,255,255,0.2);
    box-shadow: 0 0 24px rgba(255,255,255,0.12);
}
@media (min-width: 640px) {
    .logo-inner { width: 128px; height: 128px; }
}

/* Soft glow */
.logo-glow {
    position: absolute;
    inset: -8px;
    border-radius: 9999px;
    background: radial-gradient(circle, rgba(255,255,255,0.09) 0%, transparent 70%);
    animation: logo-glow-pulse 3s ease-in-out infinite;
    z-index: 4;
}
@keyframes logo-glow-pulse {
    0%, 100% { opacity: 0.5; transform: scale(1); }
    50%       { opacity: 1;   transform: scale(1.06); }
}

/* Float animation */
.logo-float {
    animation: logo-float 4s ease-in-out infinite;
}
@keyframes logo-float {
    0%, 100% { transform: translateY(0); }
    50%       { transform: translateY(-6px); }
}
/* ========== Scroll Indicator ========== */
.scroll-mouse {
    width: 26px;
    height: 42px;
    border: 2px solid rgba(255, 255, 255, 0.75);
    border-radius: 13px;
    display: flex;
    justify-content: center;
    padding-top: 6px;
    box-shadow: 0 0 12px rgba(255,255,255,0.2);
    animation: scroll-mouse-glow 2s ease-in-out infinite;
}

.scroll-wheel {
    width: 4px;
    height: 8px;
    background: white;
    border-radius: 2px;
    animation: scroll-wheel-move 1.4s ease-in-out infinite;
}

@keyframes scroll-wheel-move {
    0%   { opacity: 1; transform: translateY(0); }
    60%  { opacity: 0; transform: translateY(10px); }
    61%  { opacity: 0; transform: translateY(0); }
    100% { opacity: 1; transform: translateY(0); }
}

@keyframes scroll-mouse-glow {
    0%, 100% { box-shadow: 0 0 8px rgba(255,255,255,0.2); }
    50%       { box-shadow: 0 0 18px rgba(255,255,255,0.5); }
}

@keyframes scroll-arrow {
    0%   { opacity: 0; transform: translateY(-4px); }
    50%  { opacity: 1; transform: translateY(0); }
    100% { opacity: 0; transform: translateY(4px); }
}
</style>
