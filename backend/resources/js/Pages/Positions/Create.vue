<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { MoveLeft, Briefcase, Save, Loader2 } from "lucide-vue-next";
import { useToast } from "@/composables/useToast";

const toast = useToast();

const form = useForm({
    name: "",
    code: "",
});

const submit = () => {
    form.post(route("positions.store"), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success("Posisi jabatan baru berhasil ditambahkan!");
        },
        onError: () => {
            toast.error("Terdapat kesalahan pada isian form.");
        }
    });
};
</script>

<template>
    <Head title="Tambah Posisi" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Button variant="ghost" size="icon" class="h-8 w-8 -ml-2" as-child>
                    <Link :href="route('positions.index')">
                        <MoveLeft class="h-4 w-4" />
                    </Link>
                </Button>
                <h2 class="text-lg font-semibold leading-tight text-foreground flex items-center gap-2">
                    <Briefcase class="h-5 w-5 text-muted-foreground" />
                    Tambah Posisi
                </h2>
            </div>
        </template>

        <div class="py-6 sm:py-8 min-h-[calc(100vh-4rem)]">
            <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="bg-card rounded-xl border shadow-sm overflow-hidden">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        
                        <div class="space-y-4">
                            <!-- Name -->
                            <div class="space-y-2">
                                <Label for="name" class="text-sm font-semibold">
                                    Nama Posisi <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    placeholder="Contoh: Ketua Umum"
                                    :disabled="form.processing"
                                    :class="{ 'border-destructive focus-visible:ring-destructive': form.errors.name }"
                                />
                                <p v-if="form.errors.name" class="text-xs text-destructive mt-1">{{ form.errors.name }}</p>
                            </div>

                            <!-- Code -->
                            <div class="space-y-2">
                                <Label for="code" class="text-sm font-semibold">
                                    Kode Internal <span class="text-destructive">*</span>
                                </Label>
                                <Input
                                    id="code"
                                    v-model="form.code"
                                    type="text"
                                    placeholder="Contoh: ketua_umum"
                                    :disabled="form.processing"
                                    :class="{ 'border-destructive focus-visible:ring-destructive': form.errors.code }"
                                />
                                <p class="text-[11px] text-muted-foreground mt-1">Kode digunakan secara internal oleh sistem (huruf kecil, tanpa spasi).</p>
                                <p v-if="form.errors.code" class="text-xs text-destructive">{{ form.errors.code }}</p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t">
                            <Button type="button" variant="outline" :disabled="form.processing" as-child>
                                <Link :href="route('positions.index')">Batal</Link>
                            </Button>
                            <Button type="submit" :disabled="form.processing" class="min-w-[120px]">
                                <Loader2 v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                                <Save v-else class="w-4 h-4 mr-2" />
                                Simpan
                            </Button>
                        </div>

                     </form>
                </div>
                
            </div>
        </div>
    </AuthenticatedLayout>
</template>
