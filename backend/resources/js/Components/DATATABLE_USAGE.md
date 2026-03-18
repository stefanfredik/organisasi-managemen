# 📊 DataTable Component - Usage Guide

Enhanced DataTable component dengan shadcn/ui untuk konsistensi di seluruh aplikasi.

## Features

- ✅ **Server-side sorting** dengan visual indicators (↑/↓)
- ✅ **Debounced search** (300ms delay)
- ✅ **Column visibility toggle** (show/hide columns)
- ✅ **Active filters display**
- ✅ **Mobile-responsive** card view
- ✅ **shadcn/ui Pagination** integration
- ✅ **Badge support** untuk status columns
- ✅ **Slot support** untuk custom cell rendering
- ✅ **Striped rows** option
- ✅ **Dense mode** option

## Basic Usage

```vue
<script setup>
import DataTable from '@/Components/DataTable.vue';

const props = defineProps({
    users: Object, // Laravel paginator
    filters: Object,
});

const columns = [
    { key: 'name', label: 'Nama', sortable: true },
    { key: 'email', label: 'Email', sortable: true },
    { 
        key: 'role', 
        label: 'Role',
        type: 'badge',
        badgeVariant: (value) => ({
            variant: value === 'admin' ? 'destructive' : 'default'
        }),
        badgeLabel: (value) => value.charAt(0).toUpperCase() + value.slice(1)
    },
    { 
        key: 'created_at', 
        label: 'Terdaftar',
        format: (value) => new Date(value).toLocaleDateString('id-ID')
    },
];

const handleAction = ({ action, row }) => {
    if (action === 'view') {
        // Handle view
    } else if (action === 'edit') {
        // Handle edit
    } else if (action === 'delete') {
        // Handle delete
    }
};
</script>

<template>
    <DataTable
        :data="users"
        :columns="columns"
        :filters="filters"
        search-route="users.index"
        :sortable="true"
        :striped="true"
        :show-column-filter="true"
        @action="handleAction"
    >
        <!-- Custom cell rendering -->
        <template #cell-email="{ row }">
            <a :href="`mailto:${row.email}`" class="text-primary hover:underline">
                {{ row.email }}
            </a>
        </template>

        <!-- Custom actions -->
        <template #actions="{ row }">
            <Button variant="ghost" size="icon" as-child>
                <Link :href="route('users.show', row.id)">
                    <Eye class="h-4 w-4" />
                </Link>
            </Button>
        </template>
    </DataTable>
</template>
```

## Column Configuration

### Basic Column
```js
{ key: 'name', label: 'Nama' }
```

### Sortable Column
```js
{ key: 'name', label: 'Nama', sortable: true }
```

### Column with Formatter
```js
{ 
    key: 'created_at', 
    label: 'Tanggal',
    format: (value) => new Date(value).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}
```

### Badge Column (Status)
```js
{ 
    key: 'status', 
    label: 'Status',
    type: 'badge',
    badgeVariant: (value, row) => {
        if (value === 'active') return { variant: 'success' };
        if (value === 'pending') return { variant: 'warning' };
        return { variant: 'secondary' };
    },
    badgeLabel: (value, row) => {
        if (value === 'active') return 'Aktif';
        if (value === 'pending') return 'Menunggu';
        return value;
    }
}
```

### Column with Custom Class
```js
{ 
    key: 'amount', 
    label: 'Jumlah',
    headerClass: 'text-right',
    cellClass: 'text-right font-mono'
}
```

### Non-sortable Column
```js
{ key: 'actions', label: 'Aksi', sortable: false }
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `data` | Object | required | Laravel paginator object |
| `columns` | Array | required | Column configuration |
| `filters` | Object | `{}` | Current filters from backend |
| `searchRoute` | String | `null` | Route for search/sort requests |
| `actions` | Boolean | `true` | Show action column |
| `searchable` | Boolean | `true` | Show search input |
| `sortable` | Boolean | `true` | Enable column sorting |
| `striped` | Boolean | `false` | Striped row styling |
| `dense` | Boolean | `false` | Compact row height |
| `pageSize` | Number | `10` | Items per page |
| `showColumnFilter` | Boolean | `false` | Show column visibility toggle |

## Events

### `@action`
Triggered when action button is clicked.
```js
@action="({ action, row }) => { ... }"
```

### `@sort`
Triggered when column is sorted.
```js
@sort="({ field, direction }) => { ... }"
```

## Slots

### Cell Customization
```vue
<template #cell-[columnKey]="{ row, value }">
    <!-- Custom content -->
</template>
```

### Actions
```vue
<template #actions="{ row }">
    <!-- Custom action buttons -->
</template>
```

### Mobile Actions
```vue
<template #actions-mobile="{ row }">
    <!-- Custom mobile action buttons -->
</template>
```

## Examples

### Example 1: Simple Table
```vue
<DataTable
    :data="users"
    :columns="[
        { key: 'name', label: 'Nama' },
        { key: 'email', label: 'Email' },
    ]"
/>
```

### Example 2: Full Featured Table
```vue
<DataTable
    :data="transactions"
    :columns="[
        { key: 'code', label: 'Kode', sortable: true },
        { 
            key: 'status', 
            label: 'Status',
            type: 'badge',
            badgeVariant: (v) => ({ variant: v === 'paid' ? 'success' : 'warning' }),
            badgeLabel: (v) => v === 'paid' ? 'Lunas' : 'Belum Bayar'
        },
        { 
            key: 'amount', 
            label: 'Jumlah',
            format: (v) => formatCurrency(v),
            cellClass: 'text-right font-mono'
        },
        { 
            key: 'date', 
            label: 'Tanggal',
            format: (v) => formatDate(v),
            sortable: true
        },
    ]"
    :filters="filters"
    search-route="transactions.index"
    :sortable="true"
    :striped="true"
    :show-column-filter="true"
    @action="handleTransactionAction"
/>
```

### Example 3: Custom Cell Rendering
```vue
<DataTable :data="members" :columns="columns">
    <template #cell-full_name="{ row }">
        <div class="flex items-center gap-3">
            <img :src="row.photo_url" class="w-8 h-8 rounded-full" />
            <div>
                <div class="font-medium">{{ row.full_name }}</div>
                <div class="text-xs text-muted-foreground">{{ row.member_code }}</div>
            </div>
        </div>
    </template>

    <template #cell-actions="{ row }">
        <Button variant="ghost" size="sm" as-child>
            <Link :href="route('members.show', row.id)">Detail</Link>
        </Button>
    </template>
</DataTable>
```

## Migration Guide

### From Old DataTable

**Before:**
```vue
<DataTable
    :data="users"
    :columns="columns"
    :filters="filters"
    search-route="users.index"
>
    <template #cell-name="{ row }">...</template>
</DataTable>
```

**After:** (Same! Backward compatible)
```vue
<DataTable
    :data="users"
    :columns="columns"
    :filters="filters"
    search-route="users.index"
    :sortable="true"
    :show-column-filter="true"
>
    <template #cell-name="{ row }">...</template>
</DataTable>
```

## Best Practices

1. **Always use slot for complex cells** - Images, links, multiple elements
2. **Use badge type for status** - Consistent styling across app
3. **Enable sorting on meaningful columns** - Name, date, amount
4. **Use formatter for dates/currency** - Keep templates clean
5. **Custom actions per page** - Override default action buttons

## Tips

- Use `dense` prop for data-heavy pages
- Enable `striped` for better row readability
- Use `show-column-filter` for tables with many columns
- Always provide unique `key` in column config
- Use `cellClass` for alignment (text-right for numbers)
