<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import { Loader2, Eye, CheckCircle, XCircle, Clock, ArrowLeft } from '@lucide/vue';
import { ref, reactive } from 'vue';
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
    created_at: string;
    status: string;
    alasan_ditolak?: string | null;
}

interface Relawan {
    id: string;
    nama: string;
    nik: string;
    no_hp: string;
    alamat: string;
    desa: string;
    pemilihs_count: number;
    summary: {
        total: number;
        l: number;
        p: number;
        belum_verifikasi: number;
        terverifikasi: number;
        ditolak: number;
    };
    pemilihs: Pemilih[];
}

interface TeamChangedEvent {
    memberId: string;
    event: 'created' | 'updated' | 'deleted';
    kecamatanId: string | null;
}

const props = defineProps<{
    relawan: Relawan;
}>();

const ITEMS_PER_PAGE = 10;
const currentPage = ref(1);
const pageCache = reactive<Record<number, Pemilih[]>>({
    1: props.relawan.pemilihs
});
const isLoading = ref(false);

function clearPemilihCache() {
    Object.keys(pageCache).forEach((key) => delete pageCache[Number(key)]);
    currentPage.value = 1;
}

function reloadRelawan() {
    clearPemilihCache();
    router.reload();
}

const getPemilihPage = (): Pemilih[] => {
    return pageCache[currentPage.value] ?? [];
};

const getTotalPages = (): number => {
    return Math.ceil(props.relawan.pemilihs_count / ITEMS_PER_PAGE);
};

const goToPage = async (page: number) => {
    const total = getTotalPages();

    if (page < 1 || page > total) {
return;
}

    if (pageCache[page]) {
        currentPage.value = page;

        return;
    }

    isLoading.value = true;

    try {
        const res = await fetch(`/kecamatan/relawan/${props.relawan.id}/pemilihs?page=${page}`, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!res.ok) {
throw new Error('Gagal memuat data');
}

        const json = await res.json();
        pageCache[page] = json.data as Pemilih[];
        currentPage.value = page;
    } catch (e) {
        console.error(e);
    } finally {
        isLoading.value = false;
    }
};

const page = usePage();
const user = page.props.auth.user as any;

if (typeof window !== 'undefined' && user) {
    useEcho(`kecamatan.pemilih.${user.kecamatan_id}`, 'PemilihChanged', reloadRelawan);
    useEcho(`kecamatan.team.${user.kecamatan_id}`, 'TeamChanged', (event: TeamChangedEvent) => {
        if (event.memberId !== props.relawan.id) {
            return;
        }

        if (event.event === 'deleted' || event.kecamatanId !== user.kecamatan_id) {
            router.visit(kecamatanRoutes.relawan.index.url());

            return;
        }

        reloadRelawan();
    });
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: kecamatanRoutes.dashboard.url() },
            { title: 'Data Relawan', href: kecamatanRoutes.relawan.index.url() },
            { title: 'Detail Relawan', href: '#' },
        ],
    },
});
</script>

<template>
    <Head :title="`Detail Relawan - ${props.relawan.nama}`" />
    <div class="p-6">
        <div class="w-full flex flex-col gap-6">
            <!-- Navigation Actions -->
            <div class="flex items-center justify-between">
                <Link
                    :href="kecamatanRoutes.relawan.index.url()"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Kembali
                </Link>
            </div>

            <!-- Profile and Stats Card -->
            <div class="overflow-hidden rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="flex flex-col justify-between gap-6 md:flex-row md:items-start">
                    <!-- Relawan Info -->
                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-xl font-bold text-gray-900">
                                {{ props.relawan.nama }}
                            </h2>
                            <span class="inline-flex items-center rounded border border-green-100 bg-green-50 px-2 py-0.5 text-xs text-green-700">
                                Desa {{ props.relawan.desa }}
                            </span>
                        </div>
                        <div class="mt-3 grid grid-cols-1 gap-2 text-sm text-gray-600 sm:grid-cols-2 max-w-2xl">
                            <div><strong>NIK:</strong> <span class="font-mono">{{ props.relawan.nik || '-' }}</span></div>
                            <div><strong>No. HP:</strong> {{ props.relawan.no_hp || '-' }}</div>
                            <div class="sm:col-span-2"><strong>Alamat:</strong> {{ props.relawan.alamat || '-' }}</div>
                        </div>
                    </div>

                    <!-- Row Aligned Stats Grid -->
                    <div class="rounded-xl bg-gray-50/50 p-4 border border-gray-100/50 min-w-[280px]">
                        <div class="grid grid-cols-[125px_1fr] items-center gap-y-2 text-xs">
                            <span class="font-semibold text-gray-500">Pemilih Didampingi:</span>
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="inline-flex min-w-[28px] items-center justify-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-bold text-blue-700" title="Total Pemilih">
                                    Total: {{ props.relawan.pemilihs_count }}
                                </span>
                                <span class="inline-flex min-w-[28px] items-center justify-center rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-bold text-sky-700" title="Laki-laki">
                                    L: {{ props.relawan.summary.l }}
                                </span>
                                <span class="inline-flex min-w-[28px] items-center justify-center rounded-full bg-pink-100 px-2.5 py-0.5 text-xs font-bold text-pink-700" title="Perempuan">
                                    P: {{ props.relawan.summary.p }}
                                </span>
                            </div>

                            <span class="font-semibold text-gray-500">Status:</span>
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-amber-600" title="Belum Verifikasi">
                                    <Clock class="h-4 w-4 text-amber-500" />
                                    <span>{{ props.relawan.summary.belum_verifikasi }}</span>
                                </span>
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-green-600" title="Terverifikasi">
                                    <CheckCircle class="h-4 w-4 text-green-500" />
                                    <span>{{ props.relawan.summary.terverifikasi }}</span>
                                </span>
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-red-600" title="Ditolak">
                                    <XCircle class="h-4 w-4 text-red-500" />
                                    <span>{{ props.relawan.summary.ditolak }}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="overflow-hidden rounded-xl border border-gray-150 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Daftar Pemilih Didampingi</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Daftar pemilih yang dimasukkan oleh relawan ini</p>
                </div>

                <div class="relative overflow-x-auto">
                    <!-- Loading overlay -->
                    <div v-if="isLoading" class="absolute inset-0 z-10 flex items-center justify-center bg-white/70">
                        <Loader2 class="h-6 w-6 animate-spin text-blue-600" />
                    </div>

                    <table class="w-full text-left text-sm text-gray-500">
                        <thead class="bg-gray-50 font-semibold text-gray-700">
                            <tr>
                                <th class="w-[60px] px-4 py-3 text-center">No</th>
                                <th class="w-[140px] px-4 py-3">NIK</th>
                                <th class="px-4 py-3">Nama</th>
                                <th class="w-[60px] px-4 py-3">JK</th>
                                <th class="px-4 py-3">Alamat</th>
                                <th class="w-[80px] px-4 py-3 text-center">RT/RW</th>
                                <th class="w-[120px] px-4 py-3">Tanggal Input</th>
                                <th class="w-[80px] px-4 py-3 text-center">Status</th>
                                <th class="w-[80px] px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr
                                v-for="(p, pi) in getPemilihPage()"
                                :key="p.id"
                                class="hover:bg-gray-50/80 cursor-pointer transition-colors"
                                @click="router.visit(kecamatanRoutes.relawan.pemilih.show.url({ relawan: props.relawan.id, pemilih: p.id }))"
                            >
                                <td class="px-4 py-3 text-center text-gray-400">
                                    {{ ((currentPage - 1) * ITEMS_PER_PAGE) + pi + 1 }}
                                </td>
                                <td class="px-4 py-3 font-mono text-xs text-gray-600">
                                    {{ p.nik }}
                                </td>
                                <td class="px-4 py-3 font-medium text-gray-900">
                                    {{ p.nama }}
                                </td>
                                <td class="px-4 py-3">
                                    <span :class="[
                                        'inline-flex h-5 w-5 items-center justify-center rounded-full text-xs font-bold',
                                        p.jenis_kelamin === 'L' ? 'bg-blue-100 text-blue-700' : 'bg-pink-100 text-pink-700'
                                    ]">
                                        {{ p.jenis_kelamin }}
                                    </span>
                                </td>
                                <td class="max-w-[300px] truncate px-4 py-3 text-gray-600">
                                    {{ p.alamat }}
                                </td>
                                <td class="px-4 py-3 text-center text-gray-600">
                                    {{ p.rt }}/{{ p.rw }}
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-400">
                                    {{ p.created_at }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex justify-center" @click.stop>
                                        <CheckCircle v-if="p.status === 'terverifikasi'" class="h-4.5 w-4.5 text-green-600" title="Terverifikasi" />
                                        <XCircle v-else-if="p.status === 'ditolak'" class="h-4.5 w-4.5 text-red-600" :title="p.alasan_ditolak ? 'Ditolak: ' + p.alasan_ditolak : 'Ditolak'" />
                                        <Clock v-else class="h-4.5 w-4.5 text-amber-500" title="Belum Verifikasi" />
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center">
                                        <Link
                                            :href="kecamatanRoutes.relawan.pemilih.show.url({ relawan: props.relawan.id, pemilih: p.id })"
                                            class="inline-flex h-7 w-7 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 shadow-sm transition hover:bg-gray-50 hover:text-gray-900"
                                            title="Detail"
                                        >
                                            <Eye class="h-3.5 w-3.5" />
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!getPemilihPage().length && !isLoading">
                                <td colspan="9" class="px-4 py-8 text-center text-sm text-gray-400">
                                    Belum ada data pemilih pendamping untuk relawan ini.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <PaginationBar
                    v-if="getTotalPages() > 1"
                    :current-page="currentPage"
                    :total-pages="getTotalPages()"
                    @update:currentPage="goToPage"
                    class="border-t border-gray-100 px-6 py-4"
                />
            </div>
        </div>
    </div>
</template>
