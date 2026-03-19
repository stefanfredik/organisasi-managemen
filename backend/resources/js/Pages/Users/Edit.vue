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
import { ArrowLeft, Loader2, AlertTriangle } from 'lucide-vue-next';

const props = defineProps({
    user: Object,
    roles: Object,
    memberId: Number,
});

const form = useForm({
    password: '',
    password_confirmation: '',
    role: props.user.role,
});

const saving = ref(false);

const submit = () => {
    saving.value = true;
    form.put(route('users.update', props.user.id), {
        onFinish: () => {
            saving.value = false;
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <Head :title="`Edit User: ${user.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2.5">
                <Button variant="ghost" size="icon" class="h-8 w-8 shrink-0" as-child>
                    <Link :href="route('users.index')">
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                </Button>
                <h2 class="text-lg font-semibold leading-tight text-foreground">Edit User</h2>
            </div>
        </template>

        <div class="py-3 sm:py-6">
            <div class="max-w-3xl mx-auto px-3 sm:px-6 lg:px-8">
                <div class="bg-card rounded-xl border overflow-hidden">
                    <div class="h-1 w-full bg-primary" />

                    <form @submit.prevent="submit" class="p-4 sm:p-6 space-y-4 sm:space-y-5">
                        <!-- User Info (read-only) -->
                        <div class="bg-muted/50 rounded-lg border p-3 sm:p-4">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                                    <span class="text-primary font-semibold text-sm">{{ user.name.charAt(0).toUpperCase() }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-foreground truncate">{{ user.name }}</p>
                                    <p class="text-xs text-muted-foreground truncate">{{ user.email }}</p>
                                </div>
                            </div>
                            <p class="text-[10px] sm:text-xs text-muted-foreground">
                                Nama dan email dikelola dari menu Anggota.
                                <Link v-if="memberId" :href="`/members/${memberId}/edit`" class="text-primary font-medium hover:underline">Edit Anggota</Link>
                            </p>
                        </div>

                        <!-- Role -->
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
                                <div class="mt-1.5 flex h-9 w-full items-center rounded-md border bg-muted/50 px-3 text-sm text-muted-foreground capitalize">
                                    {{ user.status }}
                                </div>
                                <p class="text-[10px] sm:text-xs text-muted-foreground mt-1">Status akun mengikuti status Anggota.</p>
                            </div>
                        </div>

                        <div class="border-t" />

                        <!-- Password Warning -->
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800 p-3">
                            <div class="flex items-center gap-2 mb-1">
                                <AlertTriangle class="w-4 h-4 text-yellow-600 dark:text-yellow-400 shrink-0" />
                                <p class="text-xs font-semibold text-yellow-700 dark:text-yellow-400 uppercase tracking-wide">Keamanan</p>
                            </div>
                            <p class="text-[10px] sm:text-xs text-yellow-600 dark:text-yellow-300">Isi bagian password di bawah ini hanya jika ingin mengubah password. Jika tidak, biarkan kosong.</p>
                        </div>

                        <!-- Password -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                            <div>
                                <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Password Baru (Opsional)</Label>
                                <Input v-model="form.password" type="password" class="mt-1.5" placeholder="********" />
                                <InputError class="mt-1" :message="form.errors.password" />
                            </div>
                            <div>
                                <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Konfirmasi Password Baru</Label>
                                <Input v-model="form.password_confirmation" type="password" class="mt-1.5" placeholder="********" />
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="flex items-center justify-end gap-2 pt-2 border-t">
                            <Button variant="outline" size="sm" as-child>
                                <Link :href="route('users.index')">Batal</Link>
                            </Button>
                            <Button size="sm" type="submit" :disabled="saving">
                                <Loader2 v-if="saving" class="w-4 h-4 mr-1 animate-spin" />
                                {{ saving ? 'Menyimpan...' : 'Simpan Perubahan' }}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
