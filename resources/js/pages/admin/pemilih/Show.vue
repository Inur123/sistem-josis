<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
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
}

const props = defineProps<{
    desa: string;
    pemilih: PemilihData;
}>();

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
                    :href="adminRoutes.pemilih.index.url()"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Kembali
                </Link>
            </div>

            <!-- Detail Card -->
            <div
                class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm"
            >
                <!-- Card Header -->
                <div class="border-b border-gray-100 px-6 py-4">
                    <h2 class="text-base font-semibold text-gray-900">
                        Detail Informasi Pemilih
                    </h2>
                    <p class="mt-0.5 text-xs text-gray-500">
                        Desa {{ props.desa }}
                    </p>
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
</template>
