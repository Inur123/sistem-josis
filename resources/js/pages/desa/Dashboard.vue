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
                class="flex w-full items-center justify-center gap-1.5 rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium whitespace-nowrap text-white transition-colors hover:bg-black sm:w-auto"
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
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
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
        </div>

        <!-- Quick Actions -->
        <div class="flex flex-col gap-3">
            <Link
                :href="desaRoutes.pemilih.index.url()"
                class="flex items-center gap-4 rounded-xl border border-gray-100 bg-white p-4 shadow-sm transition-all hover:border-blue-200 hover:shadow-md"
            >
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50"
                >
                    <svg
                        class="h-5 w-5 text-blue-600"
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
                class="flex items-center gap-4 rounded-xl border border-gray-100 bg-white p-4 shadow-sm transition-all hover:border-green-200 hover:shadow-md"
            >
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-50"
                >
                    <svg
                        class="h-5 w-5 text-green-600"
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
        </div>
    </div>
</template>
