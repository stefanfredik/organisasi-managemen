<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent } from '@/components/ui/card';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import { Plus, Trash2 } from 'lucide-vue-next';

const props = defineProps({
    visionMission: Object,
});

const form = useForm({
    vision: props.visionMission.vision,
    missions: props.visionMission.missions || [''],
    period_start: props.visionMission.period_start,
    period_end: props.visionMission.period_end,
    status: props.visionMission.status,
});

const addMission = () => {
    form.missions.push('');
};

const removeMission = (index) => {
    form.missions.splice(index, 1);
};

const submit = () => {
    form.put(route('vision-missions.update', props.visionMission.id));
};
</script>

<template>
    <Head title="Edit Visi & Misi" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-foreground leading-tight">
                Edit Visi & Misi
            </h2>
        </template>

        <div class="py-6 sm:py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <Card>
                    <CardContent class="p-6">
                        <form @submit.prevent="submit">
                            <!-- Period -->
                            <div class="grid grid-cols-1 gap-4 sm:gap-6 md:grid-cols-2 mb-4">
                                <div>
                                    <Label for="period_start">Tahun Mulai</Label>
                                    <Input
                                        id="period_start"
                                        type="number"
                                        class="mt-1 block w-full"
                                        v-model="form.period_start"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.period_start" />
                                </div>
                                <div>
                                    <Label for="period_end">Tahun Selesai (Opsional)</Label>
                                    <Input
                                        id="period_end"
                                        type="number"
                                        class="mt-1 block w-full"
                                        v-model="form.period_end"
                                    />
                                    <InputError class="mt-2" :message="form.errors.period_end" />
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="mb-4">
                                <Label for="status">Status</Label>
                                <Select v-model="form.status">
                                    <SelectTrigger class="mt-1 w-full">
                                        <SelectValue placeholder="Pilih status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="active">Aktif</SelectItem>
                                        <SelectItem value="inactive">Tidak Aktif</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError class="mt-2" :message="form.errors.status" />
                            </div>

                            <!-- Vision -->
                            <div class="mb-4">
                                <Label for="vision">Visi</Label>
                                <Textarea
                                    id="vision"
                                    class="mt-1 block w-full"
                                    v-model="form.vision"
                                    rows="3"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.vision" />
                            </div>

                            <!-- Missions -->
                            <div class="mb-4">
                                <Label>Misi</Label>
                                <div v-for="(mission, index) in form.missions" :key="index" class="flex items-center mt-2 gap-2">
                                    <Input
                                        type="text"
                                        class="block w-full"
                                        v-model="form.missions[index]"
                                        placeholder="Masukkan butir misi..."
                                        required
                                    />
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        @click="removeMission(index)"
                                        v-if="form.missions.length > 1"
                                        class="text-destructive hover:text-destructive shrink-0"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                                <InputError class="mt-2" :message="form.errors.missions" />
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    @click="addMission"
                                    class="mt-2 text-primary"
                                >
                                    <Plus class="h-4 w-4 mr-1" />
                                    Tambah Misi
                                </Button>
                            </div>

                            <div class="flex items-center justify-end mt-4 gap-4">
                                <Button variant="ghost" as-child>
                                    <Link :href="route('vision-missions.index')">Batal</Link>
                                </Button>
                                <Button type="submit" :disabled="form.processing">
                                    Simpan Perubahan
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
