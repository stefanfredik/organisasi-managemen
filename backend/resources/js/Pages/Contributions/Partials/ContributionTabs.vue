<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  active: {
    type: String,
    default: 'history'
  }
});
</script>

<template>
  <div class="mb-6">
    <div class="flex gap-2 overflow-x-auto">
      <Link
        :href="route('contributions.index')"
        class="px-4 py-2 text-sm font-bold rounded-xl transition"
        :class="active === 'history' ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : 'bg-white text-gray-600 border border-gray-100 hover:bg-gray-50'"
      >
        Riwayat Transaksi
      </Link>
      <Link
        :href="route('contributions.verification')"
        class="px-4 py-2 text-sm font-bold rounded-xl transition"
        :class="active === 'verification' ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : 'bg-white text-gray-600 border border-gray-100 hover:bg-gray-50'"
      >
        Verifikasi
      </Link>
      <Link
        :href="route('contributions.monitoring.index')"
        class="px-4 py-2 text-sm font-bold rounded-xl transition"
        :class="active === 'monitoring' ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : 'bg-white text-gray-600 border border-gray-100 hover:bg-gray-50'"
      >
        Monitoring Iuran
      </Link>
    </div>
  </div>
</template>
