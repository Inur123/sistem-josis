<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Loader2, Eye, Pencil, Trash2 } from '@lucide/vue';
import { ref, watch, computed, reactive } from 'vue';
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
    relawan?: string;
    created_at: string;
    foto_ktp?: string | null;
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

const props = defineProps<{
    pemilihs: PaginatedPemilih;
    desa: string;
    filters: {
        search: string | null;
        jenis_kelamin: string | null;
    };
    summary: {
        total: number;
        l: number;
        p: number;
    };
}>();

const confirmDelete = ref<string | null>(null);
const searchVal = ref(props.filters.search ?? '');
const selectedJk = ref(props.filters.jenis_kelamin ?? '');

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

const selectedVoter = computed(() => {
    if (!confirmDelete.value) {
        return null;
    }

    return currentData.value.find((p) => p.id === confirmDelete.value) || null;
});

watch(selectedJk, () => {
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
        desaRoutes.pemilih.index.url(),
        {
            search: searchVal.value || undefined,
            jenis_kelamin: selectedJk.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
}

// Reset page cache when deleting voter
function submitDelete() {
    if (!confirmDelete.value) {
        return;
    }

    router.delete(desaRoutes.pemilih.destroy.url(confirmDelete.value), {
        onSuccess: () => {
            confirmDelete.value = null;
            // Clear page cache to reload fresh data
            Object.keys(pageCache).forEach((k) => delete pageCache[Number(k)]);
        },
    });
}

function clearFilters() {
    searchVal.value = '';
    selectedJk.value = '';
    router.get(desaRoutes.pemilih.index.url());
}

function openDeleteModal(id: string) {
    confirmDelete.value = id;
}

function cancelDelete() {
    confirmDelete.value = null;
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
            ...(selectedJk.value ? { jenis_kelamin: selectedJk.value } : {}),
        });
        const res = await fetch(`/desa/pemilih?${queryParams.toString()}`, {
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
            { title: 'Dashboard', href: desaRoutes.dashboard.url() },
            { title: 'Data Pemilih', href: desaRoutes.pemilih.index.url() },
        ],
    },
});
</script>

<template>
    <Head title="Data Pemilih" />
    <div class="flex flex-col gap-5 p-6">
        <!-- Header -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
        >
            <div>
                <h2 class="text-xl font-bold text-gray-900">
                    Data Pemilih — {{ props.desa }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Daftar pemilih terdaftar di desa {{ props.desa }}
                </p>
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

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
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
                    {{ currentSummary.total.toLocaleString('id-ID') }}
                </div>
                <div class="mt-0.5 text-xs text-gray-500">
                    Total Pemilih (Terfilter)
                </div>
            </div>

            <!-- Laki-laki -->
            <div
                class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm"
            >
                <div
                    class="mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-sky-50"
                >
                    <svg
                        class="h-5 w-5 text-sky-600"
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
                    {{ currentSummary.l.toLocaleString('id-ID') }}
                </div>
                <div class="mt-0.5 text-xs text-gray-500">Laki-laki</div>
            </div>

            <!-- Perempuan -->
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
                <!-- Gender Select -->
                <select
                    v-model="selectedJk"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none sm:flex-1 md:w-56"
                >
                    <option value="">Semua Jenis Kelamin</option>
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                </select>

                <!-- Reset Filter Button -->
                <button
                    v-if="searchVal || selectedJk"
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

                <table class="w-full text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50 text-left text-xs font-semibold tracking-wide text-gray-500 uppercase"
                        >
                            <th class="px-4 py-3">No</th>
                            <th class="px-4 py-3">NIK</th>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">JK</th>
                            <th class="px-4 py-3">Alamat</th>
                            <th class="px-4 py-3 text-center">RT/RW</th>
                            <th class="px-4 py-3">Relawan</th>
                            <th class="px-4 py-3">Tanggal Input</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
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
                            <td class="px-4 py-3">
                                <div
                                    class="flex items-center justify-center gap-1.5"
                                >
                                    <Link
                                        :href="
                                            desaRoutes.pemilih.show.url(p.id)
                                        "
                                        title="Detail"
                                        class="rounded-lg p-1.5 text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-900"
                                    >
                                        <Eye class="h-4.5 w-4.5" />
                                    </Link>
                                    <Link
                                        :href="
                                            desaRoutes.pemilih.edit.url(p.id)
                                        "
                                        title="Edit"
                                        class="rounded-lg p-1.5 text-blue-600 transition-colors hover:bg-blue-50 hover:text-blue-700"
                                    >
                                        <Pencil class="h-4.5 w-4.5" />
                                    </Link>
                                    <button
                                        @click="openDeleteModal(p.id)"
                                        title="Hapus"
                                        class="rounded-lg p-1.5 text-red-600 transition-colors hover:bg-red-50 hover:text-red-700"
                                    >
                                        <Trash2 class="h-4.5 w-4.5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!currentData.length && !loading">
                            <td
                                colspan="9"
                                class="px-4 py-12 text-center text-sm text-gray-400"
                            >
                                Belum ada data pemilih.
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

    <!-- Delete Confirmation Modal -->
    <div
        v-if="confirmDelete"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
    >
        <div
            class="w-full max-w-md animate-in overflow-hidden rounded-xl border border-gray-100 bg-white p-6 shadow-xl duration-200 zoom-in-95 fade-in"
        >
            <div
                class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-50 text-red-600"
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
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                    />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900">Konfirmasi Hapus</h3>
            <p class="mt-2 text-sm text-gray-500">
                Apakah Anda yakin ingin menghapus data pemilih bernama
                <strong class="font-semibold text-gray-800">{{
                    selectedVoter?.nama
                }}</strong
                >? Tindakan ini tidak dapat dibatalkan.
            </p>
            <div class="mt-6 flex justify-end gap-3">
                <button
                    @click="cancelDelete"
                    class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                >
                    Batal
                </button>
                <button
                    @click="submitDelete"
                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                >
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>
</template>
