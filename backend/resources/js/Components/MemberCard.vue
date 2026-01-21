<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    member: Object,
});
</script>

<template>
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 flex items-center gap-6 hover:shadow-xl transition-all duration-300 group">
        <div class="shrink-0 relative">
            <img 
                :src="member.photo_url || `https://ui-avatars.com/api/?name=${member.full_name}&background=random`" 
                class="w-20 h-20 rounded-2xl object-cover shadow-md group-hover:scale-110 transition-transform duration-500"
            >
            <div 
                class="absolute -bottom-2 -right-2 w-6 h-6 rounded-full border-4 border-white flex items-center justify-center shadow-sm"
                :class="member.status === 'active' ? 'bg-emerald-500' : 'bg-slate-400'"
            >
                <svg v-if="member.status === 'active'" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                </svg>
            </div>
        </div>
        <div class="flex-1 min-w-0">
            <Link :href="route('members.show', member.id)">
                <h3 class="text-lg font-black text-slate-900 truncate group-hover:text-indigo-600 transition-colors uppercase tracking-tight">
                    {{ member.full_name }}
                </h3>
            </Link>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">
                {{ member.member_code }}
            </p>
            <div class="flex items-center text-sm text-slate-500">
                <svg class="w-4 h-4 mr-1.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span class="truncate">{{ member.email || 'Email tidak tersedia' }}</span>
            </div>
        </div>
        <div class="shrink-0">
             <Link 
                :href="route('members.show', member.id)"
                class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-sm"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </Link>
        </div>
    </div>
</template>
