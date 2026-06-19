<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import desaRoutes from '@/routes/desa';

interface PemilihData {
    id?: string;
    nik?: string;
    nama?: string;
    jenis_kelamin?: string;
    alamat?: string;
    rt?: string;
    rw?: string;
}

const props = defineProps<{
    mode: 'create' | 'edit';
    desa: string;
    pemilih?: PemilihData;
}>();

const isEdit = computed(() => props.mode === 'edit');

const form = useForm({
    nik: props.pemilih?.nik ?? '',
    nama: props.pemilih?.nama ?? '',
    jenis_kelamin: props.pemilih?.jenis_kelamin ?? 'L',
    alamat: props.pemilih?.alamat ?? '',
    rt: props.pemilih?.rt ?? '',
    rw: props.pemilih?.rw ?? '',
});

function submit() {
    if (isEdit.value && props.pemilih?.id) {
        form.put(desaRoutes.pemilih.update.url(props.pemilih.id));
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
        <div class="max-w-xl">
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
                    <span
                        class="flex items-center gap-1.5 rounded-full border border-green-200 bg-green-50 px-3 py-1 text-xs text-green-700"
                    >
                        <svg
                            class="h-3.5 w-3.5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <rect
                                x="3"
                                y="11"
                                width="18"
                                height="11"
                                rx="2"
                                ry="2"
                            />
                            <path d="M7 11V7a5 5 0 0110 0v4" />
                        </svg>
                        AES-256-GCM
                    </span>
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
                            type="text"
                            inputmode="numeric"
                            maxlength="16"
                            placeholder="16 digit NIK"
                            :class="[
                                'w-full rounded-lg border px-3 py-2 text-sm text-gray-900 transition outline-none focus:ring-2',
                                form.errors.nik
                                    ? 'border-red-400 focus:ring-red-100'
                                    : 'border-gray-200 focus:border-blue-400 focus:ring-blue-100',
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
                            type="text"
                            placeholder="Nama sesuai KTP"
                            :class="[
                                'w-full rounded-lg border px-3 py-2 text-sm text-gray-900 transition outline-none focus:ring-2',
                                form.errors.nama
                                    ? 'border-red-400 focus:ring-red-100'
                                    : 'border-gray-200 focus:border-blue-400 focus:ring-blue-100',
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
                            rows="3"
                            placeholder="Alamat lengkap"
                            :class="[
                                'w-full resize-y rounded-lg border px-3 py-2 text-sm text-gray-900 transition outline-none focus:ring-2',
                                form.errors.alamat
                                    ? 'border-red-400 focus:ring-red-100'
                                    : 'border-gray-200 focus:border-blue-400 focus:ring-blue-100',
                            ]"
                        />
                        <p
                            v-if="form.errors.alamat"
                            class="text-xs text-red-500"
                        >
                            {{ form.errors.alamat }}
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
                            <input
                                id="rt"
                                v-model="form.rt"
                                type="text"
                                placeholder="001"
                                maxlength="5"
                                :class="[
                                    'w-full rounded-lg border px-3 py-2 text-sm transition outline-none focus:ring-2',
                                    form.errors.rt
                                        ? 'border-red-400 focus:ring-red-100'
                                        : 'border-gray-200 focus:border-blue-400 focus:ring-blue-100',
                                ]"
                            />
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
                            <input
                                id="rw"
                                v-model="form.rw"
                                type="text"
                                placeholder="001"
                                maxlength="5"
                                :class="[
                                    'w-full rounded-lg border px-3 py-2 text-sm transition outline-none focus:ring-2',
                                    form.errors.rw
                                        ? 'border-red-400 focus:ring-red-100'
                                        : 'border-gray-200 focus:border-blue-400 focus:ring-blue-100',
                                ]"
                            />
                            <p
                                v-if="form.errors.rw"
                                class="text-xs text-red-500"
                            >
                                {{ form.errors.rw }}
                            </p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <a
                            :href="desaRoutes.pemilih.index.url()"
                            class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-200"
                            >Batal</a
                        >
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:opacity-60"
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
