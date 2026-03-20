<script setup>
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { CheckCircle, Banknote, TrendingUp, ArrowRight } from 'lucide-vue-next';

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
</script>

<template>
    <div v-if="types && types.length > 0" class="space-y-2 sm:space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-xs sm:text-sm font-semibold text-foreground">Iuran Aktif</h3>
            <p class="text-[10px] sm:text-xs text-muted-foreground">{{ types.length }} jenis</p>
        </div>

        <!-- Mobile: Compact list -->
        <div class="sm:hidden space-y-2">
            <div
                v-for="type in types"
                :key="'m-' + type.id"
                class="bg-card rounded-lg border overflow-hidden"
            >
                <div class="flex items-center gap-3 p-2.5">
                    <!-- Color indicator -->
                    <div
                        class="w-1 self-stretch rounded-full shrink-0"
                        :class="type.user_progress?.percentage >= 100 ? 'bg-green-500' : 'bg-primary'"
                    />
                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <h4 class="text-xs font-semibold text-foreground truncate">{{ type.name }}</h4>
                            <span class="text-xs font-bold text-foreground tabular-nums shrink-0">{{ formatCurrency(type.amount) }}</span>
                        </div>
                        <div class="flex items-center gap-2 mt-1">
                            <div v-if="type.user_progress" class="flex-1 flex items-center gap-1.5">
                                <div class="flex-1 bg-muted rounded-full h-1 overflow-hidden">
                                    <div
                                        :class="['h-full rounded-full', getProgressColor(type.user_progress.percentage)]"
                                        :style="{ width: `${Math.min(type.user_progress.percentage, 100)}%` }"
                                    />
                                </div>
                                <span class="text-[10px] font-bold shrink-0" :class="type.user_progress.percentage >= 100 ? 'text-green-600 dark:text-green-400' : 'text-primary'">
                                    {{ type.user_progress.percentage }}%
                                </span>
                            </div>
                            <Badge v-else variant="outline" class="text-[9px] h-4 px-1.5">{{ getPeriodLabel(type.period) }}</Badge>
                        </div>
                    </div>
                    <!-- Action -->
                    <div class="shrink-0">
                        <div v-if="type.user_progress?.percentage >= 100" class="w-6 h-6 rounded-full bg-green-100 dark:bg-green-950/30 flex items-center justify-center">
                            <CheckCircle class="w-3.5 h-3.5 text-green-600 dark:text-green-400" />
                        </div>
                        <Button v-else size="sm" class="h-7 text-[11px] px-2.5" @click.stop="$emit('pay', type)">
                            <Banknote class="w-3.5 h-3.5 mr-1" />
                            Bayar
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Desktop: Grid cards -->
        <div class="hidden sm:grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
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
</template>
