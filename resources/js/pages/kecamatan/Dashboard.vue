<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

interface Props {
    kecamatan: string;
    stats: {
        total_pemilih: number;
        total_desa: number;
    };
    per_desa: Array<{
        nama: string;
        total: number;
        l: number;
        p: number;
    }>;
}

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [],
    },
});
</script>

<template>
    <Head title="Dashboard Kecamatan" />
    <div class="flex flex-col gap-6 p-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900">
                Dashboard Kecamatan {{ props.kecamatan }}
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Pantau data pemilih di wilayah kecamatan Anda
            </p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 gap-4">
            <div
                class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm"
            >
                <div
                    class="mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50"
                >
                    <svg
                        class="h-5 w-5 text-blue-600"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path
                            d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"
                        />
                    </svg>
                </div>
                <div class="text-2xl font-bold text-gray-900">
                    {{ props.stats.total_pemilih.toLocaleString('id-ID') }}
                </div>
                <div class="mt-0.5 text-xs text-gray-500">Total Pemilih</div>
            </div>
            <div
                class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm"
            >
                <div
                    class="mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-green-50"
                >
                    <svg
                        class="h-5 w-5 text-green-600"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"
                        />
                        <polyline points="9 22 9 12 15 12 15 22" />
                    </svg>
                </div>
                <div class="text-2xl font-bold text-gray-900">
                    {{ props.stats.total_desa }}
                </div>
                <div class="mt-0.5 text-xs text-gray-500">Desa / Kelurahan</div>
            </div>
        </div>

        <!-- Tabel per Desa -->
        <div
            class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm"
        >
            <div class="border-b border-gray-100 px-5 py-4">
                <h3 class="text-sm font-semibold text-gray-900">
                    Rekap Pemilih per Desa/Kelurahan
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase"
                        >
                            <th class="px-5 py-3">No</th>
                            <th class="px-5 py-3">Desa / Kelurahan</th>
                            <th class="px-5 py-3 text-right">Jumlah Pemilih</th>
                            <th class="px-5 py-3 text-right">Laki-laki</th>
                            <th class="px-5 py-3 text-right">Perempuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(item, i) in props.per_desa"
                            :key="item.nama"
                            class="border-b border-gray-50 last:border-0 hover:bg-gray-50"
                        >
                            <td class="px-5 py-3 text-gray-400">{{ i + 1 }}</td>
                            <td class="px-5 py-3 font-medium text-gray-900">
                                {{ item.nama }}
                            </td>
                            <td
                                class="px-5 py-3 text-right font-semibold text-gray-900"
                            >
                                {{ item.total.toLocaleString('id-ID') }}
                            </td>
                            <td class="px-5 py-3 text-right text-gray-900">
                                {{ item.l.toLocaleString('id-ID') }}
                            </td>
                            <td class="px-5 py-3 text-right text-gray-900">
                                {{ item.p.toLocaleString('id-ID') }}
                            </td>
                        </tr>
                        <tr v-if="!props.per_desa.length">
                            <td
                                colspan="5"
                                class="px-5 py-10 text-center text-gray-400"
                            >
                                Belum ada data pemilih
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
