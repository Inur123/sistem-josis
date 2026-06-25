<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import { ArrowLeft, CheckCircle, XCircle, AlertCircle, Loader2 } from '@lucide/vue';
import { ref, onMounted } from 'vue';
import adminRoutes from '@/routes/admin';

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
    verified_by_nama?: string | null;
    verified_at?: string | null;
}

interface PemilihChangedEvent {
    pemilihId: string;
    event: 'created' | 'updated' | 'verified' | 'deleted';
}

const props = defineProps<{
    desa: string;
    pemilih: PemilihData;
}>();

const isVerifying = ref(false);
const showRejectModal = ref(false);
const rejectReason = ref('');
const rejectError = ref('');

const backUrl = ref(adminRoutes.pemilih.index.url());

onMounted(() => {
    const pathname = window.location.pathname;
    const adminMatch = pathname.match(/^\/admin\/relawan\/([^\/]+)\/pemilih\/[^\/]+$/);

    if (adminMatch) {
        const urlParams = new URLSearchParams(window.location.search);
        const fromPage = urlParams.get('from');

        if (fromPage === 'index') {
            const queryParams = new URLSearchParams();
            const kec = urlParams.get('kecamatan_id');
            const des = urlParams.get('desa_id');

            if (kec) {
queryParams.set('kecamatan_id', kec);
}

            if (des) {
queryParams.set('desa_id', des);
}

            const qStr = queryParams.toString();
            backUrl.value = '/admin/relawan' + (qStr ? '?' + qStr : '');
        } else {
            backUrl.value = `/admin/relawan/${adminMatch[1]}`;
        }

        return;
    }

    const urlParams = new URLSearchParams(window.location.search);
    const fromPage = urlParams.get('from');
    const relawanId = urlParams.get('relawan_id');

    if (fromPage === 'relawan') {
        backUrl.value = '/admin/relawan';
    } else if (fromPage === 'relawan_show' && relawanId) {
        backUrl.value = `/admin/relawan/${relawanId}`;
    }
});


function verifyVoter(status: 'terverifikasi' | 'ditolak') {
    if (status === 'ditolak' && !rejectReason.value.trim()) {
        rejectError.value = 'Alasan penolakan harus diisi.';

        return;
    }

    isVerifying.value = true;
    rejectError.value = '';

    router.post(
        adminRoutes.pemilih.verify.url(props.pemilih.id),
        {
            status: status,
            alasan_ditolak: status === 'ditolak' ? rejectReason.value : null
        },
        {
            onSuccess: () => {
                showRejectModal.value = false;
                rejectReason.value = '';
            },
            onFinish: () => {
                isVerifying.value = false;
            }
        }
    );
}

if (typeof window !== 'undefined') {
    useEcho('admin.pemilih', 'PemilihChanged', (event: PemilihChangedEvent) => {
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
    });
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: adminRoutes.dashboard.url() },
            { title: 'Data Pemilih', href: adminRoutes.pemilih.index.url() },
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

                <!-- Actions for Verification -->
                <div v-if="props.pemilih.status !== 'terverifikasi'" class="flex items-center gap-2">
                    <button
                        @click="showRejectModal = true"
                        :disabled="isVerifying"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-3.5 py-2 text-sm font-semibold text-red-700 shadow-sm transition hover:bg-red-100 disabled:opacity-50"
                    >
                        <XCircle class="h-4 w-4" />
                        Tolak
                    </button>
                    <button
                        @click="verifyVoter('terverifikasi')"
                        :disabled="isVerifying"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-green-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700 disabled:opacity-50"
                    >
                        <Loader2 v-if="isVerifying" class="h-4 w-4 animate-spin" />
                        <CheckCircle v-else class="h-4 w-4" />
                        Setujui
                    </button>
                </div>
            </div>

            <!-- Detail Card -->
            <div
                class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm"
            >
                <!-- Card Header -->
                <div class="border-b border-gray-100 px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
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
                            <span class="h-1.5 w-1.5 rounded-full bg-green-600" />
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
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-600" />
                            Belum Verifikasi
                        </span>
                    </div>
                </div>

                <!-- Rejection Alert -->
                <div v-if="props.pemilih.status === 'ditolak'" class="border-b border-red-100 bg-red-50/50 p-4">
                    <div class="flex gap-2">
                        <AlertCircle class="h-5 w-5 text-red-600 shrink-0" />
                        <div>
                            <h4 class="text-sm font-semibold text-red-950">Alasan Ditolak:</h4>
                            <p class="mt-1 text-sm text-red-900 leading-relaxed">{{ props.pemilih.alasan_ditolak || '-' }}</p>
                            <p class="mt-2 text-xs text-red-700">Ditolak oleh {{ props.pemilih.verified_by_nama }} pada {{ props.pemilih.verified_at }}</p>
                        </div>
                    </div>
                </div>

                <!-- Verification Info -->
                <div v-if="props.pemilih.status === 'terverifikasi' && props.pemilih.verified_by_nama" class="border-b border-green-100 bg-green-50/50 p-4">
                    <div class="flex gap-2">
                        <CheckCircle class="h-5 w-5 text-green-600 shrink-0" />
                        <div>
                            <p class="text-sm text-green-900">Diverifikasi oleh <span class="font-semibold">{{ props.pemilih.verified_by_nama }}</span> pada {{ props.pemilih.verified_at }}</p>
                        </div>
                    </div>
                </div>

                <!-- Card Body (Grid: Left Data Form, Right KTP) -->
                <div class="grid grid-cols-1 gap-8 p-6 lg:grid-cols-12">
                    <!-- Left Column: Data Details Form (7 cols) -->
                    <div class="space-y-5 lg:col-span-7">
                        <!-- NIK -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-gray-700">NIK</label>
                            <input
                                type="text"
                                readonly
                                :value="props.pemilih.nik"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-3 py-2 text-sm text-gray-900 font-mono tracking-wider outline-none cursor-default shadow-sm"
                            />
                        </div>

                        <!-- Nama Lengkap -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-gray-700">Nama Lengkap</label>
                            <input
                                type="text"
                                readonly
                                :value="props.pemilih.nama"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-3 py-2 text-sm text-gray-900 outline-none cursor-default shadow-sm"
                            />
                        </div>

                        <!-- Jenis Kelamin -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-gray-700">Jenis Kelamin</label>
                            <input
                                type="text"
                                readonly
                                :value="props.pemilih.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan'"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-3 py-2 text-sm text-gray-900 outline-none cursor-default shadow-sm"
                            />
                        </div>

                        <!-- Alamat -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-gray-700">Alamat</label>
                            <textarea
                                readonly
                                rows="3"
                                :value="props.pemilih.alamat"
                                class="w-full resize-none rounded-lg border border-gray-200 bg-gray-50/50 px-3 py-2 text-sm text-gray-900 outline-none cursor-default shadow-sm"
                            />
                        </div>

                        <!-- RT / RW -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-gray-700">RT</label>
                                <input
                                    type="text"
                                    readonly
                                    :value="props.pemilih.rt"
                                    class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-3 py-2 text-sm text-gray-900 outline-none cursor-default shadow-sm"
                                />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-gray-700">RW</label>
                                <input
                                    type="text"
                                    readonly
                                    :value="props.pemilih.rw"
                                    class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-3 py-2 text-sm text-gray-900 outline-none cursor-default shadow-sm"
                                />
                            </div>
                        </div>

                        <!-- Relawan Pendamping -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-gray-700">Relawan Pendamping</label>
                            <input
                                type="text"
                                readonly
                                :value="props.pemilih.relawan ?? '-'"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-3 py-2 text-sm text-gray-900 outline-none cursor-default shadow-sm"
                            />
                        </div>

                        <!-- Tanggal Input -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-gray-700">Tanggal Ditambahkan</label>
                            <input
                                type="text"
                                readonly
                                :value="props.pemilih.created_at"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50/50 px-3 py-2 text-sm text-gray-500 outline-none cursor-default shadow-sm"
                            />
                        </div>
                    </div>

                    <!-- Right Column: Foto KTP (5 cols) -->
                    <div class="lg:col-span-5 flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-gray-700">
                            Foto KTP
                        </label>

                        <div v-if="props.pemilih.foto_ktp" class="overflow-hidden rounded-lg border border-gray-200 bg-white p-1.5 shadow-sm">
                            <img
                                :src="props.pemilih.foto_ktp"
                                alt="Foto KTP"
                                class="w-full h-auto max-h-[400px] rounded object-contain"
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

    <!-- Reject Modal -->
    <div
        v-if="showRejectModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
    >
        <div class="w-full max-w-md bg-white rounded-xl border border-gray-100 p-6 shadow-xl animate-in fade-in duration-200">
            <h3 class="text-lg font-bold text-gray-900">Alasan Menolak Data Pemilih</h3>
            <p class="mt-1.5 text-xs text-gray-500">Berikan penjelasan mengapa data pemilih ini ditolak.</p>

            <div class="mt-4 flex flex-col gap-1.5">
                <textarea
                    v-model="rejectReason"
                    placeholder="Contoh: Foto KTP buram / tidak terbaca, NIK tidak sesuai KTP, dll."
                    rows="4"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-red-500 focus:outline-none"
                    @input="rejectError = ''"
                />
                <span v-if="rejectError" class="text-xs font-medium text-red-600">{{ rejectError }}</span>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button
                    @click="showRejectModal = false; rejectReason = ''; rejectError = ''"
                    class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    :disabled="isVerifying"
                >
                    Batal
                </button>
                <button
                    @click="verifyVoter('ditolak')"
                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                    :disabled="isVerifying"
                >
                    <span v-if="isVerifying" class="inline-flex items-center gap-1">
                        <Loader2 class="h-3 w-3 animate-spin" />
                        Menyimpan...
                    </span>
                    <span v-else>Ya, Tolak</span>
                </button>
            </div>
        </div>
    </div>
</template>
