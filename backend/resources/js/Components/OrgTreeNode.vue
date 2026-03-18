<script setup>
defineProps({
    node: Object
});
</script>

<template>
    <li class="relative pt-8 sm:pt-12 px-1 sm:px-2">
        <!-- Node Card -->
        <div class="relative z-20 mx-auto w-64 sm:w-80 bg-card rounded-2xl sm:rounded-3xl border border shadow-[0_10px_40px_-15px_rgba(0,0,0,0.1)] p-4 sm:p-5 hover:shadow-[0_20px_60px_-15px_rgba(0,0,0,0.15)] transition-all duration-500 hover:-translate-y-2 group">
            <div class="flex items-center gap-3 sm:gap-5">
                <!-- Photo -->
                <div class="shrink-0">
                    <div class="w-14 h-14 sm:w-20 sm:h-20 rounded-xl sm:rounded-2xl bg-muted border-2 border overflow-hidden shadow-inner group-hover:scale-105 transition-transform duration-500">
                        <img 
                            :src="node.photo_url || node.member?.photo_url || 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80'" 
                            class="w-full h-full object-cover"
                            alt="Avatar"
                        >
                    </div>
                </div>
                <!-- Info -->
                <div class="min-w-0">
                    <h4 class="text-sm sm:text-lg font-black text-foreground leading-tight mb-1 truncate">{{ node.position_name }}</h4>
                    <p class="text-muted-foreground font-bold text-xs sm:text-sm truncate mb-2 sm:mb-3">
                        {{ node.member?.full_name || '-' }}
                    </p>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded-lg bg-success/10 text-success-600 text-xs font-black uppercase tracking-widest leading-none">
                            Aktif
                        </span>
                        <span class="text-xs font-bold text-muted-foreground uppercase tracking-widest hidden sm:inline">
                            {{ node.period_start || '-' }}
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Connection Pulse Ball -->
            <div v-if="node.children && node.children.length > 0" class="absolute -bottom-3 left-1/2 -translate-x-1/2 w-6 h-6 rounded-full border-2 border bg-card flex items-center justify-center text-muted-foreground z-30 group-hover:border-primary-200 group-hover:text-primary-500 transition-colors shadow-sm">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </div>

        <!-- Children Rendering -->
        <div v-if="node.children && node.children.length > 0" class="relative pt-8 sm:pt-12">
            <!-- Vertical Line from parent -->
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[2px] h-8 sm:h-12 bg-muted"></div>
            
            <ul class="flex justify-center relative">
                <!-- Recursive Call -->
                <OrgTreeNode v-for="child in node.children" :key="child.id" :node="child" />
            </ul>
        </div>
    </li>
</template>

<script>
// This recursive component needs a name to call itself
export default {
    name: 'OrgTreeNode'
}
</script>
