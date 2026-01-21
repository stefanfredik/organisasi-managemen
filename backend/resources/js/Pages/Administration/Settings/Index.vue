<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    settings: Object,
});

const activeTab = ref('general');

// Prepare form data from props
const initialData = {
    settings: []
};

Object.values(props.settings).forEach(group => {
    group.forEach(setting => {
        initialData.settings.push({
            key: setting.key,
            value: setting.value,
            description: setting.description,
            group: setting.group,
            type: setting.type
        });
    });
});

const form = useForm(initialData);

const submit = () => {
    form.post(route('settings.update'), {
        preserveScroll: true,
    });
};

const getGroupLabel = (group) => {
    switch (group) {
        case 'general': return 'Umum';
        case 'financial': return 'Keuangan';
        case 'social': return 'Media Sosial';
        case 'portal': return 'Portal Publik';
        case 'system': return 'Sistem';
        default: return group;
    }
};

const getGroupIcon = (group) => {
    switch (group) {
        case 'general': return '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>';
        case 'financial': return '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
        case 'social': return '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" /></svg>';
        case 'portal': return '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>';
        case 'system': return '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>';
        default: return '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m12 4a2 2 0 100-4m0 4a2 2 0 110-4m-6 0v2m0-6V4m6 6v10m-6-2v2m-6-2v2" /></svg>';
    }
};
</script>

<template>
    <Head title="Pengaturan Sistem" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Pengaturan Sistem</h2>
                <p class="text-slate-500 text-sm font-medium mt-1">Konfigurasi parameter dan identitas organisasi dalam platform.</p>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex flex-col lg:flex-row gap-8">
                    <!-- Sidebar Tabs -->
                    <div class="w-full lg:w-64 shrink-0 space-y-2">
                        <button 
                            v-for="(group, key) in settings" 
                            :key="key"
                            @click="activeTab = key"
                            class="w-full flex items-center gap-3 px-6 py-4 rounded-2xl text-sm font-bold transition-all uppercase tracking-widest border shadow-sm"
                            :class="activeTab === key 
                                ? 'bg-indigo-600 text-white border-indigo-600 shadow-indigo-100' 
                                : 'bg-white text-slate-500 border-slate-100 hover:bg-slate-50'"
                        >
                            <span v-html="getGroupIcon(key)"></span>
                            {{ getGroupLabel(key) }}
                        </button>
                    </div>

                    <!-- Main Content -->
                    <div class="flex-1">
                        <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
                            <form @submit.prevent="submit" class="p-8 lg:p-12">
                                <div class="mb-10 flex items-center justify-between border-b border-slate-50 pb-6">
                                    <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">{{ getGroupLabel(activeTab) }}</h3>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Konfigurasi {{ activeTab }}</p>
                                </div>

                                <div class="space-y-8">
                                    <div 
                                        v-for="(setting, index) in form.settings" 
                                        :key="setting.key"
                                        v-show="setting.group === activeTab"
                                        class="space-y-2"
                                    >
                                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                            <div class="max-w-md">
                                                <label class="text-[11px] font-black uppercase tracking-widest text-slate-400 block ml-1">{{ setting.key.split('_').join(' ') }}</label>
                                                <p class="text-xs text-slate-500 font-medium ml-1 mt-1">{{ setting.description }}</p>
                                            </div>
                                            <div class="flex-1 max-w-lg">
                                                <!-- String Input -->
                                                <input
                                                    v-if="setting.type === 'string'"
                                                    v-model="setting.value"
                                                    type="text"
                                                    class="w-full bg-slate-50 border-slate-200 rounded-2xl py-3 px-5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all font-medium text-slate-700"
                                                >
                                                
                                                <!-- Boolean Input -->
                                                <div v-else-if="setting.type === 'boolean'" class="flex items-center">
                                                    <button 
                                                        type="button"
                                                        @click="setting.value = setting.value === '1' ? '0' : '1'"
                                                        class="relative inline-flex h-7 w-14 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2"
                                                        :class="setting.value === '1' ? 'bg-indigo-600' : 'bg-slate-200'"
                                                    >
                                                        <span 
                                                            class="pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                                            :class="setting.value === '1' ? 'translate-x-7' : 'translate-x-0'"
                                                        ></span>
                                                    </button>
                                                    <span class="ml-3 text-sm font-bold" :class="setting.value === '1' ? 'text-indigo-600' : 'text-slate-400'">
                                                        {{ setting.value === '1' ? 'Aktif' : 'Non-Aktif' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-12 pt-8 border-t border-slate-50 flex justify-end">
                                    <button
                                        type="submit"
                                        :disabled="form.processing"
                                        class="px-10 py-4 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-black rounded-2xl shadow-xl shadow-indigo-100 transition-all active:scale-95 disabled:opacity-50 uppercase tracking-[0.2em]"
                                    >
                                        Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
