<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import desaRoutes from '@/routes/desa';

interface RelawanData {
    id: string;
    nama: string;
}

interface PemilihData {
    id?: string;
    nik?: string;
    nama?: string;
    jenis_kelamin?: string;
    alamat?: string;
    rt?: string;
    rw?: string;
    relawan_id?: string;
    foto_ktp?: string;
}

const props = defineProps<{
    mode: 'create' | 'edit';
    desa: string;
    pemilih?: PemilihData;
    relawans: RelawanData[];
}>();

const isEdit = computed(() => props.mode === 'edit');

const rtRwOptions = computed(() => {
    return Array.from({ length: 50 }, (_, i) => {
        const num = i + 1;

        return num.toString().padStart(3, '0');
    });
});

const form = useForm({
    _method: isEdit.value ? 'PUT' : 'POST',
    nik: props.pemilih?.nik ?? '',
    nama: props.pemilih?.nama ?? '',
    jenis_kelamin: props.pemilih?.jenis_kelamin ?? 'L',
    alamat: props.pemilih?.alamat ?? '',
    rt: props.pemilih?.rt ?? '',
    rw: props.pemilih?.rw ?? '',
    relawan_id: props.pemilih?.relawan_id ?? '',
    foto_ktp: null as File | null,
});

const imagePreview = ref<string | null>(props.pemilih?.foto_ktp ?? null);
const compressError = ref<string | null>(null);

function handleFileChange(event: Event) {
    const target = event.target as HTMLInputElement;

    if (!target.files || target.files.length === 0) {
        return;
    }

    const file = target.files[0];
    compressError.value = null;

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
                            form.foto_ktp = compressedFile;
                            imagePreview.value =
                                URL.createObjectURL(compressedFile);
                        } else {
                            compressError.value = 'Gagal mengompresi gambar.';
                        }
                    },
                    'image/webp',
                    0.75,
                );
            } else {
                compressError.value = 'Gagal memproses gambar.';
            }
        };
        img.src = e.target?.result as string;
    };
    reader.readAsDataURL(file);
}

function submit() {
    if (isEdit.value && props.pemilih?.id) {
        // Gunakan POST dengan spoofing _method agar file terkirim dengan baik di PHP
        form.post(desaRoutes.pemilih.update.url(props.pemilih.id));
    } else {
        form.post(desaRoutes.pemilih.store.url());
    }
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: desaRoutes.dashboard.url() },
            { title: 'Data Pemilih', href: desaRoutes.pemilih.index.url() },
            { title: 'Form Pemilih', href: '#' },
        ],
    },
});
</script>

<template>
    <Head :title="isEdit ? 'Edit Pemilih' : 'Tambah Pemilih'" />
    <div class="p-6">
        <div class="w-full">
            <div
                class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm"
            >
                <!-- Card Header -->
                <div
                    class="flex items-start justify-between border-b border-gray-100 px-6 py-4"
                >
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">
                            {{
                                isEdit
                                    ? 'Edit Data Pemilih'
                                    : 'Tambah Pemilih Baru'
                            }}
                        </h2>
                        <p class="mt-0.5 text-xs text-gray-500">
                            Desa {{ props.desa }}
                        </p>
                    </div>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="flex flex-col gap-5 p-6">
                    <!-- NIK -->
                    <div class="flex flex-col gap-1.5">
                        <label
                            for="nik"
                            class="text-sm font-medium text-gray-700"
                        >
                            NIK <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="nik"
                            v-model="form.nik"
                            @input="form.nik = form.nik.replace(/\D/g, '')"
                            type="text"
                            inputmode="numeric"
                            pattern="[0-9]{16}"
                            maxlength="16"
                            placeholder="16 digit NIK"
                            required
                            :class="[
                                'w-full rounded-lg border px-3 py-2 text-sm text-gray-900 transition outline-none',
                                form.errors.nik
                                    ? 'border-red-400 focus:border-red-500'
                                    : 'border-gray-200 focus:border-blue-500',
                            ]"
                        />
                        <p v-if="form.errors.nik" class="text-xs text-red-500">
                            {{ form.errors.nik }}
                        </p>
                    </div>

                    <!-- Nama -->
                    <div class="flex flex-col gap-1.5">
                        <label
                            for="nama"
                            class="text-sm font-medium text-gray-700"
                        >
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="nama"
                            v-model="form.nama"
                            @input="
                                form.nama = form.nama.replace(
                                    /[^a-zA-Z\s\.\'-]/g,
                                    '',
                                )
                            "
                            type="text"
                            placeholder="Nama sesuai KTP"
                            required
                            :class="[
                                'w-full rounded-lg border px-3 py-2 text-sm text-gray-900 transition outline-none',
                                form.errors.nama
                                    ? 'border-red-400 focus:border-red-500'
                                    : 'border-gray-200 focus:border-blue-500',
                            ]"
                        />
                        <p v-if="form.errors.nama" class="text-xs text-red-500">
                            {{ form.errors.nama }}
                        </p>
                    </div>

                    <!-- Jenis Kelamin -->
                    <div class="flex flex-col gap-1.5">
                        <span class="text-sm font-medium text-gray-700">
                            Jenis Kelamin <span class="text-red-500">*</span>
                        </span>
                        <div class="flex gap-3">
                            <label
                                :class="[
                                    'flex flex-1 cursor-pointer items-center gap-2 rounded-lg border-2 px-4 py-2.5 text-sm transition',
                                    form.jenis_kelamin === 'L'
                                        ? 'border-blue-500 bg-blue-50 font-semibold text-blue-700'
                                        : 'border-gray-200 text-gray-500',
                                ]"
                            >
                                <input
                                    type="radio"
                                    v-model="form.jenis_kelamin"
                                    value="L"
                                    class="sr-only"
                                />
                                <span class="text-lg">♂</span> Laki-laki
                            </label>
                            <label
                                :class="[
                                    'flex flex-1 cursor-pointer items-center gap-2 rounded-lg border-2 px-4 py-2.5 text-sm transition',
                                    form.jenis_kelamin === 'P'
                                        ? 'border-pink-500 bg-pink-50 font-semibold text-pink-700'
                                        : 'border-gray-200 text-gray-500',
                                ]"
                            >
                                <input
                                    type="radio"
                                    v-model="form.jenis_kelamin"
                                    value="P"
                                    class="sr-only"
                                />
                                <span class="text-lg">♀</span> Perempuan
                            </label>
                        </div>
                    </div>

                    <!-- Alamat -->
                    <div class="flex flex-col gap-1.5">
                        <label
                            for="alamat"
                            class="text-sm font-medium text-gray-700"
                        >
                            Alamat <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            id="alamat"
                            v-model="form.alamat"
                            @input="
                                form.alamat = form.alamat.replace(
                                    /[^a-zA-Z0-9\s\.,\/#-]/g,
                                    '',
                                )
                            "
                            rows="3"
                            placeholder="Alamat lengkap"
                            required
                            :class="[
                                'w-full resize-y rounded-lg border px-3 py-2 text-sm text-gray-900 transition outline-none',
                                form.errors.alamat
                                    ? 'border-red-400 focus:border-red-500'
                                    : 'border-gray-200 focus:border-blue-500',
                            ]"
                        />
                        <p
                            v-if="form.errors.alamat"
                            class="text-xs text-red-500"
                        >
                            {{ form.errors.alamat }}
                        </p>
                    </div>

                    <!-- Relawan Pendamping -->
                    <div class="flex flex-col gap-1.5">
                        <label
                            for="relawan_id"
                            class="text-sm font-medium text-gray-700"
                        >
                            Relawan Pendamping
                            <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="relawan_id"
                            v-model="form.relawan_id"
                            required
                            :class="[
                                'w-full rounded-lg border bg-white px-3 py-2 text-sm transition outline-none',
                                form.errors.relawan_id
                                    ? 'border-red-400 focus:border-red-500'
                                    : 'border-gray-200 focus:border-blue-500',
                            ]"
                        >
                            <option value="" disabled>Pilih Relawan</option>
                            <option
                                v-for="relawan in props.relawans"
                                :key="relawan.id"
                                :value="relawan.id"
                            >
                                {{ relawan.nama }}
                            </option>
                        </select>
                        <p
                            v-if="form.errors.relawan_id"
                            class="text-xs text-red-500"
                        >
                            {{ form.errors.relawan_id }}
                        </p>
                    </div>

                    <!-- RT / RW -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label
                                for="rt"
                                class="text-sm font-medium text-gray-700"
                            >
                                RT <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="rt"
                                v-model="form.rt"
                                required
                                :class="[
                                    'w-full rounded-lg border bg-white px-3 py-2 text-sm transition outline-none',
                                    form.errors.rt
                                        ? 'border-red-400 focus:border-red-500'
                                        : 'border-gray-200 focus:border-blue-500',
                                ]"
                            >
                                <option value="" disabled>Pilih RT</option>
                                <option
                                    v-for="opt in rtRwOptions"
                                    :key="opt"
                                    :value="opt"
                                >
                                    {{ opt }}
                                </option>
                            </select>
                            <p
                                v-if="form.errors.rt"
                                class="text-xs text-red-500"
                            >
                                {{ form.errors.rt }}
                            </p>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label
                                for="rw"
                                class="text-sm font-medium text-gray-700"
                            >
                                RW <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="rw"
                                v-model="form.rw"
                                required
                                :class="[
                                    'w-full rounded-lg border bg-white px-3 py-2 text-sm transition outline-none',
                                    form.errors.rw
                                        ? 'border-red-400 focus:border-red-500'
                                        : 'border-gray-200 focus:border-blue-500',
                                ]"
                            >
                                <option value="" disabled>Pilih RW</option>
                                <option
                                    v-for="opt in rtRwOptions"
                                    :key="opt"
                                    :value="opt"
                                >
                                    {{ opt }}
                                </option>
                            </select>
                            <p
                                v-if="form.errors.rw"
                                class="text-xs text-red-500"
                            >
                                {{ form.errors.rw }}
                            </p>
                        </div>
                    </div>

                    <!-- Foto KTP -->
                    <div class="flex flex-col gap-1.5">
                        <label
                            for="foto_ktp"
                            class="text-sm font-medium text-gray-700"
                        >
                            Foto KTP
                            <span v-if="!isEdit" class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-4">
                            <!-- Image preview -->
                            <div
                                v-if="imagePreview"
                                class="border-gray-250 relative flex h-20 w-32 shrink-0 items-center justify-center overflow-hidden rounded-lg border bg-gray-50"
                            >
                                <img
                                    :src="imagePreview"
                                    alt="KTP Preview"
                                    class="h-full w-full object-cover"
                                />
                            </div>

                            <!-- Custom File Upload Button -->
                            <label
                                class="flex flex-1 cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-200 bg-gray-50/50 p-4 text-center transition hover:border-blue-400 hover:bg-blue-50/10"
                            >
                                <span class="text-sm font-medium text-gray-600"
                                    >Klik untuk upload foto KTP</span
                                >
                                <span class="mt-1 text-xs text-gray-400"
                                    >Format: JPG, PNG, WebP (Max 10MB)</span
                                >
                                <input
                                    id="foto_ktp"
                                    type="file"
                                    accept="image/*"
                                    class="sr-only"
                                    @change="handleFileChange"
                                    :required="!isEdit"
                                />
                            </label>
                        </div>
                        <p
                            v-if="form.errors.foto_ktp"
                            class="text-xs text-red-500"
                        >
                            {{ form.errors.foto_ktp }}
                        </p>
                        <p v-if="compressError" class="text-xs text-red-500">
                            {{ compressError }}
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="flex w-full items-center gap-3 pt-2">
                        <Link
                            :href="desaRoutes.pemilih.index.url()"
                            class="flex-1 rounded-lg bg-gray-100 py-2.5 text-center text-sm font-semibold text-gray-600 transition hover:bg-gray-200"
                            >Batal</Link
                        >
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex flex-1 items-center justify-center gap-2 rounded-lg bg-gray-900 py-2.5 text-sm font-semibold text-white transition hover:bg-black disabled:opacity-60"
                        >
                            <svg
                                v-if="form.processing"
                                class="h-4 w-4 animate-spin"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M21 12a9 9 0 11-6.219-8.56" />
                            </svg>
                            {{ isEdit ? 'Simpan Perubahan' : 'Tambah Pemilih' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
