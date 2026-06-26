<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import { Pencil, ArrowLeft, AlertCircle } from '@lucide/vue';
import { ref, onMounted } from 'vue';
import desaRoutes from '@/routes/desa';

interface PemilihData {
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
    status: 'belum_verifikasi' | 'terverifikasi' | 'ditolak';
    alasan_ditolak?: string | null;
}

interface PemilihChangedEvent {
    pemilihId: string;
    event: 'created' | 'updated' | 'verified' | 'deleted';
}

const props = defineProps<{
    desa: string;
    pemilih: PemilihData;
}>();

const backUrl = ref(desaRoutes.pemilih.index.url());

onMounted(() => {
    const pathname = window.location.pathname;
    const match = pathname.match(
        /^\/desa\/relawan\/([^\/]+)\/pemilih\/[^\/]+$/,
    );

    if (match) {
        const urlParams = new URLSearchParams(window.location.search);
        const fromPage = urlParams.get('from');

        if (fromPage === 'index') {
            backUrl.value = '/desa/relawan';
        } else {
            backUrl.value = `/desa/relawan/${match[1]}`;
        }

        return;
    }

    const urlParams = new URLSearchParams(window.location.search);
    const fromPage = urlParams.get('from');
    const relawanId = urlParams.get('relawan_id');

    if (fromPage === 'relawan') {
        backUrl.value = '/desa/relawan';
    } else if (fromPage === 'relawan_show' && relawanId) {
        backUrl.value = `/desa/relawan/${relawanId}`;
    }
});

const page = usePage();
const user = page.props.auth.user as any;

if (typeof window !== 'undefined' && user) {
    useEcho(
        `desa.pemilih.${user.desa_id}`,
        'PemilihChanged',
        (event: PemilihChangedEvent) => {
            if (event.pemilihId !== props.pemilih.id) {
                return;
            }

            if (event.event === 'deleted') {
                router.visit(backUrl.value);

                return;
            }

            router.reload({
                only: ['pemilih', 'desa'],
            });
        },
    );
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: desaRoutes.dashboard.url() },
            { title: 'Data Pemilih', href: desaRoutes.pemilih.index.url() },
            { title: 'Detail Pemilih', href: '#' },
        ],
    },
});
</script>

<template>
    <Head title="Detail Pemilih" />
    <div class="p-6">
        <div class="w-full">
            <!-- Navigation Actions -->
            <div class="mb-5 flex items-center justify-between">
                <Link
                    :href="backUrl"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Kembali
                </Link>

                <Link
                    :href="desaRoutes.pemilih.edit.url(props.pemilih.id)"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-black"
                >
                    <Pencil class="h-4 w-4" />
                    Edit Data
                </Link>
            </div>

            <!-- Detail Card -->
            <div
                class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm"
            >
                <!-- Card Header -->
                <div
                    class="flex flex-col gap-4 border-b border-gray-100 px-6 py-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">
                            Detail Informasi Pemilih
                        </h2>
                        <p class="mt-0.5 text-xs text-gray-500">
                            Desa {{ props.desa }}
                        </p>
                    </div>

                    <!-- Status Badge -->
                    <div>
                        <span
                            v-if="props.pemilih.status === 'terverifikasi'"
                            class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700 ring-1 ring-green-600/20 ring-inset"
                        >
                            <span
                                class="h-1.5 w-1.5 rounded-full bg-green-600"
                            />
                            Terverifikasi
                        </span>
                        <span
                            v-else-if="props.pemilih.status === 'ditolak'"
                            class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-700 ring-1 ring-red-600/20 ring-inset"
                        >
                            <span class="h-1.5 w-1.5 rounded-full bg-red-600" />
                            Ditolak
                        </span>
                        <span
                            v-else
                            class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-600/20 ring-inset"
                        >
                            <span
                                class="h-1.5 w-1.5 rounded-full bg-amber-600"
                            />
                            Belum Verifikasi
                        </span>
                    </div>
                </div>

                <!-- Rejection Alert -->
                <div
                    v-if="props.pemilih.status === 'ditolak'"
                    class="border-b border-red-100 bg-red-50/50 p-4"
                >
                    <div class="flex gap-2">
                        <AlertCircle class="h-5 w-5 shrink-0 text-red-600" />
                        <div>
                            <h4 class="text-sm font-semibold text-red-950">
                                Alasan Ditolak:
                            </h4>
                            <p
                                class="mt-1 text-sm leading-relaxed text-red-900"
                            >
                                {{ props.pemilih.alasan_ditolak || '-' }}
                            </p>
                            <p class="mt-2 text-xs text-red-700">
                                Silakan edit kembali data pemilih ini untuk
                                mengajukan verifikasi ulang.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Card Body (Grid: Left Data Form, Right KTP) -->
                <div class="grid grid-cols-1 gap-8 p-6 lg:grid-cols-12">
                    <!-- Left Column: Data Details Form (7 cols) -->
                    <div class="space-y-5 lg:col-span-7">
                        <!-- NIK -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-gray-700"
                                >NIK</label
                            >
                            <input
                                type="text"
                                readonly
                                :value="props.pemilih.nik"
                                class="w-full cursor-default rounded-lg border border-gray-200 bg-gray-50/50 px-3 py-2 font-mono text-sm tracking-wider text-gray-900 shadow-sm outline-none"
                            />
                        </div>

                        <!-- Nama Lengkap -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-gray-700"
                                >Nama Lengkap</label
                            >
                            <input
                                type="text"
                                readonly
                                :value="props.pemilih.nama"
                                class="w-full cursor-default rounded-lg border border-gray-200 bg-gray-50/50 px-3 py-2 text-sm text-gray-900 shadow-sm outline-none"
                            />
                        </div>

                        <!-- Jenis Kelamin -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-gray-700"
                                >Jenis Kelamin</label
                            >
                            <input
                                type="text"
                                readonly
                                :value="
                                    props.pemilih.jenis_kelamin === 'L'
                                        ? 'Laki-laki'
                                        : 'Perempuan'
                                "
                                class="w-full cursor-default rounded-lg border border-gray-200 bg-gray-50/50 px-3 py-2 text-sm text-gray-900 shadow-sm outline-none"
                            />
                        </div>

                        <!-- Alamat -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-gray-700"
                                >Alamat</label
                            >
                            <textarea
                                readonly
                                rows="3"
                                :value="props.pemilih.alamat"
                                class="w-full cursor-default resize-none rounded-lg border border-gray-200 bg-gray-50/50 px-3 py-2 text-sm text-gray-900 shadow-sm outline-none"
                            />
                        </div>

                        <!-- RT / RW -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-gray-700"
                                    >RT</label
                                >
                                <input
                                    type="text"
                                    readonly
                                    :value="props.pemilih.rt"
                                    class="w-full cursor-default rounded-lg border border-gray-200 bg-gray-50/50 px-3 py-2 text-sm text-gray-900 shadow-sm outline-none"
                                />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-gray-700"
                                    >RW</label
                                >
                                <input
                                    type="text"
                                    readonly
                                    :value="props.pemilih.rw"
                                    class="w-full cursor-default rounded-lg border border-gray-200 bg-gray-50/50 px-3 py-2 text-sm text-gray-900 shadow-sm outline-none"
                                />
                            </div>
                        </div>

                        <!-- Relawan Pendamping -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-gray-700"
                                >Relawan Pendamping</label
                            >
                            <input
                                type="text"
                                readonly
                                :value="props.pemilih.relawan ?? '-'"
                                class="w-full cursor-default rounded-lg border border-gray-200 bg-gray-50/50 px-3 py-2 text-sm text-gray-900 shadow-sm outline-none"
                            />
                        </div>

                        <!-- Tanggal Input -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-gray-700"
                                >Tanggal Ditambahkan</label
                            >
                            <input
                                type="text"
                                readonly
                                :value="props.pemilih.created_at"
                                class="w-full cursor-default rounded-lg border border-gray-200 bg-gray-50/50 px-3 py-2 text-sm text-gray-500 shadow-sm outline-none"
                            />
                        </div>
                    </div>

                    <!-- Right Column: Foto KTP (5 cols) -->
                    <div class="flex flex-col gap-1.5 lg:col-span-5">
                        <label class="text-sm font-medium text-gray-700">
                            Foto KTP
                        </label>

                        <div
                            v-if="props.pemilih.foto_ktp"
                            class="overflow-hidden rounded-lg border border-gray-200 bg-white p-1.5 shadow-sm"
                        >
                            <img
                                :src="props.pemilih.foto_ktp"
                                alt="Foto KTP"
                                class="h-auto max-h-[400px] w-full rounded object-contain"
                            />
                        </div>
                        <div
                            v-else
                            class="flex min-h-[250px] flex-col items-center justify-center rounded-lg border border-gray-200 bg-gray-50/50 p-6 text-gray-400 shadow-sm"
                        >
                            <svg
                                class="mb-3 h-12 w-12 text-gray-300"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                />
                            </svg>
                            <p class="text-sm font-medium">
                                Foto KTP belum diunggah
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
