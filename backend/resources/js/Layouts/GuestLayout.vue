<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
import { Card, CardContent } from '@/components/ui/card';
</script>

<template>
    <div class="flex min-h-screen flex-col items-center bg-muted px-4 pt-6 sm:justify-center sm:pt-0">
        <div class="shrink-0">
            <Link href="/">
                <ApplicationLogo class="h-16 w-16 sm:h-20 sm:w-20 fill-current text-muted-foreground" />
            </Link>
        </div>

        <Card class="mt-6 w-full sm:max-w-md">
            <CardContent class="pt-6">
                <slot />
            </CardContent>
        </Card>
    </div>
</template>
