<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import {
    Dialog, DialogContent, DialogHeader, DialogTitle,
} from '@/components/ui/dialog';
import DeleteConfirmDialog from '@/Components/DeleteConfirmDialog.vue';
import {
    DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    ArrowLeft, Pencil, Trash2, Calendar, Mail, Phone, MapPin, User,
    MoreVertical, Briefcase, Heart, Shield, Clock, FileText, CreditCard,
    ChevronRight,
} from 'lucide-vue-next';
import { useToast } from '@/composables/useToast';

const toast = useToast();

const props = defineProps({
    member: Object,
    contributions: Array,
    events: Array,
    donationTransactions: Array,
});

const activeTab = ref('profile');
const isPhotoZoomed = ref(false);
const showDeleteDialog = ref(false);

const tabs = [
    { id: 'profile', name: 'Profil', icon: User },
    { id: 'contributions', name: 'Iuran', icon: CreditCard },
    { id: 'donations', name: 'Donasi', icon: Heart },
    { id: 'events', name: 'Kegiatan', icon: Calendar },
];

const deleteMember = () => {
    router.delete(`/members/${props.member.id}`, {
        onError: (errors) => toast.error(Object.values(errors).flat().join(', ') || 'Gagal menghapus anggota.'),
        onFinish: () => (showDeleteDialog.value = false),
    });
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatDateShort = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

const formatCurrency = (amount) => {
    if (!amount) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(amount);
};

const getStatusBadgeClass = (status) => {
    const classes = {
        'paid': 'bg-success/20 text-success-foreground',
        'pending': 'bg-warning-100 text-warning-800',
        'cancelled': 'bg-destructive/20 text-destructive',
        'rejected': 'bg-destructive/20 text-destructive',
        'present': 'bg-success/20 text-success-foreground',
        'absent': 'bg-destructive/20 text-destructive',
        'excused': 'bg-warning-100 text-warning-800',
    };
    return classes[status] || 'bg-muted text-foreground';
};

const getStatusLabel = (status) => {
    const labels = {
        'paid': 'Lunas',
        'pending': 'Menunggu',
        'cancelled': 'Dibatalkan',
        'rejected': 'Ditolak',
        'present': 'Hadir',
        'absent': 'Tidak Hadir',
        'excused': 'Izin',
    };
    return labels[status] || status;
};
</script>

<template>
    <Head :title="`Detail Anggota - ${member.full_name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link href="/members" class="text-muted-foreground hover:text-foreground">
                        <ArrowLeft class="w-5 h-5" />
                    </Link>
                    <h2 class="text-lg font-semibold text-foreground">Detail Anggota</h2>
                </div>
                <!-- Desktop actions -->
                <div class="hidden sm:flex gap-2">
                    <Button as-child variant="default" size="sm">
                        <Link :href="`/members/${member.id}/edit`">
                            <Pencil class="w-4 h-4 mr-1" />
                            Ubah
                        </Link>
                    </Button>
                    <Button variant="destructive" size="sm" @click="showDeleteDialog = true">
                        <Trash2 class="w-4 h-4 mr-1" />
                        Hapus
                    </Button>
                </div>
                <!-- Mobile hamburger -->
                <div class="sm:hidden">
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="ghost" size="icon">
                                <MoreVertical class="w-5 h-5" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuItem @click="router.visit(`/members/${member.id}/edit`)">
                                <Pencil class="h-4 w-4 mr-2" />
                                Ubah Data
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="showDeleteDialog = true" class="text-destructive">
                                <Trash2 class="h-4 w-4 mr-2" />
                                Hapus Anggota
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>
        </template>

        <div class="py-4 sm:py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">

                <!-- Member Header Card - Mobile: centered profile card -->
                <div class="bg-card border rounded-xl overflow-hidden">
                    <!-- Mobile: Centered profile layout -->
                    <div class="sm:hidden p-6">
                        <div class="flex flex-col items-center text-center">
                            <!-- Large centered avatar -->
                            <div class="mb-4" @click="member.photo_url && (isPhotoZoomed = true)">
                                <img
                                    v-if="member.photo_url"
                                    :src="member.photo_url"
                                    :alt="member.full_name"
                                    class="h-24 w-24 rounded-full object-cover ring-4 ring-primary/10 cursor-pointer"
                                />
                                <div v-else class="h-24 w-24 rounded-full bg-primary/10 flex items-center justify-center ring-4 ring-primary/5">
                                    <span class="text-primary font-bold text-3xl">{{ member.full_name.charAt(0).toUpperCase() }}</span>
                                </div>
                            </div>
                            <h3 class="text-xl font-bold text-foreground">{{ member.full_name }}</h3>
                            <p v-if="member.nickname" class="text-sm text-muted-foreground">({{ member.nickname }})</p>
                            <p class="text-xs text-muted-foreground mt-1">{{ member.member_code }}</p>
                            <Badge
                                class="mt-3"
                                :variant="member.status === 'active' ? 'default' : 'secondary'"
                            >
                                {{ member.status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                            </Badge>

                            <!-- Quick contact actions -->
                            <div class="flex gap-3 mt-5 w-full">
                                <a v-if="member.phone" :href="`tel:${member.phone}`" class="flex-1 flex items-center justify-center gap-2 py-2.5 bg-primary/10 text-primary rounded-xl text-sm font-medium">
                                    <Phone class="w-4 h-4" />
                                    Telepon
                                </a>
                                <a v-if="member.email" :href="`mailto:${member.email}`" class="flex-1 flex items-center justify-center gap-2 py-2.5 bg-primary/10 text-primary rounded-xl text-sm font-medium">
                                    <Mail class="w-4 h-4" />
                                    Email
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Desktop: Horizontal layout -->
                    <div class="hidden sm:block p-6">
                        <div class="flex items-start gap-6">
                            <div class="flex-shrink-0">
                                <img
                                    v-if="member.photo_url"
                                    :src="member.photo_url"
                                    :alt="member.full_name"
                                    class="h-32 w-32 rounded-lg object-cover cursor-pointer hover:opacity-90 transition-opacity"
                                    @click="isPhotoZoomed = true"
                                />
                                <div v-else class="h-32 w-32 rounded-lg bg-muted flex items-center justify-center">
                                    <span class="text-muted-foreground font-medium text-4xl">{{ member.full_name.charAt(0).toUpperCase() }}</span>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-2xl font-bold text-foreground">{{ member.full_name }}</h3>
                                <p class="mt-1 text-sm text-muted-foreground">{{ member.member_code }}</p>
                                <div class="mt-2">
                                    <Badge :variant="member.status === 'active' ? 'default' : 'secondary'">
                                        {{ member.status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                                    </Badge>
                                </div>
                                <div class="flex gap-4 mt-4 text-sm text-muted-foreground">
                                    <span v-if="member.phone" class="flex items-center gap-1.5">
                                        <Phone class="w-4 h-4" />
                                        {{ member.phone }}
                                    </span>
                                    <span v-if="member.email" class="flex items-center gap-1.5">
                                        <Mail class="w-4 h-4" />
                                        {{ member.email }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="bg-card border rounded-xl overflow-hidden">
                    <!-- Tab Navigation - scrollable on mobile -->
                    <div class="border-b">
                        <nav class="-mb-px flex overflow-x-auto no-scrollbar" aria-label="Tabs">
                            <button
                                v-for="tab in tabs"
                                :key="tab.id"
                                type="button"
                                :class="[
                                    activeTab === tab.id
                                        ? 'border-primary text-primary'
                                        : 'border-transparent text-muted-foreground hover:text-foreground hover:border-input',
                                    'whitespace-nowrap py-3 sm:py-4 px-4 sm:px-5 border-b-2 font-medium text-sm flex items-center gap-2 min-h-[44px]',
                                ]"
                                @click="activeTab = tab.id"
                            >
                                <component :is="tab.icon" class="w-4 h-4 sm:hidden" />
                                <span>{{ tab.name }}</span>
                            </button>
                        </nav>
                    </div>

                    <!-- Tab Content -->
                    <div class="p-4 sm:p-6">
                        <!-- Profile Tab -->
                        <div v-if="activeTab === 'profile'" class="space-y-6">
                            <!-- Mobile: info sections as grouped cards -->
                            <div class="space-y-4 sm:hidden">
                                <!-- Personal Info -->
                                <div class="space-y-1">
                                    <h4 class="text-xs font-bold text-muted-foreground uppercase tracking-wider px-1 mb-2">Data Pribadi</h4>
                                    <div class="bg-muted/30 rounded-xl divide-y divide-border">
                                        <div class="flex justify-between items-center px-4 py-3">
                                            <span class="text-sm text-muted-foreground">NIK</span>
                                            <span class="text-sm font-medium text-foreground">{{ member.nik || '-' }}</span>
                                        </div>
                                        <div class="flex justify-between items-center px-4 py-3">
                                            <span class="text-sm text-muted-foreground">Jenis Kelamin</span>
                                            <span class="text-sm font-medium text-foreground">{{ member.gender === 'male' ? 'Laki-laki' : member.gender === 'female' ? 'Perempuan' : '-' }}</span>
                                        </div>
                                        <div class="flex justify-between items-center px-4 py-3">
                                            <span class="text-sm text-muted-foreground">Agama</span>
                                            <span class="text-sm font-medium text-foreground">{{ member.religion || '-' }}</span>
                                        </div>
                                        <div class="flex justify-between items-center px-4 py-3">
                                            <span class="text-sm text-muted-foreground">Tanggal Lahir</span>
                                            <span class="text-sm font-medium text-foreground">{{ formatDateShort(member.date_of_birth) }}</span>
                                        </div>
                                        <div class="flex justify-between items-center px-4 py-3">
                                            <span class="text-sm text-muted-foreground">Status Nikah</span>
                                            <span class="text-sm font-medium text-foreground">{{ member.marital_status || '-' }}</span>
                                        </div>
                                        <div class="flex justify-between items-center px-4 py-3">
                                            <span class="text-sm text-muted-foreground">Pekerjaan</span>
                                            <span class="text-sm font-medium text-foreground">{{ member.occupation || '-' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contact & Address -->
                                <div class="space-y-1">
                                    <h4 class="text-xs font-bold text-muted-foreground uppercase tracking-wider px-1 mb-2">Kontak & Alamat</h4>
                                    <div class="bg-muted/30 rounded-xl divide-y divide-border">
                                        <div class="flex justify-between items-center px-4 py-3">
                                            <span class="text-sm text-muted-foreground">Email</span>
                                            <span class="text-sm font-medium text-foreground truncate ml-4">{{ member.email || '-' }}</span>
                                        </div>
                                        <div class="flex justify-between items-center px-4 py-3">
                                            <span class="text-sm text-muted-foreground">Telepon</span>
                                            <span class="text-sm font-medium text-foreground">{{ member.phone || '-' }}</span>
                                        </div>
                                        <div class="px-4 py-3">
                                            <span class="text-sm text-muted-foreground block mb-1">Alamat</span>
                                            <span class="text-sm font-medium text-foreground">{{ member.address || '-' }}</span>
                                        </div>
                                        <div class="px-4 py-3">
                                            <span class="text-sm text-muted-foreground block mb-1">Alamat Domisili</span>
                                            <span class="text-sm font-medium text-foreground">{{ member.domicile_address || '-' }}</span>
                                        </div>
                                        <div class="flex justify-between items-center px-4 py-3">
                                            <span class="text-sm text-muted-foreground">Tempat Tinggal</span>
                                            <span class="text-sm font-medium text-foreground">{{ { 'kos': 'Kos', 'rumah': 'Rumah', 'mess': 'Mess' }[member.living_status] || member.living_status || '-' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Membership -->
                                <div class="space-y-1">
                                    <h4 class="text-xs font-bold text-muted-foreground uppercase tracking-wider px-1 mb-2">Keanggotaan</h4>
                                    <div class="bg-muted/30 rounded-xl divide-y divide-border">
                                        <div class="flex justify-between items-center px-4 py-3">
                                            <span class="text-sm text-muted-foreground">Bergabung</span>
                                            <span class="text-sm font-medium text-foreground">{{ formatDateShort(member.join_date) }}</span>
                                        </div>
                                        <div class="flex justify-between items-center px-4 py-3">
                                            <span class="text-sm text-muted-foreground">Tiba di Bali</span>
                                            <span class="text-sm font-medium text-foreground">{{ formatDateShort(member.arrival_date_bali) }}</span>
                                        </div>
                                        <div class="flex justify-between items-center px-4 py-3">
                                            <span class="text-sm text-muted-foreground">BPJS Kesehatan</span>
                                            <Badge :variant="member.bpjs_health_active ? 'default' : 'secondary'" class="text-xs">
                                                {{ member.bpjs_health_active ? 'Aktif' : 'Tidak' }}
                                            </Badge>
                                        </div>
                                        <div class="flex justify-between items-center px-4 py-3">
                                            <span class="text-sm text-muted-foreground">BPJS Tenaga Kerja</span>
                                            <Badge :variant="member.bpjs_employment_active ? 'default' : 'secondary'" class="text-xs">
                                                {{ member.bpjs_employment_active ? 'Aktif' : 'Tidak' }}
                                            </Badge>
                                        </div>
                                    </div>
                                </div>

                                <!-- Origin -->
                                <div class="space-y-1">
                                    <h4 class="text-xs font-bold text-muted-foreground uppercase tracking-wider px-1 mb-2">Asal Daerah</h4>
                                    <div class="bg-muted/30 rounded-xl divide-y divide-border">
                                        <div class="flex justify-between items-center px-4 py-3">
                                            <span class="text-sm text-muted-foreground">Kampung</span>
                                            <span class="text-sm font-medium text-foreground">{{ member.origin_hamlet || '-' }}</span>
                                        </div>
                                        <div class="flex justify-between items-center px-4 py-3">
                                            <span class="text-sm text-muted-foreground">Desa</span>
                                            <span class="text-sm font-medium text-foreground">{{ member.origin_village || '-' }}</span>
                                        </div>
                                        <div class="flex justify-between items-center px-4 py-3">
                                            <span class="text-sm text-muted-foreground">Kecamatan</span>
                                            <span class="text-sm font-medium text-foreground">{{ member.origin_subdistrict || '-' }}</span>
                                        </div>
                                        <div class="flex justify-between items-center px-4 py-3">
                                            <span class="text-sm text-muted-foreground">Kabupaten</span>
                                            <span class="text-sm font-medium text-foreground">{{ member.origin_regency || '-' }}</span>
                                        </div>
                                        <div class="flex justify-between items-center px-4 py-3">
                                            <span class="text-sm text-muted-foreground">Provinsi</span>
                                            <span class="text-sm font-medium text-foreground">{{ member.origin_province || '-' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Notes -->
                                <div v-if="member.notes" class="space-y-1">
                                    <h4 class="text-xs font-bold text-muted-foreground uppercase tracking-wider px-1 mb-2">Catatan</h4>
                                    <div class="bg-muted/30 rounded-xl px-4 py-3">
                                        <p class="text-sm text-foreground">{{ member.notes }}</p>
                                    </div>
                                </div>

                                <!-- KTP Photo -->
                                <div class="space-y-1">
                                    <h4 class="text-xs font-bold text-muted-foreground uppercase tracking-wider px-1 mb-2">Foto KTP</h4>
                                    <div class="bg-muted/30 rounded-xl p-3">
                                        <img v-if="member.ktp_photo_url" :src="member.ktp_photo_url" alt="Foto KTP" class="w-full rounded-lg object-cover" />
                                        <p v-else class="text-sm text-muted-foreground text-center py-4">Tidak tersedia</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Desktop: Grid layout -->
                            <div class="hidden sm:grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h4 class="text-sm font-medium text-muted-foreground">NIK</h4>
                                    <p class="mt-1 text-sm text-foreground">{{ member.nik || '-' }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-muted-foreground">Nama Panggilan</h4>
                                    <p class="mt-1 text-sm text-foreground">{{ member.nickname || '-' }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-muted-foreground">Jenis Kelamin</h4>
                                    <p class="mt-1 text-sm text-foreground">
                                        {{ member.gender === 'male' ? 'Laki-laki' : member.gender === 'female' ? 'Perempuan' : '-' }}
                                    </p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-muted-foreground">Agama</h4>
                                    <p class="mt-1 text-sm text-foreground">{{ member.religion || '-' }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-muted-foreground">Email</h4>
                                    <p class="mt-1 text-sm text-foreground">{{ member.email || '-' }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-muted-foreground">Nomor Telepon</h4>
                                    <p class="mt-1 text-sm text-foreground">{{ member.phone || '-' }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-muted-foreground">Tanggal Lahir</h4>
                                    <p class="mt-1 text-sm text-foreground">{{ formatDate(member.date_of_birth) }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-muted-foreground">Tanggal Bergabung</h4>
                                    <p class="mt-1 text-sm text-foreground">{{ formatDate(member.join_date) }}</p>
                                </div>
                                <div class="md:col-span-2">
                                    <h4 class="text-sm font-medium text-muted-foreground">Alamat</h4>
                                    <p class="mt-1 text-sm text-foreground">{{ member.address || '-' }}</p>
                                </div>
                                <div class="md:col-span-2">
                                    <h4 class="text-sm font-medium text-muted-foreground">Alamat Domisili</h4>
                                    <p class="mt-1 text-sm text-foreground">{{ member.domicile_address || '-' }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-muted-foreground">Status Tempat Tinggal</h4>
                                    <p class="mt-1 text-sm text-foreground">{{ { 'kos': 'Kos', 'rumah': 'Rumah', 'mess': 'Mess' }[member.living_status] || member.living_status || '-' }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-muted-foreground">Status Pernikahan</h4>
                                    <p class="mt-1 text-sm text-foreground">{{ member.marital_status || '-' }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-muted-foreground">Pekerjaan</h4>
                                    <p class="mt-1 text-sm text-foreground">{{ member.occupation || '-' }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-muted-foreground">Waktu Tiba di Bali</h4>
                                    <p class="mt-1 text-sm text-foreground">{{ formatDate(member.arrival_date_bali) }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-muted-foreground">Kampung Asal</h4>
                                    <p class="mt-1 text-sm text-foreground">{{ member.origin_hamlet || '-' }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-muted-foreground">Desa</h4>
                                    <p class="mt-1 text-sm text-foreground">{{ member.origin_village || '-' }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-muted-foreground">Kecamatan</h4>
                                    <p class="mt-1 text-sm text-foreground">{{ member.origin_subdistrict || '-' }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-muted-foreground">Kabupaten</h4>
                                    <p class="mt-1 text-sm text-foreground">{{ member.origin_regency || '-' }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-muted-foreground">Provinsi</h4>
                                    <p class="mt-1 text-sm text-foreground">{{ member.origin_province || '-' }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-muted-foreground">BPJS Kesehatan</h4>
                                    <p class="mt-1 text-sm text-foreground">{{ member.bpjs_health_active ? 'Aktif' : 'Tidak Aktif' }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-muted-foreground">BPJS Tenaga Kerja</h4>
                                    <p class="mt-1 text-sm text-foreground">{{ member.bpjs_employment_active ? 'Aktif' : 'Tidak Aktif' }}</p>
                                </div>
                                <div class="md:col-span-2">
                                    <h4 class="text-sm font-medium text-muted-foreground">Catatan</h4>
                                    <p class="mt-1 text-sm text-foreground">{{ member.notes || '-' }}</p>
                                </div>
                            </div>

                            <div class="hidden sm:block mt-6">
                                <h4 class="text-sm font-medium text-muted-foreground">Foto KTP</h4>
                                <div class="mt-2">
                                    <img v-if="member.ktp_photo_url" :src="member.ktp_photo_url" alt="Foto KTP" class="h-48 w-auto rounded-lg object-cover" />
                                    <p v-else class="text-sm text-foreground">-</p>
                                </div>
                            </div>
                        </div>

                        <!-- Contributions Tab -->
                        <div v-if="activeTab === 'contributions'">
                            <div v-if="contributions && contributions.length > 0">
                                <!-- Mobile: Card list -->
                                <div class="sm:hidden space-y-3">
                                    <div
                                        v-for="c in contributions"
                                        :key="c.id"
                                        class="bg-muted/30 rounded-xl p-4"
                                    >
                                        <div class="flex items-start justify-between mb-2">
                                            <div>
                                                <p class="text-sm font-semibold text-foreground">{{ c.type_name }}</p>
                                                <p class="text-xs text-muted-foreground mt-0.5">{{ formatDateShort(c.payment_date) }}</p>
                                            </div>
                                            <Badge class="text-xs" :class="getStatusBadgeClass(c.status)">
                                                {{ getStatusLabel(c.status) }}
                                            </Badge>
                                        </div>
                                        <div class="flex items-center justify-between pt-2 border-t border-border">
                                            <span class="text-sm font-bold text-primary">{{ formatCurrency(c.amount) }}</span>
                                            <span class="text-xs text-muted-foreground">{{ c.payment_period || '-' }} &bull; {{ c.payment_method || '-' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Desktop: Table -->
                                <div class="hidden sm:block overflow-x-auto">
                                    <table class="min-w-full divide-y divide-border">
                                        <thead class="bg-muted">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Jenis Iuran</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Jumlah</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Tanggal Bayar</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Periode</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Metode</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-card divide-y divide-border">
                                            <tr v-for="c in contributions" :key="c.id">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ c.type_name }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-foreground">{{ formatCurrency(c.amount) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">{{ formatDate(c.payment_date) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">{{ c.payment_period || '-' }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground capitalize">{{ c.payment_method || '-' }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStatusBadgeClass(c.status)">{{ getStatusLabel(c.status) }}</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div v-else class="text-center py-12">
                                <CreditCard class="mx-auto h-12 w-12 text-muted-foreground/40" />
                                <h3 class="mt-3 text-sm font-medium text-foreground">Belum ada riwayat iuran</h3>
                                <p class="mt-1 text-sm text-muted-foreground">Anggota ini belum memiliki catatan pembayaran iuran</p>
                            </div>
                        </div>

                        <!-- Donations Tab -->
                        <div v-if="activeTab === 'donations'">
                            <div v-if="donationTransactions && donationTransactions.length > 0">
                                <!-- Mobile: Card list -->
                                <div class="sm:hidden space-y-3">
                                    <div
                                        v-for="tx in donationTransactions"
                                        :key="tx.id"
                                        class="bg-muted/30 rounded-xl p-4"
                                    >
                                        <div class="flex items-start justify-between mb-2">
                                            <div class="flex-1 min-w-0 mr-3">
                                                <p class="text-sm font-semibold text-foreground truncate">{{ tx.program_name }}</p>
                                                <p class="text-xs text-muted-foreground mt-0.5">{{ formatDateShort(tx.donation_date) }}</p>
                                            </div>
                                            <Badge class="text-xs shrink-0" :class="getStatusBadgeClass(tx.status)">
                                                {{ getStatusLabel(tx.status) }}
                                            </Badge>
                                        </div>
                                        <div class="flex items-center justify-between pt-2 border-t border-border">
                                            <span class="text-sm font-bold text-success-600">{{ formatCurrency(tx.amount) }}</span>
                                            <span v-if="tx.notes" class="text-xs text-muted-foreground italic truncate ml-3">{{ tx.notes }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Desktop: Table -->
                                <div class="hidden sm:block overflow-x-auto">
                                    <table class="min-w-full divide-y divide-border">
                                        <thead class="bg-muted">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Program Donasi</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Jumlah</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Tanggal Donasi</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Status</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Catatan</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-card divide-y divide-border">
                                            <tr v-for="tx in donationTransactions" :key="tx.id">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ tx.program_name }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-success-600">{{ formatCurrency(tx.amount) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">{{ formatDate(tx.donation_date) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStatusBadgeClass(tx.status)">{{ getStatusLabel(tx.status) }}</span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground italic">{{ tx.notes || '-' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div v-else class="text-center py-12">
                                <Heart class="mx-auto h-12 w-12 text-muted-foreground/40" />
                                <h3 class="mt-3 text-sm font-medium text-foreground">Belum ada riwayat donasi</h3>
                                <p class="mt-1 text-sm text-muted-foreground">Anggota ini belum melakukan donasi apapun</p>
                            </div>
                        </div>

                        <!-- Events Tab -->
                        <div v-if="activeTab === 'events'">
                            <div v-if="events && events.length > 0">
                                <!-- Mobile: Card list -->
                                <div class="sm:hidden space-y-3">
                                    <div
                                        v-for="event in events"
                                        :key="event.id"
                                        class="bg-muted/30 rounded-xl p-4"
                                    >
                                        <div class="flex items-start justify-between mb-2">
                                            <div class="flex-1 min-w-0 mr-3">
                                                <p class="text-sm font-semibold text-foreground">{{ event.title }}</p>
                                                <div class="flex items-center gap-3 mt-1">
                                                    <span class="text-xs text-muted-foreground flex items-center gap-1">
                                                        <Calendar class="w-3 h-3" />
                                                        {{ formatDateShort(event.event_date) }}
                                                    </span>
                                                    <span v-if="event.location" class="text-xs text-muted-foreground flex items-center gap-1">
                                                        <MapPin class="w-3 h-3" />
                                                        {{ event.location }}
                                                    </span>
                                                </div>
                                            </div>
                                            <Badge class="text-xs shrink-0" :class="getStatusBadgeClass(event.attendance_status)">
                                                {{ getStatusLabel(event.attendance_status) }}
                                            </Badge>
                                        </div>
                                    </div>
                                </div>

                                <!-- Desktop: Table -->
                                <div class="hidden sm:block overflow-x-auto">
                                    <table class="min-w-full divide-y divide-border">
                                        <thead class="bg-muted">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Nama Kegiatan</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Tanggal</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Lokasi</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Tanggal Daftar</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Kehadiran</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-card divide-y divide-border">
                                            <tr v-for="event in events" :key="event.id">
                                                <td class="px-6 py-4 text-sm font-medium text-foreground">{{ event.title }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">{{ formatDate(event.event_date) }}</td>
                                                <td class="px-6 py-4 text-sm text-muted-foreground">{{ event.location || '-' }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">{{ formatDate(event.registration_date) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStatusBadgeClass(event.attendance_status)">{{ getStatusLabel(event.attendance_status) }}</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div v-else class="text-center py-12">
                                <Calendar class="mx-auto h-12 w-12 text-muted-foreground/40" />
                                <h3 class="mt-3 text-sm font-medium text-foreground">Belum ada riwayat kegiatan</h3>
                                <p class="mt-1 text-sm text-muted-foreground">Anggota ini belum terdaftar dalam kegiatan apapun</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Photo Zoom Dialog -->
        <Dialog :open="isPhotoZoomed" @update:open="(val) => { if (!val) isPhotoZoomed = false; }">
            <DialogContent class="max-w-2xl">
                <DialogHeader>
                    <DialogTitle class="text-center">{{ member.full_name }}</DialogTitle>
                </DialogHeader>
                <div class="flex flex-col items-center py-4">
                    <img :src="member.photo_url" :alt="member.full_name" class="max-w-full max-h-[70vh] rounded-lg shadow-lg" />
                    <p class="mt-4 text-sm text-muted-foreground">{{ member.member_code }}</p>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation -->
        <DeleteConfirmDialog
            :open="showDeleteDialog"
            title="Hapus Anggota"
            :description="`Apakah Anda yakin ingin menghapus anggota ${member.full_name}? Tindakan ini tidak dapat dibatalkan.`"
            @confirm="deleteMember"
            @cancel="showDeleteDialog = false"
        />
    </AuthenticatedLayout>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
