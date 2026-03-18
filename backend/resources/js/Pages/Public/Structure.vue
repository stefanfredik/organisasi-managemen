<script setup>
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import OrgTreeNode from '@/Components/OrgTreeNode.vue';
import { computed } from 'vue';
import { useScrollReveal } from '@/composables/useScrollReveal';
import { Users, UserX } from 'lucide-vue-next';

useScrollReveal();

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
        <div class="bg-muted min-h-screen pb-32">
            <!-- Hero Section -->
            <div class="relative overflow-hidden bg-gradient-to-br from-primary/90 via-primary to-primary/80 pt-24 sm:pt-32">
                <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PGNpcmNsZSBjeD0iMzAiIGN5PSIzMCIgcj0iMiIvPjwvZz48L2c+PC9zdmc+')] opacity-60"></div>
                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24 text-center">
                    <div data-reveal="scale" class="inline-flex items-center justify-center w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white/15 backdrop-blur-sm mb-6 sm:mb-8">
                        <Users class="w-8 h-8 sm:w-10 sm:h-10 text-white" />
                    </div>
                    <h1 data-reveal class="text-3xl sm:text-4xl lg:text-6xl font-black text-white uppercase tracking-tight mb-4">
                        Struktur Organisasi
                    </h1>
                    <p data-reveal data-reveal-delay="100" class="text-lg text-white/80 max-w-2xl mx-auto font-medium leading-relaxed">
                        Mengenal jajaran kepengurusan yang menjalankan roda organisasi dengan profesional dan berintegritas.
                    </p>
                </div>
                <div class="absolute bottom-0 left-0 right-0 h-16 bg-gradient-to-t from-muted to-transparent"></div>
            </div>

            <div class="w-full px-4 sm:px-8 py-12 sm:py-20 overflow-x-auto">
                <div class="flex justify-center min-w-max pb-12">
                    <ul v-if="tree.length > 0" class="tree-container" data-reveal="fade">
                        <OrgTreeNode
                            v-for="root in tree"
                            :key="root.id"
                            :node="root"
                        />
                    </ul>

                    <div v-else data-reveal="scale" class="bg-card rounded-2xl sm:rounded-[2.5rem] p-10 sm:p-24 shadow-xl shadow-muted/40 border border text-center max-w-3xl mx-auto">
                        <div class="w-24 h-24 bg-muted rounded-2xl flex items-center justify-center mx-auto mb-8 rotate-3 hover:rotate-0 transition-transform duration-500">
                            <UserX class="w-12 h-12 text-muted-foreground" />
                        </div>
                        <h3 class="text-2xl font-black text-foreground mb-2 uppercase tracking-tight">Data Belum Tersedia</h3>
                        <p class="text-muted-foreground font-medium max-w-sm mx-auto">Informasi struktur organisasi sedang dalam tahap pemutakhiran data kepengurusan.</p>
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
    height: 32px;
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
    left: 128px; /* Offset to center of first card */
    right: 128px; /* Offset to center of last card */
    height: 2px;
    background-color: #E2E8F0;
}

/* Adjust horizontal line for single children */
.tree-container li > .relative > ul:has(> li:only-child)::before {
    display: none;
}
</style>
