<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import { Eye, FileImage, Search } from '@lucide/vue';
import { ref, watch, computed } from 'vue';
import PaginationBar from '@/components/PaginationBar.vue';
import adminRoutes from '@/routes/admin';

interface TpsRow {
    id: string;
    nama: string;
    desa: string;
    kecamatan: string;
    total_suara: number | null;
    has_c_hasil: boolean;
    c_hasil_url: string | null;
    sudah_diisi: boolean;
}

interface DropdownItem {
    id: string;
    nama: string;
    kecamatan_id?: string;
}

const props = defineProps<{
    tpsList: TpsRow[];
    totalTps: number;
    kecamatans: DropdownItem[];
    desas: DropdownItem[];
    filters: {
        kecamatan_id: string | null;
        desa_id: string | null;
    };
}>();

// Realtime
if (typeof window !== 'undefined') {
    useEcho('admin.tps', 'TpsChanged', () => {
        if (props.filters.kecamatan_id) {
            router.reload({ only: ['tpsList'] });
        }
    });
    useEcho('admin.tps', 'DataSuaraChanged', () => {
        if (props.filters.kecamatan_id) {
            router.reload({ only: ['tpsList'] });
        }
    });
}

const selectedKecamatan = ref(props.filters.kecamatan_id || '');
const selectedDesa = ref(props.filters.desa_id || '');

// Sync filters with props when props change (back navigation or manual reload)
watch(
    () => props.filters,
    (newFilters) => {
        selectedKecamatan.value = newFilters.kecamatan_id || '';
        selectedDesa.value = newFilters.desa_id || '';
    },
    { deep: true }
);

// Filter desas by selected kecamatan
const filteredDesas = computed(() => {
    if (!selectedKecamatan.value) {
        return [];
    }

    return props.desas.filter((desa) => desa.kecamatan_id === selectedKecamatan.value);
});

function handleKecamatanChange() {
    selectedDesa.value = '';
    handleFilterChange();
}

function handleFilterChange() {
    router.get(
        adminRoutes.tps.index.url(),
        {
            kecamatan_id: selectedKecamatan.value || null,
            desa_id: selectedDesa.value || null,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
}

function clearFilters() {
    selectedKecamatan.value = '';
    selectedDesa.value = '';
    router.get(adminRoutes.tps.index.url());
}

// Preview Modal
const previewUrl = ref<string | null>(null);
const isPreviewOpen = ref(false);


function closePreview() {
    isPreviewOpen.value = false;
    previewUrl.value = null;
}

// Pagination
const PAGE_SIZE = 10;
const currentPage = ref(1);
const totalPages = computed(() => Math.max(1, Math.ceil(props.tpsList.length / PAGE_SIZE)));
const pagedTps = computed(() => {
    const start = (currentPage.value - 1) * PAGE_SIZE;

    return props.tpsList.slice(start, start + PAGE_SIZE);
});
function goToPage(page: number) {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
}

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Data TPS & Suara', href: adminRoutes.tps.index.url() }],
    },
});
</script>

<template>

    <Head title="Data TPS & Suara Admin" />

    <div class="flex flex-col gap-6 p-6">
        <!-- Header -->
        <div>
            <h1 class="text-xl font-bold text-gray-900">Data TPS & Perolehan Suara</h1>
            <p class="text-sm text-gray-500 mt-0.5">Monitoring perolehan suara TPS per Kecamatan/Desa</p>
        </div>

        <!-- Filter Dropdown Card -->
        <div class="rounded-2xl border border-amber-100/50 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                <!-- Dropdown Kecamatan -->
                <div class="flex-1">
                    <label
                        class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Kecamatan</label>
                    <select v-model="selectedKecamatan" @change="handleKecamatanChange"
                        class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm bg-white focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100">
                        <option value="">-- Pilih Kecamatan --</option>
                        <option v-for="k in kecamatans" :key="k.id" :value="k.id">
                            {{ k.nama }}
                        </option>
                    </select>
                </div>

                <!-- Dropdown Desa -->
                <div class="flex-1">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Desa</label>
                    <select v-model="selectedDesa" @change="handleFilterChange" :disabled="!selectedKecamatan"
                        class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm bg-white focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100 disabled:bg-gray-50 disabled:text-gray-400">
                        <option value="">-- Pilih Desa --</option>
                        <option v-for="d in filteredDesas" :key="d.id" :value="d.id">
                            {{ d.nama }}
                        </option>
                    </select>
                </div>

                <!-- Reset Button -->
                <button v-if="selectedKecamatan || selectedDesa" @click="clearFilters"
                    class="rounded-xl bg-amber-400 px-4 py-2.5 text-sm font-semibold text-gray-900 transition cursor-pointer sm:w-auto h-[42px] flex items-center justify-center self-end whitespace-nowrap">
                    Reset
                </button>
            </div>
        </div>

        <!-- Konten Hasil Query -->
        <div v-if="!filters.kecamatan_id"
            class="rounded-2xl border border-amber-100/50 bg-white shadow-sm py-20 text-center flex flex-col items-center justify-center">
            <Search class="h-12 w-12 text-amber-600 mb-4" />
            <h3 class="text-sm font-semibold text-gray-600">Pilih Filter Terlebih Dahulu</h3>
            <p class="text-xs text-gray-400 mt-1 max-w-sm">
                Silakan pilih kecamatan pada dropdown di atas untuk menampilkan seluruh TPS dan data suara yang
                terdaftar.
            </p>
        </div>

        <div v-else class="grid grid-cols-1 gap-6 lg:grid-cols-4">
            <!-- Stat Card -->
            <div class="lg:col-span-1">
                <div
                    class="rounded-2xl border border-amber-200 bg-linear-to-br from-yellow-400 to-amber-400 p-6 shadow-md text-gray-900">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-sm font-semibold uppercase tracking-wide opacity-80">Total TPS</span>
                        <div class="rounded-xl bg-gray-900/10 p-2">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                <circle cx="12" cy="10" r="3" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-5xl font-black">{{ totalTps }}</p>
                    <p class="text-sm mt-2 opacity-80">TPS terdaftar di wilayah filter terpilih</p>
                </div>
            </div>

            <!-- Tabel Data -->
            <div class="lg:col-span-3">
                <div class="rounded-2xl border border-amber-100/50 bg-white shadow-sm overflow-hidden">
                    <div
                        class="px-5 py-4 border-b border-amber-100/30 bg-amber-50/30 text-amber-800 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-amber-800">Daftar TPS &amp; Perolehan Suara</h3>
                        <span class="text-xs text-amber-600">{{ tpsList.length }} TPS</span>
                    </div>

                    <!-- Empty state -->
                    <div v-if="tpsList.length === 0"
                        class="flex flex-col items-center justify-center py-16 text-center">
                        <FileImage class="h-10 w-10 text-gray-200 mb-3" />
                        <p class="text-sm font-medium text-gray-400">Tidak ada data TPS</p>
                        <p class="text-xs text-gray-300 mt-1">Belum ada TPS yang terdaftar di wilayah filter terpilih.
                        </p>
                    </div>

                    <!-- Tabel (scrollable on mobile) -->
                    <div v-else class="overflow-x-auto">
                        <table class="w-full min-w-[700px] text-sm">
                            <thead>
                                <tr class="border-b border-amber-100/30 bg-amber-50/30 text-amber-800 font-semibold">
                                    <th class="px-5 py-3 text-left text-xs uppercase tracking-wide">#</th>
                                    <th class="px-5 py-3 text-left text-xs uppercase tracking-wide">Nama TPS</th>
                                    <th class="px-5 py-3 text-left text-xs uppercase tracking-wide">Kecamatan</th>
                                    <th class="px-5 py-3 text-left text-xs uppercase tracking-wide">Desa</th>
                                    <th class="px-5 py-3 text-left text-xs uppercase tracking-wide">Total Suara</th>
                                    <th class="px-5 py-3 text-left text-xs uppercase tracking-wide">C-Hasil</th>
                                    <th class="px-5 py-3 text-center text-xs uppercase tracking-wide">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-amber-50/40">
                                <tr v-for="(tps, idx) in pagedTps" :key="tps.id"
                                    class="border-b border-amber-50/40 transition hover:bg-amber-50/20">
                                    <td class="px-5 py-4 text-gray-400 font-mono text-xs">{{ (currentPage - 1) *
                                        PAGE_SIZE + idx + 1 }}</td>
                                    <td class="px-5 py-4 font-semibold text-gray-800 whitespace-nowrap">{{ tps.nama }}
                                    </td>
                                    <td class="px-5 py-4 text-gray-600 whitespace-nowrap">{{ tps.kecamatan }}</td>
                                    <td class="px-5 py-4 text-gray-600 whitespace-nowrap">{{ tps.desa }}</td>
                                    <td class="px-5 py-4 font-mono font-semibold text-gray-700">
                                        {{ tps.total_suara !== null ? tps.total_suara.toLocaleString('id-ID') : '-' }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <!-- Thumbnail Preview jika file ada -->
                                        <a v-if="tps.has_c_hasil && tps.c_hasil_url" :href="tps.c_hasil_url"
                                            target="_blank"
                                            class="relative h-9 w-9 block overflow-hidden rounded-lg border border-amber-200 bg-gray-50 group">
                                            <img :src="tps.c_hasil_url"
                                                class="h-full w-full object-cover transition duration-150 group-hover:scale-105"
                                                alt="C-Hasil Thumbnail" />
                                            <div
                                                class="absolute inset-0 flex items-center justify-center bg-black/20 opacity-0 group-hover:opacity-100 transition duration-150">
                                                <Eye class="h-3 w-3 text-white" />
                                            </div>
                                        </a>
                                        <span v-else class="text-xs text-gray-400 italic">Belum diunggah</span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <span v-if="tps.sudah_diisi"
                                            class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-semibold text-green-700">
                                            Sudah Diisi
                                        </span>
                                        <span v-else
                                            class="inline-flex items-center rounded-full bg-gray-50 px-2.5 py-0.5 text-xs font-semibold text-gray-500">
                                            Belum Diisi
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="totalPages > 1" class="px-4 py-2 border-t border-gray-50">
                        <PaginationBar :current-page="currentPage" :total-pages="totalPages" @go="goToPage" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Fullscreen Image Preview -->
    <Teleport to="body">
        <div v-if="isPreviewOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4"
            @click="closePreview">
            <div class="relative max-h-screen max-w-4xl overflow-hidden rounded-xl bg-transparent" @click.stop>
                <img v-if="previewUrl" :src="previewUrl"
                    class="max-h-[85vh] max-w-full rounded-lg object-contain shadow-2xl" alt="C-Hasil Full Preview" />
                <button @click="closePreview"
                    class="absolute top-4 right-4 rounded-full bg-black/40 hover:bg-black/60 p-2 text-white transition focus:outline-none">
                    ✕
                </button>
            </div>
        </div>
    </Teleport>
</template>
