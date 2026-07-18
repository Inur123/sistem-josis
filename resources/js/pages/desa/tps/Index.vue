<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import { Plus, Pencil, Trash2, MapPin } from '@lucide/vue';
import { ref, computed } from 'vue';
import PaginationBar from '@/components/PaginationBar.vue';
import desaRoutes from '@/routes/desa';

interface Tps {
    id: string;
    nama: string;
    created_at: string;
}

const props = defineProps<{
    tpsList: Tps[];
    totalTps: number;
    desa: string;
}>();

const page = usePage();
const user = page.props.auth.user as any;

// Realtime
if (typeof window !== 'undefined' && user) {
    useEcho(`desa.tps.${user.desa_id}`, 'TpsChanged', () => {
        router.reload();
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

// Form tambah
const formNama = ref('');
const formError = ref('');
const isAdding = ref(false);

function handleAdd() {
    if (!formNama.value.trim()) {
        formError.value = 'Nama TPS wajib diisi.';

        return;
    }

    isAdding.value = true;
    router.post(
        desaRoutes.tps.store.url(),
        { nama: formNama.value.trim() },
        {
            onSuccess: () => {
                formNama.value = '';
                formError.value = '';
            },
            onError: (errors) => {
                formError.value = errors.nama ?? 'Terjadi kesalahan.';
            },
            onFinish: () => {
                isAdding.value = false;
            },
        },
    );
}

// Modal Edit
const editModal = ref(false);
const editTps = ref<Tps | null>(null);
const editNama = ref('');
const editError = ref('');
const isSavingEdit = ref(false);

function openEdit(tps: Tps) {
    editTps.value = tps;
    editNama.value = tps.nama;
    editError.value = '';
    editModal.value = true;
}

function closeEdit() {
    editModal.value = false;
    editTps.value = null;
}

function handleEdit() {
    if (!editTps.value) {
        return;
    }

    if (!editNama.value.trim()) {
        editError.value = 'Nama TPS wajib diisi.';

        return;
    }

    isSavingEdit.value = true;
    router.put(
        desaRoutes.tps.update.url({ tp: editTps.value.id }),
        { nama: editNama.value.trim() },
        {
            onSuccess: () => {
                closeEdit();
            },
            onError: (errors) => {
                editError.value = errors.nama ?? 'Terjadi kesalahan.';
            },
            onFinish: () => {
                isSavingEdit.value = false;
            },
        },
    );
}

// Modal Hapus
const deleteModal = ref(false);
const deleteTps = ref<Tps | null>(null);
const isDeleting = ref(false);

function openDelete(tps: Tps) {
    deleteTps.value = tps;
    deleteModal.value = true;
}

function closeDelete() {
    deleteModal.value = false;
    deleteTps.value = null;
}

function handleDelete() {
    if (!deleteTps.value) {
        return;
    }

    isDeleting.value = true;
    router.delete(
        desaRoutes.tps.destroy.url({ tp: deleteTps.value.id }),
        {
            onSuccess: () => {
                closeDelete();
            },
            onFinish: () => {
                isDeleting.value = false;
            },
        },
    );
}

defineOptions({ layout: { breadcrumbs: [{ title: 'Data TPS', href: desaRoutes.tps.index.url() }] } });
</script>

<template>

    <Head title="Data TPS" />

    <div class="flex flex-col gap-6 p-6">
        <!-- Header -->
        <div>
            <h1 class="text-xl font-bold text-gray-900">Data TPS</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ desa }}</p>
        </div>

        <!-- Layout 2 Kolom -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Kolom Kiri: Card Total TPS -->
            <div class="lg:col-span-1">
                <div
                    class="rounded-2xl border border-blue-200 bg-linear-to-br from-blue-400 to-blue-500 p-6 shadow-md text-white">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-sm font-semibold uppercase tracking-wide opacity-90">Total TPS</span>
                        <div class="rounded-xl bg-white/20 p-2">
                            <MapPin class="h-5 w-5" />
                        </div>
                    </div>
                    <p class="text-5xl font-black">{{ totalTps }}</p>
                    <p class="text-sm mt-2 opacity-80">TPS terdaftar di {{ desa }}</p>
                </div>

                <!-- Form Tambah TPS -->
                <div class="mt-4 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Tambah TPS Baru</h3>
                    <div class="flex flex-col gap-2">
                        <input v-model="formNama" type="text" placeholder="Nama TPS (cth: TPS 001)" maxlength="100"
                            class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100"
                            @keyup.enter="handleAdd" />
                        <p v-if="formError" class="text-xs text-red-500">{{ formError }}</p>
                        <button @click="handleAdd" :disabled="isAdding"
                            class="flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:opacity-50">
                            <Plus class="h-4 w-4" />
                            {{ isAdding ? 'Menambahkan...' : 'Tambah TPS' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Tabel TPS -->
            <div class="lg:col-span-2">
                <div class="rounded-2xl border border-gray-100 bg-white shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-700">Daftar TPS</h3>
                        <span class="text-xs text-gray-400">{{ tpsList.length }} TPS</span>
                    </div>

                    <!-- Empty state -->
                    <div v-if="tpsList.length === 0"
                        class="flex flex-col items-center justify-center py-16 text-center">
                        <MapPin class="h-10 w-10 text-gray-200 mb-3" />
                        <p class="text-sm font-medium text-gray-400">Belum ada TPS</p>
                        <p class="text-xs text-gray-300 mt-1">Tambahkan TPS menggunakan form di sebelah kiri</p>
                    </div>

                    <!-- Tabel (scrollable on mobile) -->
                    <div v-else class="overflow-x-auto">
                        <table class="w-full min-w-[480px] text-sm">
                            <thead>
                                <tr class="border-b border-gray-100 bg-gray-50/60">
                                    <th
                                        class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">
                                        #</th>
                                    <th
                                        class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">
                                        Nama TPS</th>
                                    <th
                                        class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">
                                        Ditambahkan</th>
                                    <th
                                        class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-400">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(tps, idx) in pagedTps" :key="tps.id"
                                    class="border-b border-gray-50 transition hover:bg-blue-50/40">
                                    <td class="px-5 py-3 text-gray-400 font-mono text-xs">{{ (currentPage - 1) *
                                        PAGE_SIZE + idx + 1 }}</td>
                                    <td class="px-5 py-3 font-semibold text-gray-800">{{ tps.nama }}</td>
                                    <td class="px-5 py-3 text-gray-400 text-xs whitespace-nowrap">{{ tps.created_at }}
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button @click="openEdit(tps)"
                                                class="rounded-lg p-1.5 text-gray-400 hover:bg-blue-100 hover:text-blue-600 transition"
                                                title="Edit TPS">
                                                <Pencil class="h-4 w-4" />
                                            </button>
                                            <button @click="openDelete(tps)"
                                                class="rounded-lg p-1.5 text-gray-400 hover:bg-red-100 hover:text-red-500 transition"
                                                title="Hapus TPS">
                                                <Trash2 class="h-4 w-4" />
                                            </button>
                                        </div>
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

    <!-- Modal Edit -->
    <Teleport to="body">
        <div v-if="editModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-4">
            <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Edit Nama TPS</h2>
                <input v-model="editNama" type="text" maxlength="100"
                    class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100"
                    @keyup.enter="handleEdit" />
                <p v-if="editError" class="text-xs text-red-500 mt-1.5">{{ editError }}</p>
                <div class="flex gap-3 mt-5">
                    <button @click="closeEdit"
                        class="flex-1 rounded-xl border border-gray-200 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition">
                        Batal
                    </button>
                    <button @click="handleEdit" :disabled="isSavingEdit"
                        class="flex-1 rounded-xl bg-blue-600 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition disabled:opacity-50">
                        {{ isSavingEdit ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>

    <!-- Modal Hapus -->
    <Teleport to="body">
        <div v-if="deleteModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-4">
            <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="rounded-full bg-red-100 p-2">
                        <Trash2 class="h-5 w-5 text-red-500" />
                    </div>
                    <h2 class="text-lg font-bold text-gray-900">Hapus TPS</h2>
                </div>
                <p class="text-sm text-gray-600">
                    Anda yakin ingin menghapus <strong>{{ deleteTps?.nama }}</strong>?
                    Seluruh data suara dan C-Hasil yang terkait juga akan ikut terhapus.
                </p>
                <div class="flex gap-3 mt-6">
                    <button @click="closeDelete"
                        class="flex-1 rounded-xl border border-gray-200 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition">
                        Batal
                    </button>
                    <button @click="handleDelete" :disabled="isDeleting"
                        class="flex-1 rounded-xl bg-red-500 py-2.5 text-sm font-semibold text-white hover:bg-red-600 transition disabled:opacity-50">
                        {{ isDeleting ? 'Menghapus...' : 'Ya, Hapus' }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
