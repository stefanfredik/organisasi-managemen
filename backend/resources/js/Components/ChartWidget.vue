<script setup>
import { ref, computed, onMounted, watch } from 'vue';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    data: {
        type: Array,
        required: true,
        // Format: [{ label: 'Jan', value: 100 }, ...]
    },
    type: {
        type: String,
        default: 'bar',
        validator: (value) => ['bar', 'line'].includes(value),
    },
    color: {
        type: String,
        default: 'indigo',
    },
    height: {
        type: String,
        default: '300px',
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const maxValue = computed(() => {
    if (!props.data || props.data.length === 0) return 0;
    return Math.max(...props.data.map(d => d.value || 0));
});

const getBarHeight = (value) => {
    if (maxValue.value === 0) return '0%';
    return `${(value / maxValue.value) * 100}%`;
};

const formatValue = (value) => {
    if (typeof value === 'number') {
        return value.toLocaleString('id-ID');
    }
    return value;
};

const colorClasses = computed(() => {
    const colors = {
        indigo: 'bg-indigo-500 hover:bg-indigo-600',
        green: 'bg-green-500 hover:bg-green-600',
        blue: 'bg-blue-500 hover:bg-blue-600',
        yellow: 'bg-yellow-500 hover:bg-yellow-600',
        red: 'bg-red-500 hover:bg-red-600',
        purple: 'bg-purple-500 hover:bg-purple-600',
    };
    return colors[props.color] || colors.indigo;
});
</script>

<template>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ title }}</h3>
        
        <!-- Loading State -->
        <div v-if="loading" class="animate-pulse" :style="{ height }">
            <div class="h-full bg-gray-200 rounded"></div>
        </div>
        
        <!-- Empty State -->
        <div v-else-if="!data || data.length === 0" class="flex items-center justify-center" :style="{ height }">
            <div class="text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <p class="mt-2 text-sm text-gray-500">Tidak ada data</p>
            </div>
        </div>
        
        <!-- Bar Chart -->
        <div v-else-if="type === 'bar'" class="relative" :style="{ height }">
            <div class="flex items-end justify-between h-full space-x-2">
                <div 
                    v-for="(item, index) in data" 
                    :key="index"
                    class="flex-1 flex flex-col items-center group"
                >
                    <!-- Bar -->
                    <div class="w-full flex items-end justify-center relative" style="height: calc(100% - 40px)">
                        <div 
                            :class="[colorClasses, 'w-full rounded-t transition-all duration-300 relative']"
                            :style="{ height: getBarHeight(item.value) }"
                        >
                            <!-- Tooltip on hover -->
                            <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                {{ formatValue(item.value) }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Label -->
                    <div class="mt-2 text-xs text-gray-600 text-center truncate w-full">
                        {{ item.label }}
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Line Chart (Simple implementation) -->
        <div v-else-if="type === 'line'" class="relative" :style="{ height }">
            <svg class="w-full h-full" viewBox="0 0 400 200" preserveAspectRatio="none">
                <!-- Grid lines -->
                <line v-for="i in 5" :key="`grid-${i}`" 
                    :x1="0" :y1="i * 40" :x2="400" :y2="i * 40" 
                    stroke="#e5e7eb" stroke-width="1" 
                />
                
                <!-- Line path -->
                <polyline
                    :points="data.map((item, index) => {
                        const x = (index / (data.length - 1)) * 400;
                        const y = 200 - (item.value / maxValue) * 180;
                        return `${x},${y}`;
                    }).join(' ')"
                    fill="none"
                    :stroke="colorClasses.split(' ')[0].replace('bg-', '')"
                    stroke-width="3"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
                
                <!-- Data points -->
                <circle
                    v-for="(item, index) in data"
                    :key="`point-${index}`"
                    :cx="(index / (data.length - 1)) * 400"
                    :cy="200 - (item.value / maxValue) * 180"
                    r="4"
                    :fill="colorClasses.split(' ')[0].replace('bg-', '')"
                    class="cursor-pointer"
                >
                    <title>{{ item.label }}: {{ formatValue(item.value) }}</title>
                </circle>
            </svg>
            
            <!-- Labels -->
            <div class="flex justify-between mt-2">
                <span v-for="(item, index) in data" :key="`label-${index}`" class="text-xs text-gray-600">
                    {{ item.label }}
                </span>
            </div>
        </div>
    </div>
</template>
