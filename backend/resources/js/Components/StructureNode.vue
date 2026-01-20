<script setup>
const props = defineProps({
    node: { type: Object, required: true },
    collapsed: { type: Object, required: true },
    toggle: { type: Function, required: true },
});

const statusBadge = (active) =>
    active ? "bg-green-100 text-green-700" : "bg-gray-100 text-gray-700";
const statusText = (active) => (active ? "Aktif" : "Tidak Aktif");

const avatarSrc = (node) => {
    if (node.photo_path) {
        return `/storage/${node.photo_path}`;
    }
    if (node.member?.photo_url) {
        return node.member.photo_url;
    }
    return null;
};
</script>

<template>
    <div class="structure-node">
        <div class="flex flex-col items-center">
            <div class="relative">
                <div class="rounded-xl bg-white shadow-sm border border-gray-200 px-4 py-3 w-64">
                    <div class="flex items-center gap-3">
                        <img
                            v-if="avatarSrc(node)"
                            class="h-10 w-10 rounded-full border object-cover"
                            :src="avatarSrc(node)"
                            alt="avatar"
                            @error="$event.target.style.display = 'none'"
                        />
                        <div v-else class="h-10 w-10 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold">
                            {{ (node.member?.full_name || node.position_name || '?').charAt(0).toUpperCase() }}
                        </div>
                        <div class="min-w-0">
                            <div class="font-bold text-sm text-gray-900 truncate">
                                {{ node.position_name }}
                            </div>
                            <div class="text-xs text-gray-600 truncate">
                                {{ node.member?.full_name || '-' }}
                            </div>
                            <div class="mt-1 flex items-center gap-2">
                                <span class="inline-block text-[10px] px-2 py-0.5 rounded-full" :class="statusBadge(node.is_active)">
                                    {{ statusText(node.is_active) }}
                                </span>
                                <span class="text-[10px] text-gray-500">
                                    {{ node.period_start }} - {{ node.period_end || 'Sekarang' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <button
                    v-if="node.children && node.children.length"
                    class="absolute -bottom-3 left-1/2 -translate-x-1/2 bg-white border border-gray-200 rounded-full w-7 h-7 flex items-center justify-center shadow-sm text-gray-500 hover:text-gray-700"
                    @click="toggle(node.id)"
                    :aria-label="collapsed[node.id] ? 'Perluas' : 'Ciutkan'"
                >
                    <svg v-if="collapsed[node.id]" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m-7-7h14" />
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5" />
                    </svg>
                </button>
            </div>

            <div v-if="node.children && node.children.length && !collapsed[node.id]" class="children mt-6 w-full">
                <div class="connector h-6 w-px bg-gray-300 mx-auto"></div>
                <div class="flex items-start justify-center gap-6">
                    <div v-for="c in node.children" :key="c.id" class="relative">
                        <div class="h-px w-full bg-gray-300 absolute -top-3"></div>
                        <StructureNode :node="c" :collapsed="collapsed" :toggle="toggle" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

