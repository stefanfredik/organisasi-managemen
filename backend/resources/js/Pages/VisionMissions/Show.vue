<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    visionMission: Object,
});

const getStatusBadge = (status) => {
    return status === "active"
        ? "bg-green-100 text-green-800"
        : "bg-gray-100 text-gray-800";
};

const getStatusLabel = (status) => {
    return status === "active" ? "Aktif" : "Tidak Aktif";
};
</script>

<template>
    <Head title="Detail Visi & Misi" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Detail Visi & Misi
                </h2>
                <Link
                    :href="route('vision-missions.edit', visionMission.id)"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    Edit
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-8">
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Periode</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ visionMission.period_start }} - {{ visionMission.period_end || 'Sekarang' }}
                                </dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                        :class="getStatusBadge(visionMission.status)"
                                    >
                                        {{ getStatusLabel(visionMission.status) }}
                                    </span>
                                </dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Dibuat Oleh</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ visionMission.creator?.name }}
                                </dd>
                            </div>
                             <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Dibuat Pada</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ new Date(visionMission.created_at).toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
                                </dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Visi</dt>
                                <dd class="mt-1 text-sm text-gray-900 whitespace-pre-wrap bg-gray-50 p-4 rounded-md">
                                    {{ visionMission.vision }}
                                </dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Misi</dt>
                                <dd class="mt-1 text-sm text-gray-900 bg-gray-50 p-4 rounded-md">
                                    <ol class="list-decimal list-inside space-y-1">
                                        <li v-for="(mission, index) in visionMission.missions" :key="index">
                                            {{ mission }}
                                        </li>
                                    </ol>
                                </dd>
                            </div>
                        </dl>

                        <div class="mt-8 flex justify-end">
                            <Link
                                :href="route('vision-missions.index')"
                                class="text-sm text-gray-600 hover:text-gray-900"
                            >
                                Kembali ke Daftar
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
