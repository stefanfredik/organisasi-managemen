<script setup>
import { computed } from 'vue';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    value: {
        type: [String, Number],
        required: true,
    },
    icon: {
        type: String,
        default: 'chart',
    },
    color: {
        type: String,
        default: 'indigo',
        validator: (value) => ['indigo', 'green', 'blue', 'yellow', 'red', 'purple', 'pink'].includes(value),
    },
    trend: {
        type: Object,
        default: null,
        // { direction: 'up' | 'down', value: '12%', label: 'from last month' }
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const colorClasses = computed(() => {
    const colors = {
        indigo: {
            bg: 'bg-indigo-50',
            icon: 'text-indigo-600',
            trend: 'text-indigo-600',
        },
        green: {
            bg: 'bg-green-50',
            icon: 'text-green-600',
            trend: 'text-green-600',
        },
        blue: {
            bg: 'bg-blue-50',
            icon: 'text-blue-600',
            trend: 'text-blue-600',
        },
        yellow: {
            bg: 'bg-yellow-50',
            icon: 'text-yellow-600',
            trend: 'text-yellow-600',
        },
        red: {
            bg: 'bg-red-50',
            icon: 'text-red-600',
            trend: 'text-red-600',
        },
        purple: {
            bg: 'bg-purple-50',
            icon: 'text-purple-600',
            trend: 'text-purple-600',
        },
        pink: {
            bg: 'bg-pink-50',
            icon: 'text-pink-600',
            trend: 'text-pink-600',
        },
    };
    return colors[props.color];
});

const iconPath = computed(() => {
    const icons = {
        chart: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
        users: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
        calendar: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        cash: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        document: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        bell: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
        gift: 'M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7',
    };
    return icons[props.icon] || icons.chart;
});

const formatValue = computed(() => {
    if (typeof props.value === 'number') {
        return props.value.toLocaleString('id-ID');
    }
    return props.value;
});
</script>

<template>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-600 mb-1">{{ title }}</p>
                <div v-if="loading" class="animate-pulse">
                    <div class="h-8 bg-gray-200 rounded w-24"></div>
                </div>
                <p v-else class="text-3xl font-bold text-gray-900">{{ formatValue }}</p>
                
                <!-- Trend Indicator -->
                <div v-if="trend && !loading" class="mt-2 flex items-center text-sm">
                    <svg 
                        v-if="trend.direction === 'up'" 
                        class="w-4 h-4 mr-1 text-green-600" 
                        fill="none" 
                        stroke="currentColor" 
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                    </svg>
                    <svg 
                        v-else-if="trend.direction === 'down'" 
                        class="w-4 h-4 mr-1 text-red-600" 
                        fill="none" 
                        stroke="currentColor" 
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                    </svg>
                    <span :class="trend.direction === 'up' ? 'text-green-600' : 'text-red-600'" class="font-medium">
                        {{ trend.value }}
                    </span>
                    <span class="text-gray-500 ml-1">{{ trend.label }}</span>
                </div>
            </div>
            
            <!-- Icon -->
            <div :class="[colorClasses.bg, 'p-3 rounded-lg']">
                <svg :class="[colorClasses.icon, 'w-8 h-8']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="iconPath" />
                </svg>
            </div>
        </div>
    </div>
</template>
