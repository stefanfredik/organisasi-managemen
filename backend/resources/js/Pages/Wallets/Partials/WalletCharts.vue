<script setup>
import { computed, ref, onMounted } from 'vue';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  LineElement,
  PointElement,
  CategoryScale,
  LinearScale,
  ArcElement,
  Filler
} from 'chart.js';
import { Line, Doughnut } from 'vue-chartjs';

ChartJS.register(CategoryScale, LinearScale, LineElement, PointElement, ArcElement, Title, Tooltip, Legend, Filler);

const props = defineProps({
    data: Object
});

const chartRef = ref(null);

const createGradient = (ctx, colorStart, colorEnd) => {
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, colorStart);
    gradient.addColorStop(1, colorEnd);
    return gradient;
};

// Enhancing the data with gradients and fill
const trendData = computed(() => {
    const original = props.data.trend;
    if (!chartRef.value) return original; // Fallback

    const ctx = chartRef.value.chart.ctx; // Access canvas context via ref

    return {
        ...original,
        datasets: original.datasets.map(ds => ({
            ...ds,
            fill: true,
            tension: 0.5,
            pointRadius: 0, // Hide points for clean look like image
            pointHoverRadius: 6,
            borderWidth: 3,
            borderColor: ds.label === 'Pemasukan' ? '#34d399' : '#f87171', // Solid line color
            backgroundColor: ds.label === 'Pemasukan' 
                ? createGradient(ctx, 'rgba(52, 211, 153, 0.5)', 'rgba(52, 211, 153, 0.0)') 
                : createGradient(ctx, 'rgba(248, 113, 113, 0.5)', 'rgba(248, 113, 113, 0.0)'),
        }))
    };
});

const lineOptions = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: {
        mode: 'index',
        intersect: false,
    },
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                usePointStyle: true,
                padding: 20
            }
        },
        tooltip: {
            backgroundColor: 'rgba(255, 255, 255, 0.9)',
            titleColor: '#1f2937',
            bodyColor: '#4b5563',
            borderColor: '#e5e7eb',
            borderWidth: 1,
            padding: 10,
            displayColors: true,
            boxPadding: 4
        }
    },
    scales: {
        x: {
            grid: {
                display: false // Clean look
            },
            border: {
                display: false
            }
        },
        y: {
            beginAtZero: true,
            grid: {
                color: '#f3f4f6',
                borderDash: [5, 5]
            },
            border: {
                display: false
            },
            ticks: {
                callback: (value) => {
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', notation: 'compact' }).format(value);
                },
                color: '#9ca3af',
                font: {
                    size: 10
                }
            }
        }
    }
};

const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'right',
        }
    }
};

const hasData = computed(() => {
    // Check if there is any non-zero data in trends or categories
    const hasTrend = props.data.trend.datasets.some(ds => ds.data.some(v => v > 0));
    const hasCategories = props.data.categories.datasets[0].data.length > 0;
    return hasTrend || hasCategories;
});
</script>

<template>
    <div v-if="hasData" class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
        <!-- Trend Chart -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 h-80">
            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Tren Arus Kas (6 Bulan)</h3>
            <div class="h-64">
                <Line ref="chartRef" :data="trendData" :options="lineOptions" />
            </div>
        </div>

        <!-- Category Chart -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 h-80">
             <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Distribusi Pengeluaran</h3>
             <div class="h-64 relative">
                 <div v-if="props.data.categories.datasets[0].data.length === 0" class="absolute inset-0 flex items-center justify-center text-gray-400 text-xs">
                     Belum ada data pengeluaran.
                 </div>
                <Doughnut v-else :data="props.data.categories" :options="doughnutOptions" />
            </div>
        </div>
    </div>
    <div v-else class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 text-center mb-8">
        <p class="text-gray-400">Belum ada cukup data untuk menampilkan grafik analisis.</p>
    </div>
</template>
