<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useScrollReveal } from '@/composables/useScrollReveal';
import { Shield, Eye, Users, Lightbulb, Target, BookOpen } from 'lucide-vue-next';

useScrollReveal();

const page = usePage();
const app = page.props.appSettings;

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
            <div class="relative bg-gradient-to-br from-primary to-primary/80 pt-24 pb-10 px-4 sm:px-6 lg:px-8 text-center overflow-hidden">
                <div class="relative z-10 max-w-3xl mx-auto" data-reveal="scale">
                    <p class="text-primary-foreground/70 font-semibold text-xs sm:text-sm mb-3" data-reveal="fade">
                        Tentang Kami
                    </p>
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-primary-foreground leading-tight" data-reveal data-reveal-delay="100">
                        {{ app.name }}
                    </h1>
                    <p v-if="app.motto" class="mt-3 text-base sm:text-lg leading-relaxed text-primary-foreground/80 italic font-medium max-w-xl mx-auto" data-reveal data-reveal-delay="150">
                        "{{ app.motto }}"
                    </p>
                    <p class="mt-3 text-base sm:text-lg leading-relaxed text-primary-foreground/70 max-w-xl mx-auto" data-reveal data-reveal-delay="200">
                        {{ app.description || 'Mengenal lebih dekat sejarah, nilai-nilai, dan dedikasi kami dalam menjalankan organisasi ini.' }}
                    </p>
                </div>
            </div>

            <!-- History Section -->
            <section class="py-12 sm:py-16">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-3xl">
                        <div class="flex items-center gap-3 mb-5" data-reveal="left">
                            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                                <BookOpen class="w-5 h-5" />
                            </div>
                            <p class="text-primary font-semibold text-sm">Sejarah</p>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-bold text-foreground tracking-tight mb-6" data-reveal data-reveal-delay="100">
                            Sejarah Kami
                        </h2>
                        <p class="text-base text-muted-foreground leading-relaxed" data-reveal data-reveal-delay="200">
                            {{ app.portal?.about_text || 'Organisasi ini didirikan dengan semangat kebersamaan dan keinginan untuk memberikan kontribusi nyata bagi masyarakat. Berawal dari komunitas kecil, kini kami telah tumbuh menjadi sebuah wadah profesional yang mengelola berbagai bidang kegiatan.' }}
                        </p>
                        <div v-if="app.founded_date" class="mt-5 inline-flex items-center gap-2 px-4 py-2 bg-primary/5 rounded-lg border border-primary/10" data-reveal data-reveal-delay="300">
                            <span class="text-sm text-muted-foreground">Berdiri sejak</span>
                            <span class="text-sm font-bold text-primary">{{ app.founded_date }}</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Divider -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="border-t border"></div>
            </div>

            <!-- Values Section -->
            <section class="py-12 sm:py-16">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="text-center mb-10" data-reveal>
                        <p class="text-primary font-semibold text-sm mb-2">Prinsip</p>
                        <h2 class="text-2xl sm:text-3xl font-bold text-foreground tracking-tight">Nilai-Nilai Kami</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 max-w-4xl mx-auto">
                        <div
                            v-for="(value, idx) in values"
                            :key="value.title"
                            data-reveal
                            :data-reveal-delay="idx * 100"
                            class="group relative bg-card rounded-2xl p-6 border border hover:border-primary/30 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 overflow-hidden"
                        >
                            <div class="absolute top-0 right-0 w-28 h-28 bg-primary/5 rounded-full -translate-y-1/2 translate-x-1/2 group-hover:scale-150 transition-transform duration-500"></div>

                            <div class="relative z-10">
                                <div class="w-11 h-11 rounded-xl bg-primary/10 flex items-center justify-center text-primary mb-4 group-hover:scale-110 group-hover:bg-primary/20 transition-all duration-300">
                                    <component :is="value.icon" class="w-5 h-5" />
                                </div>
                                <h3 class="text-lg font-bold text-foreground mb-1.5 group-hover:text-primary transition-colors duration-300">
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
            <section class="py-12 sm:py-16">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-3xl">
                        <div class="flex items-center gap-3 mb-5" data-reveal="left">
                            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                                <Target class="w-5 h-5" />
                            </div>
                            <p class="text-primary font-semibold text-sm">Visi</p>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-bold text-foreground tracking-tight mb-6" data-reveal data-reveal-delay="100">
                            Tujuan Organisasi
                        </h2>
                        <p class="text-base text-muted-foreground leading-relaxed" data-reveal data-reveal-delay="200">
                            Tujuan utama kami adalah menjadi pelopor dalam manajemen organisasi yang modern dan transparan, serta memberikan manfaat seluas-luasnya melalui program-program kerja yang inovatif dan terukur.
                        </p>
                    </div>
                </div>
            </section>
        </div>
    </PublicLayout>
</template>
