<script setup>
import { ref, computed } from "vue";
import { Head, useForm, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
    Building2, Coins, Hash, Globe, Settings, Loader2, Check,
    Upload, X, Image as ImageIcon, Palette,
} from "lucide-vue-next";
import { getThemes, applyTheme } from "@/composables/useTheme";

const props = defineProps({
    settings: Object,
});

const groupKeys = computed(() => Object.keys(props.settings));
const activeTab = ref(groupKeys.value[0] || "general");

// Prepare form data
const initialData = { settings: [] };
Object.values(props.settings).forEach(group => {
    group.forEach(setting => {
        initialData.settings.push({
            key: setting.key,
            value: setting.value,
            description: setting.description,
            group: setting.group,
            type: setting.type,
        });
    });
});

const form = useForm(initialData);

// Image file refs
const imageFiles = ref({});
const imagePreviews = ref({});
const imageRemovals = ref({});

const handleImageSelect = (key, event) => {
    const file = event.target.files[0];
    if (!file) return;
    imageFiles.value[key] = file;
    imagePreviews.value[key] = URL.createObjectURL(file);
    imageRemovals.value[key] = false;
};

const removeImage = (key) => {
    imageFiles.value[key] = null;
    imagePreviews.value[key] = null;
    imageRemovals.value[key] = true;
};

const cancelImageChange = (key) => {
    imageFiles.value[key] = null;
    imagePreviews.value[key] = null;
    imageRemovals.value[key] = false;
};

const getImagePreview = (setting) => {
    if (imagePreviews.value[setting.key]) return imagePreviews.value[setting.key];
    if (imageRemovals.value[setting.key]) return null;
    if (setting.value) return `/storage/${setting.value}`;
    return null;
};

const saving = ref(false);
const submit = () => {
    saving.value = true;

    const formData = new FormData();

    // Add regular settings
    form.settings.forEach((setting, index) => {
        formData.append(`settings[${index}][key]`, setting.key);
        formData.append(`settings[${index}][value]`, setting.value ?? '');
    });

    // Add image files
    Object.entries(imageFiles.value).forEach(([key, file]) => {
        if (file) formData.append(key, file);
    });

    // Add image removal flags
    Object.entries(imageRemovals.value).forEach(([key, removed]) => {
        if (removed) formData.append(`remove_${key}`, '1');
    });

    router.post(route("settings.update"), formData, {
        preserveScroll: true,
        forceFormData: true,
        onFinish: () => { saving.value = false; },
    });
};

const getGroupConfig = (group) => {
    const config = {
        general: { label: "Umum", icon: Building2, desc: "Informasi dasar organisasi, logo, dan kontak" },
        financial: { label: "Keuangan", icon: Coins, desc: "Rekening bank, e-wallet, QRIS, dan instruksi pembayaran" },
        social: { label: "Media Sosial", icon: Hash, desc: "Link media sosial organisasi" },
        portal: { label: "Portal Publik", icon: Globe, desc: "Pengaturan tampilan & SEO portal publik" },
        system: { label: "Sistem", icon: Settings, desc: "Konfigurasi fitur & perilaku sistem" },
    };
    return config[group] || { label: group, icon: Settings, desc: "" };
};

// ===== Readable label mapping per key =====
const labelMap = {
    // General
    organization_name: "Nama Organisasi",
    organization_short_name: "Nama Singkat",
    organization_logo: "Logo Organisasi",
    organization_favicon: "Favicon",
    organization_type: "Jenis Organisasi",
    organization_founded_date: "Tanggal Berdiri",
    organization_registration_number: "Nomor SK/Registrasi",
    organization_email: "Email",
    organization_phone: "Nomor Telepon",
    organization_address: "Alamat",
    organization_city: "Kota / Kabupaten",
    organization_province: "Provinsi",
    organization_postal_code: "Kode Pos",
    organization_description: "Deskripsi Organisasi",
    organization_motto: "Motto / Slogan",
    organization_npwp: "NPWP",

    // Financial
    currency_symbol: "Simbol Mata Uang",
    currency_code: "Kode Mata Uang",
    bank_name: "Nama Bank",
    bank_account_number: "Nomor Rekening",
    bank_account_name: "Nama Pemilik Rekening",
    bank_branch: "Cabang Bank",
    ewallet_provider: "Provider E-Wallet",
    ewallet_number: "Nomor E-Wallet",
    ewallet_name: "Nama Pemilik E-Wallet",
    qris_image: "Gambar QRIS",
    payment_instructions: "Instruksi Pembayaran",
    fiscal_year_start_month: "Bulan Awal Tahun Buku",
    payment_deadline_days: "Batas Waktu Pembayaran (Hari)",
    late_payment_penalty: "Denda Keterlambatan",
    late_payment_penalty_amount: "Nominal Denda Per Hari",

    // Social
    social_facebook: "Facebook",
    social_instagram: "Instagram",
    social_twitter: "Twitter / X",
    social_youtube: "YouTube",
    social_tiktok: "TikTok",
    social_whatsapp: "WhatsApp",
    social_telegram: "Telegram",
    social_website: "Website",

    // Portal
    portal_meta_title: "Judul SEO",
    portal_meta_description: "Deskripsi SEO",
    portal_meta_keywords: "Kata Kunci SEO",
    portal_welcome_text: "Teks Sambutan",
    portal_welcome_subtitle: "Sub-Judul Sambutan",
    portal_about_text: "Tentang Kami",
    portal_footer_text: "Teks Footer",
    portal_google_maps_embed: "Embed Google Maps",
    portal_hero_image: "Gambar Hero / Banner",
    portal_show_member_count: "Tampilkan Jumlah Anggota",
    portal_show_event_section: "Tampilkan Section Kegiatan",
    portal_show_donation_section: "Tampilkan Section Donasi",
    portal_show_gallery_section: "Tampilkan Section Galeri",
    portal_contact_email: "Email Kontak Portal",
    portal_primary_color: "Warna Utama Portal",

    // System
    allow_public_registration: "Registrasi Publik",
    maintenance_mode: "Mode Pemeliharaan",
    enable_donations: "Modul Donasi",
    enable_gallery: "Modul Galeri Foto",
    enable_contributions: "Modul Iuran",
    enable_events: "Modul Kegiatan",
    enable_meeting_minutes: "Modul Notulensi",
    enable_announcements: "Modul Pengumuman",
    enable_public_portal: "Portal Publik",
    enable_email_notifications: "Notifikasi Email",
    enable_whatsapp_notifications: "Notifikasi WhatsApp",
    enable_member_self_update: "Anggota Update Profil Sendiri",
    enable_member_card: "Kartu Anggota Digital",
    enable_financial_reports: "Laporan Keuangan Otomatis",
    default_member_status: "Status Default Anggota Baru",
    pagination_per_page: "Data Per Halaman",
    app_timezone: "Zona Waktu",
    app_locale: "Bahasa",
    max_upload_size: "Maks Ukuran Upload (KB)",
    session_lifetime: "Durasi Sesi Login (Menit)",
    date_format: "Format Tanggal",
    backup_auto_enabled: "Backup Otomatis",
    backup_frequency_days: "Frekuensi Backup (Hari)",
    app_theme_color: "Tema Warna Aplikasi",
};

const formatLabel = (key) => {
    return labelMap[key] || key
        .replace(/^(app_|organization_|social_|portal_|feature_|enable_|default_|currency_|bank_|ewallet_|qris_|payment_|pagination_|allow_|maintenance_|fiscal_|late_|max_|session_|date_|backup_)/, "")
        .split("_")
        .map(w => w.charAt(0).toUpperCase() + w.slice(1))
        .join(" ");
};

// ===== Sub-sections per group =====
const groupSections = {
    general: [
        { title: "Identitas", desc: "Nama, logo, dan identitas organisasi", keys: ["organization_name", "organization_short_name", "organization_motto", "organization_logo", "organization_favicon"] },
        { title: "Detail Organisasi", desc: "Tipe, tanggal berdiri, dan registrasi", keys: ["organization_type", "organization_founded_date", "organization_registration_number", "organization_npwp", "organization_description"] },
        { title: "Kontak & Alamat", desc: "Informasi kontak dan lokasi", keys: ["organization_email", "organization_phone", "organization_address", "organization_city", "organization_province", "organization_postal_code"] },
    ],
    financial: [
        { title: "Mata Uang", desc: "Pengaturan simbol dan kode mata uang", keys: ["currency_symbol", "currency_code"] },
        { title: "Rekening Bank", desc: "Informasi rekening untuk penerimaan", keys: ["bank_name", "bank_account_number", "bank_account_name", "bank_branch"] },
        { title: "E-Wallet", desc: "Metode pembayaran digital", keys: ["ewallet_provider", "ewallet_number", "ewallet_name"] },
        { title: "QRIS & Instruksi", desc: "Kode QRIS dan panduan pembayaran", keys: ["qris_image", "payment_instructions"] },
        { title: "Kebijakan Pembayaran", desc: "Tahun buku, deadline, dan denda", keys: ["fiscal_year_start_month", "payment_deadline_days", "late_payment_penalty", "late_payment_penalty_amount"] },
    ],
    social: [
        { title: "Media Sosial", desc: "Link profil media sosial organisasi", keys: ["social_facebook", "social_instagram", "social_twitter", "social_youtube", "social_tiktok"] },
        { title: "Pesan & Website", desc: "Kontak langsung dan website", keys: ["social_whatsapp", "social_telegram", "social_website"] },
    ],
    portal: [
        { title: "SEO & Meta", desc: "Optimasi untuk mesin pencari", keys: ["portal_meta_title", "portal_meta_description", "portal_meta_keywords"] },
        { title: "Halaman Depan", desc: "Konten hero dan sambutan", keys: ["portal_hero_image", "portal_welcome_text", "portal_welcome_subtitle", "portal_about_text"] },
        { title: "Tampilan Section", desc: "Kontrol section yang ditampilkan di portal", keys: ["portal_show_member_count", "portal_show_event_section", "portal_show_donation_section", "portal_show_gallery_section"] },
        { title: "Lainnya", desc: "Footer, kontak, maps, dan warna tema", keys: ["portal_footer_text", "portal_contact_email", "portal_google_maps_embed", "portal_primary_color"] },
    ],
    system: [
        { title: "Tema Warna", desc: "Pilih warna tema utama aplikasi", keys: ["app_theme_color"] },
        { title: "Modul Fitur", desc: "Aktifkan atau nonaktifkan modul sistem", keys: ["enable_donations", "enable_gallery", "enable_contributions", "enable_events", "enable_meeting_minutes", "enable_announcements", "enable_public_portal"] },
        { title: "Fitur Anggota", desc: "Pengaturan terkait anggota", keys: ["enable_member_self_update", "enable_member_card", "default_member_status", "allow_public_registration"] },
        { title: "Notifikasi", desc: "Pengaturan kanal notifikasi", keys: ["enable_email_notifications", "enable_whatsapp_notifications"] },
        { title: "Laporan & Backup", desc: "Laporan keuangan dan backup data", keys: ["enable_financial_reports", "backup_auto_enabled", "backup_frequency_days"] },
        { title: "Konfigurasi Umum", desc: "Zona waktu, bahasa, dan format", keys: ["app_timezone", "app_locale", "date_format", "pagination_per_page", "max_upload_size", "session_lifetime", "maintenance_mode"] },
    ],
};

const getSections = (group) => {
    return groupSections[group] || null;
};

const getSettingByKey = (key) => {
    return form.settings.find(s => s.key === key);
};

// ===== Theme Color Picker =====
const themePresets = getThemes();

const themeSetting = computed(() => getSettingByKey('app_theme_color'));
const currentTheme = computed(() => themeSetting.value?.value || 'indigo');

const selectTheme = (key) => {
    if (themeSetting.value) {
        themeSetting.value.value = key;
    }
    applyTheme(key);
};

</script>

<template>
    <Head title="Pengaturan Sistem" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold leading-tight text-foreground">Pengaturan Sistem</h2>
                <Button size="sm" :disabled="saving" @click="submit">
                    <Loader2 v-if="saving" class="w-4 h-4 mr-1 animate-spin" />
                    <Check v-else class="w-4 h-4 mr-1" />
                    {{ saving ? "Menyimpan..." : "Simpan" }}
                </Button>
            </div>
        </template>

        <div class="py-3 sm:py-6">
            <div class="max-w-6xl mx-auto px-3 sm:px-6 lg:px-8">
                <div class="flex flex-col lg:flex-row gap-3 sm:gap-4">

                    <!-- Sidebar Tabs -->
                    <div class="w-full lg:w-56 shrink-0">
                        <div class="flex lg:flex-col gap-1.5 overflow-x-auto pb-1 -mx-3 px-3 lg:mx-0 lg:px-0">
                            <button
                                v-for="(group, key) in settings"
                                :key="key"
                                @click="activeTab = key"
                                class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-all shrink-0 lg:w-full"
                                :class="activeTab === key
                                    ? 'bg-primary text-primary-foreground'
                                    : 'bg-card border text-muted-foreground hover:bg-muted hover:text-foreground'"
                            >
                                <component :is="getGroupConfig(key).icon" class="w-4 h-4 shrink-0" />
                                <span class="whitespace-nowrap">{{ getGroupConfig(key).label }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="flex-1 min-w-0">
                        <div class="bg-card rounded-xl border overflow-hidden">
                            <div class="h-1 w-full bg-primary" />

                            <form @submit.prevent="submit" class="p-4 sm:p-6">
                                <!-- Tab Header -->
                                <div class="mb-4 sm:mb-5 pb-3 sm:pb-4 border-b">
                                    <div class="flex items-center gap-2.5">
                                        <component :is="getGroupConfig(activeTab).icon" class="w-5 h-5 text-primary shrink-0" />
                                        <div>
                                            <h3 class="text-sm sm:text-base font-semibold text-foreground">{{ getGroupConfig(activeTab).label }}</h3>
                                            <p class="text-[10px] sm:text-xs text-muted-foreground">{{ getGroupConfig(activeTab).desc }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sectioned Settings Fields -->
                                <div v-if="getSections(activeTab)" class="space-y-6 sm:space-y-8">
                                    <div v-for="(section, sIdx) in getSections(activeTab)" :key="sIdx">
                                        <!-- Section Header -->
                                        <div class="flex items-center gap-2 mb-3">
                                            <div class="flex-1">
                                                <h4 class="text-xs sm:text-sm font-semibold text-foreground">{{ section.title }}</h4>
                                                <p class="text-[10px] sm:text-xs text-muted-foreground">{{ section.desc }}</p>
                                            </div>
                                        </div>

                                        <div class="space-y-4 sm:space-y-5 pl-0 sm:pl-3 border-l-0 sm:border-l-2 sm:border-primary/10">
                                            <template v-for="key in section.keys" :key="key">
                                                <template v-if="getSettingByKey(key)">
                                                    <!-- Theme Color Picker (must be before string check) -->
                                                    <div v-if="key === 'app_theme_color'" class="sm:pl-3">
                                                        <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide flex items-center gap-1.5">
                                                            <Palette class="w-3.5 h-3.5" />
                                                            {{ formatLabel(key) }}
                                                        </Label>
                                                        <p class="text-[10px] sm:text-xs text-muted-foreground mt-1 mb-3">Pilih template warna untuk tampilan aplikasi. Perubahan langsung terlihat.</p>
                                                        <div class="grid grid-cols-3 sm:grid-cols-5 gap-2">
                                                            <button
                                                                v-for="(theme, tKey) in themePresets"
                                                                :key="tKey"
                                                                type="button"
                                                                @click="selectTheme(tKey)"
                                                                class="group relative flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 transition-all duration-200 hover:shadow-md"
                                                                :class="currentTheme === tKey
                                                                    ? 'border-foreground bg-muted shadow-sm scale-[1.02]'
                                                                    : 'border-transparent bg-card hover:border-muted-foreground/20 hover:bg-muted/50'"
                                                            >
                                                                <div
                                                                    class="w-8 h-8 sm:w-10 sm:h-10 rounded-full shadow-inner transition-transform group-hover:scale-110"
                                                                    :style="{ backgroundColor: theme.color }"
                                                                />
                                                                <span class="text-[10px] sm:text-xs font-medium text-foreground">{{ theme.label }}</span>
                                                                <div
                                                                    v-if="currentTheme === tKey"
                                                                    class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-foreground text-background flex items-center justify-center"
                                                                >
                                                                    <Check class="w-3 h-3" />
                                                                </div>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <div v-else-if="getSettingByKey(key).type === 'string'" class="sm:pl-3">
                                                        <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">{{ formatLabel(key) }}</Label>
                                                        <Input v-model="getSettingByKey(key).value" type="text" class="mt-1.5" :placeholder="getSettingByKey(key).description" />
                                                        <p v-if="getSettingByKey(key).description" class="text-[10px] sm:text-xs text-muted-foreground mt-1">{{ getSettingByKey(key).description }}</p>
                                                    </div>

                                                    <div v-else-if="getSettingByKey(key).type === 'integer'" class="sm:pl-3">
                                                        <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">{{ formatLabel(key) }}</Label>
                                                        <Input v-model="getSettingByKey(key).value" type="number" class="mt-1.5 max-w-xs" :placeholder="getSettingByKey(key).description" />
                                                        <p v-if="getSettingByKey(key).description" class="text-[10px] sm:text-xs text-muted-foreground mt-1">{{ getSettingByKey(key).description }}</p>
                                                    </div>

                                                    <div v-else-if="getSettingByKey(key).type === 'text'" class="sm:pl-3">
                                                        <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">{{ formatLabel(key) }}</Label>
                                                        <textarea
                                                            v-model="getSettingByKey(key).value"
                                                            rows="3"
                                                            :placeholder="getSettingByKey(key).description"
                                                            class="mt-1.5 flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 resize-y"
                                                        />
                                                        <p v-if="getSettingByKey(key).description" class="text-[10px] sm:text-xs text-muted-foreground mt-1">{{ getSettingByKey(key).description }}</p>
                                                    </div>

                                                    <div v-else-if="getSettingByKey(key).type === 'boolean'" class="flex items-center justify-between gap-4 bg-muted/30 rounded-lg p-3 border sm:ml-3">
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-xs sm:text-sm font-medium text-foreground">{{ formatLabel(key) }}</p>
                                                            <p v-if="getSettingByKey(key).description" class="text-[10px] sm:text-xs text-muted-foreground mt-0.5">{{ getSettingByKey(key).description }}</p>
                                                        </div>
                                                        <button
                                                            type="button"
                                                            @click="getSettingByKey(key).value = getSettingByKey(key).value === '1' ? '0' : '1'"
                                                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                                                            :class="getSettingByKey(key).value === '1' ? 'bg-primary' : 'bg-muted'"
                                                        >
                                                            <span
                                                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                                                :class="getSettingByKey(key).value === '1' ? 'translate-x-5' : 'translate-x-0'"
                                                            />
                                                        </button>
                                                    </div>

                                                    <div v-else-if="getSettingByKey(key).type === 'image'" class="sm:pl-3">
                                                        <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">{{ formatLabel(key) }}</Label>
                                                        <div class="mt-1.5">
                                                            <!-- Preview -->
                                                            <div v-if="getImagePreview(getSettingByKey(key))" class="mb-2">
                                                                <div class="relative inline-block">
                                                                    <div class="border rounded-lg overflow-hidden bg-muted/30 p-2">
                                                                        <img
                                                                            :src="getImagePreview(getSettingByKey(key))"
                                                                            :alt="formatLabel(key)"
                                                                            class="max-h-24 sm:max-h-32 w-auto object-contain rounded"
                                                                        />
                                                                    </div>
                                                                    <button
                                                                        type="button"
                                                                        @click="removeImage(key)"
                                                                        class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-destructive text-destructive-foreground rounded-full flex items-center justify-center hover:bg-destructive/90 transition-colors"
                                                                    >
                                                                        <X class="w-3 h-3" />
                                                                    </button>
                                                                </div>
                                                                <div class="mt-1.5">
                                                                    <label class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md border bg-background hover:bg-muted cursor-pointer transition-colors">
                                                                        <Upload class="w-3.5 h-3.5" />
                                                                        Ganti Gambar
                                                                        <input type="file" accept="image/*" class="hidden" @change="handleImageSelect(key, $event)" />
                                                                    </label>
                                                                    <button
                                                                        v-if="imagePreviews[key]"
                                                                        type="button"
                                                                        @click="cancelImageChange(key)"
                                                                        class="ml-2 text-xs text-muted-foreground hover:text-foreground underline"
                                                                    >
                                                                        Batal
                                                                    </button>
                                                                </div>
                                                            </div>

                                                            <!-- Upload area (no image) -->
                                                            <label
                                                                v-else
                                                                class="flex flex-col items-center justify-center gap-2 py-6 sm:py-8 border-2 border-dashed rounded-lg bg-muted/20 hover:bg-muted/40 cursor-pointer transition-colors"
                                                            >
                                                                <div class="w-10 h-10 rounded-full bg-muted flex items-center justify-center">
                                                                    <ImageIcon class="w-5 h-5 text-muted-foreground" />
                                                                </div>
                                                                <div class="text-center">
                                                                    <p class="text-xs font-medium text-foreground">Klik untuk upload gambar</p>
                                                                    <p class="text-[10px] text-muted-foreground mt-0.5">PNG, JPG, SVG, WEBP (maks 2MB)</p>
                                                                </div>
                                                                <input type="file" accept="image/*" class="hidden" @change="handleImageSelect(key, $event)" />
                                                            </label>
                                                        </div>
                                                        <p v-if="getSettingByKey(key).description" class="text-[10px] sm:text-xs text-muted-foreground mt-1">{{ getSettingByKey(key).description }}</p>
                                                    </div>
                                                </template>
                                            </template>
                                        </div>

                                        <!-- Divider between sections -->
                                        <div v-if="sIdx < getSections(activeTab).length - 1" class="border-t mt-6 sm:mt-8" />
                                    </div>
                                </div>

                                <!-- Fallback: flat list for groups without sections defined -->
                                <div v-else class="space-y-4 sm:space-y-5">
                                    <div
                                        v-for="setting in form.settings"
                                        :key="setting.key"
                                        v-show="setting.group === activeTab && setting.type !== 'json'"
                                    >
                                        <div v-if="setting.type === 'string'">
                                            <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">{{ formatLabel(setting.key) }}</Label>
                                            <Input v-model="setting.value" type="text" class="mt-1.5" />
                                            <p v-if="setting.description" class="text-[10px] sm:text-xs text-muted-foreground mt-1">{{ setting.description }}</p>
                                        </div>
                                        <div v-else-if="setting.type === 'integer'">
                                            <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">{{ formatLabel(setting.key) }}</Label>
                                            <Input v-model="setting.value" type="number" class="mt-1.5" />
                                            <p v-if="setting.description" class="text-[10px] sm:text-xs text-muted-foreground mt-1">{{ setting.description }}</p>
                                        </div>
                                        <div v-else-if="setting.type === 'text'">
                                            <Label class="text-xs font-medium text-muted-foreground uppercase tracking-wide">{{ formatLabel(setting.key) }}</Label>
                                            <textarea v-model="setting.value" rows="3" class="mt-1.5 flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 resize-y" />
                                            <p v-if="setting.description" class="text-[10px] sm:text-xs text-muted-foreground mt-1">{{ setting.description }}</p>
                                        </div>
                                        <div v-else-if="setting.type === 'boolean'" class="flex items-center justify-between gap-4 bg-muted/30 rounded-lg p-3 border">
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs sm:text-sm font-medium text-foreground">{{ formatLabel(setting.key) }}</p>
                                                <p v-if="setting.description" class="text-[10px] sm:text-xs text-muted-foreground mt-0.5">{{ setting.description }}</p>
                                            </div>
                                            <button
                                                type="button"
                                                @click="setting.value = setting.value === '1' ? '0' : '1'"
                                                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                                                :class="setting.value === '1' ? 'bg-primary' : 'bg-muted'"
                                            >
                                                <span
                                                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                                    :class="setting.value === '1' ? 'translate-x-5' : 'translate-x-0'"
                                                />
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Mobile Save -->
                                <div class="sm:hidden mt-4 pt-3 border-t">
                                    <Button class="w-full" :disabled="saving" @click="submit">
                                        <Loader2 v-if="saving" class="w-4 h-4 mr-1 animate-spin" />
                                        <Check v-else class="w-4 h-4 mr-1" />
                                        {{ saving ? "Menyimpan..." : "Simpan Perubahan" }}
                                    </Button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
