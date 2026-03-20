<script setup>
import ActionLink from '@/Components/ActionLink.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import OrgTreeNode from '@/Components/OrgTreeNode.vue';
import { useScrollReveal } from '@/composables/useScrollReveal';
import {
    Megaphone, ChevronRight, ArrowRight, Plus, Heart, CreditCard,
    BookOpen, Shield, Eye, Users, Lightbulb, Target,
    ClipboardList, Calendar, MapPin, Mail, Phone, Send,
    UserX, ImageOff,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';

import { ref, computed, onMounted, onUnmounted } from 'vue';

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

// Photo slider
const currentSlide = ref(0);
let slideTimer = null;
const startSlide = () => {
    slideTimer = setInterval(() => {
        if (props.latestPhotos?.length > 0) {
            currentSlide.value = (currentSlide.value + 1) % props.latestPhotos.length;
        }
    }, 5000);
};
const stopSlide = () => { if (slideTimer) clearInterval(slideTimer); };
onMounted(() => { startSlide(); });
onUnmounted(() => { stopSlide(); });

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
        <section id="beranda" class="scroll-mt-16 relative bg-black overflow-hidden h-[80vh] min-h-[480px] max-h-[800px]">
            <div class="absolute inset-0">
                <TransitionGroup
                    enter-active-class="transition duration-1000 ease-out"
                    enter-from-class="opacity-0 scale-105"
                    enter-to-class="opacity-100 scale-100"
                    leave-active-class="transition duration-1000 ease-in absolute inset-0"
                    leave-from-class="opacity-100 scale-100"
                    leave-to-class="opacity-0 scale-95"
                >
                    <div v-for="(photo, index) in latestPhotos" :key="photo.id" v-show="currentSlide === index" class="absolute inset-0">
                        <img :src="photo.url" class="w-full h-full object-cover" loading="eager">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-black/20"></div>
                    </div>
                    <div v-if="!latestPhotos || latestPhotos.length === 0" class="absolute inset-0">
                        <div class="absolute inset-0 bg-gradient-to-br from-primary/20 via-primary/10 to-black"></div>
                    </div>
                </TransitionGroup>
            </div>

            <div class="relative z-10 h-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col justify-end pb-16 sm:pb-20">
                <div class="max-w-2xl">
                    <Transition appear enter-active-class="transition duration-700 delay-300" enter-from-class="opacity-0 translate-y-8" enter-to-class="opacity-100 translate-y-0">
                        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-[1.05] text-white">
                            {{ app.name }}
                        </h1>
                    </Transition>
                    <Transition appear enter-active-class="transition duration-700 delay-500" enter-from-class="opacity-0 translate-y-8" enter-to-class="opacity-100 translate-y-0">
                        <p class="mt-4 text-base sm:text-lg text-white/70 leading-relaxed max-w-lg">
                            {{ portal.welcome_text || 'Selamat Datang di Portal Resmi Organisasi Kami' }}
                        </p>
                    </Transition>
                    <Transition appear enter-active-class="transition duration-700 delay-700" enter-from-class="opacity-0 translate-y-8" enter-to-class="opacity-100 translate-y-0">
                        <div class="mt-6 flex flex-wrap gap-3">
                            <ActionLink :href="$page.props.auth.user ? route('dashboard') : route('login')" size="md">
                                {{ $page.props.auth.user ? 'Ke Dashboard' : 'Mulai Sekarang' }}
                            </ActionLink>
                            <a href="#tentang" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-white/20 bg-white/10 backdrop-blur-sm text-white hover:bg-white/20 h-10 px-5">
                                Tentang Kami
                            </a>
                        </div>
                    </Transition>
                </div>
            </div>

            <div v-if="latestPhotos?.length > 1" class="absolute bottom-6 right-6 z-20 flex items-center gap-2">
                <button
                    v-for="(_, index) in latestPhotos"
                    :key="index"
                    @click="currentSlide = index; stopSlide(); startSlide();"
                    class="h-2 rounded-full transition-all duration-300"
                    :class="currentSlide === index ? 'w-8 bg-primary' : 'w-2 bg-white/40 hover:bg-white/60'"
                />
            </div>
        </section>

        <!-- ==================== TENTANG ==================== -->
        <section id="tentang" class="scroll-mt-16 bg-card py-14 sm:py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- History -->
                <div class="mx-auto max-w-3xl mb-14">
                    <div class="flex items-center gap-3 mb-5" data-reveal="left">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                            <BookOpen class="w-5 h-5" />
                        </div>
                        <p class="text-primary font-semibold text-sm">Tentang Kami</p>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-bold text-foreground tracking-tight mb-4" data-reveal data-reveal-delay="100">
                        {{ app.name }}
                    </h2>
                    <p v-if="app.motto" class="text-base italic text-primary/80 font-medium mb-4" data-reveal data-reveal-delay="150">
                        "{{ app.motto }}"
                    </p>
                    <p class="text-base text-muted-foreground leading-relaxed" data-reveal data-reveal-delay="200">
                        {{ portal.about_text || 'Organisasi ini didirikan dengan semangat kebersamaan dan keinginan untuk memberikan kontribusi nyata bagi masyarakat. Berawal dari komunitas kecil, kini kami telah tumbuh menjadi sebuah wadah profesional yang mengelola berbagai bidang kegiatan.' }}
                    </p>
                    <div v-if="app.founded_date" class="mt-5 inline-flex items-center gap-2 px-4 py-2 bg-primary/5 rounded-lg border border-primary/10" data-reveal data-reveal-delay="300">
                        <span class="text-sm text-muted-foreground">Berdiri sejak</span>
                        <span class="text-sm font-bold text-primary">{{ app.founded_date }}</span>
                    </div>
                </div>

                <!-- Values -->
                <div class="text-center mb-8" data-reveal>
                    <p class="text-primary font-semibold text-sm mb-2">Prinsip</p>
                    <h3 class="text-2xl sm:text-3xl font-bold text-foreground tracking-tight">Nilai-Nilai Kami</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 max-w-5xl mx-auto">
                    <div
                        v-for="(value, idx) in values"
                        :key="value.title"
                        data-reveal
                        :data-reveal-delay="idx * 100"
                        class="group relative bg-card rounded-2xl p-5 border hover:border-primary/30 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 overflow-hidden"
                    >
                        <div class="absolute top-0 right-0 w-24 h-24 bg-primary/5 rounded-full -translate-y-1/2 translate-x-1/2 group-hover:scale-150 transition-transform duration-500"></div>
                        <div class="relative z-10">
                            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary mb-3 group-hover:scale-110 group-hover:bg-primary/20 transition-all duration-300">
                                <component :is="value.icon" class="w-5 h-5" />
                            </div>
                            <h4 class="text-base font-bold text-foreground mb-1 group-hover:text-primary transition-colors duration-300">{{ value.title }}</h4>
                            <p class="text-sm text-muted-foreground leading-relaxed">{{ value.description }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ==================== VISI & MISI ==================== -->
        <section id="visi-misi" class="scroll-mt-16 bg-muted py-14 sm:py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-10" data-reveal>
                    <p class="text-primary font-semibold text-sm mb-2">Arah & Landasan</p>
                    <h2 class="text-2xl sm:text-3xl font-bold text-foreground tracking-tight">Visi & Misi</h2>
                </div>

                <div v-if="visionMission" class="grid grid-cols-1 lg:grid-cols-2 gap-8 max-w-5xl mx-auto">
                    <!-- Vision -->
                    <div data-reveal="left" class="relative bg-card border-2 border-primary/20 rounded-2xl p-6 shadow-sm hover:shadow-lg hover:border-primary/40 transition-all duration-300">
                        <div class="absolute inset-0 bg-gradient-to-br from-primary/5 to-transparent rounded-2xl"></div>
                        <div class="relative">
                            <div class="flex items-center gap-3 mb-5">
                                <div class="p-2.5 bg-primary/10 rounded-xl"><Eye class="w-5 h-5 text-primary" /></div>
                                <h3 class="text-xl font-bold text-foreground">Visi Kami</h3>
                            </div>
                            <blockquote class="text-lg italic text-foreground/90 leading-relaxed border-l-4 border-primary/30 pl-4">
                                "{{ visionMission.vision }}"
                            </blockquote>
                        </div>
                    </div>
                    <!-- Mission -->
                    <div data-reveal="right" class="relative bg-card border-2 border-emerald-500/20 rounded-2xl p-6 shadow-sm hover:shadow-lg hover:border-emerald-500/40 transition-all duration-300">
                        <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-transparent rounded-2xl"></div>
                        <div class="relative">
                            <div class="flex items-center gap-3 mb-5">
                                <div class="p-2.5 bg-emerald-500/10 rounded-xl"><ClipboardList class="w-5 h-5 text-emerald-600" /></div>
                                <h3 class="text-xl font-bold text-foreground">Misi Kami</h3>
                            </div>
                            <ul class="space-y-3">
                                <li v-for="(mission, index) in visionMission.missions" :key="index" class="flex gap-3 items-start">
                                    <span class="flex-shrink-0 w-7 h-7 rounded-full bg-emerald-500/10 text-emerald-600 flex items-center justify-center font-bold text-xs ring-2 ring-emerald-500/20">{{ index + 1 }}</span>
                                    <p class="text-base text-foreground pt-0.5">{{ mission }}</p>
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

        <!-- ==================== STRUKTUR ==================== -->
        <section id="struktur" class="scroll-mt-16 bg-card py-14 sm:py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-10" data-reveal>
                    <p class="text-primary font-semibold text-sm mb-2">Kepengurusan</p>
                    <h2 class="text-2xl sm:text-3xl font-bold text-foreground tracking-tight">Struktur Organisasi</h2>
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

        <!-- ==================== KEGIATAN ==================== -->
        <section v-if="features.events" id="kegiatan" class="scroll-mt-16 bg-muted py-14 sm:py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-end justify-between gap-4 mb-8" data-reveal>
                    <div>
                        <p class="text-primary text-xs font-semibold tracking-widest uppercase mb-1">Kegiatan</p>
                        <h2 class="text-2xl sm:text-3xl font-bold text-foreground tracking-tight">Agenda Mendatang</h2>
                    </div>
                    <Link :href="route('public.events.index')" class="text-primary text-sm font-medium hover:text-primary/80 transition-colors flex items-center gap-1 shrink-0">
                        Lihat Semua <ArrowRight class="w-3.5 h-3.5" />
                    </Link>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div
                        v-for="(event, idx) in upcomingEvents"
                        :key="event.id"
                        data-reveal
                        :data-reveal-delay="idx * 80"
                        class="bg-card rounded-xl p-5 border shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 group"
                    >
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-12 h-12 rounded-xl bg-primary/10 flex flex-col items-center justify-center text-primary shrink-0">
                                <span class="text-lg font-black leading-none">{{ new Date(event.start_date).getDate() }}</span>
                                <span class="text-[10px] font-semibold uppercase">{{ new Date(event.start_date).toLocaleDateString('id-ID', { month: 'short' }) }}</span>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-base font-semibold text-foreground leading-snug group-hover:text-primary transition-colors truncate">{{ event.title }}</h3>
                                <p class="text-xs text-muted-foreground">{{ event.location || '-' }}</p>
                            </div>
                        </div>
                        <p v-if="event.description" class="text-sm text-muted-foreground line-clamp-2 leading-relaxed">{{ event.description }}</p>
                    </div>
                </div>

                <div v-if="!upcomingEvents || upcomingEvents.length === 0" class="text-center py-12 bg-card rounded-xl border border-dashed" data-reveal="fade">
                    <Calendar class="w-8 h-8 text-muted-foreground mx-auto mb-2" />
                    <p class="text-muted-foreground text-sm">Belum ada kegiatan mendatang.</p>
                </div>
            </div>
        </section>

        <!-- ==================== PENGUMUMAN ==================== -->
        <section class="scroll-mt-16 bg-card py-14 sm:py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-end justify-between gap-4 mb-8" data-reveal>
                    <div>
                        <p class="text-primary text-xs font-semibold tracking-widest uppercase mb-1">Pengumuman</p>
                        <h2 class="text-2xl sm:text-3xl font-bold text-foreground tracking-tight">Kabar Terbaru</h2>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="(announcement, idx) in latestAnnouncements"
                        :key="announcement.id"
                        data-reveal
                        :data-reveal-delay="idx * 80"
                        class="bg-card rounded-xl p-5 border shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col group"
                    >
                        <div class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center text-primary mb-3 group-hover:bg-primary/15 transition-colors">
                            <Megaphone class="w-4 h-4" />
                        </div>
                        <h3 class="text-base font-semibold text-foreground mb-1 leading-snug group-hover:text-primary transition-colors line-clamp-2">{{ announcement.title }}</h3>
                        <p class="text-muted-foreground text-xs mb-3">{{ formatDate(announcement.created_at) }}</p>
                        <div class="text-muted-foreground line-clamp-2 text-sm leading-relaxed mb-4" v-html="announcement.content"></div>
                        <div class="mt-auto pt-3 border-t">
                            <Link :href="route('announcements.show', announcement.id)" class="text-primary text-xs font-medium hover:text-primary/80 transition-colors flex items-center gap-1">
                                Baca Detail <ChevronRight class="w-3 h-3" />
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-if="latestAnnouncements.length === 0" class="text-center py-12 bg-card rounded-xl border border-dashed" data-reveal="fade">
                    <p class="text-muted-foreground text-sm">Belum ada pengumuman terbaru.</p>
                </div>
            </div>
        </section>

        <!-- ==================== DONASI ==================== -->
        <section v-if="features.donations" id="donasi" class="scroll-mt-16 bg-muted py-14 sm:py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-end justify-between gap-4 mb-8" data-reveal>
                    <div>
                        <p class="text-primary text-xs font-semibold tracking-widest uppercase mb-1">Donasi</p>
                        <h2 class="text-2xl sm:text-3xl font-bold text-foreground tracking-tight">Program Donasi</h2>
                    </div>
                    <Link :href="route('public.donations.index')" class="text-primary text-sm font-medium hover:text-primary/80 transition-colors flex items-center gap-1 shrink-0">
                        Lihat Semua <ArrowRight class="w-3.5 h-3.5" />
                    </Link>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div
                        v-for="(donation, idx) in activeDonations"
                        :key="donation.id"
                        data-reveal
                        :data-reveal-delay="idx * 80"
                        class="bg-card rounded-xl p-5 border shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 group"
                    >
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-9 h-9 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-600 shrink-0">
                                <Heart class="w-4 h-4" />
                            </div>
                            <h3 class="text-base font-semibold text-foreground leading-snug group-hover:text-primary transition-colors line-clamp-1">{{ donation.title }}</h3>
                        </div>
                        <p v-if="donation.description" class="text-sm text-muted-foreground line-clamp-2 mb-4">{{ donation.description }}</p>
                        <div v-if="donation.target_amount" class="space-y-2">
                            <div class="w-full h-2 bg-muted rounded-full overflow-hidden">
                                <div class="h-full bg-emerald-500 rounded-full transition-all duration-500" :style="{ width: donationProgress(donation) + '%' }"></div>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-muted-foreground">{{ formatCurrency(donation.current_amount || 0) }}</span>
                                <span class="font-semibold text-foreground">{{ formatCurrency(donation.target_amount) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="!activeDonations || activeDonations.length === 0" class="text-center py-12 bg-card rounded-xl border border-dashed" data-reveal="fade">
                    <Heart class="w-8 h-8 text-muted-foreground mx-auto mb-2" />
                    <p class="text-muted-foreground text-sm">Belum ada program donasi aktif.</p>
                </div>
            </div>
        </section>

        <!-- ==================== GALERI ==================== -->
        <section v-if="features.gallery && featuredAlbums?.length" id="galeri" class="scroll-mt-16 bg-card py-14 sm:py-20 overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-8" data-reveal>
                    <p class="text-primary text-xs font-semibold tracking-widest uppercase mb-1">Galeri</p>
                    <h2 class="text-2xl sm:text-3xl font-bold text-foreground tracking-tight">Momen Berharga</h2>
                </div>

                <div class="flex gap-4 sm:gap-6 overflow-x-auto pb-4 snap-x no-scrollbar" data-reveal="fade">
                    <div
                        v-for="album in featuredAlbums"
                        :key="album.id"
                        class="snap-center shrink-0 w-[260px] sm:w-[320px] h-[340px] sm:h-[400px] group relative rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-shadow duration-300"
                    >
                        <img
                            :src="album.cover_image_url || 'https://images.unsplash.com/photo-1542038784456-1ea8e935640e?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80'"
                            class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                        >
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                        <div class="absolute bottom-0 left-0 right-0 p-5">
                            <span class="text-white/60 text-xs font-medium">{{ album.category }} &middot; {{ album.photos_count }} Foto</span>
                            <h3 class="text-lg font-bold text-white mt-1 leading-tight">{{ album.name }}</h3>
                            <Link :href="route('public.gallery.show', album.slug)" class="mt-3 inline-flex items-center justify-center w-9 h-9 rounded-full bg-white/10 backdrop-blur-sm border border-white/20 text-white hover:bg-primary hover:border-primary transition-all">
                                <Plus class="w-4 h-4" />
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ==================== KONTAK ==================== -->
        <section id="kontak" class="scroll-mt-16 bg-muted py-14 sm:py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-10" data-reveal>
                    <p class="text-primary font-semibold text-sm mb-2">Hubungi Kami</p>
                    <h2 class="text-2xl sm:text-3xl font-bold text-foreground tracking-tight">Kontak</h2>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 max-w-5xl mx-auto">
                    <!-- Contact Info -->
                    <div class="space-y-4">
                        <div data-reveal="left" class="group flex gap-4 p-5 bg-card border rounded-xl shadow-sm hover:shadow-md transition-all duration-300">
                            <div class="flex-shrink-0 w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center group-hover:bg-primary/20 transition-colors">
                                <MapPin class="w-5 h-5 text-primary" />
                            </div>
                            <div>
                                <h4 class="text-base font-semibold text-foreground">Alamat</h4>
                                <p class="text-sm text-muted-foreground mt-1">{{ [app.address, app.city, app.province, app.postal_code].filter(Boolean).join(', ') || '-' }}</p>
                            </div>
                        </div>

                        <div data-reveal="left" data-reveal-delay="100" class="group flex gap-4 p-5 bg-card border rounded-xl shadow-sm hover:shadow-md transition-all duration-300">
                            <div class="flex-shrink-0 w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center group-hover:bg-primary/20 transition-colors">
                                <Mail class="w-5 h-5 text-primary" />
                            </div>
                            <div>
                                <h4 class="text-base font-semibold text-foreground">Email</h4>
                                <p class="text-sm text-muted-foreground mt-1">{{ portal.contact_email || app.email || '-' }}</p>
                            </div>
                        </div>

                        <div data-reveal="left" data-reveal-delay="200" class="group flex gap-4 p-5 bg-card border rounded-xl shadow-sm hover:shadow-md transition-all duration-300">
                            <div class="flex-shrink-0 w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center group-hover:bg-primary/20 transition-colors">
                                <Phone class="w-5 h-5 text-primary" />
                            </div>
                            <div>
                                <h4 class="text-base font-semibold text-foreground">Telepon</h4>
                                <p class="text-sm text-muted-foreground mt-1">{{ app.phone || '-' }}</p>
                            </div>
                        </div>

                        <!-- Social Media -->
                        <div v-if="social.facebook || social.instagram || social.twitter || social.youtube || social.whatsapp || social.telegram" data-reveal="left" data-reveal-delay="300" class="pt-3">
                            <h4 class="text-sm font-semibold text-muted-foreground mb-3">Ikuti Kami</h4>
                            <div class="flex gap-3 flex-wrap">
                                <a v-if="social.facebook" :href="social.facebook" target="_blank" class="w-10 h-10 bg-muted rounded-full flex items-center justify-center text-muted-foreground hover:bg-[#1877F2] hover:text-white hover:scale-110 transition-all duration-300">
                                    <svg class="w-4.5 h-4.5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                </a>
                                <a v-if="social.instagram" :href="social.instagram" target="_blank" class="w-10 h-10 bg-muted rounded-full flex items-center justify-center text-muted-foreground hover:bg-[#E4405F] hover:text-white hover:scale-110 transition-all duration-300">
                                    <svg class="w-4.5 h-4.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.791-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.209-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                </a>
                                <a v-if="social.whatsapp" :href="'https://wa.me/' + social.whatsapp" target="_blank" class="w-10 h-10 bg-muted rounded-full flex items-center justify-center text-muted-foreground hover:bg-[#25D366] hover:text-white hover:scale-110 transition-all duration-300">
                                    <svg class="w-4.5 h-4.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                </a>
                                <a v-if="social.telegram" :href="social.telegram" target="_blank" class="w-10 h-10 bg-muted rounded-full flex items-center justify-center text-muted-foreground hover:bg-[#0088cc] hover:text-white hover:scale-110 transition-all duration-300">
                                    <svg class="w-4.5 h-4.5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.479.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Google Maps -->
                    <div data-reveal="right">
                        <div v-if="portal.google_maps_embed" class="rounded-2xl overflow-hidden border shadow-sm h-full min-h-[300px]" v-html="portal.google_maps_embed"></div>
                        <div v-else class="bg-card rounded-2xl border p-8 flex flex-col items-center justify-center text-center h-full min-h-[300px]">
                            <MapPin class="w-10 h-10 text-muted-foreground mb-3" />
                            <p class="text-sm text-muted-foreground">Peta lokasi belum tersedia.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ==================== CTA ==================== -->
        <section class="relative py-16 sm:py-20 bg-gradient-to-br from-primary-900 via-primary-800 to-primary-950" data-reveal="scale">
            <div class="max-w-3xl mx-auto text-center px-4 sm:px-6 relative z-10">
                <div class="inline-flex items-center gap-2 mb-4 px-3 py-1.5 rounded-full bg-white/10 border border-white/10">
                    <Heart class="w-3.5 h-3.5 text-primary-200" />
                    <span class="text-primary-100 text-xs font-medium">Dukung Kami</span>
                </div>
                <h2 class="text-2xl sm:text-4xl font-bold text-white tracking-tight mb-3">Bangun Bersama Masa Depan</h2>
                <p class="text-base text-primary-100/80 mb-8 leading-relaxed max-w-xl mx-auto">
                    Dukungan Anda melalui iuran dan donasi menjadi energi utama pergerakan organisasi kami.
                </p>
                <div class="flex flex-wrap justify-center gap-3">
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
</style>
