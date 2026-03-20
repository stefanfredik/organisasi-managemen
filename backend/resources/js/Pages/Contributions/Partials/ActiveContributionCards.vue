<script setup>
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { CheckCircle, Banknote, TrendingUp, ArrowRight, AlertCircle } from 'lucide-vue-next';

const props = defineProps({
    types: Array,
    userPosition: String,
    formatCurrency: Function,
});

const emit = defineEmits(['pay']);

const getPeriodLabel = (period) => {
    switch (period) {
        case 'once': return 'Sekali Bayar';
        case 'monthly': return 'Bulanan';
        case 'weekly': return 'Mingguan';
        case 'yearly': return 'Tahunan';
        default: return period;
    }
};

const getProgressColor = (pct) => {
    if (pct >= 100) return 'bg-green-500';
    if (pct >= 50) return 'bg-primary';
    return 'bg-amber-500';
};

const unpaidTypes = computed(() => {
    if (!props.types) return [];
    return props.types.filter(t => !t.user_progress || t.user_progress.percentage < 100);
});

const paidTypes = computed(() => {
    if (!props.types) return [];
    return props.types.filter(t => t.user_progress?.percentage >= 100);
});
</script>

<template>
    <div v-if="types && types.length > 0" class="space-y-2 sm:space-y-3">

        <!-- ===================== Mobile ===================== -->
        <div class="sm:hidden space-y-2">
            <!-- Unpaid: prominent cards -->
            <div
                v-for="type in unpaidTypes"
                :key="'m-' + type.id"
                class="bg-card rounded-xl border border-primary/30 overflow-hidden shadow-sm"
                @click="$emit('pay', type)"
            >
                <!-- Accent bar -->
                <div class="h-1 w-full bg-primary" />
                <div class="p-3 space-y-2.5">
                    <!-- Top row: name + badge -->
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <h4 class="text-sm font-semibold text-foreground leading-tight">{{ type.name }}</h4>
                            <p class="text-[11px] text-muted-foreground mt-0.5">{{ getPeriodLabel(type.period) }}</p>
                        </div>
                        <Badge
                            v-if="type.user_progress && type.user_progress.percentage < 100"
                            class="shrink-0 text-[10px] h-5 px-1.5 bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border-0"
                        >
                            <AlertCircle class="w-3 h-3 mr-0.5" />
                            Belum Lunas
                        </Badge>
                    </div>

                    <!-- Amount + progress -->
                    <div class="flex items-end justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-lg font-bold text-foreground tabular-nums leading-none">{{ formatCurrency(type.amount) }}</p>
                            <div v-if="type.user_progress" class="mt-2">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 bg-muted rounded-full h-1.5 overflow-hidden">
                                        <div
                                            :class="['h-full rounded-full transition-all', getProgressColor(type.user_progress.percentage)]"
                                            :style="{ width: `${Math.min(type.user_progress.percentage, 100)}%` }"
                                        />
                                    </div>
                                    <span class="text-[11px] font-bold text-primary shrink-0 tabular-nums">{{ type.user_progress.percentage }}%</span>
                                </div>
                                <p v-if="type.user_progress.text" class="text-[10px] text-muted-foreground mt-0.5">{{ type.user_progress.text }}</p>
                            </div>
                        </div>
                        <!-- CTA -->
                        <Button size="sm" class="shrink-0 h-8 px-3 text-xs gap-1.5" @click.stop="$emit('pay', type)">
                            <Banknote class="w-3.5 h-3.5" />
                            Bayar
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Paid: compact list -->
            <div v-if="paidTypes.length > 0" class="space-y-1.5">
                <div
                    v-for="type in paidTypes"
                    :key="'mp-' + type.id"
                    class="bg-card rounded-lg border px-3 py-2 flex items-center gap-2.5"
                >
                    <div class="w-5 h-5 rounded-full bg-green-100 dark:bg-green-950/30 flex items-center justify-center shrink-0">
                        <CheckCircle class="w-3 h-3 text-green-600 dark:text-green-400" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-foreground truncate">{{ type.name }}</p>
                    </div>
                    <span class="text-[10px] font-semibold text-green-600 dark:text-green-400 shrink-0">Lunas</span>
                </div>
            </div>
        </div>

        <!-- ===================== Desktop ===================== -->
        <div class="hidden sm:block space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-foreground">Iuran Aktif</h3>
                <p class="text-xs text-muted-foreground">{{ types.length }} jenis</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
                <div
                    v-for="type in types"
                    :key="'d-' + type.id"
                    class="group relative bg-card rounded-xl border overflow-hidden transition-all duration-200 hover:shadow-md hover:border-primary/20"
                >
                    <div
                        class="h-1 w-full"
                        :class="type.user_progress?.percentage >= 100 ? 'bg-green-500' : 'bg-primary'"
                    />
                    <div class="p-4 space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0 flex-1">
                                <h4 class="text-sm font-semibold text-foreground leading-tight line-clamp-1 group-hover:text-primary transition-colors">
                                    {{ type.name }}
                                </h4>
                                <p v-if="type.description" class="text-xs text-muted-foreground mt-0.5 line-clamp-1">
                                    {{ type.description }}
                                </p>
                            </div>
                            <Badge variant="outline" class="text-[10px] shrink-0 font-medium">
                                {{ getPeriodLabel(type.period) }}
                            </Badge>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">Nominal</p>
                            <p class="text-lg font-bold text-foreground tabular-nums tracking-tight">
                                {{ formatCurrency(type.amount) }}
                            </p>
                        </div>
                        <div v-if="type.user_progress" class="space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-muted-foreground flex items-center gap-1">
                                    <TrendingUp class="w-3 h-3" />
                                    Kelunasan
                                </span>
                                <span class="font-bold" :class="type.user_progress.percentage >= 100 ? 'text-green-600 dark:text-green-400' : 'text-primary'">
                                    {{ type.user_progress.percentage }}%
                                </span>
                            </div>
                            <div class="w-full bg-muted rounded-full h-1.5 overflow-hidden">
                                <div
                                    :class="['h-full rounded-full transition-all duration-700 ease-out', getProgressColor(type.user_progress.percentage)]"
                                    :style="{ width: `${Math.min(type.user_progress.percentage, 100)}%` }"
                                />
                            </div>
                            <p class="text-[11px] text-muted-foreground">{{ type.user_progress.text }}</p>
                        </div>
                    </div>
                    <div class="px-4 pb-4">
                        <div
                            v-if="type.user_progress?.percentage >= 100"
                            class="flex items-center justify-center gap-1.5 py-2 rounded-lg bg-green-50 dark:bg-green-950/30 text-green-700 dark:text-green-400 text-xs font-semibold"
                        >
                            <CheckCircle class="w-3.5 h-3.5" />
                            Sudah Lunas
                        </div>
                        <Button
                            v-else
                            variant="default"
                            size="sm"
                            class="w-full group/btn"
                            @click.stop="$emit('pay', type)"
                        >
                            <Banknote class="w-4 h-4 mr-1.5" />
                            {{ userPosition === 'anggota' ? 'Bayar Sekarang' : 'Input Data' }}
                            <ArrowRight class="w-3.5 h-3.5 ml-auto opacity-0 -translate-x-1 group-hover/btn:opacity-100 group-hover/btn:translate-x-0 transition-all" />
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
