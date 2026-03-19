<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import { ArrowLeft, Loader2 } from 'lucide-vue-next';

const props = defineProps({
    donation: Object,
    isEdit: Boolean,
});

const form = useForm({
    program_name: props.donation?.program_name || '',
    description: props.donation?.description || '',
    target_amount: props.donation?.target_amount || '',
    start_date: props.donation?.start_date || '',
    end_date: props.donation?.end_date || '',
    status: props.donation?.status || 'active',
    is_public: props.donation ? !!props.donation.is_public : true,
});

const submit = () => {
    if (props.isEdit) {
        form.put(route('donations.update', props.donation.id));
    } else {
        form.post(route('donations.store'));
    }
};
</script>

<template>
    <Head :title="isEdit ? 'Edit Program Donasi' : 'Buat Program Donasi'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2.5">
                <Button variant="ghost" size="icon" class="h-8 w-8 shrink-0" as-child>
                    <Link :href="isEdit ? route('donations.show', donation.id) : route('donations.index')">
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                </Button>
                <h2 class="text-lg font-semibold leading-tight text-foreground">
                    {{ isEdit ? 'Edit Program Donasi' : 'Buat Program Donasi' }}
                </h2>
            </div>
        </template>

        <div class="py-4 sm:py-6">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <div class="bg-card rounded-xl border overflow-hidden">
                    <div class="h-1 w-full bg-primary" />
                    <form @submit.prevent="submit" class="p-5 space-y-4">
                        <div>
                            <Label class="text-xs">Nama Program Donasi</Label>
                            <Input
                                v-model="form.program_name"
                                class="mt-1"
                                required
                                autofocus
                                placeholder="Contoh: Donasi Anak Yatim 2026"
                            />
                            <p v-if="form.errors.program_name" class="mt-1 text-xs text-destructive">{{ form.errors.program_name }}</p>
                        </div>

                        <div>
                            <Label class="text-xs">Deskripsi</Label>
                            <Textarea
                                v-model="form.description"
                                rows="4"
                                class="mt-1"
                                placeholder="Jelaskan tujuan dan detail penggalangan dana..."
                            />
                            <p v-if="form.errors.description" class="mt-1 text-xs text-destructive">{{ form.errors.description }}</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <Label class="text-xs">Target Dana (Rp)</Label>
                                <Input
                                    v-model="form.target_amount"
                                    type="number"
                                    class="mt-1"
                                    required
                                    min="0"
                                    placeholder="10000000"
                                />
                                <p v-if="form.errors.target_amount" class="mt-1 text-xs text-destructive">{{ form.errors.target_amount }}</p>
                            </div>
                            <div class="flex items-end pb-1">
                                <div class="flex items-center gap-2">
                                    <Checkbox :checked="form.is_public" @update:checked="(v) => form.is_public = v" />
                                    <Label class="text-sm text-muted-foreground">Terlihat di Halaman Publik</Label>
                                </div>
                                <p v-if="form.errors.is_public" class="mt-1 text-xs text-destructive">{{ form.errors.is_public }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <Label class="text-xs">Tanggal Mulai</Label>
                                <Input v-model="form.start_date" type="date" class="mt-1" required />
                                <p v-if="form.errors.start_date" class="mt-1 text-xs text-destructive">{{ form.errors.start_date }}</p>
                            </div>
                            <div>
                                <Label class="text-xs">Tanggal Selesai (Opsional)</Label>
                                <Input v-model="form.end_date" type="date" class="mt-1" />
                                <p v-if="form.errors.end_date" class="mt-1 text-xs text-destructive">{{ form.errors.end_date }}</p>
                            </div>
                        </div>

                        <div v-if="isEdit">
                            <Label class="text-xs">Status Program</Label>
                            <Select v-model="form.status">
                                <SelectTrigger class="mt-1 w-full">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="active">Aktif</SelectItem>
                                    <SelectItem value="completed">Selesai</SelectItem>
                                    <SelectItem value="cancelled">Dibatalkan</SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.status" class="mt-1 text-xs text-destructive">{{ form.errors.status }}</p>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-4 border-t">
                            <Button variant="outline" size="sm" as-child>
                                <Link :href="isEdit ? route('donations.show', donation.id) : route('donations.index')">Batal</Link>
                            </Button>
                            <Button size="sm" type="submit" :disabled="form.processing">
                                <Loader2 v-if="form.processing" class="w-4 h-4 mr-1 animate-spin" />
                                {{ isEdit ? 'Simpan Perubahan' : 'Buat Program' }}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
