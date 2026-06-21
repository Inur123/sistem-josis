<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Loader2 } from '@lucide/vue';
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
    kecamatan: string;
    desa: string;
    relawan?: string;
    created_at: string;
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
    kecamatan_id?: string;
}

const props = defineProps<{
    pemilihs: PaginatedPemilih;
    kecamatans: DropdownItem[];
    desas: DropdownItem[];
    filters: {
        kecamatan_id: string | null;
        desa_id: string | null;
        search: string | null;
    };
    summary: {
        total: number;
        l: number;
        p: number;
    };
}>();

const searchVal = ref(props.filters.search ?? '');
const selectedKecamatan = ref(props.filters.kecamatan_id ?? '');
const selectedDesa = ref(props.filters.desa_id ?? '');

// ─── State ────────────────────────────────────────────────────────────────────
const currentPage = ref(props.pemilihs.current_page);
const totalPages  = ref(props.pemilihs.last_page);
const currentData = ref<Pemilih[]>([...props.pemilihs.data]);
const currentSummary = ref({ ...props.summary });
const loading     = ref(false);

const pageCache = reactive<Record<number, Pemilih[]>>({
    [props.pemilihs.current_page]: [...props.pemilihs.data],
});

// Watch props to reset when filters change (via Inertia reload)
watch(() => props.pemilihs, (newPemilihs) => {
    currentPage.value = newPemilihs.current_page;
    totalPages.value = newPemilihs.last_page;
    currentData.value = [...newPemilihs.data];
    Object.keys(pageCache).forEach(k => delete pageCache[Number(k)]);
    pageCache[newPemilihs.current_page] = [...newPemilihs.data];
}, { deep: true });

watch(() => props.summary, (newSummary) => {
    currentSummary.value = { ...newSummary };
}, { deep: true });

// Filter desas options based on selected kecamatan
const filteredDesas = computed(() => {
    if (!selectedKecamatan.value) {
        return [];
    }

    return props.desas.filter(
        (desa) => desa.kecamatan_id === selectedKecamatan.value,
    );
});

// Reset desa when kecamatan changes
watch(selectedKecamatan, () => {
    selectedDesa.value = '';
    applyFilters();
});

// Trigger filters when desa or search changes
watch(selectedDesa, () => {
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
        adminRoutes.pemilih.index.url(),
        {
            search: searchVal.value || undefined,
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
    searchVal.value = '';
    selectedKecamatan.value = '';
    selectedDesa.value = '';
    router.get(adminRoutes.pemilih.index.url());
}

async function goToPage(page: number) {
    if (page < 1 || page > totalPages.value || page === currentPage.value || loading.value) {
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
            ...(selectedKecamatan.value ? { kecamatan_id: selectedKecamatan.value } : {}),
            ...(selectedDesa.value ? { desa_id: selectedDesa.value } : {}),
        });
        const res = await fetch(`/admin/pemilih?${queryParams.toString()}`, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

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

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: adminRoutes.dashboard.url() },
            { title: 'Data Pemilih', href: adminRoutes.pemilih.index.url() },
        ],
    },
});
</script>

<template>
    <Head title="Data Pemilih (Admin)" />
    <div class="flex flex-col gap-5 p-6">
        <!-- Header -->
        <div class="flex items-start justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900">
                    Data Pemilih (Magetan)
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Total
                    {{ currentSummary.total.toLocaleString('id-ID') }} pemilih
                    terdaftar di seluruh wilayah.
                </p>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <!-- Total Pemilih -->
            <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                <div class="mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50">
                    <svg class="h-5 w-5 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" />
                    </svg>
                </div>
                <div class="text-2xl font-bold text-gray-900">
                    {{ currentSummary.total.toLocaleString('id-ID') }}
                </div>
                <div class="mt-0.5 text-xs text-gray-500">Total Pemilih (Terfilter)</div>
            </div>

            <!-- Laki-laki -->
            <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                <div class="mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-sky-50">
                    <svg class="h-5 w-5 text-sky-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                </div>
                <div class="text-2xl font-bold text-gray-900">
                    {{ currentSummary.l.toLocaleString('id-ID') }}
                </div>
                <div class="mt-0.5 text-xs text-gray-500">Laki-laki</div>
            </div>

            <!-- Perempuan -->
            <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                <div class="mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-pink-50">
                    <svg class="h-5 w-5 text-pink-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                </div>
                <div class="text-2xl font-bold text-gray-900">
                    {{ currentSummary.p.toLocaleString('id-ID') }}
                </div>
                <div class="mt-0.5 text-xs text-gray-500">Perempuan</div>
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
                        @input="searchVal = searchVal.replace(/[^a-zA-Z0-9\s\.\'-]/g, '')"
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

            <div class="flex flex-col gap-3 w-full sm:flex-row md:w-auto md:items-center">
                <!-- Kecamatan Select -->
                <select
                    v-model="selectedKecamatan"
                    class="w-full sm:flex-1 md:w-48 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
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
                    class="w-full sm:flex-1 md:w-48 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none disabled:bg-gray-50 disabled:text-gray-400"
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

                <!-- Reset Filter Button -->
                <button
                    v-if="searchVal || selectedKecamatan || selectedDesa"
                    @click="clearFilters"
                    class="w-full sm:w-auto rounded-lg bg-gray-100 px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-200"
                >
                    Reset
                </button>
            </div>
        </div>

        <!-- Table -->
        <div
            class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm"
        >
            <div class="overflow-x-auto relative">
                <!-- Loading Overlay -->
                <div
                    v-if="loading"
                    class="absolute inset-0 z-10 flex items-center justify-center rounded-lg bg-white/70"
                >
                    <Loader2 class="h-6 w-6 animate-spin text-blue-600" />
                </div>

                <table class="w-full text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50 text-left text-xs font-semibold tracking-wide text-gray-500 uppercase"
                        >
                            <th class="px-4 py-3">No</th>
                            <th class="px-4 py-3">NIK</th>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">JK</th>
                            <th class="px-4 py-3">Kecamatan</th>
                            <th class="px-4 py-3">Desa / Kel</th>
                            <th class="px-4 py-3">Alamat</th>
                            <th class="px-4 py-3 text-center">RT/RW</th>
                            <th class="px-4 py-3">Relawan</th>
                            <th class="px-4 py-3">Tanggal Input</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr
                            v-for="(p, i) in currentData"
                            :key="p.id"
                            class="hover:bg-gray-50"
                        >
                            <td class="px-4 py-3 text-gray-400">
                                {{
                                    (currentPage - 1) *
                                        props.pemilihs.per_page +
                                    i +
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
                                    >{{ p.jenis_kelamin }}</span
                                >
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                Kec. {{ p.kecamatan }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ p.desa }}
                            </td>
                            <td
                                class="max-w-[180px] truncate px-4 py-3 text-gray-600"
                            >
                                {{ p.alamat }}
                            </td>
                            <td class="px-4 py-3 text-center text-gray-600">
                                {{ p.rt }}/{{ p.rw }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ p.relawan ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-400">
                                {{ p.created_at }}
                            </td>
                        </tr>
                        <tr v-if="!currentData.length && !loading">
                            <td
                                colspan="10"
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
