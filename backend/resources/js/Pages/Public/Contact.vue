<script setup>
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useScrollReveal } from '@/composables/useScrollReveal';
import { MapPin, Mail, Phone, Send } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';

useScrollReveal();
</script>

<template>
    <Head title="Kontak Kami" />

    <PublicLayout>
        <div class="bg-card">
            <!-- Hero Section -->
            <div class="relative bg-gradient-to-br from-primary via-primary/90 to-primary/70 pt-24 sm:pt-32 pb-16 sm:pb-20 px-4 sm:px-6 lg:px-8 text-center overflow-hidden">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_50%,rgba(255,255,255,0.1),transparent_70%)]"></div>
                <div class="relative z-10 max-w-3xl mx-auto" data-reveal="scale">
                    <h1 class="text-3xl font-bold tracking-tight text-primary-foreground sm:text-5xl">Hubungi Kami</h1>
                    <p class="mt-4 text-lg text-primary-foreground/80 max-w-2xl mx-auto">
                        Punya pertanyaan atau ingin bergabung? Jangan ragu untuk menghubungi kami melalui saluran di bawah ini.
                    </p>
                </div>
            </div>

            <!-- Content -->
            <div class="mx-auto max-w-7xl px-4 sm:px-6 py-16 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16">
                    <!-- Contact Info -->
                    <div class="space-y-6">
                        <!-- Address Card -->
                        <div
                            data-reveal="left"
                            class="group flex gap-5 p-6 bg-card border rounded-xl shadow-sm hover:shadow-md transition-all duration-300"
                        >
                            <div class="flex-shrink-0 w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center group-hover:bg-primary/20 transition-colors">
                                <MapPin class="w-6 h-6 text-primary" />
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-foreground">Alamat</h4>
                                <p class="text-muted-foreground mt-1">Jl. Contoh No. 123, Jakarta Selatan, Indonesia</p>
                            </div>
                        </div>

                        <!-- Email Card -->
                        <div
                            data-reveal="left"
                            data-reveal-delay="100"
                            class="group flex gap-5 p-6 bg-card border rounded-xl shadow-sm hover:shadow-md transition-all duration-300"
                        >
                            <div class="flex-shrink-0 w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center group-hover:bg-primary/20 transition-colors">
                                <Mail class="w-6 h-6 text-primary" />
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-foreground">Email</h4>
                                <p class="text-muted-foreground mt-1">info@organisasi.com</p>
                            </div>
                        </div>

                        <!-- Phone Card -->
                        <div
                            data-reveal="left"
                            data-reveal-delay="200"
                            class="group flex gap-5 p-6 bg-card border rounded-xl shadow-sm hover:shadow-md transition-all duration-300"
                        >
                            <div class="flex-shrink-0 w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center group-hover:bg-primary/20 transition-colors">
                                <Phone class="w-6 h-6 text-primary" />
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-foreground">Telepon</h4>
                                <p class="text-muted-foreground mt-1">(021) 1234-5678</p>
                            </div>
                        </div>

                        <!-- Social Media -->
                        <div data-reveal="left" data-reveal-delay="300" class="pt-4">
                            <h4 class="text-sm font-black text-muted-foreground uppercase tracking-widest mb-4">Ikuti Kami</h4>
                            <div class="flex gap-3">
                                <a href="#" class="w-11 h-11 bg-muted rounded-full flex items-center justify-center text-muted-foreground hover:bg-primary hover:text-primary-foreground hover:scale-110 transition-all duration-300">
                                    <span class="sr-only">Facebook</span>
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                </a>
                                <a href="#" class="w-11 h-11 bg-muted rounded-full flex items-center justify-center text-muted-foreground hover:bg-primary hover:text-primary-foreground hover:scale-110 transition-all duration-300">
                                    <span class="sr-only">Instagram</span>
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.791-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.209-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Form -->
                    <div data-reveal="right" class="bg-muted/50 p-8 rounded-2xl border">
                        <h3 class="text-xl font-semibold text-foreground mb-6">Kirim Pesan</h3>
                        <form action="#" method="POST" class="space-y-5">
                            <div class="space-y-2">
                                <Label for="full-name">Nama Lengkap</Label>
                                <Input type="text" name="full-name" id="full-name" placeholder="Masukkan nama lengkap" />
                            </div>

                            <div class="space-y-2">
                                <Label for="email">Email</Label>
                                <Input type="email" name="email" id="email" placeholder="nama@email.com" />
                            </div>

                            <div class="space-y-2">
                                <Label for="subject">Subjek</Label>
                                <Input type="text" name="subject" id="subject" placeholder="Subjek pesan" />
                            </div>

                            <div class="space-y-2">
                                <Label for="message">Pesan</Label>
                                <Textarea id="message" name="message" rows="4" placeholder="Tulis pesan Anda..." />
                            </div>

                            <Button type="submit" class="w-full" size="lg">
                                <Send class="w-4 h-4 mr-2" />
                                Kirim Pesan
                            </Button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
