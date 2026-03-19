<script setup>
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Sheet, SheetContent, SheetHeader, SheetTitle, SheetDescription,
} from '@/components/ui/sheet';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import {
    SlidersHorizontal, ListFilter, CalendarRange, Wallet, CreditCard, Tag, RotateCcw,
} from 'lucide-vue-next';

const props = defineProps({
    open: Boolean,
    filters: Object,
    types: Array,
    wallets: Array,
    statusOptions: Array,
    methodOptions: Array,
    periodOptions: Array,
});

const emit = defineEmits(['update:open', 'reset']);

// Helper to map empty string <-> "__all__" sentinel for radix-vue Select
const makeSelectModel = (key) => computed({
    get: () => props.filters[key] || '__all__',
    set: (val) => { props.filters[key] = val === '__all__' ? '' : val; },
});

const typeModel = makeSelectModel('contribution_type_id');
const statusModel = makeSelectModel('status');
const methodModel = makeSelectModel('payment_method');
const walletModel = makeSelectModel('wallet_id');
const periodModel = makeSelectModel('payment_period');
</script>

<template>
    <Sheet :open="open" @update:open="(val) => $emit('update:open', val)">
        <SheetContent side="right" class="w-[20rem] sm:w-[24rem] flex flex-col p-0">
            <!-- Header -->
            <div class="px-5 pt-5 pb-4 border-b">
                <SheetHeader>
                    <SheetTitle class="flex items-center gap-2 text-base">
                        <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                            <SlidersHorizontal class="w-4 h-4 text-primary" />
                        </div>
                        Filter Transaksi
                    </SheetTitle>
                    <SheetDescription class="mt-1 text-xs">Pilih kriteria untuk menyaring data pembayaran.</SheetDescription>
                </SheetHeader>
            </div>

            <!-- Filter Fields -->
            <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4">
                <!-- Jenis Iuran -->
                <div>
                    <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide flex items-center gap-1.5">
                        <Tag class="w-3 h-3" />
                        Jenis Iuran
                    </Label>
                    <Select v-model="typeModel">
                        <SelectTrigger class="mt-1.5 w-full">
                            <SelectValue placeholder="Semua jenis" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="__all__">Semua jenis</SelectItem>
                            <SelectItem v-for="t in types" :key="t.id" :value="t.id.toString()">{{ t.name }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Status -->
                <div>
                    <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide flex items-center gap-1.5">
                        <ListFilter class="w-3 h-3" />
                        Status
                    </Label>
                    <Select v-model="statusModel">
                        <SelectTrigger class="mt-1.5 w-full">
                            <SelectValue placeholder="Semua Status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="s in statusOptions" :key="s.value || '__all__'" :value="s.value || '__all__'">{{ s.label }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Metode Pembayaran -->
                <div>
                    <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide flex items-center gap-1.5">
                        <CreditCard class="w-3 h-3" />
                        Metode Pembayaran
                    </Label>
                    <Select v-model="methodModel">
                        <SelectTrigger class="mt-1.5 w-full">
                            <SelectValue placeholder="Semua Metode" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="m in methodOptions" :key="m.value || '__all__'" :value="m.value || '__all__'">{{ m.label }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Dompet -->
                <div v-if="wallets && wallets.length">
                    <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide flex items-center gap-1.5">
                        <Wallet class="w-3 h-3" />
                        Dompet
                    </Label>
                    <Select v-model="walletModel">
                        <SelectTrigger class="mt-1.5 w-full">
                            <SelectValue placeholder="Semua dompet" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="__all__">Semua dompet</SelectItem>
                            <SelectItem v-for="w in wallets" :key="w.id" :value="w.id.toString()">{{ w.name }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Periode -->
                <div v-if="periodOptions.length">
                    <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide flex items-center gap-1.5">
                        <CalendarRange class="w-3 h-3" />
                        Periode Pembayaran
                    </Label>
                    <Select v-model="periodModel">
                        <SelectTrigger class="mt-1.5 w-full">
                            <SelectValue placeholder="Semua Periode" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="__all__">Semua Periode</SelectItem>
                            <SelectItem v-for="p in periodOptions" :key="p.value" :value="p.value">{{ p.label }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Rentang Tanggal -->
                <div>
                    <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide flex items-center gap-1.5">
                        <CalendarRange class="w-3 h-3" />
                        Rentang Tanggal
                    </Label>
                    <div class="grid grid-cols-2 gap-2 mt-1.5">
                        <div>
                            <p class="text-[11px] text-muted-foreground mb-1">Dari</p>
                            <Input type="date" v-model="filters.start_date" />
                        </div>
                        <div>
                            <p class="text-[11px] text-muted-foreground mb-1">Sampai</p>
                            <Input type="date" v-model="filters.end_date" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-5 py-3 border-t flex items-center gap-2">
                <Button variant="outline" size="sm" class="flex-1" @click="$emit('update:open', false)">Tutup</Button>
                <Button variant="outline" size="sm" class="flex-1" @click="$emit('reset')">
                    <RotateCcw class="w-3.5 h-3.5 mr-1.5" />
                    Reset
                </Button>
            </div>
        </SheetContent>
    </Sheet>
</template>
