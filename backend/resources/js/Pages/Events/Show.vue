<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import {
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter,
} from '@/components/ui/dialog';
import DeleteConfirmDialog from '@/Components/DeleteConfirmDialog.vue';
import {
    DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Calendar, MapPin, Users, FileText, Camera, ArrowLeft, Pencil, Trash2,
    X, ChevronLeft, ChevronRight, MoreVertical, Clock, User, Eye, Globe, UserPlus, Upload, Navigation, ExternalLink,
} from 'lucide-vue-next';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIconImg from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';
import { useToast } from '@/composables/useToast';

const toast = useToast();

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIconImg,
    shadowUrl: markerShadow,
});

const props = defineProps({
    event: Object,
    members: Array,
});

const showParticipantModal = ref(false);
const showDocModal = ref(false);
const zoomedIndex = ref(null);
const showDeleteDialog = ref(false);
const deleteParticipantTarget = ref(null);
const deleteDocTarget = ref(null);
const activeTab = ref('info');

const tabs = [
    { id: 'info', name: 'Info', icon: FileText },
    { id: 'docs', name: 'Dokumentasi', icon: Camera },
    { id: 'participants', name: 'Peserta', icon: Users },
];

const zoomedDoc = computed(() => {
    if (zoomedIndex.value === null) return null;
    return props.event.documentations[zoomedIndex.value];
});

const prevDoc = () => {
    if (zoomedIndex.value > 0) zoomedIndex.value--;
    else zoomedIndex.value = props.event.documentations.length - 1;
};

const nextDoc = () => {
    if (zoomedIndex.value < props.event.documentations.length - 1) zoomedIndex.value++;
    else zoomedIndex.value = 0;
};

const handleKeyDown = (e) => {
    if (zoomedIndex.value === null) return;
    if (e.key === 'ArrowLeft') prevDoc();
    if (e.key === 'ArrowRight') nextDoc();
    if (e.key === 'Escape') zoomedIndex.value = null;
};

onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);
    countdownInterval = setInterval(() => { now.value = new Date(); }, 1000);
});
onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
    clearInterval(countdownInterval);
});

const mapContainer = ref(null);
const mapContainerMobile = ref(null);
const hasCoordinates = computed(() => props.event.latitude && props.event.longitude);
const googleMapsUrl = computed(() => {
    if (!hasCoordinates.value) return '';
    return `https://www.google.com/maps?q=${props.event.latitude},${props.event.longitude}`;
});

// Countdown timer
const now = ref(new Date());
let countdownInterval = null;

const countdown = computed(() => {
    const startDate = new Date(props.event.start_date);
    const endDate = props.event.end_date ? new Date(props.event.end_date) : null;
    const current = now.value;

    if (endDate && current > endDate) {
        return { type: 'ended', label: 'Kegiatan telah selesai' };
    }
    if (current >= startDate && (!endDate || current <= endDate)) {
        const diff = endDate ? endDate - current : 0;
        if (!endDate) return { type: 'ongoing', label: 'Sedang berlangsung' };
        const { days, hours, minutes, seconds } = parseDiff(diff);
        return { type: 'ongoing', label: 'Sedang berlangsung', remaining: 'Sisa', days, hours, minutes, seconds };
    }
    const diff = startDate - current;
    const { days, hours, minutes, seconds } = parseDiff(diff);
    return { type: 'upcoming', label: 'Dimulai dalam', days, hours, minutes, seconds };
});

function parseDiff(ms) {
    const totalSeconds = Math.floor(ms / 1000);
    return {
        days: Math.floor(totalSeconds / 86400),
        hours: Math.floor((totalSeconds % 86400) / 3600),
        minutes: Math.floor((totalSeconds % 3600) / 60),
        seconds: totalSeconds % 60,
    };
}

const padZero = (n) => String(n).padStart(2, '0');

const initMap = (el) => {
    if (!el || !hasCoordinates.value) return;
    const lat = parseFloat(props.event.latitude);
    const lng = parseFloat(props.event.longitude);
    const map = L.map(el, { scrollWheelZoom: false }).setView([lat, lng], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);
    L.marker([lat, lng]).addTo(map);
};

onMounted(() => {
    if (hasCoordinates.value) {
        if (mapContainer.value) initMap(mapContainer.value);
        if (mapContainerMobile.value) initMap(mapContainerMobile.value);
    }
});

const participantForm = useForm({ member_id: '', notes: '' });
const docForm = useForm({ files: [], caption: '' });

const getStatusBadge = (status) => {
    const map = { draft: 'secondary', published: 'default', ongoing: 'warning', completed: 'success', cancelled: 'destructive' };
    return map[status] || 'secondary';
};

const getStatusLabel = (status) => {
    const map = { draft: 'Draft', published: 'Published', ongoing: 'Berlangsung', completed: 'Selesai', cancelled: 'Dibatalkan' };
    return map[status] || status;
};

const getAttendanceBadgeClass = (s) => {
    const map = {
        registered: 'bg-primary/20 text-primary',
        attended: 'bg-success/20 text-success-foreground',
        absent: 'bg-destructive/20 text-destructive',
    };
    return map[s] || 'bg-muted text-foreground';
};

const getAttendanceLabel = (s) => {
    const map = { registered: 'Terdaftar', attended: 'Hadir', absent: 'Tidak Hadir' };
    return map[s] || s;
};

const formatDate = (date) => {
    return new Date(date).toLocaleString('id-ID', {
        day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit',
    });
};

const formatDateShort = (date) => {
    return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

const deleteEvent = () => {
    router.delete(route('events.destroy', props.event), {
        onError: (errors) => toast.error(Object.values(errors).flat().join(', ') || 'Gagal menghapus kegiatan.'),
        onFinish: () => (showDeleteDialog.value = false),
    });
};

const addParticipant = () => {
    participantForm.post(route('events.participants.add', props.event), {
        onSuccess: () => { showParticipantModal.value = false; participantForm.reset(); },
    });
};

const confirmRemoveParticipant = (member) => { deleteParticipantTarget.value = member; };
const doRemoveParticipant = () => {
    if (deleteParticipantTarget.value) {
        router.delete(route('events.participants.remove', [props.event, deleteParticipantTarget.value]), {
            onError: (errors) => toast.error(Object.values(errors).flat().join(', ') || 'Gagal menghapus peserta.'),
            onFinish: () => (deleteParticipantTarget.value = null),
        });
    }
};

const updateAttendance = (member, status) => {
    router.patch(route('events.participants.update-status', [props.event, member]), { attendance_status: status });
};

const uploadDoc = () => {
    docForm.post(route('events.documentations.upload', props.event), {
        forceFormData: true,
        onSuccess: () => { showDocModal.value = false; resetDocForm(); },
    });
};

const confirmDeleteDoc = (doc) => { deleteDocTarget.value = doc; };
const doDeleteDoc = () => {
    if (deleteDocTarget.value) {
        router.delete(route('events.documentations.destroy', [props.event, deleteDocTarget.value]), {
            onError: (errors) => toast.error(Object.values(errors).flat().join(', ') || 'Gagal menghapus dokumentasi.'),
            onFinish: () => (deleteDocTarget.value = null),
        });
    }
};

const docPreviews = ref([]);
const fileInputRef = ref(null);
const isDragging = ref(false);

const handleFileSelect = (e) => {
    addFiles(Array.from(e.target.files));
};

const handleDrop = (e) => {
    isDragging.value = false;
    const files = Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/'));
    if (files.length) addFiles(files);
};

const addFiles = (newFiles) => {
    const combined = [...docForm.files, ...newFiles];
    docForm.files = combined;
    newFiles.forEach(file => {
        const reader = new FileReader();
        reader.onload = (e) => { docPreviews.value.push({ name: file.name, url: e.target.result, size: file.size }); };
        reader.readAsDataURL(file);
    });
};

const removeFile = (index) => {
    docForm.files.splice(index, 1);
    docPreviews.value.splice(index, 1);
};

const formatFileSize = (bytes) => {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
};

const resetDocForm = () => {
    docForm.reset();
    docPreviews.value = [];
};
</script>

<template>
    <Head :title="`Detail Kegiatan - ${event.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="route('events.index')" class="text-muted-foreground hover:text-foreground">
                        <ArrowLeft class="w-5 h-5" />
                    </Link>
                    <h2 class="text-lg font-semibold text-foreground">Detail Kegiatan</h2>
                </div>
                <!-- Desktop actions -->
                <div class="hidden sm:flex gap-2" v-if="hasPermission('manage_events')">
                    <Button as-child variant="default" size="sm">
                        <Link :href="route('events.edit', event)">
                            <Pencil class="w-4 h-4 mr-1" /> Ubah
                        </Link>
                    </Button>
                    <Button variant="destructive" size="sm" @click="showDeleteDialog = true">
                        <Trash2 class="w-4 h-4 mr-1" /> Hapus
                    </Button>
                </div>
                <!-- Mobile hamburger -->
                <div class="sm:hidden" v-if="hasPermission('manage_events')">
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="ghost" size="icon">
                                <MoreVertical class="w-5 h-5" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuItem @click="router.visit(route('events.edit', event))">
                                <Pencil class="h-4 w-4 mr-2" /> Ubah Kegiatan
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="showDeleteDialog = true" class="text-destructive">
                                <Trash2 class="h-4 w-4 mr-2" /> Hapus Kegiatan
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>
        </template>

        <div class="py-4 sm:py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">

                <!-- Event Header Card -->
                <div class="bg-card border rounded-xl overflow-hidden">
                    <!-- Mobile: Centered header -->
                    <div class="sm:hidden p-5">
                        <div class="flex flex-col items-center text-center">
                            <div class="w-16 h-16 bg-primary/10 rounded-2xl flex flex-col items-center justify-center mb-4">
                                <span class="text-primary font-bold text-lg leading-none">{{ new Date(event.start_date).getDate() }}</span>
                                <span class="text-primary/70 text-[10px] font-semibold uppercase">{{ new Date(event.start_date).toLocaleDateString('id-ID', { month: 'short' }) }}</span>
                            </div>
                            <h3 class="text-xl font-bold text-foreground">{{ event.name }}</h3>
                            <Badge :variant="getStatusBadge(event.status)" class="mt-2">{{ getStatusLabel(event.status) }}</Badge>

                            <!-- Countdown Mobile -->
                            <div v-if="countdown.type !== 'ended'" class="mt-4 w-full">
                                <div class="countdown-banner rounded-xl px-4 py-3" :class="countdown.type === 'ongoing' ? 'countdown-ongoing' : 'countdown-upcoming'">
                                    <p class="text-xs font-semibold uppercase tracking-wider mb-2 countdown-label">
                                        <span class="countdown-pulse-dot"></span>
                                        {{ countdown.remaining || countdown.label }}
                                    </p>
                                    <div v-if="countdown.days !== undefined" class="flex items-center justify-center gap-1.5">
                                        <div v-if="countdown.days > 0" class="countdown-unit">
                                            <span class="countdown-number">{{ countdown.days }}</span>
                                            <span class="countdown-text">Hari</span>
                                        </div>
                                        <span v-if="countdown.days > 0" class="countdown-separator">:</span>
                                        <div class="countdown-unit">
                                            <span class="countdown-number">{{ padZero(countdown.hours) }}</span>
                                            <span class="countdown-text">Jam</span>
                                        </div>
                                        <span class="countdown-separator">:</span>
                                        <div class="countdown-unit">
                                            <span class="countdown-number">{{ padZero(countdown.minutes) }}</span>
                                            <span class="countdown-text">Menit</span>
                                        </div>
                                        <span class="countdown-separator countdown-blink">:</span>
                                        <div class="countdown-unit">
                                            <span class="countdown-number countdown-seconds">{{ padZero(countdown.seconds) }}</span>
                                            <span class="countdown-text">Detik</span>
                                        </div>
                                    </div>
                                    <p v-else class="text-sm font-bold countdown-label">{{ countdown.label }}</p>
                                </div>
                            </div>

                            <!-- Quick info pills -->
                            <div class="flex flex-wrap justify-center gap-2 mt-4">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-muted rounded-lg text-xs text-muted-foreground">
                                    <Clock class="w-3.5 h-3.5" />
                                    {{ new Date(event.start_date).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}
                                </span>
                                <a v-if="hasCoordinates" :href="googleMapsUrl" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary/10 text-primary rounded-lg text-xs font-medium hover:bg-primary/20 transition-colors">
                                    <Navigation class="w-3.5 h-3.5" />
                                    Google Maps
                                </a>
                                <span v-if="event.location" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-muted rounded-lg text-xs text-muted-foreground">
                                    <MapPin class="w-3.5 h-3.5" />
                                    {{ event.location }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-muted rounded-lg text-xs text-muted-foreground">
                                    <Users class="w-3.5 h-3.5" />
                                    {{ event.participants.length }} / {{ event.max_participants || '∞' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Desktop: Horizontal header -->
                    <div class="hidden sm:block p-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-2xl font-bold text-foreground">{{ event.name }}</h3>
                                <Badge :variant="getStatusBadge(event.status)" class="mt-2">{{ getStatusLabel(event.status) }}</Badge>
                            </div>
                            <!-- Countdown Desktop -->
                            <div v-if="countdown.type !== 'ended'" class="countdown-banner rounded-xl px-5 py-3 text-right" :class="countdown.type === 'ongoing' ? 'countdown-ongoing' : 'countdown-upcoming'">
                                <p class="text-xs font-semibold uppercase tracking-wider mb-1.5 countdown-label flex items-center justify-end gap-1.5">
                                    <span class="countdown-pulse-dot"></span>
                                    {{ countdown.remaining || countdown.label }}
                                </p>
                                <div v-if="countdown.days !== undefined" class="flex items-center gap-2">
                                    <div v-if="countdown.days > 0" class="countdown-unit">
                                        <span class="countdown-number text-xl">{{ countdown.days }}</span>
                                        <span class="countdown-text">Hari</span>
                                    </div>
                                    <span v-if="countdown.days > 0" class="countdown-separator text-xl">:</span>
                                    <div class="countdown-unit">
                                        <span class="countdown-number text-xl">{{ padZero(countdown.hours) }}</span>
                                        <span class="countdown-text">Jam</span>
                                    </div>
                                    <span class="countdown-separator text-xl">:</span>
                                    <div class="countdown-unit">
                                        <span class="countdown-number text-xl">{{ padZero(countdown.minutes) }}</span>
                                        <span class="countdown-text">Menit</span>
                                    </div>
                                    <span class="countdown-separator text-xl countdown-blink">:</span>
                                    <div class="countdown-unit">
                                        <span class="countdown-number text-xl countdown-seconds">{{ padZero(countdown.seconds) }}</span>
                                        <span class="countdown-text">Detik</span>
                                    </div>
                                </div>
                                <p v-else class="text-base font-bold countdown-label">{{ countdown.label }}</p>
                            </div>
                        </div>
                        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <h4 class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Waktu Pelaksanaan</h4>
                                    <div class="mt-1 flex items-center gap-3">
                                        <p class="text-foreground">
                                            {{ formatDate(event.start_date) }}
                                            <span v-if="event.end_date"> - {{ formatDate(event.end_date) }}</span>
                                        </p>
                                        <a v-if="hasCoordinates" :href="googleMapsUrl" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary/10 text-primary rounded-lg text-xs font-medium hover:bg-primary/20 transition-colors">
                                            <Navigation class="w-3.5 h-3.5" />
                                            Google Maps
                                        </a>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Lokasi</h4>
                                    <p class="mt-1 text-foreground">{{ event.location || '-' }}</p>
                                    <div v-if="hasCoordinates" class="mt-1 flex items-center gap-3">
                                        <span class="text-xs text-muted-foreground">
                                            <Navigation class="w-3 h-3 inline mr-1" />{{ parseFloat(event.latitude).toFixed(7) }}, {{ parseFloat(event.longitude).toFixed(7) }}
                                        </span>
                                        <a :href="googleMapsUrl" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-xs text-primary hover:underline">
                                            <ExternalLink class="w-3 h-3" /> Google Maps
                                        </a>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Penanggung Jawab (PIC)</h4>
                                    <p class="mt-1 text-foreground">{{ event.pic ? event.pic.full_name : '-' }}</p>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <h4 class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Maksimal Peserta</h4>
                                    <p class="mt-1 text-foreground">{{ event.max_participants || 'Tanpa Batas' }}</p>
                                </div>
                                <div>
                                    <h4 class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Visibilitas Publik</h4>
                                    <p class="mt-1 text-foreground">{{ event.is_public ? 'Ya (Tampil di halaman depan)' : 'Tidak' }}</p>
                                </div>
                                <div>
                                    <h4 class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Dibuat Oleh</h4>
                                    <p class="mt-1 text-foreground text-sm">{{ event.creator.name }} pada {{ formatDate(event.created_at) }}</p>
                                </div>
                            </div>
                        </div>
                        <div v-if="event.description" class="mt-8">
                            <h4 class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Deskripsi Kegiatan</h4>
                            <div class="mt-2 text-foreground whitespace-pre-wrap">{{ event.description }}</div>
                        </div>
                        <div v-if="hasCoordinates" class="mt-8">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Peta Lokasi</h4>
                                <a :href="googleMapsUrl" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-xs text-primary hover:underline">
                                    <ExternalLink class="w-3 h-3" /> Buka di Google Maps
                                </a>
                            </div>
                            <div ref="mapContainer" class="w-full h-64 rounded-lg border border-input z-0"></div>
                        </div>
                    </div>
                </div>

                <!-- Tabs (mobile) / Sections (desktop) -->
                <div class="bg-card border rounded-xl overflow-hidden">
                    <!-- Tab Navigation - mobile only -->
                    <div class="border-b sm:hidden">
                        <nav class="-mb-px flex overflow-x-auto no-scrollbar">
                            <button
                                v-for="tab in tabs"
                                :key="tab.id"
                                :class="[
                                    activeTab === tab.id
                                        ? 'border-primary text-primary'
                                        : 'border-transparent text-muted-foreground',
                                    'flex-1 whitespace-nowrap py-3 px-3 border-b-2 font-medium text-sm flex items-center justify-center gap-1.5 min-h-[44px]',
                                ]"
                                @click="activeTab = tab.id"
                            >
                                <component :is="tab.icon" class="w-4 h-4" />
                                <span>{{ tab.name }}</span>
                                <Badge v-if="tab.id === 'participants'" variant="secondary" class="text-[10px] px-1 py-0 ml-0.5">{{ event.participants.length }}</Badge>
                                <Badge v-if="tab.id === 'docs'" variant="secondary" class="text-[10px] px-1 py-0 ml-0.5">{{ event.documentations.length }}</Badge>
                            </button>
                        </nav>
                    </div>

                    <!-- Mobile Tab Content -->
                    <div class="sm:hidden p-4">
                        <!-- Info Tab -->
                        <div v-if="activeTab === 'info'" class="space-y-4">
                            <div class="space-y-1">
                                <h4 class="text-xs font-bold text-muted-foreground uppercase tracking-wider px-1 mb-2">Detail Kegiatan</h4>
                                <div class="bg-muted/30 rounded-xl divide-y divide-border">
                                    <div class="px-4 py-3">
                                        <span class="text-sm text-muted-foreground block mb-1">Waktu Mulai</span>
                                        <span class="text-sm font-medium text-foreground">{{ formatDate(event.start_date) }}</span>
                                    </div>
                                    <div v-if="event.end_date" class="px-4 py-3">
                                        <span class="text-sm text-muted-foreground block mb-1">Waktu Selesai</span>
                                        <span class="text-sm font-medium text-foreground">{{ formatDate(event.end_date) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center px-4 py-3">
                                        <span class="text-sm text-muted-foreground">Lokasi</span>
                                        <span class="text-sm font-medium text-foreground">{{ event.location || '-' }}</span>
                                    </div>
                                    <div v-if="hasCoordinates" class="px-4 py-3">
                                        <span class="text-sm text-muted-foreground block mb-1">Koordinat</span>
                                        <span class="text-sm font-medium text-foreground">{{ parseFloat(event.latitude).toFixed(7) }}, {{ parseFloat(event.longitude).toFixed(7) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center px-4 py-3">
                                        <span class="text-sm text-muted-foreground">PIC</span>
                                        <span class="text-sm font-medium text-foreground">{{ event.pic ? event.pic.full_name : '-' }}</span>
                                    </div>
                                    <div class="flex justify-between items-center px-4 py-3">
                                        <span class="text-sm text-muted-foreground">Maks. Peserta</span>
                                        <span class="text-sm font-medium text-foreground">{{ event.max_participants || 'Tanpa Batas' }}</span>
                                    </div>
                                    <div class="flex justify-between items-center px-4 py-3">
                                        <span class="text-sm text-muted-foreground">Publik</span>
                                        <Badge :variant="event.is_public ? 'default' : 'secondary'" class="text-xs">{{ event.is_public ? 'Ya' : 'Tidak' }}</Badge>
                                    </div>
                                    <div class="flex justify-between items-center px-4 py-3">
                                        <span class="text-sm text-muted-foreground">Dibuat</span>
                                        <span class="text-sm font-medium text-foreground">{{ event.creator.name }}</span>
                                    </div>
                                </div>
                            </div>

                            <div v-if="event.description" class="space-y-1">
                                <h4 class="text-xs font-bold text-muted-foreground uppercase tracking-wider px-1 mb-2">Deskripsi</h4>
                                <div class="bg-muted/30 rounded-xl px-4 py-3">
                                    <p class="text-sm text-foreground whitespace-pre-wrap">{{ event.description }}</p>
                                </div>
                            </div>

                            <div v-if="hasCoordinates" class="space-y-1">
                                <div class="flex items-center justify-between px-1 mb-2">
                                    <h4 class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Peta Lokasi</h4>
                                    <a :href="googleMapsUrl" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-xs text-primary hover:underline">
                                        <ExternalLink class="w-3 h-3" /> Google Maps
                                    </a>
                                </div>
                                <div ref="mapContainerMobile" class="w-full h-48 rounded-xl border border-input z-0"></div>
                            </div>
                        </div>

                        <!-- Docs Tab -->
                        <div v-if="activeTab === 'docs'">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-bold text-muted-foreground uppercase tracking-wider">{{ event.documentations.length }} foto</span>
                                <Button v-if="hasPermission('manage_events')" size="sm" variant="outline" @click="showDocModal = true" class="h-8 text-xs gap-1">
                                    <Upload class="w-3.5 h-3.5" /> Upload
                                </Button>
                            </div>
                            <div v-if="event.documentations.length > 0" class="grid grid-cols-3 gap-2">
                                <div v-for="(doc, index) in event.documentations" :key="doc.id" class="relative aspect-square rounded-xl overflow-hidden group">
                                    <img :src="doc.url" :alt="doc.caption" class="w-full h-full object-cover" @click="zoomedIndex = index" />
                                    <button
                                        v-if="hasPermission('manage_events')"
                                        @click.stop="confirmDeleteDoc(doc)"
                                        class="absolute top-1 right-1 w-6 h-6 bg-black/50 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                                    >
                                        <X class="w-3 h-3" />
                                    </button>
                                </div>
                            </div>
                            <div v-else class="text-center py-10">
                                <Camera class="mx-auto h-10 w-10 text-muted-foreground/40 mb-3" />
                                <p class="text-sm text-muted-foreground">Belum ada dokumentasi.</p>
                            </div>
                        </div>

                        <!-- Participants Tab -->
                        <div v-if="activeTab === 'participants'">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-bold text-muted-foreground uppercase tracking-wider">{{ event.participants.length }} / {{ event.max_participants || '∞' }} peserta</span>
                                <Button v-if="hasPermission('manage_events')" size="sm" variant="outline" @click="showParticipantModal = true" class="h-8 text-xs gap-1">
                                    <UserPlus class="w-3.5 h-3.5" /> Tambah
                                </Button>
                            </div>
                            <div v-if="event.participants.length > 0" class="divide-y divide-border bg-muted/30 rounded-xl overflow-hidden">
                                <div v-for="p in event.participants" :key="p.id" class="flex items-center gap-3 px-4 py-3">
                                    <div class="shrink-0 w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                                        <span class="text-primary font-semibold text-sm">{{ p.full_name.charAt(0).toUpperCase() }}</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-foreground truncate">{{ p.full_name }}</p>
                                        <p class="text-xs text-muted-foreground">{{ p.member_code }}</p>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <span class="px-2 py-0.5 text-xs rounded-full font-semibold" :class="getAttendanceBadgeClass(p.pivot.attendance_status)">
                                            {{ getAttendanceLabel(p.pivot.attendance_status) }}
                                        </span>
                                        <DropdownMenu v-if="hasPermission('manage_events')">
                                            <DropdownMenuTrigger as-child>
                                                <button class="w-7 h-7 flex items-center justify-center rounded-full hover:bg-muted">
                                                    <MoreVertical class="w-3.5 h-3.5 text-muted-foreground" />
                                                </button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuItem @click="updateAttendance(p, 'attended')">Hadir</DropdownMenuItem>
                                                <DropdownMenuItem @click="updateAttendance(p, 'absent')">Tidak Hadir</DropdownMenuItem>
                                                <DropdownMenuItem @click="updateAttendance(p, 'registered')">Terdaftar</DropdownMenuItem>
                                                <DropdownMenuItem @click="confirmRemoveParticipant(p)" class="text-destructive">
                                                    <Trash2 class="h-4 w-4 mr-2" /> Hapus
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-10">
                                <Users class="mx-auto h-10 w-10 text-muted-foreground/40 mb-3" />
                                <p class="text-sm text-muted-foreground">Belum ada peserta terdaftar.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Desktop: Documentation Section -->
                <div class="hidden sm:block bg-card border rounded-xl overflow-hidden">
                    <div class="p-6 border-b flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-foreground">Dokumentasi ({{ event.documentations.length }})</h3>
                        <Button v-if="hasPermission('manage_events')" size="sm" @click="showDocModal = true">
                            <Upload class="w-4 h-4 mr-1" /> Upload Foto
                        </Button>
                    </div>
                    <div v-if="event.documentations.length > 0" class="p-6 grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        <div v-for="(doc, index) in event.documentations" :key="doc.id" class="relative group">
                            <img :src="doc.url" :alt="doc.caption" class="w-full h-32 object-cover rounded shadow cursor-pointer" @click="zoomedIndex = index" />
                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity rounded">
                                <button v-if="hasPermission('manage_events')" @click.stop="confirmDeleteDoc(doc)" class="text-white hover:text-destructive p-2">
                                    <Trash2 class="w-5 h-5" />
                                </button>
                            </div>
                            <p v-if="doc.caption" class="mt-1 text-xs text-muted-foreground truncate">{{ doc.caption }}</p>
                        </div>
                    </div>
                    <div v-else class="p-12 text-center text-muted-foreground">Belum ada dokumentasi.</div>
                </div>

                <!-- Desktop: Participants Section -->
                <div class="hidden sm:block bg-card border rounded-xl overflow-hidden">
                    <div class="p-6 border-b flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-foreground">Daftar Peserta ({{ event.participants.length }} / {{ event.max_participants || '∞' }})</h3>
                        <Button v-if="hasPermission('manage_events')" size="sm" @click="showParticipantModal = true">
                            <UserPlus class="w-4 h-4 mr-1" /> Tambah Peserta
                        </Button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-border">
                            <thead class="bg-muted">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Nama Anggota</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Tanggal Daftar</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Status Kehadiran</th>
                                    <th v-if="hasPermission('manage_events')" class="px-6 py-3 text-right text-xs font-medium text-muted-foreground uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-card divide-y divide-border">
                                <tr v-for="p in event.participants" :key="p.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-foreground">{{ p.full_name }}</div>
                                        <div class="text-xs text-muted-foreground">{{ p.member_code }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ formatDate(p.pivot.registration_date) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <span class="px-2 py-1 text-xs rounded-full font-semibold" :class="getAttendanceBadgeClass(p.pivot.attendance_status)">
                                                {{ getAttendanceLabel(p.pivot.attendance_status) }}
                                            </span>
                                            <select
                                                v-if="hasPermission('manage_events')"
                                                :value="p.pivot.attendance_status"
                                                @change="updateAttendance(p, $event.target.value)"
                                                class="text-xs rounded border-input py-0"
                                            >
                                                <option value="registered">Terdaftar</option>
                                                <option value="attended">Hadir</option>
                                                <option value="absent">Tidak Hadir</option>
                                            </select>
                                        </div>
                                    </td>
                                    <td v-if="hasPermission('manage_events')" class="px-6 py-4 whitespace-nowrap text-right">
                                        <Button variant="ghost" size="sm" class="text-destructive h-7 px-2" @click="confirmRemoveParticipant(p)">
                                            <Trash2 class="w-4 h-4" />
                                        </Button>
                                    </td>
                                </tr>
                                <tr v-if="event.participants.length === 0">
                                    <td colspan="4" class="px-6 py-12 text-center text-muted-foreground">Belum ada peserta terdaftar.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Participant Dialog -->
        <Dialog :open="showParticipantModal" @update:open="(val) => { if (!val) showParticipantModal = false; }">
            <DialogContent>
                <DialogHeader><DialogTitle>Tambah Peserta Kegiatan</DialogTitle></DialogHeader>
                <div class="space-y-4">
                    <div>
                        <Label for="member_id">Pilih Anggota</Label>
                        <select id="member_id" v-model="participantForm.member_id" class="mt-1 block w-full border-input focus:border-ring focus:ring-ring rounded-md shadow-sm">
                            <option value="">Pilih Member...</option>
                            <option v-for="member in members" :key="member.id" :value="member.id">{{ member.full_name }} ({{ member.member_code }})</option>
                        </select>
                        <p v-if="participantForm.errors.member_id" class="mt-2 text-sm text-destructive">{{ participantForm.errors.member_id }}</p>
                    </div>
                    <div>
                        <Label for="notes">Catatan (Opsional)</Label>
                        <Textarea id="notes" v-model="participantForm.notes" rows="2" />
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="showParticipantModal = false">Batal</Button>
                    <Button :disabled="participantForm.processing" @click="addParticipant">Tambah</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Upload Doc Dialog -->
        <Dialog :open="showDocModal" @update:open="(val) => { if (!val) { showDocModal = false; resetDocForm(); } }">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader><DialogTitle>Upload Dokumentasi</DialogTitle></DialogHeader>
                <div class="space-y-4">
                    <!-- Drop zone -->
                    <div>
                        <Label class="mb-2 block">Pilih Foto</Label>
                        <div
                            @dragover.prevent="isDragging = true"
                            @dragleave.prevent="isDragging = false"
                            @drop.prevent="handleDrop"
                            @click="fileInputRef?.click()"
                            class="relative border-2 border-dashed rounded-xl p-6 text-center cursor-pointer transition-all duration-200"
                            :class="isDragging ? 'border-primary bg-primary/5 scale-[1.01]' : 'border-border hover:border-primary/50 hover:bg-muted/30'"
                        >
                            <input
                                ref="fileInputRef"
                                type="file"
                                multiple
                                accept="image/*"
                                @change="handleFileSelect"
                                class="hidden"
                            />
                            <div class="flex flex-col items-center gap-2">
                                <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center">
                                    <Camera class="w-6 h-6 text-primary" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-foreground">
                                        <span class="text-primary">Pilih foto</span> atau seret ke sini
                                    </p>
                                    <p class="text-xs text-muted-foreground mt-1">PNG, JPG, WEBP &bull; Maks 5MB per file</p>
                                </div>
                            </div>
                        </div>
                        <p v-if="docForm.errors['files.0']" class="mt-2 text-sm text-destructive">{{ docForm.errors['files.0'] }}</p>
                        <p v-if="docForm.errors.files" class="mt-2 text-sm text-destructive">{{ docForm.errors.files }}</p>
                    </div>

                    <!-- File previews -->
                    <div v-if="docPreviews.length > 0" class="space-y-2">
                        <div class="flex items-center justify-between">
                            <Label class="text-xs text-muted-foreground">{{ docPreviews.length }} foto dipilih</Label>
                            <button @click="resetDocForm" class="text-xs text-destructive hover:underline">Hapus semua</button>
                        </div>
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 max-h-[200px] overflow-y-auto rounded-lg">
                            <div
                                v-for="(preview, index) in docPreviews"
                                :key="index"
                                class="relative aspect-square rounded-lg overflow-hidden group bg-muted"
                            >
                                <img :src="preview.url" :alt="preview.name" class="w-full h-full object-cover" />
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-colors flex items-center justify-center">
                                    <button
                                        @click.stop="removeFile(index)"
                                        class="w-7 h-7 bg-destructive text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow-sm"
                                    >
                                        <X class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                                <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/60 to-transparent px-1.5 py-1">
                                    <p class="text-[10px] text-white/80 truncate">{{ formatFileSize(preview.size) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Caption -->
                    <div>
                        <Label for="caption">Keterangan (Opsional)</Label>
                        <Input id="caption" v-model="docForm.caption" type="text" placeholder="Tambahkan keterangan foto..." class="mt-1" />
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="showDocModal = false; resetDocForm()">Batal</Button>
                    <Button :disabled="docForm.processing || docPreviews.length === 0" @click="uploadDoc">
                        <Upload class="w-4 h-4 mr-1" />
                        Upload {{ docPreviews.length > 0 ? `(${docPreviews.length})` : '' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Photo Zoom Dialog -->
        <Dialog :open="zoomedIndex !== null" @update:open="(val) => { if (!val) zoomedIndex = null; }">
            <DialogContent class="max-w-4xl p-2 sm:p-6">
                <div class="flex flex-col items-center relative">
                    <button v-if="event.documentations.length > 1" @click="prevDoc" class="absolute left-1 sm:left-2 top-1/2 -translate-y-1/2 bg-black/50 text-white p-2 rounded-full hover:bg-black/70 transition z-10">
                        <ChevronLeft class="h-5 w-5 sm:h-6 sm:w-6" />
                    </button>
                    <img v-if="zoomedDoc" :src="zoomedDoc.url" :alt="zoomedDoc.caption" class="max-w-full max-h-[70vh] rounded shadow-xl" />
                    <button v-if="event.documentations.length > 1" @click="nextDoc" class="absolute right-1 sm:right-2 top-1/2 -translate-y-1/2 bg-black/50 text-white p-2 rounded-full hover:bg-black/70 transition z-10">
                        <ChevronRight class="h-5 w-5 sm:h-6 sm:w-6" />
                    </button>
                    <div v-if="zoomedDoc" class="mt-3 text-center">
                        <p v-if="zoomedDoc.caption" class="text-foreground font-medium text-sm">{{ zoomedDoc.caption }}</p>
                        <p class="text-xs text-muted-foreground mt-1">{{ zoomedIndex + 1 }} / {{ event.documentations.length }}</p>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Delete Event -->
        <DeleteConfirmDialog
            :open="showDeleteDialog"
            title="Hapus Kegiatan"
            :description="`Apakah Anda yakin ingin menghapus kegiatan ${event.name}? Tindakan ini tidak dapat dibatalkan.`"
            @confirm="deleteEvent"
            @cancel="showDeleteDialog = false"
        />

        <!-- Delete Participant -->
        <DeleteConfirmDialog
            :open="!!deleteParticipantTarget"
            title="Hapus Peserta"
            :description="`Hapus ${deleteParticipantTarget?.full_name} dari daftar peserta?`"
            @confirm="doRemoveParticipant"
            @cancel="deleteParticipantTarget = null"
        />

        <!-- Delete Doc -->
        <DeleteConfirmDialog
            :open="!!deleteDocTarget"
            title="Hapus Dokumentasi"
            description="Apakah Anda yakin ingin menghapus foto ini?"
            @confirm="doDeleteDoc"
            @cancel="deleteDocTarget = null"
        />
    </AuthenticatedLayout>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

/* Countdown styles */
.countdown-upcoming {
    background: hsl(var(--primary) / 0.08);
}
.countdown-ongoing {
    background: hsl(142 71% 45% / 0.1);
}
.countdown-ongoing .countdown-label { color: hsl(142 71% 35%); }
.countdown-ongoing .countdown-number { color: hsl(142 71% 30%); }
.countdown-ongoing .countdown-separator { color: hsl(142 71% 35%); }
.countdown-ongoing .countdown-text { color: hsl(142 71% 40%); }
.countdown-ongoing .countdown-pulse-dot { background: hsl(142 71% 45%); }

.countdown-upcoming .countdown-label { color: hsl(var(--primary)); }
.countdown-upcoming .countdown-number { color: hsl(var(--primary)); }
.countdown-upcoming .countdown-separator { color: hsl(var(--primary) / 0.5); }
.countdown-upcoming .countdown-text { color: hsl(var(--primary) / 0.7); }
.countdown-upcoming .countdown-pulse-dot { background: hsl(var(--primary)); }

.countdown-unit {
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 2.5rem;
}
.countdown-number {
    font-size: 1.125rem;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
    line-height: 1;
}
.countdown-text {
    font-size: 0.6rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-top: 2px;
}
.countdown-separator {
    font-weight: 700;
    font-size: 1.125rem;
    line-height: 1;
    align-self: flex-start;
}

/* Blinking colon */
.countdown-blink {
    animation: blink 1s step-end infinite;
}
@keyframes blink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0; }
}

/* Seconds flip animation */
.countdown-seconds {
    animation: tick 1s ease-out infinite;
}
@keyframes tick {
    0% { transform: translateY(-2px); opacity: 0.6; }
    20% { transform: translateY(0); opacity: 1; }
    100% { transform: translateY(0); opacity: 1; }
}

/* Pulsing dot */
.countdown-pulse-dot {
    display: inline-block;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    animation: pulse-dot 2s ease-in-out infinite;
}
@keyframes pulse-dot {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.4; transform: scale(0.7); }
}
</style>
