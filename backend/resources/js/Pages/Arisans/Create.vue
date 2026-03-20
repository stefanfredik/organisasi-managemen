<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { ArrowLeft, Save, Loader2, Users, Search, CheckCircle2 } from 'lucide-vue-next';
import { useForm } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';
import { ref, computed } from 'vue';

const props = defineProps({
    allMembers: Array,
});

const toast = useToast();
const searchQuery = ref('');

const form = useForm({
    name: '',
    amount_per_month: '',
    start_date: new Date().toISOString().split('T')[0],
    participant_ids: [],
});

const filteredMembers = computed(() => {
    if (!searchQuery.value) return props.allMembers || [];
    const q = searchQuery.value.toLowerCase();
    return (props.allMembers || []).filter(m => m.full_name.toLowerCase().includes(q));
});

const toggleSelection = (id) => {
    const idx = form.participant_ids.indexOf(id);
    if (idx > -1) form.participant_ids.splice(idx, 1);
    else form.participant_ids.push(id);
};

const selectAll = () => {
    if (form.participant_ids.length === props.allMembers.length) {
        form.participant_ids = [];
    } else {
        form.participant_ids = props.allMembers.map(m => m.id);
    }
};

const submit = () => {
    form.post(route('arisans.store'), {
        onSuccess: (page) => {
            if (page.props.flash?.success) toast.success(page.props.flash.success);
        },
        onError: () => toast.error('Gagal membuat program arisan.'),
    });
};
</script>

<template>
    <Head title="Buat Program Arisan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Button variant="ghost" size="icon" as-child class="shrink-0 rounded-full hover:bg-muted">
                    <Link :href="route('arisans.index')">
                        <ArrowLeft class="w-5 h-5 text-muted-foreground" />
                    </Link>
                </Button>
                <div>
                    <h2 class="text-lg font-semibold leading-tight text-foreground">Program Arisan Baru</h2>
                </div>
            </div>
        </template>

        <div class="py-6 sm:py-8 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <Card>
                <form @submit.prevent="submit">
                    <CardHeader>
                        <CardTitle>Rincian Program</CardTitle>
                        <CardDescription>Masukkan detail dasar kelompok arisan yang ingin dibuat.</CardDescription>
                    </CardHeader>
                    <CardContent class="grid gap-6">

                        <div class="grid sm:grid-cols-2 gap-4">
                            <div class="space-y-2 sm:col-span-2">
                                <Label for="name">Nama Kegiatan *</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    placeholder="Cth: Arisan Bulanan Angkatan 2024"
                                    required
                                    :disabled="form.processing"
                                />
                                <p v-if="form.errors.name" class="text-xs text-destructive">{{ form.errors.name }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="amount_per_month">Iuran per Bulan (Rp) *</Label>
                                <Input
                                    id="amount_per_month"
                                    type="number"
                                    v-model="form.amount_per_month"
                                    placeholder="50000"
                                    required
                                    min="1"
                                    :disabled="form.processing"
                                />
                                <p v-if="form.errors.amount_per_month" class="text-xs text-destructive">{{ form.errors.amount_per_month }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="start_date">Dimulai Pada *</Label>
                                <Input
                                    id="start_date"
                                    type="date"
                                    v-model="form.start_date"
                                    required
                                    :disabled="form.processing"
                                />
                                <p v-if="form.errors.start_date" class="text-xs text-destructive">{{ form.errors.start_date }}</p>
                            </div>
                        </div>

                        <!-- Pilih Peserta -->
                        <div class="space-y-3 pt-4 border-t">
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <div>
                                    <Label class="text-sm">Tetapkan Peserta Arisan</Label>
                                    <p class="text-[11px] text-muted-foreground mt-0.5">Pilih siapa saja yang berpartisipasi sejak awal.</p>
                                </div>
                                <Button type="button" variant="outline" size="sm" @click="selectAll">
                                    <Users class="w-3.5 h-3.5 mr-1.5" />
                                    {{ form.participant_ids.length === allMembers?.length ? 'Batal Semua' : 'Pilih Semua' }}
                                </Button>
                            </div>

                            <!-- Search -->
                            <div class="relative">
                                <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                                <Input v-model="searchQuery" placeholder="Cari anggota..." class="pl-9" />
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 max-h-[280px] overflow-y-auto p-1 border rounded-lg bg-muted/10">
                                <div v-for="member in filteredMembers" :key="member.id"
                                     @click="toggleSelection(member.id)"
                                     class="cursor-pointer border rounded-lg p-2.5 text-sm flex items-center gap-2 transition-all select-none"
                                     :class="form.participant_ids.includes(member.id)
                                        ? 'bg-primary text-primary-foreground border-primary shadow-sm'
                                        : 'bg-background hover:border-primary/40 hover:bg-muted/30'">
                                    <CheckCircle2 v-if="form.participant_ids.includes(member.id)" class="w-4 h-4 shrink-0" />
                                    <div v-else class="w-4 h-4 rounded-full border-2 border-muted-foreground/30 shrink-0"></div>
                                    <span class="truncate">{{ member.full_name }}</span>
                                </div>
                                <div v-if="filteredMembers.length === 0" class="col-span-full py-6 text-center text-sm text-muted-foreground">
                                    {{ searchQuery ? 'Tidak ditemukan.' : 'Tidak ada data anggota aktif.' }}
                                </div>
                            </div>

                            <p class="text-xs font-medium">
                                <span class="text-primary font-bold">{{ form.participant_ids.length }}</span>
                                <span class="text-muted-foreground"> dari {{ allMembers?.length || 0 }} anggota terpilih</span>
                            </p>
                            <p v-if="form.errors.participant_ids" class="text-xs text-destructive">{{ form.errors.participant_ids }}</p>
                        </div>

                    </CardContent>
                    <CardFooter class="flex justify-end gap-3 border-t bg-muted/20 px-6 py-4">
                        <Button variant="outline" type="button" as-child :disabled="form.processing">
                            <Link :href="route('arisans.index')">Batal</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            <Loader2 v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                            <Save v-else class="w-4 h-4 mr-2" />
                            Simpan Program
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
