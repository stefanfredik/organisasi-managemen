<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Badge } from '@/components/ui/badge';
import {
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription,
} from '@/components/ui/dialog';
import { Coins, CheckCircle, Clock, ChevronRight, Users, AlertCircle, Loader2, User } from 'lucide-vue-next';
import { ref } from 'vue';
import axios from 'axios';

defineProps({
    types: Array,
});

const formatCurrency = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val);

// Members by status modal
const showMembersModal = ref(false);
const membersLoading = ref(false);
const membersList = ref([]);
const membersCategory = ref('');
const membersTypeName = ref('');

const categoryConfig = {
    paid: { label: 'Lunas', icon: CheckCircle, color: 'text-green-600', bg: 'bg-green-500/10' },
    unpaid: { label: 'Belum Bayar', icon: Users, color: 'text-amber-600', bg: 'bg-amber-500/10' },
    arrears: { label: 'Menunggak', icon: AlertCircle, color: 'text-red-600', bg: 'bg-red-500/10' },
};

const fetchMembers = async (typeId, typeName, status) => {
    membersCategory.value = status;
    membersTypeName.value = typeName;
    membersList.value = [];
    showMembersModal.value = true;
    membersLoading.value = true;
    try {
        const res = await axios.get(route('contributions.monitoring.members-by-status', typeId), {
            params: { status },
        });
        membersList.value = res.data;
    } catch (e) {
        console.error(e);
    } finally {
        membersLoading.value = false;
    }
};
</script>

<template>
    <Head title="Monitoring Iuran" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-foreground">
                Monitoring & Verifikasi Iuran
            </h2>
        </template>

        <div class="py-4">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-4">

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="type in types"
                        :key="type.id"
                        class="group relative bg-card rounded-xl border shadow-sm overflow-hidden"
                    >
                        <!-- Gradient accent top -->
                        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-primary to-primary/60 opacity-0 group-hover:opacity-100 transition-opacity duration-300" />

                        <Link
                            :href="route('contributions.monitoring.dashboard', type.id)"
                            class="block p-5 pb-3 hover:bg-muted/30 transition-colors"
                        >
                            <!-- Header -->
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 flex items-center justify-center bg-primary/10 text-primary rounded-xl group-hover:bg-primary group-hover:text-primary-foreground transition-colors duration-300 shrink-0">
                                        <Coins class="w-5 h-5" />
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="font-semibold text-foreground text-sm leading-tight group-hover:text-primary transition-colors truncate">
                                            {{ type.name }}
                                        </h3>
                                        <Badge variant="secondary" class="mt-1 text-[10px] uppercase tracking-wider">
                                            {{ type.period === 'once' ? 'Sekali Bayar' : type.period }}
                                        </Badge>
                                    </div>
                                </div>
                                <ChevronRight class="w-4 h-4 text-muted-foreground opacity-0 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all duration-300 shrink-0" />
                            </div>

                            <!-- Collected amount -->
                            <div class="bg-muted/50 rounded-lg p-3.5 mb-3">
                                <div class="flex justify-between items-center mb-1.5">
                                    <p class="text-[10px] font-semibold text-muted-foreground uppercase tracking-widest">Terkumpul</p>
                                    <span class="text-xs font-bold text-primary">
                                        {{ ((type.current_period_paid || 0) / (type.target_count || 1) * 100).toFixed(0) }}%
                                    </span>
                                </div>
                                <p class="text-xl font-extrabold text-foreground tracking-tight mb-2.5">
                                    {{ formatCurrency(type.collected_amount || 0).replace('Rp', '').trim() }}
                                    <span class="text-xs font-semibold text-muted-foreground">IDR</span>
                                </p>
                                <div class="w-full bg-muted rounded-full h-1.5 overflow-hidden">
                                    <div
                                        class="bg-gradient-to-r from-primary to-primary/70 h-1.5 rounded-full transition-all duration-500"
                                        :style="{ width: `${Math.min(((type.current_period_paid || 0) / (type.target_count || 1) * 100), 100)}%` }"
                                    />
                                </div>
                            </div>
                        </Link>

                        <!-- Stats list (compact vertical with percentage bars) -->
                        <div class="px-5 pb-4 pt-0 space-y-0.5">
                            <button @click.stop="fetchMembers(type.id, type.name, 'paid')" class="w-full flex items-center gap-2 py-1 rounded hover:bg-green-500/5 transition-colors cursor-pointer group/item">
                                <span class="w-1 h-3.5 rounded-full bg-green-500 shrink-0" />
                                <span class="text-[11px] text-muted-foreground group-hover/item:text-green-600 transition-colors">Lunas</span>
                                <div class="flex-1 bg-muted rounded-full h-1 overflow-hidden mx-1">
                                    <div class="bg-green-500 h-1 rounded-full transition-all duration-500" :style="{ width: `${Math.min(((type.lunas_count || 0) / (type.target_count || 1)) * 100, 100)}%` }" />
                                </div>
                                <span class="text-[11px] font-bold text-green-600 tabular-nums shrink-0">{{ type.lunas_count || 0 }}</span>
                            </button>
                            <button @click.stop="fetchMembers(type.id, type.name, 'unpaid')" class="w-full flex items-center gap-2 py-1 rounded hover:bg-amber-500/5 transition-colors cursor-pointer group/item">
                                <span class="w-1 h-3.5 rounded-full bg-amber-500 shrink-0" />
                                <span class="text-[11px] text-muted-foreground group-hover/item:text-amber-600 transition-colors">Belum</span>
                                <div class="flex-1 bg-muted rounded-full h-1 overflow-hidden mx-1">
                                    <div class="bg-amber-500 h-1 rounded-full transition-all duration-500" :style="{ width: `${Math.min(((type.belum_count || 0) / (type.target_count || 1)) * 100, 100)}%` }" />
                                </div>
                                <span class="text-[11px] font-bold text-amber-600 tabular-nums shrink-0">{{ type.belum_count || 0 }}</span>
                            </button>
                            <button @click.stop="fetchMembers(type.id, type.name, 'arrears')" class="w-full flex items-center gap-2 py-1 rounded hover:bg-red-500/5 transition-colors cursor-pointer group/item">
                                <span class="w-1 h-3.5 rounded-full bg-red-500 shrink-0" />
                                <span class="text-[11px] text-muted-foreground group-hover/item:text-red-600 transition-colors">Tunggak</span>
                                <div class="flex-1 bg-muted rounded-full h-1 overflow-hidden mx-1">
                                    <div class="bg-red-500 h-1 rounded-full transition-all duration-500" :style="{ width: `${Math.min(((type.tunggak_count || 0) / (type.target_count || 1)) * 100, 100)}%` }" />
                                </div>
                                <span class="text-[11px] font-bold text-red-600 tabular-nums shrink-0">{{ type.tunggak_count || 0 }}</span>
                            </button>
                            <div class="flex items-center gap-2 py-1">
                                <span class="w-1 h-3.5 rounded-full shrink-0" :class="(type.pending_count || 0) > 0 ? 'bg-blue-500' : 'bg-muted-foreground/30'" />
                                <span class="text-[11px] text-muted-foreground">Pending</span>
                                <div class="flex-1 bg-muted rounded-full h-1 overflow-hidden mx-1">
                                    <div class="h-1 rounded-full transition-all duration-500" :class="(type.pending_count || 0) > 0 ? 'bg-blue-500' : ''" :style="{ width: `${Math.min(((type.pending_count || 0) / (type.target_count || 1)) * 100, 100)}%` }" />
                                </div>
                                <span class="text-[11px] font-bold tabular-nums shrink-0" :class="(type.pending_count || 0) > 0 ? 'text-blue-600' : 'text-muted-foreground'">{{ type.pending_count || 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="types.length === 0" class="text-center py-12">
                    <Coins class="w-10 h-10 text-muted-foreground/50 mx-auto mb-3" />
                    <p class="text-sm text-muted-foreground">Belum ada jenis iuran yang aktif.</p>
                </div>

            </div>
        </div>

        <!-- Members List Modal -->
        <Dialog v-model:open="showMembersModal">
            <DialogContent class="max-w-md max-h-[80vh] flex flex-col">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <div
                            class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0"
                            :class="categoryConfig[membersCategory]?.bg"
                        >
                            <component
                                :is="categoryConfig[membersCategory]?.icon"
                                class="w-4 h-4"
                                :class="categoryConfig[membersCategory]?.color"
                            />
                        </div>
                        Anggota {{ categoryConfig[membersCategory]?.label }}
                    </DialogTitle>
                    <DialogDescription>
                        Daftar anggota dengan status <strong>{{ categoryConfig[membersCategory]?.label?.toLowerCase() }}</strong> untuk iuran {{ membersTypeName }}.
                    </DialogDescription>
                </DialogHeader>

                <!-- Loading -->
                <div v-if="membersLoading" class="py-8 flex flex-col items-center gap-2 text-muted-foreground">
                    <Loader2 class="w-5 h-5 animate-spin" />
                    <p class="text-xs">Memuat data...</p>
                </div>

                <!-- Members List -->
                <div v-else-if="membersList.length > 0" class="flex-1 overflow-y-auto -mx-1 px-1">
                    <div class="space-y-1">
                        <div
                            v-for="(member, idx) in membersList"
                            :key="member.id"
                            class="flex items-center gap-3 p-2.5 rounded-lg hover:bg-muted/50 transition-colors"
                        >
                            <div class="w-8 h-8 rounded-full bg-primary/10 border flex items-center justify-center shrink-0">
                                <span class="text-xs font-bold text-primary">{{ (member.full_name || '?').charAt(0).toUpperCase() }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-foreground truncate">{{ member.full_name }}</p>
                                <p class="text-[10px] text-muted-foreground uppercase tracking-wider">{{ member.member_code }}</p>
                            </div>
                            <span class="text-xs text-muted-foreground tabular-nums shrink-0">{{ idx + 1 }}</span>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="py-8 text-center">
                    <div class="w-10 h-10 rounded-xl bg-muted flex items-center justify-center mx-auto mb-2">
                        <User class="w-5 h-5 text-muted-foreground" />
                    </div>
                    <p class="text-sm text-muted-foreground">Tidak ada anggota dalam kategori ini.</p>
                </div>

                <!-- Footer count -->
                <div v-if="!membersLoading && membersList.length > 0" class="pt-3 border-t mt-2">
                    <p class="text-xs text-muted-foreground text-center">
                        Total: <span class="font-bold text-foreground">{{ membersList.length }}</span> anggota
                    </p>
                </div>
            </DialogContent>
        </Dialog>
    </AuthenticatedLayout>
</template>
