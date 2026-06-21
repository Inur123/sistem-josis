<script setup lang="ts">
import { Toaster } from '@/components/ui/sonner';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex min-h-svh w-full bg-gray-50/50">
        <!-- Left Side: Immersive Golkar-themed Branding Panel (Visible on MD and larger screens) -->
        <div class="relative hidden w-1/2 overflow-hidden bg-linear-to-tr from-amber-600 via-yellow-500 to-yellow-400 lg:flex lg:flex-col lg:justify-between lg:p-12 z-0">
            <!-- Background Decorative Grid/Abstract Patterns -->
            <div class="absolute inset-0 opacity-15 mix-blend-overlay z-0 pointer-events-none">
                <svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                            <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="1.5"/>
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#grid)" />
                </svg>
            </div>

            <!-- Abstract glowing circles in background -->
            <div class="absolute -top-40 -left-40 h-[600px] w-[600px] rounded-full bg-yellow-300/30 blur-3xl z-0"></div>
            <div class="absolute -bottom-40 -right-40 h-[600px] w-[600px] rounded-full bg-amber-400/20 blur-3xl z-0"></div>

            <!-- Branding Content Header -->
            <div class="relative z-10 flex items-center gap-3">
                <img src="/images/logo_golkar.svg" alt="Logo Golkar" class="h-12 w-auto object-contain filter drop-shadow-md" />
                <div class="h-8 w-[1.5px] bg-white/40"></div>
                <div class="text-white text-lg font-bold tracking-wider">SISTEM JOSIS</div>
            </div>

            <!-- Main Title & Large Banyan tree background silhouette -->
            <div class="relative z-10 my-auto max-w-lg">
                <!-- Huge background banyan tree watermark -->
                <div class="absolute -left-10 -top-20 opacity-10 pointer-events-none select-none z-0">
                    <img src="/images/logo_golkar.svg" alt="Banyan Watermark" class="h-[400px] w-auto object-contain" />
                </div>

                <div class="relative z-10">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-white backdrop-blur-md border border-white/20 mb-6">
                        <span class="h-1.5 w-1.5 rounded-full bg-yellow-300 animate-pulse"></span>
                        Sistem Pendataan Pemilih Magetan
                    </span>
                    <h2 class="text-4xl lg:text-5xl font-black text-white leading-tight drop-shadow-sm">
                        KAWAL SUARA<br />KARYA NYATA
                    </h2>
                    <p class="mt-4 text-white/90 text-base leading-relaxed font-medium">
                        Platform pemantauan data relawan pendamping dan koordinasi input data pemilih di wilayah Kabupaten Magetan.
                    </p>
                </div>
            </div>

            <!-- Footer info -->
            <div class="relative z-10 flex items-center justify-between text-xs text-white/80 font-medium">
                <div>© 2026 Sistem Josis. All rights reserved.</div>
                <div class="flex gap-4">
                    <span class="hover:text-white transition-colors cursor-pointer">Panduan</span>
                    <span class="hover:text-white transition-colors cursor-pointer">Bantuan</span>
                </div>
            </div>
        </div>

        <!-- Right Side: Login Form Panel -->
        <div class="flex w-full flex-col justify-center px-6 py-12 lg:w-1/2 lg:px-16 xl:px-24 bg-white relative">
            <!-- Subtle top accent line -->
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-linear-to-r from-amber-500 to-yellow-400 lg:hidden"></div>

            <div class="mx-auto w-full max-w-md">
                <!-- Mobile header logo (Only visible on small/medium screens) -->
                <div class="flex items-center gap-3 lg:hidden mb-10">
                    <img src="/images/logo_golkar.svg" alt="Logo Golkar" class="h-10 w-auto object-contain" />
                    <div class="h-6 w-[1.5px] bg-gray-200"></div>
                    <div class="text-gray-900 text-base font-bold tracking-wider">SISTEM JOSIS</div>
                </div>

                <!-- Form Header -->
                <div class="text-left mb-8">
                    <h1 class="text-3xl font-extrabold tracking-tight text-gray-900">
                        {{ title }}
                    </h1>
                    <p class="mt-2 text-sm text-gray-500 font-medium">
                        {{ description }}
                    </p>
                </div>

                <!-- Form Card / Slot -->
                <div class="relative">
                    <slot />
                </div>
            </div>
        </div>

        <Toaster position="top-right" />
    </div>
</template>
