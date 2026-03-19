<script setup>
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useScrollReveal } from '@/composables/useScrollReveal';
import { Eye, ClipboardList, Clock } from 'lucide-vue-next';

useScrollReveal();

defineProps({
    visionMission: Object,
    history: Array,
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};
</script>

<template>
    <Head title="Visi & Misi" />

    <PublicLayout>
        <div class="bg-card">
            <!-- Hero Header -->
            <div class="relative bg-gradient-to-br from-primary to-primary/80 pt-24 pb-10 px-4 sm:px-6 lg:px-8 text-center overflow-hidden">
                <div class="relative z-10 max-w-3xl mx-auto" data-reveal="scale">
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-primary-foreground">Visi & Misi</h1>
                    <p class="mt-3 text-base text-primary-foreground/80 max-w-2xl mx-auto">
                        Arah dan landasan perjuangan kami dalam mewujudkan cita-cita organisasi.
                    </p>
                </div>
            </div>

            <div class="mx-auto max-w-7xl px-4 sm:px-6 py-12 sm:py-16 lg:px-8">
                <div v-if="visionMission" class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                    <!-- Vision Card -->
                    <div
                        data-reveal="left"
                        class="relative bg-card border-2 border-primary/20 rounded-2xl p-6 shadow-sm hover:shadow-lg hover:border-primary/40 transition-all duration-300"
                    >
                        <div class="absolute inset-0 bg-gradient-to-br from-primary/5 to-transparent rounded-2xl"></div>
                        <div class="relative">
                            <div class="flex items-center gap-3 mb-5">
                                <div class="p-2.5 bg-primary/10 rounded-xl">
                                    <Eye class="w-5 h-5 text-primary" />
                                </div>
                                <h3 class="text-xl font-bold text-foreground">Visi Kami</h3>
                            </div>
                            <blockquote class="text-lg italic text-foreground/90 leading-relaxed border-l-4 border-primary/30 pl-4">
                                "{{ visionMission.vision }}"
                            </blockquote>
                        </div>
                    </div>

                    <!-- Mission Card -->
                    <div
                        data-reveal="right"
                        class="relative bg-card border-2 border-emerald-500/20 rounded-2xl p-6 shadow-sm hover:shadow-lg hover:border-emerald-500/40 transition-all duration-300"
                    >
                        <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-transparent rounded-2xl"></div>
                        <div class="relative">
                            <div class="flex items-center gap-3 mb-5">
                                <div class="p-2.5 bg-emerald-500/10 rounded-xl">
                                    <ClipboardList class="w-5 h-5 text-emerald-600" />
                                </div>
                                <h3 class="text-xl font-bold text-foreground">Misi Kami</h3>
                            </div>
                            <ul class="space-y-3">
                                <li v-for="(mission, index) in visionMission.missions" :key="index" class="flex gap-3 items-start">
                                    <span class="flex-shrink-0 w-7 h-7 rounded-full bg-emerald-500/10 text-emerald-600 flex items-center justify-center font-bold text-xs ring-2 ring-emerald-500/20">
                                        {{ index + 1 }}
                                    </span>
                                    <p class="text-base text-foreground pt-0.5">{{ mission }}</p>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-16 text-muted-foreground">
                    Data visi dan misi belum tersedia.
                </div>

                <!-- History Timeline -->
                <div v-if="history && history.length > 1" class="mt-16">
                    <div class="text-center mb-10" data-reveal>
                        <div class="inline-flex items-center gap-2 mb-2">
                            <Clock class="w-5 h-5 text-primary" />
                            <span class="text-sm font-semibold text-primary">Timeline</span>
                        </div>
                        <h3 class="text-2xl sm:text-3xl font-bold text-foreground">Sejarah Perubahan</h3>
                    </div>

                    <div class="relative max-w-4xl mx-auto">
                        <!-- Connecting Line -->
                        <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gradient-to-b from-primary/40 via-border to-border"></div>

                        <div class="space-y-6">
                            <div
                                v-for="(item, index) in history"
                                :key="item.id"
                                data-reveal="left"
                                :data-reveal-delay="index * 100"
                                class="relative pl-12"
                            >
                                <!-- Dot -->
                                <div
                                    class="absolute left-2 top-5 w-5 h-5 rounded-full border-2 border-background"
                                    :class="item.status === 'active'
                                        ? 'bg-primary ring-4 ring-primary/20'
                                        : 'bg-muted-foreground/40'"
                                ></div>

                                <!-- Card -->
                                <div
                                    class="p-4 rounded-xl border transition-all duration-300"
                                    :class="item.status === 'active'
                                        ? 'bg-primary/5 border-primary/20 shadow-sm'
                                        : 'bg-card border-border hover:shadow-sm'"
                                >
                                    <p class="text-sm font-semibold" :class="item.status === 'active' ? 'text-primary' : 'text-muted-foreground'">
                                        {{ formatDate(item.created_at) }}
                                        <span v-if="item.status === 'active'" class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-primary">
                                            Aktif
                                        </span>
                                    </p>
                                    <h4 class="text-base font-bold text-foreground mt-1 line-clamp-1">{{ item.vision }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
