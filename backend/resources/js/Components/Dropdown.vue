<script setup>
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

defineProps({
    align: { type: String, default: 'right' },
    width: { type: [String, Number], default: '48' },
    contentClasses: { type: String, default: '' },
});

const widthClass = {
    48: 'w-48',
    56: 'w-56',
    72: 'w-72',
    80: 'w-80',
    96: 'w-96',
};

const alignMap = {
    left: 'start',
    right: 'end',
    center: 'center',
};
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger>
            <slot name="trigger" />
        </DropdownMenuTrigger>
        <DropdownMenuContent
            :align="alignMap[align] || 'end'"
            :class="[widthClass[width] || 'w-48', contentClasses]"
        >
            <slot name="content" />
        </DropdownMenuContent>
    </DropdownMenu>
</template>
