<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import { Eye } from '@lucide/vue';
import kecamatanRoutes from '@/routes/kecamatan';

interface Props {
    kecamatan: string;
    korcams: string[];
    kordes: Array<{
        nama: string;
        desa: string;
    }>;
    stats: {
        total_pemilih: number;
        laki_laki: number;
        perempuan: number;
        total_desa: number;
    };
    per_desa: Array<{
        id: string;
        nama: string;
        total: number;
        l: number;
        p: number;
    }>;
}

const props = defineProps<Props>();

const page = usePage();
const user = page.props.auth.user as any;

if (typeof window !== 'undefined' && user) {
    useEcho(`kecamatan.pemilih.${user.kecamatan_id}`, 'PemilihChanged', () => {
        router.reload();
    });

    useEcho(`kecamatan.team.${user.kecamatan_id}`, 'TeamChanged', () => {
        router.reload();
    });
}

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
            <div class="mt-3 space-y-2 text-sm">
                <div
                    class="rounded-lg border border-gray-100 bg-gray-50/50 p-3"
                >
                    <div
                        class="flex flex-col gap-1.5 sm:flex-row sm:items-center"
                    >
                        <span
                            class="inline-flex min-w-[200px] items-center gap-1.5 font-semibold text-gray-700"
                        >
                            <span
                                class="h-1.5 w-1.5 rounded-full bg-blue-500"
                            ></span>
                            Koordinator Kecamatan (Korcam):
                        </span>
                        <span class="text-gray-600">
                            {{
                                props.korcams.length
                                    ? props.korcams.join(', ')
                                    : '-'
                            }}
                        </span>
                    </div>
                    <div
                        class="mt-2 flex flex-col gap-1.5 border-t border-gray-100/80 pt-2 sm:flex-row sm:items-start"
                    >
                        <span
                            class="inline-flex min-w-[200px] items-center gap-1.5 font-semibold text-gray-700 sm:pt-0.5"
                        >
                            <span
                                class="h-1.5 w-1.5 rounded-full bg-green-500"
                            ></span>
                            Koordinator Desa (Kordes):
                        </span>
                        <div class="flex flex-1 flex-wrap gap-1.5">
                            <template v-if="props.kordes.length">
                                <span
                                    v-for="k in props.kordes"
                                    :key="k.nama"
                                    class="border-gray-150 inline-flex items-center gap-1 rounded border bg-white px-2 py-0.5 text-xs text-gray-600 shadow-sm"
                                >
                                    <span class="font-medium text-gray-800">{{
                                        k.nama
                                    }}</span>
                                    <span class="text-[10px] text-gray-400"
                                        >({{ k.desa }})</span
                                    >
                                </span>
                            </template>
                            <span v-else class="text-gray-600">-</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
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
                <div
                    class="mt-0.5 flex items-center gap-1.5 text-xs text-gray-500"
                >
                    <span>Total Pemilih</span>
                    <span
                        class="inline-flex items-center rounded-full bg-green-50 px-1.5 py-0.5 text-[10px] font-medium text-green-700 ring-1 ring-green-600/10 ring-inset"
                        >Terverifikasi</span
                    >
                </div>
            </div>
            <div
                class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm"
            >
                <div
                    class="mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50"
                >
                    <svg
                        class="h-5 w-5 text-indigo-600"
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
                    {{ props.stats.laki_laki.toLocaleString('id-ID') }}
                </div>
                <div
                    class="mt-0.5 flex items-center gap-1.5 text-xs text-gray-500"
                >
                    <span>Laki-laki</span>
                    <span
                        class="inline-flex items-center rounded-full bg-green-50 px-1.5 py-0.5 text-[10px] font-medium text-green-700 ring-1 ring-green-600/10 ring-inset"
                        >Terverifikasi</span
                    >
                </div>
            </div>
            <div
                class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm"
            >
                <div
                    class="mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-pink-50"
                >
                    <svg
                        class="h-5 w-5 text-pink-600"
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
                    {{ props.stats.perempuan.toLocaleString('id-ID') }}
                </div>
                <div
                    class="mt-0.5 flex items-center gap-1.5 text-xs text-gray-500"
                >
                    <span>Perempuan</span>
                    <span
                        class="inline-flex items-center rounded-full bg-green-50 px-1.5 py-0.5 text-[10px] font-medium text-green-700 ring-1 ring-green-600/10 ring-inset"
                        >Terverifikasi</span
                    >
                </div>
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
                            <th class="px-5 py-3 text-right">
                                Jumlah Pemilih
                                <span
                                    class="block text-[9px] font-normal text-green-600"
                                    >(Terverifikasi)</span
                                >
                            </th>
                            <th class="px-5 py-3 text-right">
                                Laki-laki
                                <span
                                    class="block text-[9px] font-normal text-green-600"
                                    >(Terverifikasi)</span
                                >
                            </th>
                            <th class="px-5 py-3 text-right">
                                Perempuan
                                <span
                                    class="block text-[9px] font-normal text-green-600"
                                    >(Terverifikasi)</span
                                >
                            </th>
                            <th class="w-[120px] px-5 py-3 text-center">
                                Aksi
                            </th>
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
                            <td class="px-5 py-3 text-center">
                                <Link
                                    :href="
                                        kecamatanRoutes.pemilih.index.url({
                                            query: { desa_id: item.id },
                                        })
                                    "
                                    class="border-gray-250 inline-flex items-center justify-center gap-1 rounded-lg border bg-white px-2.5 py-1 text-xs font-semibold text-gray-700 shadow-sm transition-all hover:bg-gray-50"
                                >
                                    <Eye class="h-3.5 w-3.5" />
                                    Detail
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="!props.per_desa.length">
                            <td
                                colspan="6"
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
