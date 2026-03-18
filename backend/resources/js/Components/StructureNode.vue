<script setup>
import { ref, watch, defineComponent } from "vue";
import { router, Link } from "@inertiajs/vue3";

const props = defineProps({
    node: { type: Object, required: true },
    collapsed: { type: Object, required: true },
    toggle: { type: Function, required: true },
    members: { type: Array, default: () => [] },
    canManage: { type: Boolean, default: false },
});

const statusBadge = (active) =>
    active ? "bg-success/20 text-success-foreground" : "bg-muted text-muted-foreground";
const statusText = (active) => (active ? "Aktif" : "Tidak Aktif");

const avatarSrc = (node) => {
    if (node.photo_path) return `/storage/${node.photo_path}`;
    if (node.member?.photo_url) return node.member.photo_url;
    return null;
};

const avatarInitial = (node) =>
    (node.member?.full_name || node.position_name || "?").charAt(0).toUpperCase();

// Inline edit states
const assignOpen = ref(false);
const assignSelected = ref(props.node.member_id || "");
const assignSaving = ref(false);

const saveAssign = () => {
    assignSaving.value = true;
    router.put(
        route("organization-structures.update", props.node.id),
        {
            member_id: assignSelected.value || "",
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
                assignSaving.value = false;
                assignOpen.value = false;
            },
        },
    );
};

const addOpen = ref(false);
const addPlacement = ref("child");
const addPositionName = ref("");
const addMember = ref("");
const addSaving = ref(false);

const saveAdd = () => {
    if (!addPositionName.value) return;
    addSaving.value = true;
    const isChild = addPlacement.value === "child";
    router.post(
        route("organization-structures.store"),
        {
            member_id: addMember.value || "",
            position_name: addPositionName.value,
            level: (props.node.level ?? 0) + (isChild ? 1 : 0),
            parent_id: isChild ? props.node.id : (props.node.parent_id || ""),
            sort_order: isChild
                ? ((props.node.children?.length ?? 0) + 1)
                : ((props.node.sort_order ?? 0) + 1),
            period_start: new Date().getFullYear(),
            period_end: "",
            is_active: true,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                addSaving.value = false;
                addOpen.value = false;
                addPositionName.value = "";
                addMember.value = "";
            },
        },
    );
};

const showAddChild = () => {
    addPlacement.value = "child";
    addOpen.value = true;
};

const showAddSibling = () => {
    addPlacement.value = "sibling";
    addOpen.value = true;
};

const hasChildren = props.node.children && props.node.children.length > 0;

const deleteNode = () => {
    if (!confirm(`Hapus posisi "${props.node.position_name}"?`)) return;
    router.delete(route("organization-structures.destroy", props.node.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="structure-node flex flex-col items-center">
        <!-- Node Card -->
        <div class="relative group">
            <div class="rounded-xl bg-card shadow-sm border px-4 py-3 w-72 transition-shadow hover:shadow-md">
                <div class="flex items-center gap-3">
                    <!-- Avatar -->
                    <div class="shrink-0">
                        <img
                            v-if="avatarSrc(node)"
                            class="h-11 w-11 rounded-full ring-2 ring-primary/20 object-cover"
                            :src="avatarSrc(node)"
                            alt="avatar"
                            @error="$event.target.style.display = 'none'"
                        />
                        <div
                            v-else
                            class="h-11 w-11 rounded-full bg-primary text-primary-foreground flex items-center justify-center font-bold text-sm ring-2 ring-primary/20"
                        >
                            {{ avatarInitial(node) }}
                        </div>
                    </div>
                    <!-- Info -->
                    <div class="min-w-0 flex-1">
                        <div class="font-bold text-sm text-foreground truncate">
                            {{ node.position_name }}
                        </div>
                        <div class="text-xs text-muted-foreground truncate">
                            {{ node.member?.full_name || '-' }}
                        </div>
                        <div class="mt-1.5 flex items-center gap-2">
                            <span
                                class="inline-block text-[10px] px-2 py-0.5 rounded-full font-medium"
                                :class="statusBadge(node.is_active)"
                            >
                                {{ statusText(node.is_active) }}
                            </span>
                            <span class="text-[10px] text-muted-foreground">
                                {{ node.period_start }}–{{ node.period_end || 'Sekarang' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Inline: Assign Member -->
                <div v-if="canManage && assignOpen" class="mt-3 pt-3 border-t space-y-2">
                    <select
                        v-model="assignSelected"
                        class="block w-full border-input bg-background focus:border-ring focus:ring-ring rounded-md shadow-sm text-xs h-8 px-2"
                    >
                        <option value="">- Kosong -</option>
                        <option v-for="m in members" :key="m.id" :value="m.id">
                            {{ m.full_name }}
                        </option>
                    </select>
                    <div class="flex items-center gap-2">
                        <button @click="saveAssign" :disabled="assignSaving" class="px-2.5 py-1 rounded-md bg-primary text-primary-foreground text-xs font-medium disabled:opacity-50">Simpan</button>
                        <button @click="assignOpen = false" class="px-2.5 py-1 rounded-md bg-muted text-foreground text-xs">Batal</button>
                    </div>
                </div>

                <!-- Inline: Add Position -->
                <div v-if="canManage && addOpen" class="mt-3 pt-3 border-t space-y-2">
                    <div class="flex items-center gap-3">
                        <label class="text-[11px] text-muted-foreground">Letak:</label>
                        <label class="inline-flex items-center gap-1 text-[11px]">
                            <input type="radio" value="child" v-model="addPlacement" class="text-primary" /> Dibawah
                        </label>
                        <label v-if="node.parent_id !== null" class="inline-flex items-center gap-1 text-[11px]">
                            <input type="radio" value="sibling" v-model="addPlacement" class="text-primary" /> Sejajar
                        </label>
                    </div>
                    <input
                        type="text"
                        v-model="addPositionName"
                        placeholder="Nama posisi"
                        class="block w-full border-input bg-background focus:border-ring focus:ring-ring rounded-md shadow-sm text-xs h-8 px-2"
                    />
                    <select
                        v-model="addMember"
                        class="block w-full border-input bg-background focus:border-ring focus:ring-ring rounded-md shadow-sm text-xs h-8 px-2"
                    >
                        <option value="">- Tanpa anggota -</option>
                        <option v-for="m in members" :key="m.id" :value="m.id">
                            {{ m.full_name }}
                        </option>
                    </select>
                    <div class="flex items-center gap-2">
                        <button @click="saveAdd" :disabled="addSaving || !addPositionName" class="px-2.5 py-1 rounded-md bg-green-600 text-white text-xs font-medium disabled:opacity-50">Simpan</button>
                        <button @click="addOpen = false" class="px-2.5 py-1 rounded-md bg-muted text-foreground text-xs">Batal</button>
                    </div>
                </div>
            </div>

            <!-- Action buttons: visible on mobile, hover on desktop -->
            <div v-if="canManage && !assignOpen && !addOpen" class="flex items-center justify-center gap-1 mt-1.5 md:opacity-0 md:group-hover:opacity-100 transition-opacity">
                <button
                    @click="assignOpen = true"
                    class="p-1.5 rounded-lg bg-card border text-muted-foreground hover:text-primary hover:border-primary/30 transition-colors"
                    title="Ganti Anggota"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1z" />
                    </svg>
                </button>
                <button
                    @click="showAddChild"
                    class="p-1.5 rounded-lg bg-card border text-muted-foreground hover:text-green-600 hover:border-green-500/30 transition-colors"
                    title="Tambah Dibawah"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </button>
                <button
                    v-if="node.parent_id !== null"
                    @click="showAddSibling"
                    class="p-1.5 rounded-lg bg-card border text-muted-foreground hover:text-blue-600 hover:border-blue-500/30 transition-colors"
                    title="Tambah Sejajar"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12M8 12h12M8 17h12" />
                    </svg>
                </button>
                <Link
                    :href="route('organization-structures.edit', node.id)"
                    class="p-1.5 rounded-lg bg-card border text-muted-foreground hover:text-amber-600 hover:border-amber-500/30 transition-colors"
                    title="Edit"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                </Link>
                <button
                    @click="deleteNode"
                    class="p-1.5 rounded-lg bg-card border text-muted-foreground hover:text-red-600 hover:border-red-500/30 transition-colors"
                    title="Hapus"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>

            <!-- Collapse/Expand button -->
            <button
                v-if="hasChildren"
                class="absolute -bottom-3 left-1/2 -translate-x-1/2 bg-card border rounded-full w-6 h-6 flex items-center justify-center shadow-sm text-muted-foreground hover:text-foreground z-10 transition-colors"
                @click="toggle(node.id)"
                :aria-label="collapsed[node.id] ? 'Perluas' : 'Ciutkan'"
            >
                <svg v-if="collapsed[node.id]" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m-7-7h14" />
                </svg>
                <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5" />
                </svg>
            </button>
        </div>

        <!-- Children -->
        <div v-if="hasChildren && !collapsed[node.id]" class="mt-8 w-full flex flex-col items-center">
            <!-- Vertical connector from parent down to horizontal line -->
            <div class="bg-border" style="width: 2px; height: 20px;"></div>
            <!-- Children container -->
            <table class="border-collapse" style="margin: 0 auto; border-spacing: 0;">
                <!-- Row 1: horizontal connectors + vertical tick -->
                <tr v-if="node.children.length > 1">
                    <td
                        v-for="(c, idx) in node.children"
                        :key="'h-' + c.id"
                        class="p-0"
                        style="padding: 0; border-spacing: 0;"
                    >
                        <div class="relative" style="height: 20px;">
                            <!-- Left half horizontal -->
                            <div
                                v-if="idx !== 0"
                                class="absolute top-0 left-0 bg-border"
                                style="width: 50%; height: 2px;"
                            ></div>
                            <!-- Right half horizontal -->
                            <div
                                v-if="idx !== node.children.length - 1"
                                class="absolute top-0 right-0 bg-border"
                                style="width: 50%; height: 2px;"
                            ></div>
                            <!-- Vertical tick down (connects to horizontal at top) -->
                            <div
                                class="absolute bg-border"
                                style="width: 2px; top: 0; bottom: 0; left: 50%; transform: translateX(-50%);"
                            ></div>
                        </div>
                    </td>
                </tr>
                <tr v-else>
                    <td class="p-0" style="padding: 0;">
                        <div style="height: 20px; display: flex; justify-content: center;">
                            <div class="bg-border" style="width: 2px; height: 100%;"></div>
                        </div>
                    </td>
                </tr>
                <!-- Row 2: child nodes -->
                <tr>
                    <td
                        v-for="c in node.children"
                        :key="'n-' + c.id"
                        class="p-0 align-top"
                        style="padding: 0 16px;"
                    >
                        <StructureNode
                            :node="c"
                            :collapsed="collapsed"
                            :toggle="toggle"
                            :members="members"
                            :can-manage="canManage"
                        />
                    </td>
                </tr>
            </table>
        </div>
    </div>
</template>
