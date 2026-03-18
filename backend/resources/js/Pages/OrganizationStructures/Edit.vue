<script setup>
import { useForm, Head, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ImageUpload from "@/Components/ImageUpload.vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card, CardContent } from "@/components/ui/card";
import { Checkbox } from "@/components/ui/checkbox";
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from "@/components/ui/select";

const props = defineProps({
    structure: Object,
    members: Array,
    parents: Array,
});

const form = useForm({
    member_id: props.structure.member_id || "",
    position_name: props.structure.position_name || "",
    level: props.structure.level ?? 0,
    parent_id: props.structure.parent_id || "",
    sort_order: props.structure.sort_order ?? 0,
    period_start: props.structure.period_start,
    period_end: props.structure.period_end,
    is_active: props.structure.is_active,
    photo: null,
});

const currentPhoto = props.structure.photo_path ? `/storage/${props.structure.photo_path}` : null;

const submit = () => {
    form.put(route("organization-structures.update", props.structure.id));
};
</script>

<template>
    <Head title="Edit Struktur Organisasi" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-foreground leading-tight">
                Edit Struktur Organisasi
            </h2>
        </template>

        <div class="py-6 sm:py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <Card>
                    <CardContent class="p-6">
                        <form @submit.prevent="submit">
                            <!-- Posisi & Anggota -->
                            <div class="grid grid-cols-1 gap-4 sm:gap-6 md:grid-cols-2 mb-4">
                                <div>
                                    <Label for="position_name">Nama Posisi</Label>
                                    <Input id="position_name" type="text" class="mt-1 block w-full" v-model="form.position_name" required />
                                    <p v-if="form.errors.position_name" class="mt-2 text-sm text-destructive">{{ form.errors.position_name }}</p>
                                </div>
                                <div>
                                    <Label for="member_id">Anggota (Opsional)</Label>
                                    <Select v-model="form.member_id">
                                        <SelectTrigger class="mt-1 w-full">
                                            <SelectValue placeholder="Pilih anggota" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="">-</SelectItem>
                                            <SelectItem v-for="m in members" :key="m.id" :value="m.id.toString()">
                                                {{ m.full_name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="form.errors.member_id" class="mt-2 text-sm text-destructive">{{ form.errors.member_id }}</p>
                                </div>
                            </div>

                            <!-- Hierarchy -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <Label for="level">Level</Label>
                                    <Input id="level" type="number" class="mt-1 block w-full" v-model="form.level" min="0" required />
                                    <p v-if="form.errors.level" class="mt-2 text-sm text-destructive">{{ form.errors.level }}</p>
                                </div>
                                <div>
                                    <Label for="parent_id">Induk (Opsional)</Label>
                                    <Select v-model="form.parent_id">
                                        <SelectTrigger class="mt-1 w-full">
                                            <SelectValue placeholder="Pilih induk" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="">-</SelectItem>
                                            <SelectItem v-for="p in parents" :key="p.id" :value="p.id.toString()">
                                                {{ p.position_name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="form.errors.parent_id" class="mt-2 text-sm text-destructive">{{ form.errors.parent_id }}</p>
                                </div>
                                <div>
                                    <Label for="sort_order">Urutan</Label>
                                    <Input id="sort_order" type="number" class="mt-1 block w-full" v-model="form.sort_order" min="0" required />
                                    <p v-if="form.errors.sort_order" class="mt-2 text-sm text-destructive">{{ form.errors.sort_order }}</p>
                                </div>
                            </div>

                            <!-- Period -->
                            <div class="grid grid-cols-1 gap-4 sm:gap-6 md:grid-cols-2 mb-4">
                                <div>
                                    <Label for="period_start">Tahun Mulai</Label>
                                    <Input id="period_start" type="number" class="mt-1 block w-full" v-model="form.period_start" required />
                                    <p v-if="form.errors.period_start" class="mt-2 text-sm text-destructive">{{ form.errors.period_start }}</p>
                                </div>
                                <div>
                                    <Label for="period_end">Tahun Selesai (Opsional)</Label>
                                    <Input id="period_end" type="number" class="mt-1 block w-full" v-model="form.period_end" />
                                    <p v-if="form.errors.period_end" class="mt-2 text-sm text-destructive">{{ form.errors.period_end }}</p>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="mb-4 flex items-center space-x-2">
                                <Checkbox id="is_active" :checked="form.is_active" @update:checked="(val) => form.is_active = val" />
                                <Label for="is_active" class="text-sm font-normal">Aktif</Label>
                                <p v-if="form.errors.is_active" class="mt-2 text-sm text-destructive">{{ form.errors.is_active }}</p>
                            </div>

                            <!-- Photo -->
                            <div class="mb-4">
                                <Label>Foto (Opsional)</Label>
                                <div class="mt-1">
                                    <ImageUpload
                                        v-model="form.photo"
                                        :current-image="currentPhoto"
                                        label="Upload Foto"
                                        :error="form.errors.photo"
                                    />
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-4 gap-4">
                                <Button variant="ghost" as-child>
                                    <Link :href="route('organization-structures.index')">Batal</Link>
                                </Button>
                                <Button type="submit" :disabled="form.processing">Simpan Perubahan</Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

