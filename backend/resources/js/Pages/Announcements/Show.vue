<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const props = defineProps({
    announcement: Object,
});
</script>

<template>
    <Head :title="`Detail Pengumuman - ${announcement?.title || ''}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Detail Pengumuman</h2>
                <div class="flex items-center gap-2">
                    <Link :href="route('announcements.edit', announcement.id)" class="px-3 py-2 text-xs font-semibold bg-indigo-600 text-white rounded-md">
                        Edit
                    </Link>
                    <Link :href="route('announcements.index')" class="px-3 py-2 text-xs font-semibold bg-gray-100 text-gray-700 rounded-md">
                        Kembali
                    </Link>
                </div>
            </div>
        </template>
        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200 space-y-4">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">{{ announcement.title }}</h3>
                                <div class="mt-1 text-xs text-gray-500">
                                    Dibuat oleh: {{ announcement.creator?.name || '-' }} •
                                    Status: <span class="font-semibold">{{ announcement.status === 'published' ? 'Dipublikasikan' : 'Draft' }}</span> •
                                    Tanggal Publish: {{ announcement.publish_date || '-' }}
                                </div>
                            </div>
                            <div>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                      :class="announcement.status === 'published' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'">
                                    {{ announcement.status === 'published' ? 'Dipublikasikan' : 'Draft' }}
                                </span>
                            </div>
                        </div>
                        <div class="border-t pt-4">
                            <div class="prose max-w-none" v-html="announcement.content || ''"></div>
                        </div>
                        <div class="border-t pt-4">
                            <div class="text-sm text-gray-600">
                                Audiens: <span v-if="announcement.target_audience === 'all'">Semua</span>
                                <span v-else>{{ (announcement.target_roles || []).join(', ') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

