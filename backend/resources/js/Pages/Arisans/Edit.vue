<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { ArrowLeft, Save, Loader2 } from 'lucide-vue-next';
import { useForm } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    arisan: Object,
});

const toast = useToast();

const formatDateForInput = (dateValue) => {
    if (!dateValue) return '';
    return new Date(dateValue).toISOString().split('T')[0];
};

const form = useForm({
    name: props.arisan.name,
    amount_per_month: props.arisan.amount_per_month,
    start_date: formatDateForInput(props.arisan.start_date),
    status: props.arisan.status,
});

const submit = () => {
    form.put(route('arisans.update', props.arisan.id), {
        onSuccess: (page) => {
            if (page.props.flash?.success) toast.success(page.props.flash.success);
        },
        onError: () => toast.error('Gagal memperbarui program arisan.'),
    });
};
</script>

<template>
    <Head title="Edit Program Arisan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Button variant="ghost" size="icon" as-child class="shrink-0 rounded-full hover:bg-muted">
                    <Link :href="route('arisans.show', arisan.id)">
                        <ArrowLeft class="w-5 h-5 text-muted-foreground" />
                    </Link>
                </Button>
                <div>
                    <h2 class="text-lg font-semibold leading-tight text-foreground">Edit Program Arisan</h2>
                    <p class="text-xs text-muted-foreground mt-0.5">{{ arisan.name }}</p>
                </div>
            </div>
        </template>

        <div class="py-6 sm:py-8 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <Card>
                <form @submit.prevent="submit">
                    <CardHeader>
                        <CardTitle>Rincian Program</CardTitle>
                    </CardHeader>
                    <CardContent class="grid gap-6">

                        <div class="grid sm:grid-cols-2 gap-4">
                            <div class="space-y-2 sm:col-span-2">
                                <Label for="name">Nama Kegiatan *</Label>
                                <Input id="name" v-model="form.name" required :disabled="form.processing" />
                                <p v-if="form.errors.name" class="text-xs text-destructive">{{ form.errors.name }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="amount_per_month">Iuran per Bulan (Rp) *</Label>
                                <Input id="amount_per_month" type="number" v-model="form.amount_per_month" required min="1" :disabled="form.processing" />
                                <p v-if="form.errors.amount_per_month" class="text-xs text-destructive">{{ form.errors.amount_per_month }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="start_date">Dimulai Pada *</Label>
                                <Input id="start_date" type="date" v-model="form.start_date" required :disabled="form.processing" />
                                <p v-if="form.errors.start_date" class="text-xs text-destructive">{{ form.errors.start_date }}</p>
                            </div>
                        </div>

                        <div class="space-y-2 pt-4 border-t">
                            <Label for="status">Status Program</Label>
                            <select id="status" v-model="form.status" :disabled="form.processing"
                                class="flex h-10 w-full rounded-md border border-input bg-background text-foreground px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                                <option value="active">Aktif</option>
                                <option value="completed">Selesai (Ditutup)</option>
                            </select>
                            <p class="text-[11px] text-muted-foreground">Ubah ke "Selesai" jika arisan sudah berakhir.</p>
                            <p v-if="form.errors.status" class="text-xs text-destructive">{{ form.errors.status }}</p>
                        </div>

                    </CardContent>
                    <CardFooter class="flex justify-end gap-3 border-t bg-muted/20 px-6 py-4">
                        <Button variant="outline" type="button" as-child :disabled="form.processing">
                            <Link :href="route('arisans.show', arisan.id)">Batal</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            <Loader2 v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                            <Save v-else class="w-4 h-4 mr-2" />
                            Simpan Perubahan
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
