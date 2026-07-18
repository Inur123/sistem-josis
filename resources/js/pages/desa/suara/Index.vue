<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import { Save, Upload, Eye, FileImage } from '@lucide/vue';
import { ref, computed } from 'vue';
import PaginationBar from '@/components/PaginationBar.vue';
import desaRoutes from '@/routes/desa';

interface TpsSuara {
    id: string;
    nama: string;
    total_suara: number | null;
    has_c_hasil: boolean;
    c_hasil_url: string | null;
    data_suara_id: string | null;
    sudah_diisi: boolean;
}

const props = defineProps<{
    tpsList: TpsSuara[];
    desa: string;
}>();

const page = usePage();
const user = page.props.auth.user as any;

// Realtime
if (typeof window !== 'undefined' && user) {
    useEcho(`desa.tps.${user.desa_id}`, 'DataSuaraChanged', () => {
        router.reload();
    });
}

// State inputs per TPS
const suaraInputs = ref<Record<string, number>>({});
const isSaving = ref<Record<string, boolean>>({});
const uploadErrors = ref<Record<string, string>>({});
const isUploading = ref<Record<string, boolean>>({});

// Inisialisasi input suara jika sudah ada data
props.tpsList.forEach((tps) => {
    if (tps.total_suara !== null) {
        suaraInputs.value[tps.id] = tps.total_suara;
    }
});

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

function handleSaveSuara(tpsId: string) {
    const totalSuara = suaraInputs.value[tpsId];

    if (totalSuara === undefined || totalSuara === null || totalSuara < 0) {
        alert('Total suara wajib diisi dengan angka minimal 0.');

        return;
    }

    isSaving.value[tpsId] = true;
    router.post(
        desaRoutes.dataSuara.store.url(),
        {
            tps_id: tpsId,
            total_suara: totalSuara,
        },
        {
            onSuccess: () => {
                // Success handled by inertia session flash
            },
            onFinish: () => {
                isSaving.value[tpsId] = false;
            },
        }
    );
}

// Upload C-Hasil
const fileInputs = ref<Record<string, HTMLInputElement | null>>({});

function triggerUpload(tpsId: string) {
    const input = fileInputs.value[tpsId];

    if (input) {
        input.click();
    }
}

function handleFileChange(event: Event, tpsId: string) {
    const target = event.target as HTMLInputElement;

    if (target.files && target.files.length > 0) {
        const file = target.files[0];

        // Cek validasi sederhana client-side
        if (!['image/jpeg', 'image/png', 'image/jpg', 'image/webp'].includes(file.type)) {
            uploadErrors.value[tpsId] = 'Format harus JPEG, JPG, PNG, atau WebP.';

            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            uploadErrors.value[tpsId] = 'Ukuran file maksimal 5MB.';

            return;
        }

        uploadErrors.value[tpsId] = '';
        isUploading.value[tpsId] = true;

        // Lakukan kompresi gambar di browser agar ukuran filenya mengecil di bawah limit PHP (2MB)
        const reader = new FileReader();
        reader.onload = (e) => {
            const img = new Image();
            img.onload = () => {
                const MAX_WIDTH = 1200;
                const MAX_HEIGHT = 1200;
                let width = img.width;
                let height = img.height;

                if (width > height) {
                    if (width > MAX_WIDTH) {
                        height *= MAX_WIDTH / width;
                        width = MAX_WIDTH;
                    }
                } else {
                    if (height > MAX_HEIGHT) {
                        width *= MAX_HEIGHT / height;
                        height = MAX_HEIGHT;
                    }
                }

                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;

                const ctx = canvas.getContext('2d');

                if (ctx) {
                    ctx.drawImage(img, 0, 0, width, height);
                    canvas.toBlob(
                        (blob) => {
                            if (blob) {
                                const compressedFile = new File(
                                    [blob],
                                    file.name.replace(/\.[^/.]+$/, '') + '.webp',
                                    {
                                        type: 'image/webp',
                                        lastModified: Date.now(),
                                    },
                                );

                                const formData = new FormData();
                                formData.append('c_hasil', compressedFile);

                                router.post(
                                    desaRoutes.dataSuara.upload.url({ tps: tpsId }),
                                    formData,
                                    {
                                        forceFormData: true,
                                        onSuccess: () => {
                                            // Berhasil
                                        },
                                        onError: (err) => {
                                            uploadErrors.value[tpsId] = err.c_hasil ?? 'Gagal mengunggah file.';
                                        },
                                        onFinish: () => {
                                            isUploading.value[tpsId] = false;
                                            target.value = '';
                                        }
                                    }
                                );
                            } else {
                                uploadErrors.value[tpsId] = 'Gagal memproses gambar untuk diunggah.';
                                isUploading.value[tpsId] = false;
                                target.value = '';
                            }
                        },
                        'image/webp',
                        0.75,
                    );
                } else {
                    uploadErrors.value[tpsId] = 'Gagal memproses gambar.';
                    isUploading.value[tpsId] = false;
                    target.value = '';
                }
            };
            img.src = e.target?.result as string;
        };
        reader.readAsDataURL(file);
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
        breadcrumbs: [{ title: 'Data Suara', href: desaRoutes.dataSuara.index.url() }],
    },
});
</script>

<template>

    <Head title="Data Suara TPS" />

    <div class="flex flex-col gap-6 p-6">
        <!-- Header -->
        <div>
            <h1 class="text-xl font-bold text-gray-900">Data Suara TPS</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ desa }}</p>
        </div>

        <!-- Tabel TPS & Input Suara -->
        <div class="rounded-2xl border border-gray-100 bg-white shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="text-sm font-semibold text-gray-700">Daftar Suara per TPS</h3>
            </div>

            <!-- Empty state -->
            <div v-if="tpsList.length === 0" class="flex flex-col items-center justify-center py-16 text-center">
                <FileImage class="h-10 w-10 text-gray-200 mb-3" />
                <p class="text-sm font-medium text-gray-400">Belum ada TPS</p>
                <p class="text-xs text-gray-300 mt-1">Silakan tambahkan data TPS terlebih dahulu di menu "Data TPS".</p>
            </div>

            <!-- Tabel data suara (scrollable on mobile) -->
            <div v-else class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/40 text-gray-400 font-semibold">
                            <th class="px-5 py-3.5 text-left text-xs uppercase tracking-wide">#</th>
                            <th class="px-5 py-3.5 text-left text-xs uppercase tracking-wide">Nama TPS</th>
                            <th class="px-5 py-3.5 text-left text-xs uppercase tracking-wide w-48">Total Suara</th>
                            <th class="px-5 py-3.5 text-left text-xs uppercase tracking-wide w-64">C-Hasil (Foto
                                Formulir)</th>
                            <th class="px-5 py-3.5 text-center text-xs uppercase tracking-wide w-36">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(tps, idx) in pagedTps" :key="tps.id"
                            class="border-b border-gray-50 transition hover:bg-blue-50/20">
                            <td class="px-5 py-4 text-gray-400 font-mono text-xs">{{ (currentPage - 1) * PAGE_SIZE + idx
                                + 1 }}</td>
                            <td class="px-5 py-4 font-semibold text-gray-800 whitespace-nowrap">{{ tps.nama }}</td>

                            <!-- Input Total Suara -->
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <input v-model.number="suaraInputs[tps.id]" type="number" min="0" placeholder="0"
                                        class="w-24 rounded-xl border border-gray-200 px-3 py-1.5 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 no-spinners"
                                        @wheel="($event.target as HTMLInputElement).blur()" />
                                    <button @click="handleSaveSuara(tps.id)" :disabled="isSaving[tps.id]"
                                        class="rounded-xl bg-blue-600 hover:bg-blue-700 p-2 text-white transition disabled:opacity-50"
                                        title="Simpan Suara">
                                        <Save class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>

                            <!-- Upload & Preview C-Hasil -->
                            <td class="px-5 py-4">
                                <div class="flex flex-col gap-1.5">
                                    <div class="flex items-center gap-2.5">
                                        <!-- Input file tersembunyi -->
                                        <input type="file" accept=".jpg,.jpeg,.png" class="hidden"
                                            :ref="(el) => { fileInputs[tps.id] = el as HTMLInputElement }"
                                            @change="handleFileChange($event, tps.id)" />

                                        <!-- Tombol upload -->
                                        <button @click="triggerUpload(tps.id)" :disabled="isUploading[tps.id]"
                                            class="flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 px-3 py-1.5 text-xs font-semibold text-gray-600 transition disabled:opacity-50">
                                            <Upload class="h-3.5 w-3.5 text-gray-400" />
                                            {{ isUploading[tps.id] ? 'Mengunggah...' : 'Upload Foto' }}
                                        </button>

                                        <!-- Thumbnail Preview jika file ada -->
                                        <a v-if="tps.has_c_hasil && tps.c_hasil_url" :href="tps.c_hasil_url"
                                            target="_blank"
                                            class="relative h-9 w-9 block overflow-hidden rounded-lg border border-gray-200 bg-gray-50 group">
                                            <img :src="tps.c_hasil_url"
                                                class="h-full w-full object-cover transition duration-150 group-hover:scale-105"
                                                alt="C-Hasil Thumbnail" />
                                            <div
                                                class="absolute inset-0 flex items-center justify-center bg-black/20 opacity-0 group-hover:opacity-100 transition duration-150">
                                                <Eye class="h-3 w-3 text-white" />
                                            </div>
                                        </a>
                                    </div>

                                    <p v-if="uploadErrors[tps.id]" class="text-[10px] text-red-500 font-medium">
                                        {{ uploadErrors[tps.id] }}
                                    </p>
                                </div>
                            </td>

                            <!-- Status Badge -->
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

<style scoped>
/* Chrome, Safari, Edge, Opera */
.no-spinners::-webkit-outer-spin-button,
.no-spinners::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* Firefox */
.no-spinners[type=number] {
    -moz-appearance: textfield;
}
</style>
