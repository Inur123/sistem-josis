<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import desaRoutes from '@/routes/desa';

interface Props {
    desa: string;
    kecamatan: string;
    kordes: string[];
    relawans: string[];
    stats: {
        total_pemilih: number;
        laki_laki: number;
        perempuan: number;
        total_suara: number;
        total_tps: number;
    };
}

const props = defineProps<Props>();

const page = usePage();
const user = page.props.auth.user as any;

if (typeof window !== 'undefined' && user) {
    useEcho(`desa.pemilih.${user.desa_id}`, 'PemilihChanged', () => {
        router.reload();
    });

    useEcho(`desa.team.${user.desa_id}`, 'TeamChanged', () => {
        router.reload();
    });

    useEcho(`desa.tps.${user.desa_id}`, 'TpsChanged', () => {
        router.reload();
    });

    useEcho(`desa.tps.${user.desa_id}`, 'DataSuaraChanged', () => {
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
    <Head title="Dashboard Desa" />
    <div class="flex flex-col gap-6 p-6">
        <!-- Header -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
        >
            <div>
                <h2 class="text-xl font-bold text-gray-900">
                    {{ props.desa }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Kecamatan {{ props.kecamatan }} · Kabupaten Magetan
                </p>
                <div class="mt-2.5 flex flex-col gap-1 text-xs text-gray-600">
                    <div>
                        <span class="font-semibold text-gray-700"
                            >Pendamping (Kordes):</span
                        >
                        {{
                            props.kordes.length ? props.kordes.join(', ') : '-'
                        }}
                    </div>
                    <div>
                        <span class="font-semibold text-gray-700"
                            >Relawan:</span
                        >
                        {{
                            props.relawans.length
                                ? props.relawans.join(', ')
                                : '-'
                        }}
                    </div>
                </div>
            </div>
            <Link
                :href="desaRoutes.pemilih.create.url()"
                class="flex w-full items-center justify-center gap-1.5 rounded-lg bg-amber-400 px-4 py-2 text-sm font-semibold whitespace-nowrap text-gray-900 transition-colors cursor-pointer sm:w-auto"
            >
                <svg
                    class="h-4 w-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
                Tambah Pemilih
            </Link>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div
                class="rounded-xl border border-amber-100/50 bg-white p-5 shadow-sm"
            >
                <div
                    class="mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50"
                >
                    <svg
                        class="h-5 w-5 text-amber-800"
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
                class="rounded-xl border border-amber-100/50 bg-white p-5 shadow-sm"
            >
                <div
                    class="mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50"
                >
                    <svg
                        class="h-5 w-5 text-amber-800"
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
                class="rounded-xl border border-amber-100/50 bg-white p-5 shadow-sm"
            >
                <div
                    class="mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50"
                >
                    <svg
                        class="h-5 w-5 text-amber-800"
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

            <!-- Total TPS -->
            <div
                class="rounded-xl border border-amber-100/50 bg-white p-5 shadow-sm"
            >
                <div
                    class="mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50"
                >
                    <svg
                        class="h-5 w-5 text-amber-800"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                        <line x1="9" y1="3" x2="9" y2="21" />
                        <line x1="15" y1="3" x2="15" y2="21" />
                        <line x1="3" y1="9" x2="21" y2="9" />
                        <line x1="3" y1="15" x2="21" y2="15" />
                    </svg>
                </div>
                <div class="text-2xl font-bold text-gray-900">
                    {{ props.stats.total_tps.toLocaleString('id-ID') }}
                </div>
                <div class="mt-0.5 text-xs text-gray-500">Total TPS</div>
            </div>

            <!-- Total Suara -->
            <div
                class="rounded-xl border border-amber-100/50 bg-white p-5 shadow-sm"
            >
                <div
                    class="mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50"
                >
                    <svg
                        class="h-5 w-5 text-amber-800"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                        <circle cx="12" cy="10" r="3" />
                    </svg>
                </div>
                <div class="text-2xl font-bold text-gray-900">
                    {{ props.stats.total_suara.toLocaleString('id-ID') }}
                </div>
                <div
                    class="mt-0.5 flex items-center gap-1.5 text-xs text-gray-500"
                >
                    <span>Total Suara TPS</span>
                    <span
                        class="inline-flex items-center rounded-full bg-amber-50 px-1.5 py-0.5 text-[10px] font-medium text-amber-800 ring-1 ring-amber-600/10 ring-inset"
                        >Masuk</span
                    >
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="flex flex-col gap-3">
            <Link
                :href="desaRoutes.pemilih.index.url()"
                class="flex items-center gap-4 rounded-xl border border-amber-100/50 bg-white p-4 shadow-sm transition-all hover:border-amber-200 hover:shadow-md cursor-pointer"
            >
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50"
                >
                    <svg
                        class="h-5 w-5 text-amber-800"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"
                        />
                        <rect x="9" y="3" width="6" height="4" rx="1" />
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="text-sm font-semibold text-gray-900">
                        Lihat Data Pemilih
                    </div>
                    <div class="text-xs text-gray-500">
                        Daftar lengkap pemilih desa ini
                    </div>
                </div>
                <svg
                    class="h-4 w-4 text-gray-400"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="M9 18l6-6-6-6" />
                </svg>
            </Link>

            <Link
                :href="desaRoutes.pemilih.create.url()"
                class="flex items-center gap-4 rounded-xl border border-amber-100/50 bg-white p-4 shadow-sm transition-all hover:border-amber-200 hover:shadow-md cursor-pointer"
            >
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50"
                >
                    <svg
                        class="h-5 w-5 text-amber-800"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="text-sm font-semibold text-gray-900">
                        Tambah Pemilih Baru
                    </div>
                    <div class="text-xs text-gray-500">
                        Input data pemilih baru
                    </div>
                </div>
                <svg
                    class="h-4 w-4 text-gray-400"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="M9 18l6-6-6-6" />
                </svg>
            </Link>

            <!-- Kelola Data TPS -->
            <Link
                :href="desaRoutes.tps.index.url()"
                class="flex items-center gap-4 rounded-xl border border-amber-100/50 bg-white p-4 shadow-sm transition-all hover:border-amber-200 hover:shadow-md cursor-pointer"
            >
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50"
                >
                    <svg
                        class="h-5 w-5 text-amber-800"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                        <line x1="9" y1="3" x2="9" y2="21" />
                        <line x1="15" y1="3" x2="15" y2="21" />
                        <line x1="3" y1="9" x2="21" y2="9" />
                        <line x1="3" y1="15" x2="21" y2="15" />
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="text-sm font-semibold text-gray-900">
                        Kelola Data TPS
                    </div>
                    <div class="text-xs text-gray-500">
                        Lihat, tambah, edit, dan hapus TPS di desa Anda
                    </div>
                </div>
                <svg
                    class="h-4 w-4 text-gray-400"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="M9 18l6-6-6-6" />
                </svg>
            </Link>

            <!-- Input Data Suara & C-Hasil -->
            <Link
                :href="desaRoutes.dataSuara.index.url()"
                class="flex items-center gap-4 rounded-xl border border-amber-100/50 bg-white p-4 shadow-sm transition-all hover:border-amber-200 hover:shadow-md cursor-pointer"
            >
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50"
                >
                    <svg
                        class="h-5 w-5 text-amber-800"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                        <circle cx="12" cy="10" r="3" />
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="text-sm font-semibold text-gray-900">
                        Input Data Suara & C-Hasil
                    </div>
                    <div class="text-xs text-gray-500">
                        Isi perolehan total suara golkar dan unggah foto berkas C-Hasil
                    </div>
                </div>
                <svg
                    class="h-4 w-4 text-gray-400"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="M9 18l6-6-6-6" />
                </svg>
            </Link>
        </div>
    </div>
</template>
