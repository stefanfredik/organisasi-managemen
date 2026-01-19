<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
    donation: Object,
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(amount);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
};

const calculateProgress = (collected, target) => {
    if (!target || target <= 0) return 0;
    return Math.min(Math.round((collected / target) * 100), 100);
};
</script>

<template>
    <Head :title="donation.program_name" />

    <PublicLayout>
        <div class="bg-gray-50 min-h-screen py-12">
            <div class="mx-auto max-w-4xl px-6 lg:px-8">
                <!-- Breadcrumb -->
                <nav class="flex mb-8 items-center text-sm font-medium text-gray-500">
                    <Link :href="route('public.donations.index')" class="hover:text-indigo-600 transition">Program Donasi</Link>
                    <svg class="w-4 h-4 mx-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                    <span class="text-gray-900 truncate">{{ donation.program_name }}</span>
                </nav>

                <!-- Detailed Content -->
                <div class="bg-white rounded-3xl shadow-xl overflow-hidden shadow-indigo-100/50 border border-gray-100">
                    <div class="h-64 bg-indigo-600 relative flex items-center justify-center">
                        <div class="absolute inset-0 opacity-20" style="background-image: url('https://www.transparenttextures.com/patterns/cubes.png');"></div>
                        <h1 class="relative text-3xl md:text-4xl font-black text-white px-8 text-center leading-tight">
                            {{ donation.program_name }}
                        </h1>
                    </div>

                    <div class="p-8 md:p-12">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-12">
                            <!-- Left: Description -->
                            <div>
                                <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                                    <span class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center mr-3">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </span>
                                    Tentang Program
                                </h2>
                                <div class="prose prose-indigo max-w-none text-gray-600 whitespace-pre-wrap leading-relaxed">
                                    {{ donation.description || 'Tidak ada deskripsi detail untuk program ini.' }}
                                </div>

                                <div class="mt-8 pt-8 border-t flex flex-wrap gap-6">
                                    <div>
                                        <p class="text-xs font-black uppercase tracking-widest text-gray-400 mb-1">Mulai</p>
                                        <p class="text-sm font-bold text-gray-900">{{ formatDate(donation.start_date) }}</p>
                                    </div>
                                    <div v-if="donation.end_date">
                                        <p class="text-xs font-black uppercase tracking-widest text-gray-400 mb-1">Berakhir</p>
                                        <p class="text-sm font-bold text-gray-900">{{ formatDate(donation.end_date) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-black uppercase tracking-widest text-gray-400 mb-1">Status</p>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800">AKTIF</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Stats & Donation Info -->
                            <div class="bg-gray-50 rounded-2xl p-8 border border-gray-100">
                                <h2 class="text-xl font-bold text-gray-900 mb-8 tracking-tight">Status Penggalangan</h2>
                                
                                <div class="mb-8">
                                    <div class="flex justify-between items-end mb-3">
                                        <div>
                                            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Terkumpul</p>
                                            <p class="text-3xl font-black text-indigo-600">{{ formatCurrency(donation.collected_amount) }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Target</p>
                                            <p class="text-sm font-bold text-gray-700">{{ formatCurrency(donation.target_amount) }}</p>
                                        </div>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3 mb-2">
                                        <div 
                                            class="bg-indigo-600 h-3 rounded-full shadow-sm transition-all duration-1000 ease-out" 
                                            :style="{ width: calculateProgress(donation.collected_amount, donation.target_amount) + '%' }"
                                        ></div>
                                    </div>
                                    <p class="text-right text-sm font-black text-indigo-600">
                                        {{ calculateProgress(donation.collected_amount, donation.target_amount) }}% Tercapai
                                    </p>
                                </div>

                                <div class="space-y-4">
                                    <div class="bg-indigo-600 text-white rounded-xl p-6 shadow-lg shadow-indigo-100">
                                        <p class="text-xs font-bold uppercase tracking-widest opacity-80 mb-4">Cara Berdonasi</p>
                                        <div class="space-y-3 text-sm">
                                            <div class="flex items-start gap-3">
                                                <div class="w-5 h-5 bg-white/20 rounded-full flex items-center justify-center shrink-0 mt-0.5">1</div>
                                                <p>Transfer ke rekening resmi organisasi</p>
                                            </div>
                                            <div class="bg-white/10 rounded-lg p-3 font-mono text-sm border border-white/20">
                                                <p class="opacity-70 text-[10px] mb-1">BANK MANDIRI</p>
                                                <p class="font-bold tracking-wider">123-456-7890-123</p>
                                                <p class="opacity-70 text-[10px] mt-1 uppercase">A.N KOPERASI ORGANISASI</p>
                                            </div>
                                            <div class="flex items-start gap-3 pt-2">
                                                <div class="w-5 h-5 bg-white/20 rounded-full flex items-center justify-center shrink-0 mt-0.5">2</div>
                                                <p>Kirim bukti transfer ke WhatsApp Bendahara via tombol di bawah.</p>
                                            </div>
                                        </div>
                                        
                                        <a 
                                            href="https://wa.me/6281234567890?text=Halo%20Bendahara,%20saya%20ingin%20mengirimkan%20bukti%20donasi%20untuk%20program%20" 
                                            class="mt-6 flex items-center justify-center gap-2 w-full py-3 bg-white text-indigo-600 rounded-xl font-black text-sm hover:bg-indigo-50 transition shadow-sm active:scale-95"
                                        >
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.414 0 .004 5.408.001 12.045a11.811 11.811 0 001.591 5.976L0 24l6.117-1.605a11.803 11.803 0 005.925 1.586h.005c6.637 0 12.046-5.409 12.049-12.048a11.811 11.811 0 00-3.414-8.513" /></svg>
                                            Konfirmasi Donasi
                                        </a>
                                    </div>
                                    <p class="text-[10px] text-center text-gray-400 font-bold uppercase leading-relaxed tracking-wider">
                                        Data donasi akan diperbarui secara manual oleh bendahara setelah verifikasi bukti transfer.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Donors Sidebar for Detail -->
                        <div class="border-t pt-12">
                            <h3 class="text-lg font-bold text-gray-900 mb-8 flex items-center">
                                <span class="w-8 h-8 rounded-lg bg-green-100 text-green-600 flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                </span>
                                Donatur Terbaru
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div v-for="tx in donation.transactions" :key="tx.id" class="bg-gray-50 rounded-2xl p-5 border border-gray-100 flex items-center gap-4">
                                    <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 shrink-0 font-black">
                                        {{ tx.is_anonymous ? '?' : (tx.donor_name ? tx.donor_name.charAt(0).toUpperCase() : 'A') }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900">{{ tx.is_anonymous ? 'Hamba Allah' : (tx.donor_name || 'Anonim') }}</p>
                                        <p class="text-xs text-indigo-600 font-black">{{ formatCurrency(tx.amount) }}</p>
                                        <p class="text-[10px] text-gray-400 mt-1 font-bold tracking-widest">{{ formatDate(tx.donation_date) }}</p>
                                    </div>
                                </div>
                                <div v-if="donation.transactions.length === 0" class="col-span-1 md:col-span-2 lg:col-span-3 py-12 text-center text-gray-400 bg-gray-50 rounded-2xl border-2 border-dashed">
                                    Jadilah donatur pertama untuk program ini.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-12 text-center">
                    <Link :href="route('public.donations.index')" class="text-sm font-bold text-gray-500 hover:text-indigo-600 transition flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        Kembali ke Daftar Program
                    </Link>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
