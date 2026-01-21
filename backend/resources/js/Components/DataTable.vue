<script setup>
import Pagination from '@/Components/Pagination.vue';
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    data: Object, // Paginated object
    columns: Array, // [{ key, label, sortable, headerClass, cellClass, format }]
    filters: Object,
    actions: {
        type: Boolean,
        default: true
    },
    searchable: {
        type: Boolean,
        default: true
    },
    searchRoute: String,
});

const search = ref(props.filters?.search || '');

watch(search, debounce((value) => {
    if (props.searchRoute) {
        router.get(props.searchRoute, {
            ...props.filters,
            search: value,
        }, {
            preserveState: true,
            replace: true,
        });
    }
}, 300));
</script>

<template>
    <div class="bg-white overflow-hidden shadow-xl shadow-slate-200/50 sm:rounded-[2rem] border border-slate-100">
        <!-- Header / Search -->
        <div v-if="searchable" class="p-6 border-b border-slate-100">
            <div class="relative max-w-sm">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cari data..."
                    class="w-full bg-slate-50 border-slate-200 rounded-2xl py-3 pl-11 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all font-medium"
                >
                <svg class="absolute left-4 top-3.5 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th 
                            v-for="col in columns" 
                            :key="col.key"
                            class="px-8 py-5 text-[11px] font-black uppercase tracking-[0.2em] text-slate-400"
                            :class="col.headerClass"
                        >
                            {{ col.label }}
                        </th>
                        <th v-if="actions" class="px-8 py-5 text-[11px] font-black uppercase tracking-[0.2em] text-slate-400 text-right">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <tr v-for="(row, index) in data.data" :key="row.id || index" class="hover:bg-slate-50/50 transition-colors group">
                        <td 
                            v-for="col in columns" 
                            :key="col.key"
                            class="px-8 py-5"
                            :class="col.cellClass"
                        >
                            <slot :name="'cell-' + col.key" :row="row">
                                <span class="text-sm font-medium text-slate-600">
                                    {{ col.format ? col.format(row[col.key]) : row[col.key] }}
                                </span>
                            </slot>
                        </td>
                        <td v-if="actions" class="px-8 py-5 text-right">
                            <slot name="actions" :row="row"></slot>
                        </td>
                    </tr>
                    <tr v-if="data.data.length === 0">
                        <td :colspan="columns.length + (actions ? 1 : 0)" class="px-8 py-20 text-center text-slate-400 italic">
                            Data tidak ditemukan.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-8 py-6 bg-slate-50/50 border-t border-slate-100">
            <Pagination :links="data.links" />
        </div>
    </div>
</template>
