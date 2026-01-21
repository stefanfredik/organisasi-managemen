<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
    roles: Object,
    statuses: Object,
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    role: props.user.role,
    status: props.user.status,
});

const submit = () => {
    form.put(route('users.update', props.user.id), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head :title="`Edit User: ${user.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('users.index')" class="p-2 bg-white rounded-xl shadow-sm border border-slate-100 text-slate-400 hover:text-indigo-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <div>
                    <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Edit Pengguna</h2>
                    <p class="text-slate-500 text-sm font-medium mt-1">Perbarui informasi akun dan hak akses: <span class="text-indigo-600 font-bold">{{ user.name }}</span></p>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
                    <form @submit.prevent="submit" class="p-8 lg:p-12 space-y-8">
                        <!-- Information Section -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="col-span-2">
                                <label class="text-[11px] font-black uppercase tracking-widest text-slate-400 mb-2 block ml-1">Nama Lengkap</label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    class="w-full bg-slate-50 border-slate-200 rounded-2xl py-4 px-6 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all font-medium"
                                    placeholder="Masukkan nama lengkap..."
                                    required
                                >
                                <div v-if="form.errors.name" class="text-rose-500 text-xs mt-2 ml-1 font-bold">{{ form.errors.name }}</div>
                            </div>

                            <div class="col-span-2">
                                <label class="text-[11px] font-black uppercase tracking-widest text-slate-400 mb-2 block ml-1">Alamat Email</label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="w-full bg-slate-50 border-slate-200 rounded-2xl py-4 px-6 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all font-medium"
                                    placeholder="nama@email.com"
                                    required
                                >
                                <div v-if="form.errors.email" class="text-rose-500 text-xs mt-2 ml-1 font-bold">{{ form.errors.email }}</div>
                            </div>

                            <div>
                                <label class="text-[11px] font-black uppercase tracking-widest text-slate-400 mb-2 block ml-1">Role Akses</label>
                                <select
                                    v-model="form.role"
                                    class="w-full bg-slate-50 border-slate-200 rounded-2xl py-4 px-6 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all font-bold"
                                    required
                                >
                                    <option v-for="(label, value) in roles" :key="value" :value="value">{{ label }}</option>
                                </select>
                                <div v-if="form.errors.role" class="text-rose-500 text-xs mt-2 ml-1 font-bold">{{ form.errors.role }}</div>
                            </div>

                            <div>
                                <label class="text-[11px] font-black uppercase tracking-widest text-slate-400 mb-2 block ml-1">Status Akun</label>
                                <select
                                    v-model="form.status"
                                    class="w-full bg-slate-50 border-slate-200 rounded-2xl py-4 px-6 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all font-bold"
                                    required
                                >
                                    <option v-for="(label, value) in statuses" :key="value" :value="value">{{ label }}</option>
                                </select>
                                <div v-if="form.errors.status" class="text-rose-500 text-xs mt-2 ml-1 font-bold">{{ form.errors.status }}</div>
                            </div>

                            <div class="col-span-2 pt-6">
                                <div class="bg-amber-50 rounded-2xl p-6 border border-amber-100 mb-6">
                                    <h4 class="text-xs font-black text-amber-700 uppercase tracking-widest mb-1 flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                        Keamanan
                                    </h4>
                                    <p class="text-amber-600 text-[11px] font-medium leading-relaxed">Isi bagian password di bawah ini hanya jika Anda ingin merubah password pengguna tersebut. Jika tidak, biarkan kosong.</p>
                                </div>
                                <hr class="border-slate-100">
                            </div>

                            <div>
                                <label class="text-[11px] font-black uppercase tracking-widest text-slate-400 mb-2 block ml-1">Password Baru (Opsional)</label>
                                <input
                                    v-model="form.password"
                                    type="password"
                                    class="w-full bg-slate-50 border-slate-200 rounded-2xl py-4 px-6 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all font-medium"
                                    placeholder="••••••••"
                                >
                                <div v-if="form.errors.password" class="text-rose-500 text-xs mt-2 ml-1 font-bold">{{ form.errors.password }}</div>
                            </div>

                            <div>
                                <label class="text-[11px] font-black uppercase tracking-widest text-slate-400 mb-2 block ml-1">Konfirmasi Password Baru</label>
                                <input
                                    v-model="form.password_confirmation"
                                    type="password"
                                    class="w-full bg-slate-50 border-slate-200 rounded-2xl py-4 px-6 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all font-medium"
                                    placeholder="••••••••"
                                >
                            </div>
                        </div>

                        <div class="pt-8 flex items-center justify-end gap-4">
                            <Link
                                :href="route('users.index')"
                                class="px-8 py-4 text-sm font-bold text-slate-400 hover:text-slate-600 transition-colors uppercase tracking-widest"
                            >
                                Batal
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-10 py-4 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-black rounded-2xl shadow-xl shadow-indigo-100 transition-all active:scale-95 disabled:opacity-50 uppercase tracking-[0.2em]"
                            >
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
