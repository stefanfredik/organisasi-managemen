<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref, watch } from 'vue';

const props = defineProps({
    types: Array,
    stats: Object,
    filters: Object,
});

const form = ref({
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
    type_id: '',
});

const formatCurrency = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(val);

const applyFilter = () => {
    router.get(route('contributions.monitoring'), form.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

</script>

<template>
    <Head title="Monitoring Iuran" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Monitoring Iuran
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                
                <!-- Navigation Tabs -->
                <div class="flex space-x-2 border-b border-gray-200 pb-2 overflow-x-auto">
                    <Link :href="route('contributions.monitoring')" class="px-4 py-2 text-sm font-bold rounded-lg bg-indigo-50 text-indigo-700">
                        Dashboard
                    </Link>
                    <Link :href="route('contributions.verification')" class="px-4 py-2 text-sm font-bold rounded-lg text-gray-600 hover:bg-gray-50">
                        Verifikasi
                    </Link>
                    <Link :href="route('contributions.matrix')" class="px-4 py-2 text-sm font-bold rounded-lg text-gray-600 hover:bg-gray-50">
                        Matrix
                    </Link>
                    <Link :href="route('contributions.index')" class="px-4 py-2 text-sm font-bold rounded-lg text-gray-600 hover:bg-gray-50">
                        Riwayat Transaksi
                    </Link>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <div class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Total Terkumpul</div>
                        <div class="text-2xl font-black text-indigo-600">{{ formatCurrency(stats.total_collected) }}</div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <div class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Menunggu Verifikasi</div>
                        <div class="text-2xl font-black text-amber-500">{{ stats.total_pending }} <span class="text-sm text-gray-400 font-bold">Transaksi</span></div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <div class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Jenis Iuran Aktif</div>
                        <div class="text-2xl font-black text-gray-700">{{ stats.total_contribution_active }}</div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <div class="col-span-1">
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Dari Tanggal</label>
                            <input type="date" v-model="form.start_date" class="w-full rounded-xl border-gray-200 text-sm font-bold text-gray-700 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div class="col-span-1">
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Sampai Tanggal</label>
                            <input type="date" v-model="form.end_date" class="w-full rounded-xl border-gray-200 text-sm font-bold text-gray-700 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                         <div class="col-span-1">
                            <PrimaryButton @click="applyFilter" class="w-full justify-center h-[42px]">Filter</PrimaryButton>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 text-center py-24">
                     <p class="text-gray-400 font-bold">Grafik & Analisis Detail akan segera hadir di sini.</p>
                     <p class="text-gray-300 text-sm mt-2">Menampilkan tren pembayaran berdasarkan filter di atas.</p>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
