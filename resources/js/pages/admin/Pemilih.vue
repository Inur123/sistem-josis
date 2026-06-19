<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
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
    created_at: string;
}

interface PaginatedPemilih {
    data: Pemilih[];
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
    next_page_url: string | null;
    prev_page_url: string | null;
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
}>();

const searchVal = ref(props.filters.search ?? '');
const selectedKecamatan = ref(props.filters.kecamatan_id ?? '');
const selectedDesa = ref(props.filters.desa_id ?? '');

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
                    Data Pemilih Nasional (Magetan)
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Total
                    {{ props.pemilihs.total.toLocaleString('id-ID') }} pemilih
                    terdaftar di seluruh wilayah.
                </p>
            </div>
        </div>

        <!-- Filter & Search Controls -->
        <div
            class="flex flex-col gap-4 rounded-xl border border-gray-100 bg-white p-4 shadow-sm md:flex-row md:items-center"
        >
            <form @submit="handleSearch" class="flex-1">
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

            <div class="flex flex-wrap items-center gap-3">
                <!-- Kecamatan Select -->
                <select
                    v-model="selectedKecamatan"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
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
                    class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none disabled:bg-gray-50 disabled:text-gray-400"
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
                    class="rounded-lg bg-gray-100 px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-200"
                >
                    Reset
                </button>
            </div>
        </div>

        <!-- Table -->
        <div
            class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm"
        >
            <div class="overflow-x-auto">
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
                            <th class="px-4 py-3">Tanggal Input</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr
                            v-for="(p, i) in props.pemilihs.data"
                            :key="p.id"
                            class="hover:bg-gray-50"
                        >
                            <td class="px-4 py-3 text-gray-400">
                                {{
                                    (props.pemilihs.current_page - 1) *
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
                            <td class="px-4 py-3 text-xs text-gray-400">
                                {{ p.created_at }}
                            </td>
                        </tr>
                        <tr v-if="!props.pemilihs.data.length">
                            <td
                                colspan="9"
                                class="px-4 py-12 text-center text-sm text-gray-400"
                            >
                                Tidak ada data pemilih ditemukan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="props.pemilihs.last_page > 1"
                class="flex items-center justify-center gap-3 border-t border-gray-100 py-4"
            >
                <Link
                    v-if="props.pemilihs.prev_page_url"
                    :href="props.pemilihs.prev_page_url"
                    class="rounded-lg bg-gray-100 px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-200"
                    >← Sebelumnya</Link
                >
                <span class="text-sm text-gray-500">
                    Halaman {{ props.pemilihs.current_page }} /
                    {{ props.pemilihs.last_page }}
                </span>
                <Link
                    v-if="props.pemilihs.next_page_url"
                    :href="props.pemilihs.next_page_url"
                    class="rounded-lg bg-gray-100 px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-200"
                    >Selanjutnya →</Link
                >
            </div>
        </div>
    </div>
</template>
