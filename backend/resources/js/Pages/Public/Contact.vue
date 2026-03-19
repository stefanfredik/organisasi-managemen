<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useScrollReveal } from '@/composables/useScrollReveal';
import { MapPin, Mail, Phone, Send } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';

useScrollReveal();

const page = usePage();
const app = page.props.appSettings;
const social = app.social || {};
</script>

<template>
    <Head title="Kontak Kami" />

    <PublicLayout>
        <div class="bg-card">
            <!-- Hero Section -->
            <div class="relative bg-gradient-to-br from-primary to-primary/80 pt-24 pb-10 px-4 sm:px-6 lg:px-8 text-center overflow-hidden">
                <div class="relative z-10 max-w-3xl mx-auto" data-reveal="scale">
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-primary-foreground">Hubungi Kami</h1>
                    <p class="mt-3 text-base text-primary-foreground/80 max-w-2xl mx-auto">
                        Punya pertanyaan atau ingin bergabung? Jangan ragu untuk menghubungi kami melalui saluran di bawah ini.
                    </p>
                </div>
            </div>

            <!-- Google Maps Embed -->
            <div v-if="app.portal?.google_maps_embed" class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 -mt-8 mb-6 relative z-10">
                <div class="rounded-2xl overflow-hidden border shadow-lg" v-html="app.portal.google_maps_embed"></div>
            </div>

            <!-- Content -->
            <div class="mx-auto max-w-7xl px-4 sm:px-6 py-12 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-14">
                    <!-- Contact Info -->
                    <div class="space-y-4">
                        <!-- Address Card -->
                        <div
                            data-reveal="left"
                            class="group flex gap-4 p-5 bg-card border rounded-xl shadow-sm hover:shadow-md transition-all duration-300"
                        >
                            <div class="flex-shrink-0 w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center group-hover:bg-primary/20 transition-colors">
                                <MapPin class="w-5 h-5 text-primary" />
                            </div>
                            <div>
                                <h4 class="text-base font-semibold text-foreground">Alamat</h4>
                                <p class="text-sm text-muted-foreground mt-1">{{ [app.address, app.city, app.province, app.postal_code].filter(Boolean).join(', ') || '-' }}</p>
                            </div>
                        </div>

                        <!-- Email Card -->
                        <div
                            data-reveal="left"
                            data-reveal-delay="100"
                            class="group flex gap-4 p-5 bg-card border rounded-xl shadow-sm hover:shadow-md transition-all duration-300"
                        >
                            <div class="flex-shrink-0 w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center group-hover:bg-primary/20 transition-colors">
                                <Mail class="w-5 h-5 text-primary" />
                            </div>
                            <div>
                                <h4 class="text-base font-semibold text-foreground">Email</h4>
                                <p class="text-sm text-muted-foreground mt-1">{{ app.portal?.contact_email || app.email || '-' }}</p>
                            </div>
                        </div>

                        <!-- Phone Card -->
                        <div
                            data-reveal="left"
                            data-reveal-delay="200"
                            class="group flex gap-4 p-5 bg-card border rounded-xl shadow-sm hover:shadow-md transition-all duration-300"
                        >
                            <div class="flex-shrink-0 w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center group-hover:bg-primary/20 transition-colors">
                                <Phone class="w-5 h-5 text-primary" />
                            </div>
                            <div>
                                <h4 class="text-base font-semibold text-foreground">Telepon</h4>
                                <p class="text-sm text-muted-foreground mt-1">{{ app.phone || '-' }}</p>
                            </div>
                        </div>

                        <!-- Social Media -->
                        <div v-if="social.facebook || social.instagram || social.twitter || social.youtube || social.tiktok || social.whatsapp || social.telegram" data-reveal="left" data-reveal-delay="300" class="pt-3">
                            <h4 class="text-sm font-semibold text-muted-foreground mb-3">Ikuti Kami</h4>
                            <div class="flex gap-3 flex-wrap">
                                <a v-if="social.facebook" :href="social.facebook" target="_blank" class="w-10 h-10 bg-muted rounded-full flex items-center justify-center text-muted-foreground hover:bg-[#1877F2] hover:text-white hover:scale-110 transition-all duration-300">
                                    <span class="sr-only">Facebook</span>
                                    <svg class="w-4.5 h-4.5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                </a>
                                <a v-if="social.instagram" :href="social.instagram" target="_blank" class="w-10 h-10 bg-muted rounded-full flex items-center justify-center text-muted-foreground hover:bg-[#E4405F] hover:text-white hover:scale-110 transition-all duration-300">
                                    <span class="sr-only">Instagram</span>
                                    <svg class="w-4.5 h-4.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.791-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.209-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                </a>
                                <a v-if="social.twitter" :href="social.twitter" target="_blank" class="w-10 h-10 bg-muted rounded-full flex items-center justify-center text-muted-foreground hover:bg-black hover:text-white hover:scale-110 transition-all duration-300">
                                    <span class="sr-only">Twitter / X</span>
                                    <svg class="w-4.5 h-4.5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                </a>
                                <a v-if="social.youtube" :href="social.youtube" target="_blank" class="w-10 h-10 bg-muted rounded-full flex items-center justify-center text-muted-foreground hover:bg-[#FF0000] hover:text-white hover:scale-110 transition-all duration-300">
                                    <span class="sr-only">YouTube</span>
                                    <svg class="w-4.5 h-4.5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                </a>
                                <a v-if="social.whatsapp" :href="'https://wa.me/' + social.whatsapp" target="_blank" class="w-10 h-10 bg-muted rounded-full flex items-center justify-center text-muted-foreground hover:bg-[#25D366] hover:text-white hover:scale-110 transition-all duration-300">
                                    <span class="sr-only">WhatsApp</span>
                                    <svg class="w-4.5 h-4.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                </a>
                                <a v-if="social.telegram" :href="social.telegram" target="_blank" class="w-10 h-10 bg-muted rounded-full flex items-center justify-center text-muted-foreground hover:bg-[#0088cc] hover:text-white hover:scale-110 transition-all duration-300">
                                    <span class="sr-only">Telegram</span>
                                    <svg class="w-4.5 h-4.5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.479.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Form -->
                    <div data-reveal="right" class="bg-muted/50 p-6 rounded-2xl border">
                        <h3 class="text-lg font-semibold text-foreground mb-5">Kirim Pesan</h3>
                        <form action="#" method="POST" class="space-y-4">
                            <div class="space-y-1.5">
                                <Label for="full-name">Nama Lengkap</Label>
                                <Input type="text" name="full-name" id="full-name" placeholder="Masukkan nama lengkap" />
                            </div>

                            <div class="space-y-1.5">
                                <Label for="email">Email</Label>
                                <Input type="email" name="email" id="email" placeholder="nama@email.com" />
                            </div>

                            <div class="space-y-1.5">
                                <Label for="subject">Subjek</Label>
                                <Input type="text" name="subject" id="subject" placeholder="Subjek pesan" />
                            </div>

                            <div class="space-y-1.5">
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
