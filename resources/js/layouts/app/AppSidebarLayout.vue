<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import { Toaster } from '@/components/ui/sonner';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="h-screen overflow-hidden flex flex-col bg-white relative">
            <!-- Subtle Fixed Watermark Background Logo -->
            <div class="pointer-events-none absolute inset-0 flex items-center justify-center overflow-hidden select-none z-0">
                <img 
                    src="/images/logo_golkar.svg" 
                    class="w-[500px] h-[500px] opacity-[0.07]" 
                    alt="Golkar Watermark"
                />
            </div>

            <AppSidebarHeader :breadcrumbs="breadcrumbs" class="relative z-10" />
            <div class="flex-1 overflow-y-auto overflow-x-hidden relative z-10 pb-8">
                <slot />
            </div>
            
            <!-- Fixed Footer -->
            <footer class="flex flex-col gap-1 py-3 md:py-0 md:h-12 shrink-0 md:flex-row items-center justify-between border-t border-sidebar-border/70 px-6 bg-white text-[10px] md:text-xs text-gray-500 relative z-0 text-center md:text-left">
                <span>&copy; {{ new Date().getFullYear() }} Sistem Josis. All rights reserved.</span>
                <span>Kab. Magetan, Jawa Timur</span>
            </footer>
        </AppContent>
        <Toaster position="top-right" />
    </AppShell>
</template>
