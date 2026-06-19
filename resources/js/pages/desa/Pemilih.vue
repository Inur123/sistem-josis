<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import desaRoutes from '@/routes/desa';

interface Pemilih {
    id: string;
    nik: string;
    nama: string;
    jenis_kelamin: string;
    alamat: string;
    rt: string;
    rw: string;
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

const props = defineProps<{
    pemilihs: PaginatedPemilih;
    desa: string;
}>();

const confirmDelete = ref<string | null>(null);

function hapus(id: string) {
    if (confirmDelete.value !== id) {
        confirmDelete.value = id;

        return;
    }

    router.delete(desaRoutes.pemilih.destroy.url(id), {
        onSuccess: () => {
            confirmDelete.value = null;
        },
    });
}

function cancelDelete() {
    confirmDelete.value = null;
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
        <div class="flex items-start justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900">
                    Data Pemilih — {{ props.desa }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Total
                    {{ props.pemilihs.total.toLocaleString('id-ID') }} pemilih
                    terdaftar
                </p>
            </div>
            <Link
                :href="desaRoutes.pemilih.create.url()"
                class="flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700"
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
                            <th class="px-4 py-3">Alamat</th>
                            <th class="px-4 py-3 text-center">RT/RW</th>
                            <th class="px-4 py-3">Tanggal Input</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
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
                            <td class="px-4 py-3">
                                <div
                                    class="flex items-center justify-center gap-2"
                                >
                                    <Link
                                        :href="
                                            desaRoutes.pemilih.edit.url(p.id)
                                        "
                                        class="rounded-md bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600 hover:bg-gray-200"
                                        >Edit</Link
                                    >
                                    <button
                                        @click="hapus(p.id)"
                                        :class="[
                                            'rounded-md px-2.5 py-1 text-xs font-medium transition-colors',
                                            confirmDelete === p.id
                                                ? 'bg-red-500 text-white'
                                                : 'bg-red-50 text-red-600 hover:bg-red-100',
                                        ]"
                                    >
                                        {{
                                            confirmDelete === p.id
                                                ? 'Yakin?'
                                                : 'Hapus'
                                        }}
                                    </button>
                                    <button
                                        v-if="confirmDelete === p.id"
                                        @click="cancelDelete"
                                        class="rounded-md bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-500"
                                    >
                                        Batal
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!props.pemilihs.data.length">
                            <td
                                colspan="8"
                                class="px-4 py-12 text-center text-sm text-gray-400"
                            >
                                Belum ada data pemilih.
                                <Link
                                    :href="desaRoutes.pemilih.create.url()"
                                    class="text-blue-600 underline"
                                    >Tambah sekarang →</Link
                                >
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
