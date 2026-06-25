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
        <div
            class="relative z-0 hidden w-1/2 overflow-hidden bg-linear-to-tr from-amber-600 via-yellow-500 to-yellow-400 lg:flex lg:flex-col lg:justify-between lg:p-12"
        >
            <!-- Background Decorative Grid/Abstract Patterns -->
            <div
                class="pointer-events-none absolute inset-0 z-0 opacity-15 mix-blend-overlay"
            >
                <svg
                    width="100%"
                    height="100%"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <defs>
                        <pattern
                            id="grid"
                            width="40"
                            height="40"
                            patternUnits="userSpaceOnUse"
                        >
                            <path
                                d="M 40 0 L 0 0 0 40"
                                fill="none"
                                stroke="white"
                                stroke-width="1.5"
                            />
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#grid)" />
                </svg>
            </div>

            <!-- Abstract glowing circles in background -->
            <div
                class="absolute -top-40 -left-40 z-0 h-[600px] w-[600px] rounded-full bg-yellow-300/30 blur-3xl"
            ></div>
            <div
                class="absolute -right-40 -bottom-40 z-0 h-[600px] w-[600px] rounded-full bg-amber-400/20 blur-3xl"
            ></div>

            <!-- Branding Content Header -->
            <div class="relative z-10 flex items-center gap-3">
                <img
                    src="/images/logo_golkar.svg"
                    alt="Logo Golkar"
                    class="h-12 w-auto object-contain drop-shadow-md filter"
                />
                <div class="h-8 w-[1.5px] bg-white/40"></div>
                <div class="text-lg font-bold tracking-wider text-white">
                    SISTEM JOSIS
                </div>
            </div>

            <!-- Main Title & Large Banyan tree background silhouette -->
            <div class="relative z-10 my-auto max-w-lg">
                <!-- Huge background banyan tree watermark -->
                <div
                    class="pointer-events-none absolute -top-20 -left-10 z-0 opacity-10 select-none"
                >
                    <img
                        src="/images/logo_golkar.svg"
                        alt="Banyan Watermark"
                        class="h-[400px] w-auto object-contain"
                    />
                </div>

                <div class="relative z-10">
                    <span
                        class="mb-6 inline-flex items-center gap-1.5 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold text-white backdrop-blur-md"
                    >
                        <span
                            class="h-1.5 w-1.5 animate-pulse rounded-full bg-yellow-300"
                        ></span>
                        Sistem Pendataan Pemilih Magetan
                    </span>
                    <h2
                        class="text-4xl leading-tight font-black text-white drop-shadow-sm lg:text-5xl"
                    >
                        KAWAL SUARA<br />KARYA NYATA
                    </h2>
                    <p
                        class="mt-4 text-base leading-relaxed font-medium text-white/90"
                    >
                        Platform pemantauan data relawan pendamping dan
                        koordinasi input data pemilih di wilayah Kabupaten
                        Magetan.
                    </p>
                </div>
            </div>

            <!-- Footer info -->
            <div
                class="relative z-10 flex items-center justify-between text-xs font-medium text-white/80"
            >
                <div>© 2026 Sistem Josis. All rights reserved.</div>
                <div class="flex gap-4">
                    <span
                        class="cursor-pointer transition-colors hover:text-white"
                        >Panduan</span
                    >
                    <span
                        class="cursor-pointer transition-colors hover:text-white"
                        >Bantuan</span
                    >
                </div>
            </div>
        </div>

        <!-- Right Side: Login Form Panel -->
        <div
            class="relative flex w-full flex-col justify-center bg-white px-6 py-12 lg:w-1/2 lg:px-16 xl:px-24"
        >
            <!-- Subtle top accent line -->
            <div
                class="absolute top-0 right-0 left-0 h-1.5 bg-linear-to-r from-amber-500 to-yellow-400 lg:hidden"
            ></div>

            <div class="mx-auto w-full max-w-md">
                <!-- Mobile header logo (Only visible on small/medium screens) -->
                <div class="mb-10 flex items-center gap-3 lg:hidden">
                    <img
                        src="/images/logo_golkar.svg"
                        alt="Logo Golkar"
                        class="h-10 w-auto object-contain"
                    />
                    <div class="h-6 w-[1.5px] bg-gray-200"></div>
                    <div
                        class="text-base font-bold tracking-wider text-gray-900"
                    >
                        SISTEM JOSIS
                    </div>
                </div>

                <!-- Form Header -->
                <div class="mb-8 text-left">
                    <h1
                        class="text-3xl font-extrabold tracking-tight text-gray-900"
                    >
                        {{ title }}
                    </h1>
                    <p class="mt-2 text-sm font-medium text-gray-500">
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
