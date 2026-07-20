<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import { MapPin, Eye, FileImage } from '@lucide/vue';
import { ref, computed } from 'vue';
import PaginationBar from '@/components/PaginationBar.vue';
import kecamatanRoutes from '@/routes/kecamatan';

interface TpsRow {
    id: string;
    nama: string;
    desa: string;
    total_suara: number | null;
    has_c_hasil: boolean;
    c_hasil_url: string | null;
    sudah_diisi: boolean;
    created_at: string;
}


interface DropdownItem {
    id: string;
    nama: string;
}

const props = defineProps<{
    tpsList: TpsRow[];
    totalTps: number;
    kecamatan: string;
    desas: DropdownItem[];
    filters: {
        desa_id: string | null;
    };
}>();

const page = usePage();
const user = page.props.auth.user as any;

const selectedDesa = ref(props.filters.desa_id || '');

function handleFilterChange() {
    router.get(
        kecamatanRoutes.tps.index.url(),
        { desa_id: selectedDesa.value || null },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
}

// Realtime
if (typeof window !== 'undefined' && user) {
    useEcho(`kecamatan.tps.${user.kecamatan_id}`, 'TpsChanged', () => {
        router.reload({ only: ['tpsList', 'totalTps'] });
    });
    useEcho(`kecamatan.tps.${user.kecamatan_id}`, 'DataSuaraChanged', () => {
        router.reload({ only: ['tpsList'] });
    });
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

// Preview Modal
const previewUrl = ref<string | null>(null);
const isPreviewOpen = ref(false);


function closePreview() {
    isPreviewOpen.value = false;
    previewUrl.value = null;
}

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Data TPS & Suara', href: kecamatanRoutes.tps.index.url() }],
    },
});
</script>

<template>

    <Head title="Data TPS & Suara Kecamatan" />

    <div class="flex flex-col gap-6 p-6">
        <!-- Header -->
        <div>
            <h1 class="text-xl font-bold text-gray-900">Data TPS & Perolehan Suara</h1>
            <p class="text-sm text-gray-500 mt-0.5">Kecamatan {{ kecamatan }}</p>
        </div>

        <!-- Layout Grid: Stat Card & Tabel -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
            <!-- Stat Card -->
            <div class="lg:col-span-1">
                <div
                    class="rounded-2xl border border-amber-200 bg-linear-to-br from-yellow-400 to-amber-400 p-6 shadow-md text-gray-900">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-sm font-semibold uppercase tracking-wide text-gray-800">Total TPS</span>
                        <div class="rounded-xl bg-white/40 p-2">
                            <MapPin class="h-5 w-5 text-gray-900" />
                        </div>
                    </div>
                    <p class="text-5xl font-black">{{ totalTps }}</p>
                    <p class="text-xs mt-2 text-gray-850">TPS terdaftar di Kecamatan {{ kecamatan }}</p>
                </div>

                <!-- Filter Card -->
                <div class="mt-4 rounded-2xl border border-amber-100/50 bg-white p-4 shadow-sm">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Filter Desa</h3>
                    <div class="flex flex-col gap-2">
                        <select v-model="selectedDesa" @change="handleFilterChange"
                            class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm bg-white focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100/50">
                            <option value="">Semua Desa</option>
                            <option v-for="desa in desas" :key="desa.id" :value="desa.id">
                                {{ desa.nama }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Tabel Data -->
            <div class="lg:col-span-3">
                <div class="rounded-2xl border border-amber-100/50 bg-white shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-amber-100/30 bg-amber-50/10 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-800">Daftar TPS &amp; Perolehan Suara</h3>
                        <span class="text-xs text-amber-800 font-medium">{{ tpsList.length }} TPS</span>
                    </div>

                    <!-- Empty state -->
                    <div v-if="tpsList.length === 0"
                        class="flex flex-col items-center justify-center py-16 text-center">
                        <FileImage class="h-10 w-10 text-gray-200 mb-3" />
                        <p class="text-sm font-medium text-gray-400">Belum ada TPS</p>
                        <p class="text-xs text-gray-300 mt-1">Belum ada desa di kecamatan ini yang menginput TPS.</p>
                    </div>

                    <!-- Tabel (scrollable on mobile) -->
                    <div v-else class="overflow-x-auto">
                        <table class="w-full min-w-[600px] text-sm">
                            <thead>
                                <tr class="border-b border-amber-100/30 bg-amber-50/30 text-amber-800 font-semibold">
                                    <th class="px-5 py-3 text-left text-xs uppercase tracking-wide">#</th>
                                    <th class="px-5 py-3 text-left text-xs uppercase tracking-wide">Nama TPS</th>
                                    <th class="px-5 py-3 text-left text-xs uppercase tracking-wide">Desa</th>
                                    <th class="px-5 py-3 text-left text-xs uppercase tracking-wide">Total Suara</th>
                                    <th class="px-5 py-3 text-left text-xs uppercase tracking-wide">C-Hasil (Foto)</th>
                                    <th class="px-5 py-3 text-center text-xs uppercase tracking-wide">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-amber-50/40">
                                <tr v-for="(tps, idx) in pagedTps" :key="tps.id"
                                    class="border-b border-amber-50/20 transition hover:bg-amber-50/20">
                                    <td class="px-5 py-4 text-gray-400 font-mono text-xs">{{ (currentPage - 1) *
                                        PAGE_SIZE + idx + 1 }}</td>
                                    <td class="px-5 py-4 font-semibold text-gray-800 whitespace-nowrap">{{ tps.nama }}
                                    </td>
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
