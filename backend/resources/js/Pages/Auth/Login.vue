<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import InputError from '@/Components/InputError.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Lock, Mail, Eye, EyeOff, Loader2 } from 'lucide-vue-next';
import { ref, computed } from 'vue';

defineProps({
    canResetPassword: { type: Boolean },
    status: { type: String },
});

const page = usePage();
const appName = computed(() => page.props.appSettings?.name || 'Organisasi');
const appDesc = computed(() => page.props.appSettings?.description || 'Sistem Manajemen Organisasi');

const showPassword = ref(false);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Masuk" />

    <div class="min-h-screen flex">
        <!-- Left: Branding Panel (hidden mobile) -->
        <div class="hidden lg:flex lg:w-1/2 relative bg-primary overflow-hidden">
            <!-- Pattern overlay -->
            <div class="absolute inset-0 opacity-10">
                <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                            <circle cx="1" cy="1" r="1" fill="currentColor" class="text-white" />
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#grid)" />
                </svg>
            </div>

            <!-- Decorative circles -->
            <div class="absolute -top-20 -left-20 w-80 h-80 rounded-full bg-white/5" />
            <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-white/5" />
            <div class="absolute top-1/3 right-10 w-40 h-40 rounded-full bg-white/5" />

            <!-- Content -->
            <div class="relative z-10 flex flex-col justify-center px-12 xl:px-16 w-full">
                <div class="max-w-md">
                    <div class="w-14 h-14 rounded-2xl bg-white/15 backdrop-blur-sm flex items-center justify-center mb-8 overflow-hidden">
                        <ApplicationLogo class="h-10 w-10 fill-current text-white" />
                    </div>

                    <h1 class="text-3xl xl:text-4xl font-bold text-white leading-tight mb-4">
                        {{ appName }}
                    </h1>

                    <p class="text-base text-primary-foreground/70 leading-relaxed mb-10">
                        {{ appDesc }}
                    </p>

                    <!-- Feature highlights -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <span class="text-sm text-white/80">Kelola data anggota secara terpusat</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <span class="text-sm text-white/80">Pantau keuangan dengan transparan</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <span class="text-sm text-white/80">Koordinasi kegiatan lebih mudah</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Login Form -->
        <div class="w-full lg:w-1/2 flex flex-col bg-background min-h-screen lg:min-h-0">
            <!-- Mobile header with primary accent -->
            <div class="lg:hidden bg-primary px-6 pt-safe-top">
                <div class="flex items-center gap-3 py-5">
                    <div class="w-10 h-10 rounded-xl bg-white/15 backdrop-blur-sm flex items-center justify-center shrink-0 overflow-hidden">
                        <ApplicationLogo class="h-7 w-7 fill-current text-white" />
                    </div>
                    <div class="min-w-0">
                        <h1 class="text-base font-bold text-white truncate">{{ appName }}</h1>
                        <p class="text-xs text-primary-foreground/70 truncate">Sistem Manajemen</p>
                    </div>
                </div>
            </div>

            <!-- Form container -->
            <div class="flex-1 flex flex-col justify-center px-5 py-6 sm:px-8 sm:py-8 lg:items-center">
                <div class="w-full max-w-sm lg:mx-auto">
                    <!-- Heading -->
                    <div class="mb-6 sm:mb-8">
                        <h2 class="text-xl sm:text-2xl font-bold text-foreground">Masuk</h2>
                        <p class="text-xs sm:text-sm text-muted-foreground mt-1">Masukkan email dan password untuk melanjutkan.</p>
                    </div>

                    <!-- Status message -->
                    <div
                        v-if="status"
                        class="mb-4 sm:mb-5 rounded-lg border border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-950/50 px-3 py-2.5 sm:px-4 sm:py-3"
                    >
                        <p class="text-xs sm:text-sm text-green-700 dark:text-green-300">{{ status }}</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-4 sm:space-y-5">
                        <!-- Email -->
                        <div class="space-y-1.5 sm:space-y-2">
                            <Label for="email" class="text-sm font-medium">Email</Label>
                            <div class="relative">
                                <Mail class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none" />
                                <Input
                                    id="email"
                                    type="email"
                                    class="pl-10 h-10 sm:h-11 text-sm"
                                    v-model="form.email"
                                    placeholder="nama@email.com"
                                    required
                                    autofocus
                                    autocomplete="username"
                                />
                            </div>
                            <InputError :message="form.errors.email" />
                        </div>

                        <!-- Password -->
                        <div class="space-y-1.5 sm:space-y-2">
                            <div class="flex items-center justify-between">
                                <Label for="password" class="text-sm font-medium">Password</Label>
                                <Link
                                    v-if="canResetPassword"
                                    :href="route('password.request')"
                                    class="text-xs text-primary hover:text-primary/80 transition-colors"
                                >
                                    Lupa password?
                                </Link>
                            </div>
                            <div class="relative">
                                <Lock class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none" />
                                <Input
                                    id="password"
                                    :type="showPassword ? 'text' : 'password'"
                                    class="pl-10 pr-10 h-10 sm:h-11 text-sm"
                                    v-model="form.password"
                                    placeholder="Masukkan password"
                                    required
                                    autocomplete="current-password"
                                />
                                <button
                                    type="button"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
                                    @click="showPassword = !showPassword"
                                    tabindex="-1"
                                >
                                    <EyeOff v-if="showPassword" class="w-4 h-4" />
                                    <Eye v-else class="w-4 h-4" />
                                </button>
                            </div>
                            <InputError :message="form.errors.password" />
                        </div>

                        <!-- Remember me -->
                        <div class="flex items-center gap-2.5">
                            <Checkbox
                                id="remember"
                                :checked="form.remember"
                                @update:checked="form.remember = $event"
                            />
                            <Label for="remember" class="text-sm text-muted-foreground font-normal cursor-pointer">
                                Ingat saya
                            </Label>
                        </div>

                        <!-- Submit -->
                        <Button
                            type="submit"
                            class="w-full h-10 sm:h-11"
                            :disabled="form.processing"
                        >
                            <Loader2 v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                            {{ form.processing ? 'Memproses...' : 'Masuk' }}
                        </Button>
                    </form>

                    <!-- Footer -->
                    <p class="mt-6 sm:mt-8 text-center text-[10px] sm:text-xs text-muted-foreground">
                        &copy; {{ new Date().getFullYear() }} {{ appName }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
