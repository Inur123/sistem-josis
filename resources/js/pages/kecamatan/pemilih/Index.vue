<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import { Loader2, Eye, CheckCircle, XCircle, Clock } from '@lucide/vue';
import { ref, watch, reactive } from 'vue';
import PaginationBar from '@/components/PaginationBar.vue';
import kecamatanRoutes from '@/routes/kecamatan';

interface Pemilih {
    id: string;
    nik: string;
    nama: string;
    jenis_kelamin: string;
    alamat: string;
    rt: string;
    rw: string;
    desa: string;
    relawan?: string;
    created_at: string;
    status: 'belum_verifikasi' | 'terverifikasi' | 'ditolak';
    alasan_ditolak?: string | null;
}

interface LinkItem {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedPemilih {
    data: Pemilih[];
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
    next_page_url: string | null;
    prev_page_url: string | null;
    links: LinkItem[];
}

interface DropdownItem {
    id: string;
    nama: string;
}

const props = defineProps<{
    pemilihs: PaginatedPemilih;
    desas: DropdownItem[];
    kecamatan: string;
    filters: {
        status: string;
        desa_id: string | null;
        search: string | null;
    };
    summary: {
        total: number;
        l: number;
        p: number;
        belum_verifikasi: number;
        terverifikasi: number;
        ditolak: number;
    };
}>();

const searchVal = ref(props.filters.search ?? '');
const selectedDesa = ref(props.filters.desa_id ?? '');
const selectedStatus = ref(props.filters.status ?? '');

// ─── State ────────────────────────────────────────────────────────────────────
const currentPage = ref(props.pemilihs.current_page);
const totalPages = ref(props.pemilihs.last_page);
const currentData = ref<Pemilih[]>([...props.pemilihs.data]);
const currentSummary = ref({ ...props.summary });
const loading = ref(false);

const pageCache = reactive<Record<number, Pemilih[]>>({
    [props.pemilihs.current_page]: [...props.pemilihs.data],
});

// Watch props to reset when filters change
watch(
    () => props.pemilihs,
    (newPemilihs) => {
        currentPage.value = newPemilihs.current_page;
        totalPages.value = newPemilihs.last_page;
        currentData.value = [...newPemilihs.data];
        Object.keys(pageCache).forEach((k) => delete pageCache[Number(k)]);
        pageCache[newPemilihs.current_page] = [...newPemilihs.data];
    },
    { deep: true },
);

watch(
    () => props.summary,
    (newSummary) => {
        currentSummary.value = { ...newSummary };
    },
    { deep: true },
);

// Trigger filters when desa changes
watch(selectedDesa, () => {
    applyFilters();
});

// Trigger filters when status changes
watch(selectedStatus, () => {
    applyFilters();
});

let searchTimeout: ReturnType<typeof setTimeout>;
watch(searchVal, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 400);
});

function handleSearch(e: Event) {
    e.preventDefault();
    applyFilters();
}

function applyFilters() {
    router.get(
        '/kecamatan/pemilih',
        {
            search: searchVal.value || undefined,
            desa_id: selectedDesa.value || undefined,
            status: selectedStatus.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
}

function clearFilters() {
    searchVal.value = '';
    selectedDesa.value = '';
    selectedStatus.value = '';
    router.get('/kecamatan/pemilih');
}

async function goToPage(page: number) {
    if (
        page < 1 ||
        page > totalPages.value ||
        page === currentPage.value ||
        loading.value
    ) {
        return;
    }

    if (pageCache[page]) {
        currentPage.value = page;
        currentData.value = pageCache[page];

        return;
    }

    loading.value = true;

    try {
        const queryParams = new URLSearchParams({
            page: String(page),
            ...(searchVal.value ? { search: searchVal.value } : {}),
            ...(selectedDesa.value ? { desa_id: selectedDesa.value } : {}),
            ...(selectedStatus.value ? { status: selectedStatus.value } : {}),
        });
        const res = await fetch(
            `/kecamatan/pemilih?${queryParams.toString()}`,
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

        pageCache[page] = json.paginated.data;
        currentPage.value = page;
        currentData.value = json.paginated.data;
        totalPages.value = json.paginated.last_page;
        currentSummary.value = json.summary;
    } catch (e) {
        console.error('Error fetching pemilihs:', e);
    } finally {
        loading.value = false;
    }
}

const page = usePage();
const user = page.props.auth.user as any;

if (typeof window !== 'undefined' && user) {
    useEcho(`kecamatan.pemilih.${user.kecamatan_id}`, 'PemilihChanged', () => {
        router.reload();
    });
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/kecamatan/dashboard' },
            { title: 'Data Pemilih', href: '/kecamatan/pemilih' },
        ],
    },
});
</script>

<template>
    <Head title="Data Pemilih (Kecamatan)" />
    <div class="flex flex-col gap-5 p-6">
        <!-- Header -->
        <div class="flex items-start justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900">
                    Data Pemilih (Kec. {{ props.kecamatan }})
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Total
                    {{ currentSummary.total.toLocaleString('id-ID') }} pemilih
                    terdaftar di wilayah kecamatan ini.
                </p>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
            <!-- Total Pemilih -->
            <div
                class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm"
            >
                <div
                    class="mb-2 flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50"
                >
                    <svg
                        class="h-4.5 w-4.5 text-blue-600"
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
                <div class="text-xl leading-tight font-bold text-gray-900">
                    {{ currentSummary.total.toLocaleString('id-ID') }}
                </div>
                <div
                    class="mt-0.5 text-[10px] font-medium tracking-wider text-gray-500 uppercase"
                >
                    Total Pemilih
                </div>
            </div>

            <!-- Laki-laki -->
            <div
                class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm"
            >
                <div
                    class="mb-2 flex h-9 w-9 items-center justify-center rounded-lg bg-sky-50"
                >
                    <svg
                        class="h-4.5 w-4.5 text-sky-600"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                </div>
                <div class="text-xl leading-tight font-bold text-gray-900">
                    {{ currentSummary.l.toLocaleString('id-ID') }}
                </div>
                <div
                    class="mt-0.5 text-[10px] font-medium tracking-wider text-gray-500 uppercase"
                >
                    Laki-laki
                </div>
            </div>

            <!-- Perempuan -->
            <div
                class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm"
            >
                <div
                    class="mb-2 flex h-9 w-9 items-center justify-center rounded-lg bg-pink-50"
                >
                    <svg
                        class="h-4.5 w-4.5 text-pink-600"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                </div>
                <div class="text-xl leading-tight font-bold text-gray-900">
                    {{ currentSummary.p.toLocaleString('id-ID') }}
                </div>
                <div
                    class="mt-0.5 text-[10px] font-medium tracking-wider text-gray-500 uppercase"
                >
                    Perempuan
                </div>
            </div>

            <!-- Belum Verifikasi -->
            <div
                class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm"
            >
                <div
                    class="mb-2 flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50"
                >
                    <svg
                        class="h-4.5 w-4.5 text-amber-600"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <circle cx="12" cy="12" r="10" />
                        <polyline points="12 6 12 12 16 14" />
                    </svg>
                </div>
                <div class="text-xl leading-tight font-bold text-gray-900">
                    {{
                        (currentSummary.belum_verifikasi ?? 0).toLocaleString(
                            'id-ID',
                        )
                    }}
                </div>
                <div
                    class="mt-0.5 text-[10px] font-medium tracking-wider text-gray-500 uppercase"
                >
                    Belum Verifikasi
                </div>
            </div>

            <!-- Terverifikasi -->
            <div
                class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm"
            >
                <div
                    class="mb-2 flex h-9 w-9 items-center justify-center rounded-lg bg-green-50"
                >
                    <svg
                        class="h-4.5 w-4.5 text-green-600"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                        <polyline points="22 4 12 14.01 9 11.01" />
                    </svg>
                </div>
                <div class="text-xl leading-tight font-bold text-gray-900">
                    {{
                        (currentSummary.terverifikasi ?? 0).toLocaleString(
                            'id-ID',
                        )
                    }}
                </div>
                <div
                    class="mt-0.5 text-[10px] font-medium tracking-wider text-gray-500 uppercase"
                >
                    Terverifikasi
                </div>
            </div>

            <!-- Ditolak -->
            <div
                class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm"
            >
                <div
                    class="mb-2 flex h-9 w-9 items-center justify-center rounded-lg bg-red-50"
                >
                    <svg
                        class="h-4.5 w-4.5 text-red-600"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <circle cx="12" cy="12" r="10" />
                        <line x1="15" y1="9" x2="9" y2="15" />
                        <line x1="9" y1="9" x2="15" y2="15" />
                    </svg>
                </div>
                <div class="text-xl leading-tight font-bold text-gray-900">
                    {{ (currentSummary.ditolak ?? 0).toLocaleString('id-ID') }}
                </div>
                <div
                    class="mt-0.5 text-[10px] font-medium tracking-wider text-gray-500 uppercase"
                >
                    Ditolak
                </div>
            </div>
        </div>

        <!-- Filter & Search Controls -->
        <div
            class="flex flex-col gap-4 rounded-xl border border-gray-100 bg-white p-4 shadow-sm md:flex-row md:items-center"
        >
            <form @submit="handleSearch" class="w-full md:flex-1">
                <div class="relative">
                    <input
                        v-model="searchVal"
                        @input="
                            searchVal = searchVal.replace(
                                /[^a-zA-Z0-9\s\.\'-]/g,
                                '',
                            )
                        "
                        type="text"
                        placeholder="Cari Nama / NIK..."
                        class="w-full rounded-lg border border-gray-200 py-2 pr-4 pl-9 text-sm focus:border-blue-500 focus:outline-none"
                    />
                    <svg
                        class="absolute top-2.5 left-3 h-4 w-4 text-gray-400"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                </div>
            </form>

            <div
                class="flex w-full flex-col gap-3 sm:flex-row md:w-auto md:items-center"
            >
                <!-- Desa Select -->
                <select
                    v-model="selectedDesa"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none sm:flex-1 md:w-56"
                >
                    <option value="">Semua Desa/Kelurahan</option>
                    <option
                        v-for="desa in props.desas"
                        :key="desa.id"
                        :value="desa.id"
                    >
                        {{ desa.nama }}
                    </option>
                </select>

                <!-- Status Select -->
                <select
                    v-model="selectedStatus"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none sm:flex-1 md:w-48"
                >
                    <option value="">Semua Status</option>
                    <option value="belum_verifikasi">Belum Verifikasi</option>
                    <option value="terverifikasi">Terverifikasi</option>
                    <option value="ditolak">Ditolak</option>
                </select>

                <!-- Reset Filter Button -->
                <button
                    v-if="searchVal || selectedDesa || selectedStatus"
                    @click="clearFilters"
                    class="w-full rounded-lg bg-gray-100 px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-200 sm:w-auto"
                >
                    Reset
                </button>
            </div>
        </div>

        <!-- Table -->
        <div
            class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm"
        >
            <div class="relative overflow-x-auto">
                <!-- Loading Overlay -->
                <div
                    v-if="loading"
                    class="absolute inset-0 z-10 flex items-center justify-center rounded-lg bg-white/70"
                >
                    <Loader2 class="h-6 w-6 animate-spin text-blue-600" />
                </div>

                <table class="w-full text-xs lg:text-sm">
                    <thead>
                        <tr
                            class="text-xxs border-b border-gray-100 bg-gray-50 text-left font-semibold tracking-wide text-gray-500 uppercase lg:text-xs"
                        >
                            <th class="px-2 py-3 text-center">No</th>
                            <th class="px-2 py-3">NIK</th>
                            <th class="px-2.5 py-3">Nama</th>
                            <th class="px-1.5 py-3 text-center">JK</th>
                            <th class="px-2 py-3">Desa / Kel</th>
                            <th class="px-2.5 py-3">Alamat</th>
                            <th class="px-1.5 py-3 text-center">RT/RW</th>
                            <th class="px-2 py-3">Relawan</th>
                            <th class="px-2 py-3">Tgl Input</th>
                            <th class="px-2 py-3 text-center">Status</th>
                            <th class="px-2 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr
                            v-for="(p, i) in currentData"
                            :key="p.id"
                            class="cursor-pointer transition-colors hover:bg-gray-50/80"
                            @click="
                                router.visit(
                                    kecamatanRoutes.pemilih.show.url(p.id),
                                )
                            "
                        >
                            <td class="px-2 py-3 text-center text-gray-400">
                                {{
                                    (currentPage - 1) *
                                        props.pemilihs.per_page +
                                    i +
                                    1
                                }}
                            </td>
                            <td
                                class="text-xxs px-2 py-3 font-mono text-gray-500 lg:text-xs"
                            >
                                {{ p.nik }}
                            </td>
                            <td class="px-2.5 py-3 font-medium text-gray-900">
                                {{ p.nama }}
                            </td>
                            <td class="px-1.5 py-3 text-center">
                                <span
                                    :class="[
                                        'inline-flex h-4.5 w-4.5 items-center justify-center rounded-full text-[10px] font-bold',
                                        p.jenis_kelamin === 'L'
                                            ? 'bg-blue-100 text-blue-700'
                                            : 'bg-pink-100 text-pink-700',
                                    ]"
                                    >{{ p.jenis_kelamin }}</span
                                >
                            </td>
                            <td class="px-2 py-3 text-gray-600">
                                {{ p.desa }}
                            </td>
                            <td
                                class="max-w-[180px] truncate px-2.5 py-3 text-gray-600"
                            >
                                {{ p.alamat }}
                            </td>
                            <td class="px-1.5 py-3 text-center text-gray-600">
                                {{ p.rt }}/{{ p.rw }}
                            </td>
                            <td class="px-2 py-3 text-gray-600">
                                {{ p.relawan ?? '-' }}
                            </td>
                            <td class="px-2 py-3 text-xs text-gray-400">
                                {{ p.created_at }}
                            </td>
                            <td class="px-2 py-3 text-center">
                                <div class="flex justify-center">
                                    <CheckCircle
                                        v-if="p.status === 'terverifikasi'"
                                        class="h-5 w-5 text-green-600"
                                        title="Terverifikasi"
                                    />
                                    <XCircle
                                        v-else-if="p.status === 'ditolak'"
                                        class="h-5 w-5 animate-pulse text-red-600"
                                        :title="
                                            p.alasan_ditolak
                                                ? 'Ditolak: ' + p.alasan_ditolak
                                                : 'Ditolak'
                                        "
                                    />
                                    <Clock
                                        v-else
                                        class="h-5 w-5 text-amber-500"
                                        title="Belum Verifikasi"
                                    />
                                </div>
                            </td>
                            <td class="px-2 py-3 text-center">
                                <div class="flex items-center justify-center">
                                    <Link
                                        :href="
                                            kecamatanRoutes.pemilih.show.url(
                                                p.id,
                                            )
                                        "
                                        class="inline-flex h-7 w-7 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 shadow-sm transition hover:bg-gray-50 hover:text-gray-900"
                                        title="Detail"
                                    >
                                        <Eye class="h-3.5 w-3.5" />
                                    </Link>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!currentData.length && !loading">
                            <td
                                colspan="11"
                                class="px-4 py-12 text-center text-sm text-gray-400"
                            >
                                Tidak ada data pemilih ditemukan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="totalPages > 1" class="px-4">
                <PaginationBar
                    :current-page="currentPage"
                    :total-pages="totalPages"
                    :loading="loading"
                    @go="goToPage"
                />
            </div>
        </div>
    </div>
</template>
