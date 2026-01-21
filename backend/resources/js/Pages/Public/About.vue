<script setup>
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
</script>

<template>
    <Head title="Tentang Kami" />

    <PublicLayout>
        <div class="bg-white">
            <!-- Header -->
            <div class="relative bg-gray-800 py-24 px-6 sm:py-32 lg:px-8">
                <div class="absolute inset-0 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1521737604893-d14cc237f11d?ixid=MXwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHw%3D&ixlib=rb-1.2.1&auto=format&fit=crop&w=2850&q=80" alt="" class="h-full w-full object-cover opacity-30">
                </div>
                <div class="relative mx-auto max-w-2xl text-center">
                    <h2 class="text-4xl font-bold tracking-tight text-white sm:text-6xl">Tentang Kami</h2>
                    <p class="mt-6 text-lg leading-8 text-gray-300">
                        Mengenal lebih dekat sejarah, nilai-nilai, dan dedikasi kami dalam menjalankan organisasi ini.
                    </p>
                </div>
            </div>

            <!-- Content -->
            <div class="mx-auto max-w-7xl px-6 py-16 lg:px-8">
                <div class="mx-auto max-w-3xl">
                    <div class="prose prose-indigo prose-lg text-gray-500">
                        <h2 class="text-3xl font-bold text-gray-900 mb-6">Sejarah Kami</h2>
                        <p>
                            Organisasi ini didirikan dengan semangat kebersamaan dan keinginan untuk memberikan kontribusi nyata bagi masyarakat. Berawal dari komunitas kecil, kini kami telah tumbuh menjadi sebuah wadah profesional yang mengelola berbagai bidang kegiatan.
                        </p>
                        
                        <h2 class="text-3xl font-bold text-gray-900 mt-12 mb-6">Nilai-Nilai Kami</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 not-prose">
                            <div class="bg-gray-50 p-6 rounded-lg border border-gray-100">
                                <h3 class="text-lg font-bold text-indigo-600 mb-2">Integritas</h3>
                                <p class="text-sm text-gray-600">Menjalankan setiap tugas dengan kejujuran dan standar etika yang tinggi.</p>
                            </div>
                            <div class="bg-gray-50 p-6 rounded-lg border border-gray-100">
                                <h3 class="text-lg font-bold text-indigo-600 mb-2">Transparansi</h3>
                                <p class="text-sm text-gray-600">Terbuka dalam pengelolaan dana dan informasi kepada seluruh anggota.</p>
                            </div>
                            <div class="bg-gray-50 p-6 rounded-lg border border-gray-100">
                                <h3 class="text-lg font-bold text-indigo-600 mb-2">Kolaborasi</h3>
                                <p class="text-sm text-gray-600">Mengutamakan kerjasama tim untuk mencapai hasil yang maksimal.</p>
                            </div>
                            <div class="bg-gray-50 p-6 rounded-lg border border-gray-100">
                                <h3 class="text-lg font-bold text-indigo-600 mb-2">Inovasi</h3>
                                <p class="text-sm text-gray-600">Terus berkembang dan beradaptasi dengan teknologi dan cara baru.</p>
                            </div>
                        </div>

                        <h2 class="text-3xl font-bold text-gray-900 mt-12 mb-6">Tujuan Organisasi</h2>
                        <p>
                            Tujuan utama kami adalah menjadi pelopor dalam manajemen organisasi yang modern dan transparan, serta memberikan manfaat seluas-luasnya melalui program-program kerja yang inovatif dan terukur.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
