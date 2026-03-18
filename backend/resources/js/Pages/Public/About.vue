<script setup>
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useScrollReveal } from '@/composables/useScrollReveal';
import { Shield, Eye, Users, Lightbulb, Target, BookOpen } from 'lucide-vue-next';

useScrollReveal();

const values = [
    {
        icon: Shield,
        title: 'Integritas',
        description: 'Menjalankan setiap tugas dengan kejujuran dan standar etika yang tinggi.',
    },
    {
        icon: Eye,
        title: 'Transparansi',
        description: 'Terbuka dalam pengelolaan dana dan informasi kepada seluruh anggota.',
    },
    {
        icon: Users,
        title: 'Kolaborasi',
        description: 'Mengutamakan kerjasama tim untuk mencapai hasil yang maksimal.',
    },
    {
        icon: Lightbulb,
        title: 'Inovasi',
        description: 'Terus berkembang dan beradaptasi dengan teknologi dan cara baru.',
    },
];
</script>

<template>
    <Head title="Tentang Kami" />

    <PublicLayout>
        <div class="bg-card">
            <!-- Hero Header -->
            <div class="relative pt-16 sm:pt-18 min-h-[50vh] sm:min-h-[60vh] flex items-center justify-center overflow-hidden">
                <div class="absolute inset-0">
                    <img
                        src="https://images.unsplash.com/photo-1521737604893-d14cc237f11d?ixid=MXwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHw%3D&ixlib=rb-1.2.1&auto=format&fit=crop&w=2850&q=80"
                        alt=""
                        class="h-full w-full object-cover"
                    >
                    <div class="absolute inset-0 bg-gradient-to-b from-black/70 via-black/50 to-black/80"></div>
                    <div class="absolute inset-0 bg-gradient-to-r from-primary/20 via-transparent to-primary/10"></div>
                </div>
                <div class="relative z-10 mx-auto max-w-3xl text-center px-4 sm:px-6 py-20 sm:py-28">
                    <p class="text-primary-200 font-semibold tracking-[0.3em] uppercase text-xs sm:text-sm mb-4" data-reveal="fade">
                        Tentang Kami
                    </p>
                    <h1 class="text-4xl sm:text-5xl lg:text-7xl font-black tracking-tight text-white leading-tight" data-reveal data-reveal-delay="100">
                        Tentang Kami
                    </h1>
                    <p class="mt-6 text-lg sm:text-xl leading-relaxed text-white/70 max-w-xl mx-auto" data-reveal data-reveal-delay="200">
                        Mengenal lebih dekat sejarah, nilai-nilai, dan dedikasi kami dalam menjalankan organisasi ini.
                    </p>
                </div>
            </div>

            <!-- History Section -->
            <section class="py-20 sm:py-28 lg:py-32">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-3xl">
                        <div class="flex items-center gap-3 mb-6" data-reveal="left">
                            <div class="w-11 h-11 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                                <BookOpen class="w-5 h-5" />
                            </div>
                            <p class="text-primary font-semibold tracking-[0.2em] uppercase text-xs">Sejarah</p>
                        </div>
                        <h2 class="text-3xl sm:text-4xl font-black text-foreground tracking-tight mb-8" data-reveal data-reveal-delay="100">
                            Sejarah Kami
                        </h2>
                        <p class="text-lg text-muted-foreground leading-relaxed" data-reveal data-reveal-delay="200">
                            Organisasi ini didirikan dengan semangat kebersamaan dan keinginan untuk memberikan kontribusi nyata bagi masyarakat. Berawal dari komunitas kecil, kini kami telah tumbuh menjadi sebuah wadah profesional yang mengelola berbagai bidang kegiatan.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Divider -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="border-t border"></div>
            </div>

            <!-- Values Section -->
            <section class="py-20 sm:py-28 lg:py-32">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="text-center mb-16" data-reveal>
                        <p class="text-primary font-semibold tracking-[0.2em] uppercase text-xs mb-3">Prinsip</p>
                        <h2 class="text-3xl sm:text-4xl font-black text-foreground tracking-tight">Nilai-Nilai Kami</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">
                        <div
                            v-for="(value, idx) in values"
                            :key="value.title"
                            data-reveal
                            :data-reveal-delay="idx * 100"
                            class="group relative bg-card rounded-2xl p-8 border border hover:border-primary/30 shadow-sm hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 overflow-hidden"
                        >
                            <!-- Glass-like accent -->
                            <div class="absolute top-0 right-0 w-32 h-32 bg-primary/5 rounded-full -translate-y-1/2 translate-x-1/2 group-hover:scale-150 transition-transform duration-500"></div>

                            <div class="relative z-10">
                                <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary mb-5 group-hover:scale-110 group-hover:bg-primary/20 transition-all duration-300">
                                    <component :is="value.icon" class="w-6 h-6" />
                                </div>
                                <h3 class="text-xl font-bold text-foreground mb-2 group-hover:text-primary transition-colors duration-300">
                                    {{ value.title }}
                                </h3>
                                <p class="text-sm text-muted-foreground leading-relaxed">
                                    {{ value.description }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Divider -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="border-t border"></div>
            </div>

            <!-- Objectives Section -->
            <section class="py-20 sm:py-28 lg:py-32">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-3xl">
                        <div class="flex items-center gap-3 mb-6" data-reveal="left">
                            <div class="w-11 h-11 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                                <Target class="w-5 h-5" />
                            </div>
                            <p class="text-primary font-semibold tracking-[0.2em] uppercase text-xs">Visi</p>
                        </div>
                        <h2 class="text-3xl sm:text-4xl font-black text-foreground tracking-tight mb-8" data-reveal data-reveal-delay="100">
                            Tujuan Organisasi
                        </h2>
                        <p class="text-lg text-muted-foreground leading-relaxed" data-reveal data-reveal-delay="200">
                            Tujuan utama kami adalah menjadi pelopor dalam manajemen organisasi yang modern dan transparan, serta memberikan manfaat seluas-luasnya melalui program-program kerja yang inovatif dan terukur.
                        </p>
                    </div>
                </div>
            </section>
        </div>
    </PublicLayout>
</template>
