<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

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
        <div class="bg-gray-50 min-h-screen pb-20">
            <!-- Program Header -->
            <div class="bg-white border-b border-gray-100">
                <div class="max-w-7xl mx-auto px-6 py-8">
                    <nav class="flex mb-4" aria-label="Breadcrumb">
                        <ol class="flex items-center space-x-2 text-sm text-gray-400">
                            <li><Link :href="route('public.donations.index')" class="hover:text-indigo-600">Donasi</Link></li>
                            <li><svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg></li>
                            <li class="text-gray-900 font-medium line-clamp-1">{{ donation.program_name }}</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-6 mt-12 mb-20">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-16">
                    <!-- Left: Program Info -->
                    <div class="lg:col-span-2 space-y-12">
                        <div class="bg-white rounded-[3rem] shadow-sm border border-slate-100 overflow-hidden">
                            <div class="relative aspect-[21/9]">
                                <img :src="donation.banner_url || 'https://images.unsplash.com/photo-1532629345422-7515f3d16bb8?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80'" alt="" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/40 to-transparent"></div>
                            </div>
                            <div class="p-10 lg:p-16">
                                <h1 class="text-4xl lg:text-5xl font-black text-slate-900 mb-8 leading-tight tracking-tight uppercase">
                                    {{ donation.program_name }}
                                </h1>
                                <div class="prose prose-slate max-w-none text-slate-600 text-lg leading-relaxed" v-html="donation.description"></div>
                            </div>
                        </div>

                        <!-- Recent Transactions -->
                        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 lg:p-12">
                            <h2 class="text-2xl font-bold text-gray-900 mb-8">Donatur Terbaru</h2>
                            <div v-if="donation.transactions && donation.transactions.length > 0" class="space-y-6">
                                <div v-for="t in donation.transactions" :key="t.id" class="flex items-center gap-4 p-4 rounded-2xl bg-gray-50 border border-gray-100">
                                    <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 shrink-0">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-bold text-gray-900 truncate">{{ t.donor_name || 'Hamba Allah' }}</p>
                                        <p class="text-xs text-gray-400">{{ formatDate(t.transaction_date) }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-black text-indigo-600">{{ formatCurrency(t.amount) }}</p>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-10 text-gray-400 italic">
                                Belum ada donasi masuk untuk program ini.
                            </div>
                        </div>
                    </div>

                    <!-- Right: Donation Action -->
                    <div class="space-y-8">
                        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8 sticky top-8">
                            <div class="mb-8">
                                <div class="flex justify-between items-end mb-3">
                                    <span class="text-3xl font-black text-indigo-600">{{ formatCurrency(donation.collected_amount) }}</span>
                                    <span class="text-sm font-bold text-gray-400 uppercase tracking-widest pb-1">Terkumpul</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-4 overflow-hidden mb-4">
                                    <div 
                                        class="bg-indigo-600 h-full rounded-full"
                                        :style="{ width: `${getProgress()}%` }"
                                    ></div>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Target: <span class="font-bold text-gray-900">{{ formatCurrency(donation.target_amount) }}</span></span>
                                    <span class="font-bold text-indigo-600">{{ getProgress().toFixed(1) }}%</span>
                                </div>
                            </div>

                            <div class="space-y-4 pt-6 border-t border-gray-100">
                                <h3 class="text-lg font-bold text-gray-900 mb-4">Pilih Pembayaran</h3>
                                <div class="grid grid-cols-1 gap-3">
                                    <button class="flex items-center justify-between p-4 border border-gray-200 rounded-2xl hover:border-indigo-600 hover:bg-indigo-50 transition-all group">
                                        <span class="font-bold text-gray-700 group-hover:text-indigo-600">Transfer Bank</span>
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                    </button>
                                    <button class="flex items-center justify-between p-4 border border-gray-200 rounded-2xl hover:border-indigo-600 hover:bg-indigo-50 transition-all group">
                                        <span class="font-bold text-gray-700 group-hover:text-indigo-600">E-Wallet (OVO/Dana/Gopay)</span>
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                    </button>
                                </div>

                                <button class="w-full mt-6 flex justify-center py-5 px-4 bg-indigo-600 text-white font-black rounded-2xl hover:bg-indigo-700 transition-all shadow-lg active:scale-95 uppercase tracking-widest">
                                    Donasi Sekarang
                                </button>
                                
                                <p class="text-[10px] text-gray-400 text-center mt-4">
                                    Dengan berdonasi, Anda menyetujui syarat dan ketentuan yang berlaku dalam organisasi kami.
                                </p>
                            </div>

                            <!-- Bank Info Card (Dynamic) -->
                            <div v-if="$page.props.appSettings.financial.bank_account" class="mt-8 p-6 bg-slate-50 rounded-2xl border border-slate-100">
                                <h4 class="text-xs font-black uppercase tracking-widest text-slate-400 mb-4 text-center">Informasi Rekening</h4>
                                <div class="space-y-3">
                                    <div class="flex justify-between items-center bg-white p-3 rounded-xl border border-slate-50">
                                        <span class="text-xs text-slate-500">Bank</span>
                                        <span class="text-sm font-bold text-slate-900">{{ $page.props.appSettings.financial.bank_name }}</span>
                                    </div>
                                    <div class="flex justify-between items-center bg-white p-3 rounded-xl border border-slate-50">
                                        <span class="text-xs text-slate-500">Nomor Rekening</span>
                                        <span class="text-sm font-bold text-indigo-600">{{ $page.props.appSettings.financial.bank_account }}</span>
                                    </div>
                                    <div class="flex justify-between items-center bg-white p-3 rounded-xl border border-slate-50">
                                        <span class="text-xs text-slate-500">Atas Nama</span>
                                        <span class="text-sm font-bold text-slate-900">{{ $page.props.appSettings.financial.bank_owner }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Share Card -->
                        <div class="bg-indigo-600 rounded-3xl p-8 text-white shadow-lg">
                            <h3 class="text-xl font-bold mb-2">Bantu Sebarkan!</h3>
                            <p class="text-indigo-100 text-sm mb-6">Ajak teman dan keluarga untuk ikut berpartisipasi dalam kebaikan ini.</p>
                            <div class="flex gap-4">
                                <button class="flex-1 bg-white/20 hover:bg-white/30 p-4 rounded-2xl flex justify-center transition-all">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                </button>
                                <button class="flex-1 bg-white/20 hover:bg-white/30 p-4 rounded-2xl flex justify-center transition-all">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm4.441 16.892c-2.102 0-3.809-1.707-3.809-3.809 0-2.102 1.707-3.809 3.809-3.809 2.102 0 3.809 1.707 3.809 3.809 0 2.102-1.707 3.809-3.809 3.809zm-4.441-7.142c-2.102 0-3.809-1.707-3.809-3.809 0-2.102 1.707-3.809 3.809-3.809 2.102 0 3.809 1.707 3.809 3.809 0 2.102-1.707 3.809-3.809 3.809z"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
