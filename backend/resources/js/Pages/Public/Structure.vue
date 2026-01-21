<script setup>
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import OrgTreeNode from '@/Components/OrgTreeNode.vue';
import { computed } from 'vue';

const props = defineProps({
    structures: {
        type: Array,
        default: () => []
    },
});

// Build hierarchical tree with extreme defensiveness
const tree = computed(() => {
    const rawItems = props.structures || [];
    if (!Array.isArray(rawItems) || rawItems.length === 0) return [];

    const idMap = {};
    const items = [];

    // Clone and sanitize items
    rawItems.forEach(item => {
        if (!item || !item.id) return;
        const normalizedItem = {
            ...item,
            children: [],
            id: String(item.id),
            parent_id: item.parent_id ? String(item.parent_id) : null
        };
        idMap[normalizedItem.id] = normalizedItem;
        items.push(normalizedItem);
    });

    const rootNodes = [];
    items.forEach(node => {
        if (node.parent_id && idMap[node.parent_id] && node.parent_id !== node.id) {
            idMap[node.parent_id].children.push(node);
        } else {
            rootNodes.push(node);
        }
    });

    // Final recursive sort
    const sortRecursively = (nodes) => {
        nodes.sort((a, b) => (Number(a.sort_order) || 0) - (Number(b.sort_order) || 0));
        nodes.forEach(n => {
            if (n.children.length > 0) sortRecursively(n.children);
        });
    };

    sortRecursively(rootNodes);
    return rootNodes;
});
</script>

<template>
    <Head title="Struktur Organisasi" />

    <PublicLayout>
        <div class="bg-slate-50 min-h-screen pb-32">
            <!-- Header Section -->
            <div class="bg-white border-b border-slate-200 shadow-sm relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-50/20 via-transparent to-blue-50/20"></div>
                <div class="max-w-7xl mx-auto py-16 px-6 lg:px-8 text-center relative z-10">
                    <h1 class="text-4xl font-black text-slate-900 sm:text-6xl uppercase tracking-tight mb-4">
                        Struktur Organisasi
                    </h1>
                    <p class="text-lg text-slate-500 max-w-2xl mx-auto font-medium leading-relaxed">
                        Mengenal jajaran kepengurusan yang menjalankan roda organisasi dengan profesional dan berintegritas.
                    </p>
                </div>
            </div>

            <div class="w-full px-8 py-20 overflow-x-auto">
                <div class="flex justify-center min-w-max pb-12">
                    <ul v-if="tree.length > 0" class="tree-container">
                        <OrgTreeNode 
                            v-for="root in tree" 
                            :key="root.id" 
                            :node="root"
                        />
                    </ul>
                    
                    <div v-else class="bg-white rounded-[2.5rem] p-24 shadow-xl shadow-slate-200/40 border border-slate-100 text-center max-w-3xl mx-auto">
                        <div class="w-24 h-24 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-8 rotate-3 hover:rotate-0 transition-transform duration-500">
                            <svg class="w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656 Galilee.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-black text-slate-900 mb-2 uppercase tracking-tight">Data Belum Tersedia</h3>
                        <p class="text-slate-500 font-medium max-w-sm mx-auto">Informasi struktur organisasi sedang dalam tahap pemutakhiran data kepengurusan.</p>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>

<style>
/* CSS Tree Connector Lines */
.tree-container {
    display: flex;
    justify-content: center;
    padding: 0;
    margin: 0;
    list-style: none;
}

/* Individual node vertical connectors */
.tree-container li::before {
    content: '';
    position: absolute;
    top: 0;
    left: 50%;
    width: 2px;
    height: 48px;
    background-color: #E2E8F0;
    transform: translateX(-50%);
}

/* Root nodes don't need top lines */
.tree-container > li::before {
    display: none;
}

/* Horizontal connectors for siblings */
.tree-container li > .relative > ul::before {
    content: '';
    position: absolute;
    top: 0;
    left: 160px; /* Offset to center of first card */
    right: 160px; /* Offset to center of last card */
    height: 2px;
    background-color: #E2E8F0;
}

/* Adjust horizontal line for single children */
.tree-container li > .relative > ul:has(> li:only-child)::before {
    display: none;
}
</style>
