<script setup>
import { cn } from '@/lib/utils';
const props = defineProps({
    colspan: { type: Number, default: 1 },
    class: { type: [String, Array, Object], default: '' },
});
</script>

<template>
    <tr>
        <td :colspan="colspan" :class="cn('h-24 text-center text-muted-foreground', props.class)">
            <slot>Tidak ada data.</slot>
        </td>
    </tr>
</template>
