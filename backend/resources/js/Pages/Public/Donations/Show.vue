<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useScrollReveal } from '@/composables/useScrollReveal';
import { ChevronRight, User, CreditCard, Wallet, Share2, Heart, Building2, Copy } from 'lucide-vue-next';

useScrollReveal();

const props = defineProps({
    donation: Object,
});

const page = usePage();

const formatCurrency = (value) => {
    const currency = page.props.appSettings?.financial?.currency || 'Rp';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value).replace('Rp', currency);
};

const getProgress = () => {
    return Math.min(100, (props.donation.collected_amount / props.donation.target_amount) * 100);
};

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
    <Head :title="donation.program_name" />

    <PublicLayout>
        <div class="bg-muted min-h-screen pb-20">
            <!-- Breadcrumb Header -->
            <div class="bg-card border-b pt-24">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
                    <nav class="flex" aria-label="Breadcrumb">
                        <ol class="flex items-center gap-2 text-sm text-muted-foreground">
                            <li>
                                <Link :href="route('public.donations.index')" class="hover:text-primary transition-colors font-medium">
                                    Donasi
                                </Link>
                            </li>
                            <li>
                                <ChevronRight class="h-4 w-4" />
                            </li>
                            <li class="text-foreground font-semibold line-clamp-1">{{ donation.program_name }}</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 mt-10 mb-20">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-12">
                    <!-- Left: Program Info -->
                    <div class="lg:col-span-2 space-y-10">
                        <div class="bg-card rounded-3xl shadow-sm border overflow-hidden" data-reveal>
                            <div class="relative aspect-[21/9] overflow-hidden">
                                <img
                                    :src="donation.banner_url || 'https://images.unsplash.com/photo-1532629345422-7515f3d16bb8?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80'"
                                    alt=""
                                    class="w-full h-full object-cover"
                                >
                                <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
                            </div>
                            <div class="p-8 lg:p-12">
                                <h1 class="text-3xl lg:text-4xl font-extrabold text-foreground mb-6 leading-tight tracking-tight">
                                    {{ donation.program_name }}
                                </h1>
                                <div class="prose prose-slate max-w-none text-muted-foreground text-base leading-relaxed" v-html="donation.description"></div>
                            </div>
                        </div>

                        <!-- Recent Transactions -->
                        <div class="bg-card rounded-3xl shadow-sm border p-8 lg:p-10" data-reveal data-reveal-delay="100">
                            <div class="flex items-center gap-3 mb-8">
                                <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                                    <Heart class="w-5 h-5 text-primary" />
                                </div>
                                <h2 class="text-xl font-bold text-foreground">Donatur Terbaru</h2>
                            </div>

                            <div v-if="donation.transactions && donation.transactions.length > 0" class="space-y-4">
                                <div
                                    v-for="(t, index) in donation.transactions"
                                    :key="t.id"
                                    data-reveal="left"
                                    :data-reveal-delay="index * 80"
                                    class="flex items-center gap-4 p-4 rounded-2xl bg-muted/50 border hover:bg-muted transition-colors"
                                >
                                    <div class="w-11 h-11 rounded-full bg-primary/10 flex items-center justify-center text-primary shrink-0">
                                        <User class="w-5 h-5" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-foreground truncate">{{ t.donor_name || 'Hamba Allah' }}</p>
                                        <p class="text-xs text-muted-foreground mt-0.5">{{ formatDate(t.transaction_date) }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-primary">{{ formatCurrency(t.amount) }}</p>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="text-center py-12 text-muted-foreground">
                                <div class="inline-flex items-center justify-center w-16 h-16 bg-muted rounded-2xl mb-4">
                                    <Heart class="w-8 h-8 text-muted-foreground/50" />
                                </div>
                                <p>Belum ada donasi masuk untuk program ini.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Donation Action Sidebar -->
                    <div class="space-y-6">
                        <div class="bg-card rounded-3xl shadow-lg border p-5 sm:p-7 sticky top-20" data-reveal="right">
                            <!-- Progress Section -->
                            <div class="mb-8">
                                <div class="flex justify-between items-end mb-4">
                                    <div>
                                        <p class="text-xs text-muted-foreground font-semibold uppercase tracking-wider mb-1">Terkumpul</p>
                                        <span class="text-2xl sm:text-3xl font-extrabold text-primary">{{ formatCurrency(donation.collected_amount) }}</span>
                                    </div>
                                </div>

                                <!-- Animated progress bar -->
                                <div class="w-full bg-muted rounded-full h-3.5 overflow-hidden mb-3">
                                    <div
                                        class="bg-gradient-to-r from-primary to-primary/75 h-full rounded-full transition-all duration-1000 ease-out relative"
                                        :style="{ width: `${getProgress()}%` }"
                                    >
                                        <div class="absolute inset-0 bg-white/20 animate-pulse rounded-full"></div>
                                    </div>
                                </div>

                                <div class="flex justify-between text-sm">
                                    <span class="text-muted-foreground">
                                        Target: <span class="font-bold text-foreground">{{ formatCurrency(donation.target_amount) }}</span>
                                    </span>
                                    <span class="font-bold text-primary">{{ getProgress().toFixed(1) }}%</span>
                                </div>
                            </div>

                            <!-- Payment Methods -->
                            <div class="space-y-3 pt-6 border-t">
                                <h3 class="text-base font-bold text-foreground mb-4">Pilih Pembayaran</h3>
                                <div class="grid grid-cols-1 gap-3">
                                    <button class="flex items-center justify-between p-4 border rounded-2xl hover:border-primary hover:bg-primary/5 transition-all duration-200 group">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-muted flex items-center justify-center group-hover:bg-primary/10 transition-colors">
                                                <CreditCard class="w-4 h-4 text-muted-foreground group-hover:text-primary transition-colors" />
                                            </div>
                                            <span class="font-semibold text-foreground group-hover:text-primary transition-colors">Transfer Bank</span>
                                        </div>
                                        <ChevronRight class="w-4 h-4 text-muted-foreground group-hover:text-primary group-hover:translate-x-0.5 transition-all" />
                                    </button>
                                    <button class="flex items-center justify-between p-4 border rounded-2xl hover:border-primary hover:bg-primary/5 transition-all duration-200 group">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-muted flex items-center justify-center group-hover:bg-primary/10 transition-colors">
                                                <Wallet class="w-4 h-4 text-muted-foreground group-hover:text-primary transition-colors" />
                                            </div>
                                            <span class="font-semibold text-foreground group-hover:text-primary transition-colors">E-Wallet (OVO/Dana/Gopay)</span>
                                        </div>
                                        <ChevronRight class="w-4 h-4 text-muted-foreground group-hover:text-primary group-hover:translate-x-0.5 transition-all" />
                                    </button>
                                </div>

                                <button class="w-full mt-4 flex items-center justify-center gap-2 py-4 px-4 bg-primary text-primary-foreground font-bold rounded-2xl hover:bg-primary/90 hover:shadow-lg transition-all duration-200 shadow-md active:scale-[0.98]">
                                    <Heart class="w-5 h-5" />
                                    Donasi Sekarang
                                </button>

                                <p class="text-xs text-muted-foreground text-center mt-3 leading-relaxed">
                                    Dengan berdonasi, Anda menyetujui syarat dan ketentuan yang berlaku dalam organisasi kami.
                                </p>
                            </div>

                            <!-- Bank Info Card -->
                            <div v-if="$page.props.appSettings.financial.bank_account" class="mt-8 p-5 bg-muted/50 rounded-2xl border">
                                <div class="flex items-center gap-2 mb-4 justify-center">
                                    <Building2 class="w-4 h-4 text-muted-foreground" />
                                    <h4 class="text-xs font-bold uppercase tracking-widest text-muted-foreground">Informasi Rekening</h4>
                                </div>
                                <div class="space-y-2.5">
                                    <div class="flex justify-between items-center bg-card p-3 rounded-xl border">
                                        <span class="text-xs text-muted-foreground">Bank</span>
                                        <span class="text-sm font-bold text-foreground">{{ $page.props.appSettings.financial.bank_name }}</span>
                                    </div>
                                    <div class="flex justify-between items-center bg-card p-3 rounded-xl border">
                                        <span class="text-xs text-muted-foreground">Nomor Rekening</span>
                                        <span class="text-sm font-bold text-primary">{{ $page.props.appSettings.financial.bank_account }}</span>
                                    </div>
                                    <div class="flex justify-between items-center bg-card p-3 rounded-xl border">
                                        <span class="text-xs text-muted-foreground">Atas Nama</span>
                                        <span class="text-sm font-bold text-foreground">{{ $page.props.appSettings.financial.bank_owner }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Share Card -->
                        <div class="bg-primary rounded-3xl p-7 text-primary-foreground shadow-lg" data-reveal="right" data-reveal-delay="150">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center">
                                    <Share2 class="w-5 h-5" />
                                </div>
                                <h3 class="text-lg font-bold">Bantu Sebarkan!</h3>
                            </div>
                            <p class="text-primary-foreground/80 text-sm mb-5 leading-relaxed">
                                Ajak teman dan keluarga untuk ikut berpartisipasi dalam kebaikan ini.
                            </p>
                            <div class="flex gap-3">
                                <button class="flex-1 bg-white/15 hover:bg-white/25 p-3.5 rounded-2xl flex items-center justify-center gap-2 transition-all duration-200 text-sm font-semibold">
                                    <Copy class="w-4 h-4" />
                                    Salin Link
                                </button>
                                <button class="flex-1 bg-white/15 hover:bg-white/25 p-3.5 rounded-2xl flex items-center justify-center gap-2 transition-all duration-200 text-sm font-semibold">
                                    <Share2 class="w-4 h-4" />
                                    Bagikan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
