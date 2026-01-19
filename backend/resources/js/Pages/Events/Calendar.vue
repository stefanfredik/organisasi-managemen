<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    events: Array,
});

const currentDate = ref(new Date());

const monthNames = [
    "Januari", "Februari", "Maret", "April", "Mei", "Juni",
    "Juli", "Agustus", "September", "Oktober", "November", "Desember"
];

const currentMonthName = computed(() => monthNames[currentDate.value.getMonth()]);
const currentYear = computed(() => currentDate.value.getFullYear());

const daysInMonth = computed(() => {
    const year = currentDate.value.getFullYear();
    const month = currentDate.value.getMonth();
    const date = new Date(year, month, 1);
    const days = [];
    
    // Fill previous month days
    const firstDay = date.getDay();
    for (let i = 0; i < firstDay; i++) {
        days.push({ day: null });
    }
    
    // Fill current month days
    while (date.getMonth() === month) {
        const d = new Date(date);
        const dayEvents = props.events.filter(e => {
            const startDate = new Date(e.start_date);
            return startDate.getDate() === d.getDate() && 
                   startDate.getMonth() === d.getMonth() && 
                   startDate.getFullYear() === d.getFullYear();
        });
        
        days.push({
            day: d.getDate(),
            date: d,
            events: dayEvents
        });
        date.setDate(date.getDate() + 1);
    }
    
    return days;
});

const prevMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1, 1);
};

const nextMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 1);
};

const getStatusColor = (status) => {
    const colors = {
        draft: 'bg-gray-200 text-gray-700',
        published: 'bg-blue-200 text-blue-700',
        ongoing: 'bg-yellow-200 text-yellow-700',
        completed: 'bg-green-200 text-green-700',
        cancelled: 'bg-red-200 text-red-700',
    };
    return colors[status] || 'bg-gray-200 text-gray-700';
};
</script>

<template>
    <Head title="Kalender Kegiatan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Kalender Kegiatan
                </h2>
                <Link
                    :href="route('events.index')"
                    class="text-indigo-600 hover:text-indigo-900 font-medium"
                >
                    Kembali ke Daftar
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Calendar Header -->
                        <div class="flex items-center justify-between mb-8">
                            <h3 class="text-2xl font-bold text-gray-900">
                                {{ currentMonthName }} {{ currentYear }}
                            </h3>
                            <div class="flex gap-2">
                                <button @click="prevMonth" class="p-2 rounded hover:bg-gray-100 border">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>
                                <button @click="nextMonth" class="p-2 rounded hover:bg-gray-100 border">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Calendar Grid -->
                        <div class="grid grid-cols-7 gap-px bg-gray-200 border border-gray-200 rounded-lg overflow-hidden">
                            <!-- Header Days -->
                            <div v-for="day in ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']" :key="day" 
                                class="bg-gray-50 p-2 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                {{ day }}
                            </div>
                            
                            <!-- Day Cells -->
                            <div v-for="(item, index) in daysInMonth" :key="index" 
                                class="bg-white min-h-[120px] p-2 flex flex-col gap-1 transition hover:bg-blue-50">
                                <span v-if="item.day" class="text-sm font-medium text-gray-600">{{ item.day }}</span>
                                <div v-if="item.events && item.events.length > 0" class="flex flex-col gap-1">
                                    <Link v-for="event in item.events" :key="event.id" 
                                        :href="route('events.show', event)"
                                        class="p-1 text-[10px] rounded leading-tight truncate"
                                        :class="getStatusColor(event.status)"
                                        :title="event.name"
                                    >
                                        {{ event.name }}
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
