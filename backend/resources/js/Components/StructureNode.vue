<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    node: { type: Object, required: true },
    collapsed: { type: Object, required: true },
    toggle: { type: Function, required: true },
    members: { type: Array, default: () => [] },
    canManage: { type: Boolean, default: false },
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

const assignTrigger = ref(0);
const addTrigger = ref(0);
const addPlacement = ref("child");
</script>

<template>
    <div class="structure-node">
        <div class="flex flex-col items-center">
            <div class="relative group">
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
                            <div v-if="canManage" class="mt-2">
                                <MemberAssign :node="node" :members="members" :trigger="assignTrigger" />
                            </div>
                            <div v-if="canManage" class="mt-2">
                                <AddPositionInline :node="node" :members="members" :trigger="addTrigger" :placement-external="addPlacement" />
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="canManage" class="pointer-events-none">
                    <button
                        class="pointer-events-auto absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition bg-white border border-gray-200 rounded-full w-7 h-7 flex items-center justify-center shadow-sm text-gray-500 hover:text-indigo-700"
                        @click="assignTrigger++;"
                        aria-label="Tambah/Ganti Anggota"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1z" />
                        </svg>
                    </button>
                    <button
                        v-if="node.parent_id !== null"
                        class="pointer-events-auto absolute top-1/2 -translate-y-1/2 -right-3 opacity-0 group-hover:opacity-100 transition bg-white border border-gray-200 rounded-full w-7 h-7 flex items-center justify-center shadow-sm text-gray-500 hover:text-green-700"
                        @click="addPlacement = 'sibling'; addTrigger++;"
                        aria-label="Tambah Sejajar"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </button>
                    <button
                        class="pointer-events-auto absolute -bottom-3 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition bg-white border border-gray-200 rounded-full w-7 h-7 flex items-center justify-center shadow-sm text-gray-500 hover:text-green-700"
                        @click="addPlacement = 'child'; addTrigger++;"
                        aria-label="Tambah Dibawah"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </button>
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
                        <StructureNode :node="c" :collapsed="collapsed" :toggle="toggle" :members="members" :can-manage="canManage" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { defineComponent, ref, watch } from "vue";
import { router } from "@inertiajs/vue3";

export default {
    components: {
        MemberAssign: defineComponent({
            name: "MemberAssign",
            props: {
                node: { type: Object, required: true },
                members: { type: Array, default: () => [] },
                trigger: { type: Number, default: 0 },
            },
            setup(props, { expose }) {
                const open = ref(false);
                const selected = ref(props.node.member_id || "");
                const saving = ref(false);

                const save = () => {
                    saving.value = true;
                    router.put(
                        route("organization-structures.update", props.node.id),
                        {
                            member_id: selected.value || "",
                            position_name: props.node.position_name,
                            level: props.node.level ?? 0,
                            parent_id: props.node.parent_id || "",
                            sort_order: props.node.sort_order ?? 0,
                            period_start: props.node.period_start,
                            period_end: props.node.period_end || "",
                            is_active: props.node.is_active,
                        },
                        {
                            preserveScroll: true,
                            onFinish: () => {
                                saving.value = false;
                                open.value = false;
                            },
                        },
                    );
                };

                const openForm = () => {
                    open.value = true;
                };

                watch(
                    () => props.trigger,
                    () => {
                        open.value = true;
                    },
                );

                expose({ openForm });

                return {
                    open,
                    selected,
                    saving,
                    save,
                    openForm,
                };
            },
            template: `
            <div>
                <button
                    v-if="!open"
                    type="button"
                    class="inline-flex items-center text-xs text-indigo-600 hover:text-indigo-800"
                    @click="open = true"
                >
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ node.member ? 'Ganti Anggota' : 'Tambah Anggota' }}
                </button>
                <div v-else class="mt-2 space-y-2">
                    <select
                        v-model="selected"
                        class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-xs"
                    >
                        <option value="">- Kosong -</option>
                        <option v-for="m in members" :key="m.id" :value="m.id">
                            {{ m.full_name }}
                        </option>
                    </select>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            @click="save"
                            class="px-2 py-1 rounded bg-indigo-600 text-white text-xs disabled:opacity-50"
                            :disabled="saving"
                        >Simpan</button>
                        <button
                            type="button"
                            @click="open = false"
                            class="px-2 py-1 rounded bg-gray-100 text-gray-700 text-xs"
                        >Batal</button>
                    </div>
                </div>
            </div>
            `,
        }),
        AddPositionInline: defineComponent({
            name: "AddPositionInline",
            props: {
                node: { type: Object, required: true },
                members: { type: Array, default: () => [] },
                trigger: { type: Number, default: 0 },
                placementExternal: { type: String, default: "child" },
            },
            setup(props, { expose }) {
                const open = ref(false);
                const placement = ref("child"); // 'child' | 'sibling'
                const positionName = ref("");
                const selectedMember = ref("");
                const saving = ref(false);

                const currentYear = new Date().getFullYear();

                const computePayload = () => {
                    const isChild = placement.value === "child";
                    const level =
                        (props.node.level ?? 0) + (isChild ? 1 : 0);
                    const parent_id = isChild ? props.node.id : (props.node.parent_id || "");
                    const sort_order = isChild
                        ? ((props.node.children?.length ?? 0) + 1)
                        : ((props.node.sort_order ?? 0) + 1);
                    return {
                        member_id: selectedMember.value || "",
                        position_name: positionName.value,
                        level,
                        parent_id,
                        sort_order,
                        period_start: currentYear,
                        period_end: "",
                        is_active: true,
                    };
                };

                const save = () => {
                    if (!positionName.value) return;
                    saving.value = true;
                    const payload = computePayload();
                    router.post(route("organization-structures.store"), payload, {
                        preserveScroll: true,
                        onFinish: () => {
                            saving.value = false;
                            open.value = false;
                            positionName.value = "";
                            selectedMember.value = "";
                        },
                    });
                };

                const showAt = (p) => {
                    placement.value = p;
                    open.value = true;
                };

                watch(
                    () => props.trigger,
                    () => {
                        placement.value = props.placementExternal || "child";
                        open.value = true;
                    },
                );

                expose({ showAt });

                return {
                    open,
                    placement,
                    positionName,
                    selectedMember,
                    saving,
                    save,
                    showAt,
                };
            },
            template: `
            <div class="mt-2">
                <button
                    v-if="!open"
                    type="button"
                    class="inline-flex items-center text-xs text-green-600 hover:text-green-800"
                    @click="open = true"
                >
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Posisi
                </button>
                <div v-else class="mt-2 space-y-2">
                    <div class="flex items-center gap-2">
                        <label class="text-[11px] text-gray-600">Letak:</label>
                        <label class="inline-flex items-center gap-1 text-[11px] text-gray-700">
                            <input type="radio" value="child" v-model="placement" />
                            Dibawah
                        </label>
                        <label class="inline-flex items-center gap-1 text-[11px] text-gray-700">
                            <input type="radio" value="sibling" v-model="placement" />
                            Sejajar
                        </label>
                    </div>
                    <input
                        type="text"
                        v-model="positionName"
                        placeholder="Nama posisi"
                        class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-xs"
                    />
                    <select
                        v-model="selectedMember"
                        class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-xs"
                    >
                        <option value="">- Tanpa anggota -</option>
                        <option v-for="m in members" :key="m.id" :value="m.id">
                            {{ m.full_name }}
                        </option>
                    </select>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            @click="save"
                            class="px-2 py-1 rounded bg-green-600 text-white text-xs disabled:opacity-50"
                            :disabled="saving || !positionName"
                        >Simpan</button>
                        <button
                            type="button"
                            @click="open = false"
                            class="px-2 py-1 rounded bg-gray-100 text-gray-700 text-xs"
                        >Batal</button>
                    </div>
                </div>
            </div>
            `,
        }),
    },
};
</script>
