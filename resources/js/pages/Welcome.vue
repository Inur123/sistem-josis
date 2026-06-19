<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { login } from '@/routes';
</script>

<template>
    <Head title="JOSIS — Sistem Data Pemilih Kab. Magetan" />
    <div class="flex min-h-screen items-center justify-center bg-gray-50">
        <div class="text-center">
            <div class="mb-4 text-4xl font-bold text-blue-600">JOSIS</div>
            <p class="mb-6 text-sm text-gray-500">
                Sistem Data Pemilih · Kabupaten Magetan
            </p>
            <a
                :href="login.url()"
                class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-blue-700"
            >
                Masuk ke Sistem
            </a>
        </div>
    </div>
</template>
