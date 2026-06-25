<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Loader2, Eye } from '@lucide/vue';
import { ref, watch, computed, reactive } from 'vue';
import PaginationBar from '@/components/PaginationBar.vue';
import adminRoutes from '@/routes/admin';

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
    kecamatan: string;
    desa: string;
    pemilihs_count: number;
    pemilihs: Pemilih[];
}

interface DropdownItem {
    id: string;
    nama: string;
    kecamatan_id?: string;
}

const props = defineProps<{
    relawans: Relawan[];
    kecamatans: DropdownItem[];
    desas: DropdownItem[];
    filters: {
        kecamatan_id: string | null;
        desa_id: string | null;
    };
}>();

const selectedKecamatan = ref(props.filters.kecamatan_id ?? '');
const selectedDesa = ref(props.filters.desa_id ?? '');

// Filter desas based on selected kecamatan
const filteredDesas = computed(() => {
    if (!selectedKecamatan.value) {
        return [];
    }

    return props.desas.filter(
        (d) => d.kecamatan_id === selectedKecamatan.value,
    );
});

// Watch kecamatan to reset desa
watch(selectedKecamatan, () => {
    selectedDesa.value = '';
    applyFilters();
});

watch(selectedDesa, () => {
    applyFilters();
});

function applyFilters() {
    router.get(
        '/admin/relawan',
        {
            kecamatan_id: selectedKecamatan.value || undefined,
            desa_id: selectedDesa.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
}

function clearFilters() {
    selectedKecamatan.value = '';
    selectedDesa.value = '';
    router.get('/admin/relawan');
}

const getGenderStats = (pemilihs: Pemilih[]) => {
    const l = pemilihs.filter((p) => p.jenis_kelamin === 'L').length;
    const p = pemilihs.filter((p) => p.jenis_kelamin === 'P').length;

    return { l, p };
};

// ─── Server-side pagination per relawan ───────────────────────────────────────
const ITEMS_PER_PAGE = 10;

// Halaman saat ini untuk tiap relawan
const currentPages = reactive<Record<string, number>>({});

// Cache: relawanId -> halaman -> data pemilihs
const pageCache = reactive<Record<string, Record<number, Pemilih[]>>>({});

// Loading state per relawan
const loadingMap = reactive<Record<string, boolean>>({});

// Inisialisasi cache halaman 1 dari data yang sudah ada di props
function initCache(relawan: Relawan) {
    if (!pageCache[relawan.id]) {
        pageCache[relawan.id] = { 1: relawan.pemilihs };
    }

    if (!currentPages[relawan.id]) {
        currentPages[relawan.id] = 1;
    }
}

// Ambil pemilih untuk halaman tertentu
function getPemilihPage(relawan: Relawan): Pemilih[] {
    initCache(relawan);
    const page = currentPages[relawan.id] ?? 1;

    return pageCache[relawan.id]?.[page] ?? [];
}

// Hitung total halaman berdasarkan pemilihs_count dari server
function getTotalPages(relawan: Relawan): number {
    return Math.ceil(relawan.pemilihs_count / ITEMS_PER_PAGE);
}

// Fetch halaman dari server jika belum ada di cache
async function goToPage(relawan: Relawan, page: number) {
    initCache(relawan);
    const total = getTotalPages(relawan);

    if (page < 1 || page > total) {
        return;
    }

    // Sudah ada di cache, langsung tampilkan
    if (pageCache[relawan.id]?.[page]) {
        currentPages[relawan.id] = page;

        return;
    }

    // Fetch dari server
    loadingMap[relawan.id] = true;

    try {
        const res = await fetch(
            `/admin/relawan/${relawan.id}/pemilihs?page=${page}`,
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
            { title: 'Dashboard', href: '/admin/dashboard' },
            { title: 'Data Relawan', href: '#' },
        ],
    },
});
</script>

<template>
    <Head title="Data Relawan (Admin)" />
    <div class="flex flex-col gap-5 p-6">
        <!-- Header -->
        <div
            class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
        >
            <div class="flex flex-col gap-1">
                <h2 class="text-xl font-bold text-gray-900">Data Relawan</h2>
                <p class="text-sm text-gray-500">
                    Daftar relawan pendamping dan data pemilih mereka di seluruh
                    wilayah kabupaten
                </p>
            </div>

            <!-- Filters -->
            <div
                class="flex w-full flex-col gap-3 sm:flex-row md:w-auto md:items-center"
            >
                <!-- Kecamatan Select -->
                <select
                    v-model="selectedKecamatan"
                    class="border-gray-250 w-full rounded-lg border bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none sm:flex-1 md:w-48"
                >
                    <option value="">Semua Kecamatan</option>
                    <option
                        v-for="kec in props.kecamatans"
                        :key="kec.id"
                        :value="kec.id"
                    >
                        Kec. {{ kec.nama }}
                    </option>
                </select>

                <!-- Desa Select -->
                <select
                    v-model="selectedDesa"
                    :disabled="!selectedKecamatan"
                    class="border-gray-250 w-full rounded-lg border bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none disabled:bg-gray-50 disabled:text-gray-400 sm:flex-1 md:w-48"
                >
                    <option value="">Semua Desa/Kelurahan</option>
                    <option
                        v-for="desa in filteredDesas"
                        :key="desa.id"
                        :value="desa.id"
                    >
                        {{ desa.nama }}
                    </option>
                </select>

                <!-- Reset Button -->
                <button
                    v-if="selectedKecamatan || selectedDesa"
                    @click="clearFilters"
                    class="w-full rounded-lg bg-gray-100 px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-200 sm:w-auto"
                >
                    Reset
                </button>
            </div>
        </div>

        <!-- Volunteers Sections / Tables -->
        <div class="flex flex-col gap-6">
            <template v-if="selectedKecamatan">
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
                            <div class="flex flex-wrap items-center gap-2">
                                <h3
                                    class="flex items-center gap-2 text-base font-semibold text-gray-900"
                                >
                                    <span
                                        class="inline-flex h-2 w-2 rounded-full bg-blue-600"
                                    ></span>
                                    Relawan: {{ r.nama }}
                                </h3>
                                <span
                                    class="inline-flex items-center rounded border border-blue-100 bg-blue-50 px-2 py-0.5 text-xs text-blue-700"
                                >
                                    Kec. {{ r.kecamatan }}
                                </span>
                                <span
                                    class="inline-flex items-center rounded border border-green-100 bg-green-50 px-2 py-0.5 text-xs text-green-700"
                                >
                                    Desa {{ r.desa }}
                                </span>
                            </div>
                            <div
                                class="mt-1.5 flex flex-wrap gap-x-6 gap-y-1 font-mono text-xs text-gray-500"
                            >
                                <span v-if="r.nik"
                                    ><strong>NIK:</strong> {{ r.nik }}</span
                                >
                                <span v-if="r.no_hp"
                                    ><strong>No. HP:</strong>
                                    {{ r.no_hp }}</span
                                >
                                <span v-if="r.alamat"
                                    ><strong>Alamat:</strong>
                                    {{ r.alamat }}</span
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
                            <Loader2
                                class="h-6 w-6 animate-spin text-blue-600"
                            />
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
                                    <td
                                        class="px-4 py-3 font-medium text-gray-900"
                                    >
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
                                    <td
                                        class="px-4 py-3 text-center text-gray-600"
                                    >
                                        {{ p.rt }}/{{ p.rw }}
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-400">
                                        {{ p.created_at }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center">
                                            <Link
                                                :href="adminRoutes.pemilih.show.url(p.id)"
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
                    Belum ada data relawan di wilayah ini.
                </div>
            </template>
            <template v-else>
                <div
                    class="rounded-xl border border-gray-100 bg-white p-16 text-center shadow-sm"
                >
                    <div
                        class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-blue-50 text-blue-600"
                    >
                        <svg
                            class="h-6 w-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"
                            />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-sm font-semibold text-gray-900">
                        Pilih Wilayah Terlebih Dahulu
                    </h3>
                    <p class="mx-auto mt-1 max-w-sm text-xs text-gray-500">
                        Silakan pilih Kecamatan terlebih dahulu pada dropdown
                        filter di atas untuk memuat data relawan dan pemilih
                        pendampingnya.
                    </p>
                </div>
            </template>
        </div>
    </div>
</template>
