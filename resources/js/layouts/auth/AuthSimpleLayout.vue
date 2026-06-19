<script setup lang="ts">
import { Toaster } from '@/components/ui/sonner';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="flex min-h-svh flex-col items-center justify-center bg-gray-50/50 p-6 md:p-10"
    >
        <div class="w-full max-w-md rounded-2xl border border-gray-100 bg-white p-8 shadow-xl">
            <div class="flex flex-col gap-6">
                <div class="flex flex-col items-center gap-2">
                    <div class="space-y-1.5 text-center">
                        <h1 class="text-2xl font-bold text-gray-900">{{ title }}</h1>
                        <p class="text-center text-sm text-gray-500">
                            {{ description }}
                        </p>
                    </div>
                </div>
                <slot />
            </div>
        </div>
        <Toaster position="top-right" />
    </div>
</template>
