<script setup>
import { computed, ref } from "vue";
import StructureNode from "./StructureNode.vue";
import { Network } from "lucide-vue-next";

const props = defineProps({
    items: { type: Array, default: () => [] },
    members: { type: Array, default: () => [] },
    canManage: { type: Boolean, default: false },
});

const buildTree = (items) => {
    const byId = new Map();
    items.forEach((i) => byId.set(i.id, { ...i, children: [] }));
    const roots = [];
    byId.forEach((node) => {
        if (node.parent_id && byId.has(node.parent_id)) {
            byId.get(node.parent_id).children.push(node);
        } else {
            roots.push(node);
        }
    });
    const sortChildren = (n) => {
        n.children.sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0));
        n.children.forEach(sortChildren);
    };
    roots.sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0));
    roots.forEach(sortChildren);
    return roots;
};

const tree = computed(() => buildTree(props.items || []));
const collapsed = ref({});
const toggle = (id) => {
    collapsed.value[id] = !collapsed.value[id];
};
</script>

<template>
    <div class="structure-chart">
        <!-- Empty state -->
        <div v-if="tree.length === 0" class="flex flex-col items-center justify-center py-16 text-center">
            <div class="w-16 h-16 rounded-2xl bg-muted flex items-center justify-center mb-4">
                <Network class="w-8 h-8 text-muted-foreground" />
            </div>
            <p class="text-sm font-medium text-foreground mb-1">Belum ada data struktur</p>
            <p class="text-xs text-muted-foreground">Tambahkan posisi untuk mulai membangun struktur organisasi.</p>
        </div>

        <!-- Chart with horizontal scroll -->
        <div v-else class="overflow-x-auto pb-6">
            <div class="inline-flex min-w-max p-6">
                <div class="flex items-start justify-center gap-8">
                    <StructureNode
                        v-for="r in tree"
                        :key="r.id"
                        :node="r"
                        :collapsed="collapsed"
                        :toggle="toggle"
                        :members="members"
                        :can-manage="canManage"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
