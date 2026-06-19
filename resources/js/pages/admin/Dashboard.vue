<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

interface Props {
    stats: {
        total_pemilih: number;
        total_kecamatan: number;
        total_desa: number;
        total_akun: number;
    };
    per_kecamatan: Array<{
        nama: string;
        total: number;
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
    <Head title="Dashboard Admin" />
    <div class="flex flex-col gap-6 p-6">
        <!-- Header -->
        <div>
            <h2 class="text-xl font-bold text-gray-900">Dashboard Admin</h2>
            <p class="mt-1 text-sm text-gray-500">
                Pantau seluruh data pemilih Kabupaten Magetan
            </p>
        </div>

        <!-- Stat Cards -->
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <!-- Total Pemilih -->
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

            <!-- Total Desa -->
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

            <!-- Total Kecamatan -->
            <div
                class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm"
            >
                <div
                    class="mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50"
                >
                    <svg
                        class="h-5 w-5 text-amber-600"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <circle cx="12" cy="12" r="10" />
                        <line x1="2" y1="12" x2="22" y2="12" />
                        <path
                            d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"
                        />
                    </svg>
                </div>
                <div class="text-2xl font-bold text-gray-900">
                    {{ props.stats.total_kecamatan }}
                </div>
                <div class="mt-0.5 text-xs text-gray-500">Kecamatan</div>
            </div>

            <!-- Total Akun -->
            <div
                class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm"
            >
                <div
                    class="mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-purple-50"
                >
                    <svg
                        class="h-5 w-5 text-purple-600"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                </div>
                <div class="text-2xl font-bold text-gray-900">
                    {{ props.stats.total_akun }}
                </div>
                <div class="mt-0.5 text-xs text-gray-500">Total Akun</div>
            </div>
        </div>

        <!-- Tabel per Kecamatan -->
        <div
            class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm"
        >
            <div class="border-b border-gray-100 px-5 py-4">
                <h3 class="text-sm font-semibold text-gray-900">
                    Rekap Pemilih per Kecamatan
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase"
                        >
                            <th class="px-5 py-3">No</th>
                            <th class="px-5 py-3">Kecamatan</th>
                            <th class="px-5 py-3 text-right">Jumlah Pemilih</th>
                            <th class="px-5 py-3">Progres</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(item, i) in props.per_kecamatan"
                            :key="item.nama"
                            class="border-b border-gray-50 last:border-0 hover:bg-gray-50"
                        >
                            <td class="px-5 py-3 text-gray-400">{{ i + 1 }}</td>
                            <td class="px-5 py-3 font-medium text-gray-900">
                                Kec. {{ item.nama }}
                            </td>
                            <td
                                class="px-5 py-3 text-right font-semibold text-gray-900"
                            >
                                {{ item.total.toLocaleString('id-ID') }}
                            </td>
                            <td class="px-5 py-3">
                                <div
                                    class="h-1.5 w-32 overflow-hidden rounded-full bg-gray-100"
                                >
                                    <div
                                        class="h-full rounded-full bg-gradient-to-r from-blue-500 to-blue-400 transition-all"
                                        :style="{
                                            width: props.stats.total_pemilih
                                                ? (item.total /
                                                      props.stats
                                                          .total_pemilih) *
                                                      100 +
                                                  '%'
                                                : '0%',
                                        }"
                                    />
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!props.per_kecamatan.length">
                            <td
                                colspan="4"
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
