<script setup>
import { computed, ref } from "vue";
import StructureNode from "./StructureNode.vue";

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
        <div v-if="tree.length === 0" class="text-sm text-gray-500">Tidak ada data struktur.</div>
        <div v-else class="flex flex-col items-center">
            <div class="flex items-start justify-center gap-6">
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
</template>

<style scoped>
.structure-node .connector {
    position: relative;
}
</style>
