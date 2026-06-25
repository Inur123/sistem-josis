<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Loader2, Eye } from '@lucide/vue';
import { reactive } from 'vue';
import PaginationBar from '@/components/PaginationBar.vue';
import desaRoutes from '@/routes/desa';

interface Pemilih {
    id: string;
    nik: string;
    nama: string;
    jenis_kelamin: string;
    alamat: string;
    rt: string;
    rw: string;
    created_at: string;
}

interface Relawan {
    id: string;
    nama: string;
    nik: string;
    no_hp: string;
    alamat: string;
    pemilihs_count: number;
    pemilihs: Pemilih[];
}

const props = defineProps<{
    relawans: Relawan[];
    desa: string;
}>();

const getGenderStats = (pemilihs: Pemilih[]) => {
    const l = pemilihs.filter((p) => p.jenis_kelamin === 'L').length;
    const p = pemilihs.filter((p) => p.jenis_kelamin === 'P').length;

    return { l, p };
};

// ─── Server-side pagination per relawan ───────────────────────────────────────
const ITEMS_PER_PAGE = 10;

const currentPages = reactive<Record<string, number>>({});
const pageCache = reactive<Record<string, Record<number, Pemilih[]>>>({});
const loadingMap = reactive<Record<string, boolean>>({});

function initCache(relawan: Relawan) {
    if (!pageCache[relawan.id]) {
        pageCache[relawan.id] = { 1: relawan.pemilihs };
    }

    if (!currentPages[relawan.id]) {
        currentPages[relawan.id] = 1;
    }
}

function getPemilihPage(relawan: Relawan): Pemilih[] {
    initCache(relawan);
    const page = currentPages[relawan.id] ?? 1;

    return pageCache[relawan.id]?.[page] ?? [];
}

function getTotalPages(relawan: Relawan): number {
    return Math.ceil(relawan.pemilihs_count / ITEMS_PER_PAGE);
}

async function goToPage(relawan: Relawan, page: number) {
    initCache(relawan);
    const total = getTotalPages(relawan);

    if (page < 1 || page > total) {
        return;
    }

    if (pageCache[relawan.id]?.[page]) {
        currentPages[relawan.id] = page;

        return;
    }

    loadingMap[relawan.id] = true;

    try {
        const res = await fetch(
            `/desa/relawan/${relawan.id}/pemilihs?page=${page}`,
            {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            },
        );

        if (!res.ok) {
            throw new Error('Gagal memuat data');
        }

        const json = await res.json();

        if (!pageCache[relawan.id]) {
            pageCache[relawan.id] = {};
        }

        pageCache[relawan.id][page] = json.data as Pemilih[];
        currentPages[relawan.id] = page;
    } catch (e) {
        console.error('Error fetching pemilihs:', e);
    } finally {
        loadingMap[relawan.id] = false;
    }
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: desaRoutes.dashboard.url() },
            { title: 'Data Relawan', href: '#' },
        ],
    },
});
</script>

<template>
    <Head title="Data Relawan" />
    <div class="flex flex-col gap-5 p-6">
        <!-- Header -->
        <div class="flex flex-col gap-1">
            <h2 class="text-xl font-bold text-gray-900">
                Data Relawan — Desa {{ props.desa }}
            </h2>
            <p class="text-sm text-gray-500">
                Daftar relawan pendamping dan kontribusi data pemilih mereka
            </p>
        </div>

        <!-- Volunteers Sections / Tables -->
        <div class="flex flex-col gap-6">
            <div
                v-for="r in props.relawans"
                :key="r.id"
                class="flex flex-col gap-4 rounded-xl border border-gray-100 bg-white p-6 shadow-sm"
            >
                <!-- Volunteer Info Header -->
                <div
                    class="flex flex-col justify-between gap-4 border-b border-gray-100 pb-4 sm:flex-row sm:items-center"
                >
                    <div>
                        <h3
                            class="flex items-center gap-2 text-base font-semibold text-gray-900"
                        >
                            <span
                                class="inline-flex h-2 w-2 rounded-full bg-blue-600"
                            ></span>
                            Relawan: {{ r.nama }}
                        </h3>
                        <div
                            class="mt-1.5 flex flex-wrap gap-x-6 gap-y-1 font-mono text-xs text-gray-500"
                        >
                            <span v-if="r.nik"
                                ><strong>NIK:</strong> {{ r.nik }}</span
                            >
                            <span v-if="r.no_hp"
                                ><strong>No. HP:</strong> {{ r.no_hp }}</span
                            >
                            <span v-if="r.alamat"
                                ><strong>Alamat:</strong> {{ r.alamat }}</span
                            >
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-medium text-gray-500"
                            >Pemilih Didampingi:</span
                        >
                        <div class="flex items-center gap-1.5">
                            <span
                                class="inline-flex min-w-[28px] items-center justify-center rounded-full bg-blue-100 px-2.5 py-1 text-xs font-bold text-blue-700"
                                title="Total Pemilih"
                            >
                                Total: {{ r.pemilihs_count }}
                            </span>
                            <span
                                class="inline-flex min-w-[28px] items-center justify-center rounded-full bg-sky-100 px-2.5 py-1 text-xs font-bold text-sky-700"
                                title="Laki-laki"
                            >
                                L: {{ getGenderStats(r.pemilihs).l }}
                            </span>
                            <span
                                class="inline-flex min-w-[28px] items-center justify-center rounded-full bg-pink-100 px-2.5 py-1 text-xs font-bold text-pink-700"
                                title="Perempuan"
                            >
                                P: {{ getGenderStats(r.pemilihs).p }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Table of Voters -->
                <div class="relative overflow-x-auto">
                    <!-- Loading overlay -->
                    <div
                        v-if="loadingMap[r.id]"
                        class="absolute inset-0 z-10 flex items-center justify-center rounded-lg bg-white/70"
                    >
                        <Loader2 class="h-6 w-6 animate-spin text-blue-600" />
                    </div>

                    <table class="w-full text-sm">
                        <thead>
                            <tr
                                class="border-b border-gray-100 bg-gray-50 text-left text-xs font-semibold tracking-wide text-gray-500 uppercase"
                            >
                                <th class="w-[60px] px-4 py-3">No</th>
                                <th class="px-4 py-3">NIK</th>
                                <th class="px-4 py-3">Nama Pemilih</th>
                                <th class="w-[80px] px-4 py-3">JK</th>
                                <th class="px-4 py-3">Alamat</th>
                                <th class="w-[100px] px-4 py-3 text-center">
                                    RT/RW
                                </th>
                                <th class="w-[150px] px-4 py-3">
                                    Tanggal Input
                                </th>
                                <th class="w-[80px] px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr
                                v-for="(p, pi) in getPemilihPage(r)"
                                :key="p.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="px-4 py-3 text-gray-400">
                                    {{
                                        ((currentPages[r.id] ?? 1) - 1) *
                                            ITEMS_PER_PAGE +
                                        pi +
                                        1
                                    }}
                                </td>
                                <td
                                    class="px-4 py-3 font-mono text-xs text-gray-500"
                                >
                                    {{ p.nik }}
                                </td>
                                <td class="px-4 py-3 font-medium text-gray-900">
                                    {{ p.nama }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'inline-flex h-5 w-5 items-center justify-center rounded-full text-xs font-bold',
                                            p.jenis_kelamin === 'L'
                                                ? 'bg-blue-100 text-blue-700'
                                                : 'bg-pink-100 text-pink-700',
                                        ]"
                                    >
                                        {{ p.jenis_kelamin }}
                                    </span>
                                </td>
                                <td
                                    class="max-w-[300px] truncate px-4 py-3 text-gray-600"
                                >
                                    {{ p.alamat }}
                                </td>
                                <td class="px-4 py-3 text-center text-gray-600">
                                    {{ p.rt }}/{{ p.rw }}
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-400">
                                    {{ p.created_at }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center">
                                        <Link
                                            :href="desaRoutes.pemilih.show.url(p.id)"
                                            class="inline-flex h-7 w-7 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 shadow-sm transition hover:bg-gray-50 hover:text-gray-900"
                                            title="Detail"
                                        >
                                            <Eye class="h-3.5 w-3.5" />
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                            <tr
                                v-if="
                                    !getPemilihPage(r).length &&
                                    !loadingMap[r.id]
                                "
                            >
                                <td
                                    colspan="8"
                                    class="px-4 py-8 text-center text-sm text-gray-400"
                                >
                                    Belum ada data pemilih pendamping untuk
                                    relawan ini.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <PaginationBar
                    v-if="getTotalPages(r) > 1"
                    :current-page="currentPages[r.id] ?? 1"
                    :total-pages="getTotalPages(r)"
                    :loading="loadingMap[r.id] ?? false"
                    @go="(page) => goToPage(r, page)"
                />
            </div>

            <div
                v-if="!props.relawans.length"
                class="rounded-xl border border-gray-100 bg-white p-12 text-center text-sm text-gray-400 shadow-sm"
            >
                Belum ada data relawan.
            </div>
        </div>
    </div>
</template>
