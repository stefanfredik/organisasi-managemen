<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

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

const submit = () => {
    form.post(route('users.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Tambah User" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('users.index')" class="p-2 bg-card rounded-xl shadow-sm border border text-muted-foreground hover:text-primary transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <div>
                    <h2 class="text-2xl font-black text-foreground uppercase tracking-tight">Tambah User Baru</h2>
                    <p class="text-muted-foreground text-sm font-medium mt-1">Daftarkan pengguna baru ke dalam sistem.</p>
                </div>
            </div>
        </template>

        <div class="py-6 sm:py-8">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-card rounded-[2.5rem] shadow-xl shadow-muted/50 border border overflow-hidden">
                    <form @submit.prevent="submit" class="p-8 lg:p-12 space-y-8">
                        <!-- Information Section -->
                        <div class="grid grid-cols-1 gap-4 sm:gap-6 md:grid-cols-2">
                            <div class="col-span-2">
                                <label class="text-[11px] font-black uppercase tracking-widest text-muted-foreground mb-2 block ml-1">Nama Lengkap</label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    class="w-full bg-muted border rounded-2xl py-4 px-6 focus:ring-2 focus:ring-ring focus:border-ring transition-all font-medium"
                                    placeholder="Masukkan nama lengkap..."
                                    required
                                >
                                <div v-if="form.errors.name" class="text-rose-500 text-xs mt-2 ml-1 font-bold">{{ form.errors.name }}</div>
                            </div>

                            <div class="col-span-2">
                                <label class="text-[11px] font-black uppercase tracking-widest text-muted-foreground mb-2 block ml-1">Alamat Email</label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="w-full bg-muted border rounded-2xl py-4 px-6 focus:ring-2 focus:ring-ring focus:border-ring transition-all font-medium"
                                    placeholder="nama@email.com"
                                    required
                                >
                                <div v-if="form.errors.email" class="text-rose-500 text-xs mt-2 ml-1 font-bold">{{ form.errors.email }}</div>
                            </div>

                            <div>
                                <label class="text-[11px] font-black uppercase tracking-widest text-muted-foreground mb-2 block ml-1">Role Akses</label>
                                <select
                                    v-model="form.role"
                                    class="w-full bg-muted border rounded-2xl py-4 px-6 focus:ring-2 focus:ring-ring focus:border-ring transition-all font-bold"
                                    required
                                >
                                    <option v-for="(label, value) in roles" :key="value" :value="value">{{ label }}</option>
                                </select>
                                <div v-if="form.errors.role" class="text-rose-500 text-xs mt-2 ml-1 font-bold">{{ form.errors.role }}</div>
                            </div>

                            <div>
                                <label class="text-[11px] font-black uppercase tracking-widest text-muted-foreground mb-2 block ml-1">Status Akun</label>
                                <select
                                    v-model="form.status"
                                    class="w-full bg-muted border rounded-2xl py-4 px-6 focus:ring-2 focus:ring-ring focus:border-ring transition-all font-bold"
                                    required
                                >
                                    <option v-for="(label, value) in statuses" :key="value" :value="value">{{ label }}</option>
                                </select>
                                <div v-if="form.errors.status" class="text-rose-500 text-xs mt-2 ml-1 font-bold">{{ form.errors.status }}</div>
                            </div>

                            <div class="col-span-2 pt-4">
                                <hr class="border">
                            </div>

                            <div>
                                <label class="text-[11px] font-black uppercase tracking-widest text-muted-foreground mb-2 block ml-1">Password</label>
                                <input
                                    v-model="form.password"
                                    type="password"
                                    class="w-full bg-muted border rounded-2xl py-4 px-6 focus:ring-2 focus:ring-ring focus:border-ring transition-all font-medium"
                                    placeholder="••••••••"
                                    required
                                >
                                <div v-if="form.errors.password" class="text-rose-500 text-xs mt-2 ml-1 font-bold">{{ form.errors.password }}</div>
                            </div>

                            <div>
                                <label class="text-[11px] font-black uppercase tracking-widest text-muted-foreground mb-2 block ml-1">Konfirmasi Password</label>
                                <input
                                    v-model="form.password_confirmation"
                                    type="password"
                                    class="w-full bg-muted border rounded-2xl py-4 px-6 focus:ring-2 focus:ring-ring focus:border-ring transition-all font-medium"
                                    placeholder="••••••••"
                                    required
                                >
                            </div>
                        </div>

                        <div class="pt-8 flex items-center justify-end gap-4">
                            <Link
                                :href="route('users.index')"
                                class="px-8 py-4 text-sm font-bold text-muted-foreground hover:text-muted-foreground transition-colors uppercase tracking-widest"
                            >
                                Batal
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-10 py-4 bg-primary hover:bg-primary/90 text-white text-sm font-black rounded-2xl shadow-xl shadow-sm transition-all active:scale-95 disabled:opacity-50 uppercase tracking-[0.2em]"
                            >
                                Simpan User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
