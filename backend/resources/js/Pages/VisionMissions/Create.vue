<script setup>
import { reactive } from "vue";
import { useForm, Head, Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputError from "@/Components/InputError.vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from "@/components/ui/select";
import { ArrowLeft, Plus, Trash2, Save, Lightbulb, Target } from "lucide-vue-next";

const form = useForm({
    vision: "",
    missions: [""],
    period_start: new Date().getFullYear(),
    period_end: "",
    status: "active",
});

const clientErrors = reactive({
    vision: "",
    period_start: "",
    missions: "",
});

const touched = reactive({
    vision: false,
    period_start: false,
});

const validateVision = () => {
    if (!form.vision.trim()) {
        clientErrors.vision = "Visi harus diisi.";
    } else {
        clientErrors.vision = "";
    }
};

const validatePeriodStart = () => {
    if (!form.period_start) {
        clientErrors.period_start = "Tahun mulai harus diisi.";
    } else {
        clientErrors.period_start = "";
    }
};

const validateMissions = () => {
    const hasEmpty = form.missions.some((m) => !m.trim());
    if (form.missions.length === 0 || hasEmpty) {
        clientErrors.missions = "Setiap butir misi harus diisi.";
    } else {
        clientErrors.missions = "";
    }
};

const validateAll = () => {
    touched.vision = true;
    touched.period_start = true;
    validateVision();
    validatePeriodStart();
    validateMissions();
    return !clientErrors.vision && !clientErrors.period_start && !clientErrors.missions;
};

const addMission = () => {
    form.missions.push("");
};

const removeMission = (index) => {
    form.missions.splice(index, 1);
};

const submit = () => {
    if (!validateAll()) return;
    form.post(route("vision-missions.store"));
};
</script>

<template>
    <Head title="Tambah Visi & Misi" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('vision-missions.index')" class="shrink-0 p-1.5 rounded-lg text-muted-foreground hover:text-foreground hover:bg-muted transition-colors">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <h2 class="text-lg font-semibold leading-tight text-foreground">Tambah Visi & Misi</h2>
            </div>
        </template>

        <div class="py-4 sm:py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-2xl mx-auto">
                    <form @submit.prevent="submit" class="bg-card border rounded-xl overflow-hidden">
                        <div class="p-4 sm:p-6 space-y-5">

                            <!-- Periode & Status -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <Label for="period_start">
                                        Tahun Mulai
                                        <span class="text-destructive">*</span>
                                    </Label>
                                    <Input
                                        id="period_start"
                                        type="number"
                                        class="mt-1.5 block w-full"
                                        v-model="form.period_start"
                                        placeholder="2024"
                                        @blur="touched.period_start = true; validatePeriodStart()"
                                        @input="touched.period_start && validatePeriodStart()"
                                    />
                                    <InputError class="mt-1.5" :message="clientErrors.period_start || form.errors.period_start" />
                                </div>
                                <div>
                                    <Label for="period_end">Tahun Selesai</Label>
                                    <p class="text-[10px] text-muted-foreground">Opsional</p>
                                    <Input
                                        id="period_end"
                                        type="number"
                                        class="mt-1 block w-full"
                                        v-model="form.period_end"
                                        placeholder="2029"
                                    />
                                    <InputError class="mt-1.5" :message="form.errors.period_end" />
                                </div>
                                <div>
                                    <Label for="status">
                                        Status
                                        <span class="text-destructive">*</span>
                                    </Label>
                                    <Select v-model="form.status">
                                        <SelectTrigger class="mt-1.5 w-full">
                                            <SelectValue placeholder="Pilih status" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="active">Aktif</SelectItem>
                                            <SelectItem value="inactive">Tidak Aktif</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError class="mt-1.5" :message="form.errors.status" />
                                </div>
                            </div>

                            <!-- Visi -->
                            <div>
                                <Label for="vision" class="flex items-center gap-1.5">
                                    <Lightbulb class="w-3.5 h-3.5 text-primary" />
                                    Visi
                                    <span class="text-destructive">*</span>
                                </Label>
                                <Textarea
                                    id="vision"
                                    class="mt-1.5 block w-full"
                                    v-model="form.vision"
                                    rows="3"
                                    placeholder="Tuliskan visi organisasi..."
                                    @blur="touched.vision = true; validateVision()"
                                    @input="touched.vision && validateVision()"
                                />
                                <InputError class="mt-1.5" :message="clientErrors.vision || form.errors.vision" />
                            </div>

                            <!-- Misi -->
                            <div>
                                <Label class="flex items-center gap-1.5">
                                    <Target class="w-3.5 h-3.5 text-primary" />
                                    Misi
                                    <span class="text-destructive">*</span>
                                </Label>
                                <p class="text-xs text-muted-foreground mt-0.5 mb-2">Tambahkan butir-butir misi organisasi.</p>

                                <div class="space-y-2">
                                    <div
                                        v-for="(mission, index) in form.missions"
                                        :key="index"
                                        class="flex items-center gap-2"
                                    >
                                        <span class="shrink-0 w-7 h-7 rounded-lg bg-muted text-muted-foreground text-xs font-bold flex items-center justify-center">
                                            {{ index + 1 }}
                                        </span>
                                        <Input
                                            type="text"
                                            class="block w-full"
                                            v-model="form.missions[index]"
                                            :placeholder="`Butir misi ke-${index + 1}`"
                                        />
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon"
                                            @click="removeMission(index)"
                                            v-if="form.missions.length > 1"
                                            class="text-muted-foreground hover:text-destructive shrink-0"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>

                                <InputError class="mt-1.5" :message="clientErrors.missions || form.errors.missions" />

                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    @click="addMission"
                                    class="mt-2"
                                >
                                    <Plus class="h-3.5 w-3.5 mr-1" />
                                    Tambah Misi
                                </Button>
                            </div>

                        </div>

                        <!-- Footer -->
                        <div class="flex items-center justify-end gap-3 px-4 sm:px-6 py-4 border-t bg-muted/30">
                            <Button variant="outline" as-child>
                                <Link :href="route('vision-missions.index')">Batal</Link>
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                <Save class="w-3.5 h-3.5 mr-1.5" />
                                {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
