<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/Components/InputError.vue';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import { ArrowLeft, Loader2 } from 'lucide-vue-next';

const props = defineProps({
    roles: Object,
    statuses: Object,
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'anggota',
    status: 'active',
});

const saving = ref(false);

const submit = () => {
    saving.value = true;
    form.post(route('users.store'), {
        onFinish: () => {
            saving.value = false;
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <Head title="Tambah User" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2.5">
                <Button variant="ghost" size="icon" class="h-8 w-8 shrink-0" as-child>
                    <Link :href="route('users.index')">
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                </Button>
                <h2 class="text-lg font-semibold leading-tight text-foreground">Tambah User</h2>
            </div>
        </template>

        <div class="py-3 sm:py-6">
            <div class="max-w-3xl mx-auto px-3 sm:px-6 lg:px-8">
                <div class="bg-card rounded-xl border overflow-hidden">
                    <div class="h-1 w-full bg-primary" />

                    <form @submit.prevent="submit" class="p-4 sm:p-6 space-y-4 sm:space-y-5">
                        <!-- Name -->
                        <div>
                            <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Nama Lengkap</Label>
                            <Input v-model="form.name" type="text" class="mt-1.5" placeholder="Masukkan nama lengkap..." required />
                            <InputError class="mt-1" :message="form.errors.name" />
                        </div>

                        <!-- Email -->
                        <div>
                            <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Alamat Email</Label>
                            <Input v-model="form.email" type="email" class="mt-1.5" placeholder="nama@email.com" required />
                            <InputError class="mt-1" :message="form.errors.email" />
                        </div>

                        <!-- Role & Status -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                            <div>
                                <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Role Akses</Label>
                                <Select v-model="form.role">
                                    <SelectTrigger class="mt-1.5 w-full">
                                        <SelectValue placeholder="Pilih role" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="(label, value) in roles" :key="value" :value="value">{{ label }}</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError class="mt-1" :message="form.errors.role" />
                            </div>
                            <div>
                                <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Status Akun</Label>
                                <Select v-model="form.status">
                                    <SelectTrigger class="mt-1.5 w-full">
                                        <SelectValue placeholder="Pilih status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="(label, value) in statuses" :key="value" :value="value">{{ label }}</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError class="mt-1" :message="form.errors.status" />
                            </div>
                        </div>

                        <div class="border-t" />

                        <!-- Password -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                            <div>
                                <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Password</Label>
                                <Input v-model="form.password" type="password" class="mt-1.5" placeholder="********" required />
                                <InputError class="mt-1" :message="form.errors.password" />
                            </div>
                            <div>
                                <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Konfirmasi Password</Label>
                                <Input v-model="form.password_confirmation" type="password" class="mt-1.5" placeholder="********" required />
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="flex items-center justify-end gap-2 pt-2 border-t">
                            <Button variant="outline" size="sm" as-child>
                                <Link :href="route('users.index')">Batal</Link>
                            </Button>
                            <Button size="sm" type="submit" :disabled="saving || !form.name || !form.email">
                                <Loader2 v-if="saving" class="w-4 h-4 mr-1 animate-spin" />
                                {{ saving ? 'Menyimpan...' : 'Simpan' }}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
