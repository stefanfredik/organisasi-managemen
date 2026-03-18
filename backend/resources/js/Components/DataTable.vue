<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import { Pagination, PaginationList, PaginationListItem, PaginationFirst, PaginationLast, PaginationPrev, PaginationNext } from '@/components/ui/pagination';
import { Search, Filter, ChevronDown, ChevronUp, ChevronsUpDown, Eye, Pencil, Trash2, MoreHorizontal, X } from 'lucide-vue-next';

const props = defineProps({
    data: { type: Object, required: true },
    columns: { type: Array, required: true },
    filters: { type: Object, default: () => ({}) },
    searchRoute: { type: String, default: null },
    actions: { type: Boolean, default: true },
    searchable: { type: Boolean, default: true },
    sortable: { type: Boolean, default: true },
    striped: { type: Boolean, default: false },
    dense: { type: Boolean, default: false },
    pageSize: { type: Number, default: 10 },
    showColumnFilter: { type: Boolean, default: false },
});

const emit = defineEmits(['action', 'sort', 'filter']);

const search = ref(props.filters?.search || '');
const sortField = ref(props.filters?.sort_by || null);
const sortDirection = ref(props.filters?.sort_dir || 'asc');
const visibleColumns = ref(props.columns.map(col => col.key));
const showFilters = ref(false);

// Column visibility toggle
const toggleColumn = (key) => {
    if (visibleColumns.value.includes(key)) {
        visibleColumns.value = visibleColumns.value.filter(k => k !== key);
    } else {
        visibleColumns.value.push(key);
    }
};

// Sorting
const handleSort = (key) => {
    if (!props.sortable) return;
    
    if (sortField.value === key) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = key;
        sortDirection.value = 'asc';
    }
    
    emit('sort', { field: sortField.value, direction: sortDirection.value });
    applyFilters();
};

const getSortIcon = (key) => {
    if (sortField.value !== key) return ChevronsUpDown;
    return sortDirection.value === 'asc' ? ChevronUp : ChevronDown;
};

// Search with debounce
watch(search, debounce(() => {
    applyFilters();
}, 300));

// Apply filters to server
const applyFilters = () => {
    if (!props.searchRoute) return;

    // Build query string from filters
    const queryParams = {
        search: search.value,
        sort_by: sortField.value,
        sort_dir: sortDirection.value,
    };

    // Remove null/empty values
    Object.keys(queryParams).forEach(key => {
        if (queryParams[key] === null || queryParams[key] === undefined || queryParams[key] === '') {
            delete queryParams[key];
        }
    });

    // If searchRoute is a route name (doesn't start with / or http), use current URL
    let url = props.searchRoute;
    if (!props.searchRoute.startsWith('/') && !props.searchRoute.startsWith('http')) {
        // Use current URL path
        url = window.location.pathname;
    }

    router.get(url, queryParams, {
        preserveState: true,
        replace: true,
    });
};

// Reset filters
const resetFilters = () => {
    search.value = '';
    sortField.value = null;
    sortDirection.value = 'asc';
    applyFilters();
};

// Action handlers
const handleAction = (action, row) => {
    emit('action', { action, row });
};

// Check if column is visible
const isColumnVisible = (key) => visibleColumns.value.includes(key);

// Get visible columns
const visibleColumnsList = computed(() => 
    props.columns.filter(col => visibleColumns.value.includes(col.key))
);

// Format cell value
const formatValue = (row, column) => {
    const value = row[column.key];
    if (column.format) return column.format(value, row);
    if (column.type === 'badge') {
        const badgeProps = typeof column.badgeVariant === 'function' 
            ? column.badgeVariant(value, row) 
            : { variant: column.badgeVariant || 'default' };
        const badgeLabel = typeof column.badgeLabel === 'function'
            ? column.badgeLabel(value, row)
            : value;
        return { type: 'badge', value: badgeLabel, props: badgeProps };
    }
    return value;
};
</script>

<template>
    <Card class="w-full">
        <!-- Header with Search and Filters -->
        <div class="p-4 sm:p-6 border-b space-y-4">
            <div class="flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between">
                <!-- Search -->
                <div v-if="searchable" class="relative w-full sm:w-72">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                    <Input
                        v-model="search"
                        type="text"
                        placeholder="Cari data..."
                        class="pl-9"
                    />
                    <button
                        v-if="search"
                        @click="search = ''"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2">
                    <!-- Slot for custom header actions -->
                    <slot name="header-actions"></slot>
                    
                    <!-- Column Filter -->
                    <DropdownMenu v-if="showColumnFilter">
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" size="sm">
                                <Filter class="h-4 w-4 mr-2" />
                                Kolom
                                <ChevronDown class="h-4 w-4 ml-2 opacity-50" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-48">
                            <DropdownMenuItem
                                v-for="col in columns"
                                :key="col.key"
                                @click="toggleColumn(col.key)"
                            >
                                <div class="flex items-center gap-2">
                                    <div 
                                        class="w-4 h-4 rounded border flex items-center justify-center"
                                        :class="isColumnVisible(col.key) ? 'bg-primary border-primary' : 'border-input'"
                                    >
                                        <svg v-if="isColumnVisible(col.key)" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    {{ col.label }}
                                </div>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>

                    <!-- Reset Filters -->
                    <Button 
                        v-if="search || sortField" 
                        variant="ghost" 
                        size="sm"
                        @click="resetFilters"
                    >
                        Reset
                    </Button>
                </div>
            </div>

            <!-- Active Filters Display -->
            <div v-if="sortField" class="flex items-center gap-2 text-sm">
                <span class="text-muted-foreground">Diurutkan berdasarkan:</span>
                <Badge variant="secondary" class="gap-1">
                    {{ columns.find(c => c.key === sortField)?.label }}
                    {{ sortDirection === 'asc' ? '↑' : '↓' }}
                </Badge>
            </div>
        </div>

        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <Table :class="{ 'striped': striped }">
                <TableHeader>
                    <TableRow :class="{ 'h-10': dense }">
                        <TableHead
                            v-for="col in visibleColumnsList"
                            :key="col.key"
                            :class="[
                                col.headerClass,
                                sortable && col.sortable !== false ? 'cursor-pointer hover:bg-muted/50 select-none' : '',
                                'whitespace-nowrap'
                            ]"
                            @click="handleSort(col.key)"
                        >
                            <div class="flex items-center gap-2">
                                <span>{{ col.label }}</span>
                                <component 
                                    v-if="sortable && col.sortable !== false" 
                                    :is="getSortIcon(col.key)" 
                                    class="h-4 w-4 opacity-50"
                                />
                            </div>
                        </TableHead>
                        <TableHead v-if="actions" class="text-right w-24">
                            Aksi
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="(row, index) in data.data"
                        :key="row.id || index"
                        :class="{ 'bg-muted/30': striped && index % 2 === 1 }"
                    >
                        <TableCell
                            v-for="col in visibleColumnsList"
                            :key="col.key"
                            :class="[col.cellClass, 'max-w-xs truncate']"
                        >
                            <template v-if="row[col.key]?.type === 'badge'">
                                <Badge v-bind="row[col.key].props">
                                    {{ row[col.key].value }}
                                </Badge>
                            </template>
                            <template v-else>
                                <slot :name="'cell-' + col.key" :row="row" :value="row[col.key]">
                                    <span class="text-sm">{{ row[col.key] }}</span>
                                </slot>
                            </template>
                        </TableCell>
                        <TableCell v-if="actions" class="text-right">
                            <slot name="actions" :row="row">
                                <div class="flex justify-end gap-1">
                                    <Button 
                                        v-if="row.show !== false" 
                                        variant="ghost" 
                                        size="icon"
                                        @click="handleAction('view', row)"
                                    >
                                        <Eye class="h-4 w-4" />
                                    </Button>
                                    <Button 
                                        v-if="row.edit !== false" 
                                        variant="ghost" 
                                        size="icon"
                                        @click="handleAction('edit', row)"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </Button>
                                    <Button 
                                        v-if="row.delete !== false" 
                                        variant="ghost" 
                                        size="icon"
                                        class="text-destructive hover:text-destructive"
                                        @click="handleAction('delete', row)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </slot>
                        </TableCell>
                    </TableRow>
                    <TableRow v-if="!data.data || data.data.length === 0">
                        <TableCell
                            :colspan="visibleColumnsList.length + (actions ? 1 : 0)"
                            class="h-32 text-center"
                        >
                            <div class="flex flex-col items-center gap-2">
                                <Search class="h-8 w-8 text-muted-foreground/50" />
                                <span class="text-muted-foreground">Data tidak ditemukan</span>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <!-- Mobile Card View -->
        <div class="md:hidden divide-y">
            <div
                v-for="(row, index) in data.data"
                :key="row.id || index"
                class="p-4 space-y-3"
            >
                <div v-for="col in visibleColumnsList" :key="col.key" class="flex justify-between items-start gap-3">
                    <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider shrink-0">
                        {{ col.label }}
                    </span>
                    <div class="text-sm text-right flex-1">
                        <template v-if="row[col.key]?.type === 'badge'">
                            <Badge v-bind="row[col.key].props">
                                {{ row[col.key].value }}
                            </Badge>
                        </template>
                        <template v-else>
                            <slot :name="'cell-' + col.key" :row="row" :value="row[col.key]">
                                {{ row[col.key] }}
                            </slot>
                        </template>
                    </div>
                </div>
                <div v-if="actions" class="pt-3 flex justify-end gap-2 border-t">
                    <slot name="actions-mobile" :row="row">
                        <Button variant="outline" size="sm" @click="handleAction('view', row)">
                            <Eye class="h-4 w-4" />
                        </Button>
                        <Button variant="outline" size="sm" @click="handleAction('edit', row)">
                            <Pencil class="h-4 w-4" />
                        </Button>
                        <Button variant="outline" size="sm" class="text-destructive" @click="handleAction('delete', row)">
                            <Trash2 class="h-4 w-4" />
                        </Button>
                    </slot>
                </div>
            </div>
            <div v-if="!data.data || data.data.length === 0" class="px-4 py-16 text-center text-muted-foreground">
                <div class="flex flex-col items-center gap-2">
                    <Search class="h-8 w-8 text-muted-foreground/50" />
                    <span>Data tidak ditemukan</span>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="data.links && data.links.length > 3" class="px-4 sm:px-6 py-4 border-t bg-muted/30">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <p class="text-sm text-muted-foreground">
                    Menampilkan <span class="font-medium">{{ data.from }}</span> sampai 
                    <span class="font-medium">{{ data.to }}</span> dari 
                    <span class="font-medium">{{ data.total }}</span> hasil
                </p>
                <Pagination v-slot="{ page }" :total="data.total" :sibling-count="1" show-edges :default-page="page">
                    <PaginationList v-slot="{ items }" class="flex items-center gap-1">
                        <PaginationFirst @click="router.get(data.first_page_url)" />
                        <PaginationPrev @click="router.get(data.prev_page_url)" />
                        
                        <template v-for="(item, index) in items">
                            <PaginationListItem v-if="item.type === 'page'" :key="index" :value="item.value" as-child>
                                <Button 
                                    :variant="item.value === page ? 'default' : 'outline'" 
                                    size="sm"
                                    class="w-10"
                                >
                                    {{ item.value }}
                                </Button>
                            </PaginationListItem>
                        </template>
                        
                        <PaginationNext @click="router.get(data.next_page_url)" />
                        <PaginationLast @click="router.get(data.last_page_url)" />
                    </PaginationList>
                </Pagination>
            </div>
        </div>
    </Card>
</template>

<style scoped>
/* Striped rows */
.striped tbody tr:nth-child(even) {
    background-color: hsl(var(--muted) / 0.3);
}

/* Hover effect */
tbody tr:hover {
    background-color: hsl(var(--muted) / 0.5);
}
</style>
