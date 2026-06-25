<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import { Plus, Search, Trash2, Edit, X, Phone, MapPin } from '@lucide/vue';
import { ref, computed } from 'vue';
import adminRoutes from '@/routes/admin';

interface AnggotaTim {
    id: string;
    role: 'korcam' | 'kordes' | 'relawan';
    kecamatan_id: string | null;
    desa_id: string | null;
    nama: string;
    nik: string | null;
    no_hp: string | null;
    alamat: string | null;
    created_at: string;
}

interface KecamatanWithMembers {
    id: string;
    nama: string;
    anggota_tims: AnggotaTim[];
}

interface DesaWithMembers {
    id: string;
    nama: string;
    kecamatan_id: string;
    kecamatan: {
        id: string;
        nama: string;
    };
    anggota_tims: AnggotaTim[];
}

interface DropdownItem {
    id: string;
    nama: string;
    kecamatan_id?: string;
}

const props = defineProps<{
    korcams: KecamatanWithMembers[];
    kordes: DesaWithMembers[];
    relawans: DesaWithMembers[];
    kecamatans: DropdownItem[];
    desas: DropdownItem[];
}>();

// State
const activeTab = ref<'korcam' | 'kordes' | 'relawan'>('korcam');
const searchQuery = ref('');
const isModalOpen = ref(false);
const isEditing = ref(false);
const editingMemberId = ref<string | null>(null);

// Form
const form = useForm({
    role: 'korcam' as 'korcam' | 'kordes' | 'relawan',
    kecamatan_id: null as string | null,
    desa_id: null as string | null,
    nama: '',
    nik: '',
    no_hp: '',
    alamat: '',
});

// Dropdowns logic
const filteredDesasForForm = computed(() => {
    if (form.role === 'korcam') {
        return [];
    }

    if (!form.kecamatan_id) {
        return props.desas;
    }

    return props.desas.filter((d) => d.kecamatan_id === form.kecamatan_id);
});

// Watch role change to clear unrelated fields
const handleRoleChange = () => {
    form.kecamatan_id = null;
    form.desa_id = null;
};

// Filtered Lists for Tables
const filteredKorcamsTable = computed(() => {
    const q = searchQuery.value.toLowerCase().trim();

    if (!q) {
        return props.korcams;
    }

    return props.korcams
        .map((kec) => {
            const matchesKec = kec.nama.toLowerCase().includes(q);
            const filteredMembers = kec.anggota_tims.filter(
                (m) =>
                    m.nama.toLowerCase().includes(q) ||
                    (m.nik && m.nik.includes(q)) ||
                    (m.no_hp && m.no_hp.includes(q)) ||
                    (m.alamat && m.alamat.toLowerCase().includes(q)),
            );

            if (matchesKec || filteredMembers.length > 0) {
                return { ...kec, anggota_tims: filteredMembers };
            }

            return null;
        })
        .filter(Boolean) as KecamatanWithMembers[];
});

const filteredKordesTable = computed(() => {
    const q = searchQuery.value.toLowerCase().trim();

    if (!q) {
        return props.kordes;
    }

    return props.kordes
        .map((desa) => {
            const matchesDesa = desa.nama.toLowerCase().includes(q);
            const matchesKec = desa.kecamatan?.nama.toLowerCase().includes(q);
            const filteredMembers = desa.anggota_tims.filter(
                (m) =>
                    m.nama.toLowerCase().includes(q) ||
                    (m.nik && m.nik.includes(q)) ||
                    (m.no_hp && m.no_hp.includes(q)) ||
                    (m.alamat && m.alamat.toLowerCase().includes(q)),
            );

            if (matchesDesa || matchesKec || filteredMembers.length > 0) {
                return { ...desa, anggota_tims: filteredMembers };
            }

            return null;
        })
        .filter(Boolean) as DesaWithMembers[];
});

const filteredRelawansTable = computed(() => {
    const q = searchQuery.value.toLowerCase().trim();

    if (!q) {
        return props.relawans;
    }

    return props.relawans
        .map((desa) => {
            const matchesDesa = desa.nama.toLowerCase().includes(q);
            const matchesKec = desa.kecamatan?.nama.toLowerCase().includes(q);
            const filteredMembers = desa.anggota_tims.filter(
                (m) =>
                    m.nama.toLowerCase().includes(q) ||
                    (m.nik && m.nik.includes(q)) ||
                    (m.no_hp && m.no_hp.includes(q)) ||
                    (m.alamat && m.alamat.toLowerCase().includes(q)),
            );

            if (matchesDesa || matchesKec || filteredMembers.length > 0) {
                return { ...desa, anggota_tims: filteredMembers };
            }

            return null;
        })
        .filter(Boolean) as DesaWithMembers[];
});

// Modal Actions
const openAddModal = () => {
    isEditing.value = false;
    editingMemberId.value = null;
    form.reset();
    form.role = activeTab.value;
    isModalOpen.value = true;
};

const openEditModal = (member: AnggotaTim) => {
    isEditing.value = true;
    editingMemberId.value = member.id;
    form.role = member.role;
    form.nama = member.nama;
    form.nik = member.nik || '';
    form.no_hp = member.no_hp || '';
    form.alamat = member.alamat || '';

    if (member.role === 'korcam') {
        form.kecamatan_id = member.kecamatan_id;
        form.desa_id = null;
    } else {
        form.desa_id = member.desa_id;
        const matchingDesa = props.desas.find((d) => d.id === member.desa_id);
        form.kecamatan_id = matchingDesa
            ? matchingDesa.kecamatan_id || null
            : null;
    }

    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

// Form Submit
const submitForm = () => {
    if (isEditing.value && editingMemberId.value) {
        form.put(adminRoutes.tim.update.url(editingMemberId.value), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(adminRoutes.tim.store.url(), {
            onSuccess: () => closeModal(),
        });
    }
};

// Delete Action
const confirmDelete = ref(false);
const selectedMember = ref<AnggotaTim | null>(null);

const confirmDeleteMember = (member: AnggotaTim) => {
    selectedMember.value = member;
    confirmDelete.value = true;
};

const cancelDelete = () => {
    selectedMember.value = null;
    confirmDelete.value = false;
};

const submitDelete = () => {
    if (selectedMember.value) {
        router.delete(adminRoutes.tim.destroy.url(selectedMember.value.id), {
            onSuccess: () => cancelDelete(),
        });
    }
};

if (typeof window !== 'undefined') {
    useEcho('admin.team', 'TeamChanged', () => {
        router.reload();
    });
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: adminRoutes.dashboard.url() },
            { title: 'Kelola Tim', href: adminRoutes.tim.index.url() },
        ],
    },
});
</script>

<template>
    <Head title="Kelola Tim" />

    <div class="flex flex-col gap-6 p-6">
        <!-- Header -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h2 class="text-xl font-bold text-gray-900">Kelola Tim</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Manajemen koordinator kecamatan (Korcam), koordinator desa
                    (Kordes), dan relawan pendukung
                </p>
            </div>
            <button
                @click="openAddModal"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white transition-all hover:bg-gray-800"
            >
                <Plus class="h-4.5 w-4.5" />
                Tambah Anggota
            </button>
        </div>

        <!-- Tabs & Search -->
        <div
            class="flex flex-col items-stretch justify-between gap-4 rounded-xl border border-gray-100 bg-white p-3 shadow-sm sm:flex-row sm:items-center"
        >
            <div
                class="flex rounded-lg border-b border-gray-100 bg-gray-50 p-0.5 sm:border-b-0"
            >
                <button
                    @click="activeTab = 'korcam'"
                    :class="[
                        'rounded-md px-4 py-2 text-sm font-medium transition-all',
                        activeTab === 'korcam'
                            ? 'bg-white font-semibold text-gray-900 shadow-xs'
                            : 'text-gray-500 hover:text-gray-900',
                    ]"
                >
                    Korcam
                </button>
                <button
                    @click="activeTab = 'kordes'"
                    :class="[
                        'rounded-md px-4 py-2 text-sm font-medium transition-all',
                        activeTab === 'kordes'
                            ? 'bg-white font-semibold text-gray-900 shadow-xs'
                            : 'text-gray-500 hover:text-gray-900',
                    ]"
                >
                    Kordes
                </button>
                <button
                    @click="activeTab = 'relawan'"
                    :class="[
                        'rounded-md px-4 py-2 text-sm font-medium transition-all',
                        activeTab === 'relawan'
                            ? 'bg-white font-semibold text-gray-900 shadow-xs'
                            : 'text-gray-500 hover:text-gray-900',
                    ]"
                >
                    Relawan
                </button>
            </div>

            <!-- Search -->
            <div class="relative max-w-md flex-1">
                <Search
                    class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400"
                />
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Cari wilayah atau nama anggota..."
                    class="w-full rounded-xl border border-gray-200 bg-gray-50/50 py-2.5 pr-4 pl-10 text-sm outline-hidden focus:border-blue-500 focus:bg-white"
                />
            </div>
        </div>

        <!-- Table View -->
        <div
            class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm"
        >
            <div class="overflow-x-auto">
                <!-- 1. TAB KORCAM TABLE -->
                <table v-if="activeTab === 'korcam'" class="w-full text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase"
                        >
                            <th class="w-[60px] px-5 py-3">No</th>
                            <th class="w-[220px] px-5 py-3">Kecamatan</th>
                            <th class="px-5 py-3">
                                Daftar Koordinator Kecamatan (Korcam)
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(kec, i) in filteredKorcamsTable"
                            :key="kec.id"
                            class="border-b border-gray-50 align-top last:border-0 hover:bg-gray-50/50"
                        >
                            <td class="px-5 py-4 text-gray-400">{{ i + 1 }}</td>
                            <td
                                class="px-5 py-4 text-xs font-semibold tracking-wide text-gray-900 uppercase"
                            >
                                {{ kec.nama }}
                            </td>
                            <td class="px-5 py-4">
                                <div
                                    v-if="kec.anggota_tims.length"
                                    class="divide-y divide-gray-100"
                                >
                                    <div
                                        v-for="member in kec.anggota_tims"
                                        :key="member.id"
                                        class="flex items-start justify-between gap-4 py-2.5 first:pt-0 last:pb-0"
                                    >
                                        <div class="flex-1">
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <span
                                                    class="text-sm font-semibold text-gray-900"
                                                    >{{ member.nama }}</span
                                                >
                                                <span
                                                    v-if="member.nik"
                                                    class="rounded bg-gray-100 px-2 py-0.5 font-mono text-xs text-gray-600"
                                                    >NIK: {{ member.nik }}</span
                                                >
                                            </div>
                                            <div
                                                class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500"
                                            >
                                                <span
                                                    v-if="member.no_hp"
                                                    class="flex items-center gap-1"
                                                    ><Phone
                                                        class="h-3.5 w-3.5 shrink-0 text-gray-400"
                                                    />
                                                    {{ member.no_hp }}</span
                                                >
                                                <span
                                                    v-if="member.alamat"
                                                    class="flex items-center gap-1"
                                                    ><MapPin
                                                        class="h-3.5 w-3.5 shrink-0 text-gray-400"
                                                    />
                                                    {{ member.alamat }}</span
                                                >
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2.5">
                                            <button
                                                @click="openEditModal(member)"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 text-gray-500 transition-all hover:border-blue-100 hover:bg-blue-50 hover:text-blue-600"
                                            >
                                                <Edit class="h-4 w-4" />
                                            </button>
                                            <button
                                                @click="
                                                    confirmDeleteMember(member)
                                                "
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 text-gray-500 transition-all hover:border-red-100 hover:bg-red-50 hover:text-red-600"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <span
                                    v-else
                                    class="text-xs text-gray-400 italic"
                                    >Belum ada Korcam yang ditugaskan</span
                                >
                            </td>
                        </tr>
                        <tr v-if="!filteredKorcamsTable.length">
                            <td
                                colspan="3"
                                class="px-5 py-12 text-center text-gray-400"
                            >
                                Tidak ada data kecamatan atau korcam ditemukan.
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- 2. TAB KORDES TABLE -->
                <table v-if="activeTab === 'kordes'" class="w-full text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase"
                        >
                            <th class="w-[60px] px-5 py-3">No</th>
                            <th class="w-[180px] px-5 py-3">Kecamatan</th>
                            <th class="w-[180px] px-5 py-3">Desa</th>
                            <th class="px-5 py-3">
                                Daftar Koordinator Desa (Kordes)
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(desa, i) in filteredKordesTable"
                            :key="desa.id"
                            class="border-b border-gray-50 align-top last:border-0 hover:bg-gray-50/50"
                        >
                            <td class="px-5 py-4 text-gray-400">{{ i + 1 }}</td>
                            <td
                                class="px-5 py-4 text-xs font-medium tracking-wide text-gray-500 uppercase"
                            >
                                {{ desa.kecamatan?.nama }}
                            </td>
                            <td
                                class="px-5 py-4 text-xs font-semibold tracking-wide text-gray-900 uppercase"
                            >
                                {{ desa.nama }}
                            </td>
                            <td class="px-5 py-4">
                                <div
                                    v-if="desa.anggota_tims.length"
                                    class="divide-y divide-gray-100"
                                >
                                    <div
                                        v-for="member in desa.anggota_tims"
                                        :key="member.id"
                                        class="flex items-start justify-between gap-4 py-2.5 first:pt-0 last:pb-0"
                                    >
                                        <div class="flex-1">
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <span
                                                    class="text-sm font-semibold text-gray-900"
                                                    >{{ member.nama }}</span
                                                >
                                                <span
                                                    v-if="member.nik"
                                                    class="rounded bg-gray-100 px-2 py-0.5 font-mono text-xs text-gray-600"
                                                    >NIK: {{ member.nik }}</span
                                                >
                                            </div>
                                            <div
                                                class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500"
                                            >
                                                <span
                                                    v-if="member.no_hp"
                                                    class="flex items-center gap-1"
                                                    ><Phone
                                                        class="h-3.5 w-3.5 shrink-0 text-gray-400"
                                                    />
                                                    {{ member.no_hp }}</span
                                                >
                                                <span
                                                    v-if="member.alamat"
                                                    class="flex items-center gap-1"
                                                    ><MapPin
                                                        class="h-3.5 w-3.5 shrink-0 text-gray-400"
                                                    />
                                                    {{ member.alamat }}</span
                                                >
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2.5">
                                            <button
                                                @click="openEditModal(member)"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 text-gray-500 transition-all hover:border-blue-100 hover:bg-blue-50 hover:text-blue-600"
                                            >
                                                <Edit class="h-4 w-4" />
                                            </button>
                                            <button
                                                @click="
                                                    confirmDeleteMember(member)
                                                "
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 text-gray-500 transition-all hover:border-red-100 hover:bg-red-50 hover:text-red-600"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <span
                                    v-else
                                    class="text-xs text-gray-400 italic"
                                    >Belum ada Kordes yang ditugaskan</span
                                >
                            </td>
                        </tr>
                        <tr v-if="!filteredKordesTable.length">
                            <td
                                colspan="4"
                                class="px-5 py-12 text-center text-gray-400"
                            >
                                Tidak ada data desa atau kordes ditemukan.
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- 3. TAB RELAWAN TABLE -->
                <table v-if="activeTab === 'relawan'" class="w-full text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase"
                        >
                            <th class="w-[60px] px-5 py-3">No</th>
                            <th class="w-[180px] px-5 py-3">Kecamatan</th>
                            <th class="w-[180px] px-5 py-3">Desa</th>
                            <th class="px-5 py-3">Daftar Relawan Desa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(desa, i) in filteredRelawansTable"
                            :key="desa.id"
                            class="border-b border-gray-50 align-top last:border-0 hover:bg-gray-50/50"
                        >
                            <td class="px-5 py-4 text-gray-400">{{ i + 1 }}</td>
                            <td
                                class="px-5 py-4 text-xs font-medium tracking-wide text-gray-500 uppercase"
                            >
                                {{ desa.kecamatan?.nama }}
                            </td>
                            <td
                                class="px-5 py-4 text-xs font-semibold tracking-wide text-gray-900 uppercase"
                            >
                                {{ desa.nama }}
                            </td>
                            <td class="px-5 py-4">
                                <div
                                    v-if="desa.anggota_tims.length"
                                    class="divide-y divide-gray-100"
                                >
                                    <div
                                        v-for="member in desa.anggota_tims"
                                        :key="member.id"
                                        class="flex items-start justify-between gap-4 py-2.5 first:pt-0 last:pb-0"
                                    >
                                        <div class="flex-1">
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <span
                                                    class="text-sm font-semibold text-gray-900"
                                                    >{{ member.nama }}</span
                                                >
                                                <span
                                                    v-if="member.nik"
                                                    class="rounded bg-gray-100 px-2 py-0.5 font-mono text-xs text-gray-600"
                                                    >NIK: {{ member.nik }}</span
                                                >
                                            </div>
                                            <div
                                                class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500"
                                            >
                                                <span
                                                    v-if="member.no_hp"
                                                    class="flex items-center gap-1"
                                                    ><Phone
                                                        class="h-3.5 w-3.5 shrink-0 text-gray-400"
                                                    />
                                                    {{ member.no_hp }}</span
                                                >
                                                <span
                                                    v-if="member.alamat"
                                                    class="flex items-center gap-1"
                                                    ><MapPin
                                                        class="h-3.5 w-3.5 shrink-0 text-gray-400"
                                                    />
                                                    {{ member.alamat }}</span
                                                >
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2.5">
                                            <button
                                                @click="openEditModal(member)"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 text-gray-500 transition-all hover:border-blue-100 hover:bg-blue-50 hover:text-blue-600"
                                            >
                                                <Edit class="h-4 w-4" />
                                            </button>
                                            <button
                                                @click="
                                                    confirmDeleteMember(member)
                                                "
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 text-gray-500 transition-all hover:border-red-100 hover:bg-red-50 hover:text-red-600"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <span
                                    v-else
                                    class="text-xs text-gray-400 italic"
                                    >Belum ada Relawan yang ditugaskan</span
                                >
                            </td>
                        </tr>
                        <tr v-if="!filteredRelawansTable.length">
                            <td
                                colspan="4"
                                class="px-5 py-12 text-center text-gray-400"
                            >
                                Tidak ada data desa atau relawan ditemukan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add / Edit Modal -->
    <div
        v-if="isModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
    >
        <div
            class="w-full max-w-lg animate-in overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-xl duration-200 zoom-in-95 fade-in"
        >
            <div
                class="flex items-center justify-between border-b border-gray-100 px-6 py-4"
            >
                <h3 class="text-lg font-bold text-gray-900">
                    {{ isEditing ? 'Edit Anggota Tim' : 'Tambah Anggota Tim' }}
                </h3>
                <button
                    @click="closeModal"
                    class="rounded-lg p-1 text-gray-400 transition-all hover:bg-gray-50 hover:text-gray-600"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <form @submit.prevent="submitForm" class="space-y-4 p-6">
                <!-- Role -->
                <div>
                    <label
                        class="mb-1.5 block text-xs font-semibold tracking-wide text-gray-500 uppercase"
                        >Peran / Posisi</label
                    >
                    <select
                        v-model="form.role"
                        @change="handleRoleChange"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50/50 px-3.5 py-2.5 text-sm outline-hidden focus:border-blue-500 focus:bg-white"
                        required
                    >
                        <option value="korcam">
                            Koordinator Kecamatan (Korcam)
                        </option>
                        <option value="kordes">
                            Koordinator Desa (Kordes)
                        </option>
                        <option value="relawan">Relawan Desa</option>
                    </select>
                </div>

                <!-- Kecamatan selection -->
                <div>
                    <label
                        class="mb-1.5 block text-xs font-semibold tracking-wide text-gray-500 uppercase"
                        >Kecamatan</label
                    >
                    <select
                        v-model="form.kecamatan_id"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50/50 px-3.5 py-2.5 text-sm outline-hidden focus:border-blue-500 focus:bg-white"
                        required
                    >
                        <option :value="null" disabled>Pilih Kecamatan</option>
                        <option
                            v-for="kec in props.kecamatans"
                            :key="kec.id"
                            :value="kec.id"
                        >
                            {{ kec.nama }}
                        </option>
                    </select>
                </div>

                <!-- Desa selection (only for kordes and relawan) -->
                <div v-if="form.role !== 'korcam'">
                    <label
                        class="mb-1.5 block text-xs font-semibold tracking-wide text-gray-500 uppercase"
                        >Desa / Kelurahan</label
                    >
                    <select
                        v-model="form.desa_id"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50/50 px-3.5 py-2.5 text-sm outline-hidden focus:border-blue-500 focus:bg-white"
                        required
                        :disabled="!form.kecamatan_id"
                    >
                        <option :value="null" disabled>
                            {{
                                form.kecamatan_id
                                    ? 'Pilih Desa'
                                    : 'Pilih Kecamatan Terlebih Dahulu'
                            }}
                        </option>
                        <option
                            v-for="desa in filteredDesasForForm"
                            :key="desa.id"
                            :value="desa.id"
                        >
                            {{ desa.nama }}
                        </option>
                    </select>
                </div>

                <!-- Nama -->
                <div>
                    <label
                        class="mb-1.5 block text-xs font-semibold tracking-wide text-gray-500 uppercase"
                        >Nama Lengkap</label
                    >
                    <input
                        v-model="form.nama"
                        type="text"
                        placeholder="Masukkan nama lengkap"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50/50 px-3.5 py-2.5 text-sm outline-hidden focus:border-blue-500 focus:bg-white"
                        required
                    />
                </div>

                <!-- NIK -->
                <div>
                    <label
                        class="mb-1.5 block text-xs font-semibold tracking-wide text-gray-500 uppercase"
                        >NIK (Optional)</label
                    >
                    <input
                        v-model="form.nik"
                        type="text"
                        placeholder="Masukkan 16 digit NIK"
                        maxlength="16"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50/50 px-3.5 py-2.5 text-sm outline-hidden focus:border-blue-500 focus:bg-white"
                    />
                </div>

                <!-- No HP -->
                <div>
                    <label
                        class="mb-1.5 block text-xs font-semibold tracking-wide text-gray-500 uppercase"
                        >No Handphone (Optional)</label
                    >
                    <input
                        v-model="form.no_hp"
                        type="text"
                        placeholder="Contoh: 08123456789"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50/50 px-3.5 py-2.5 text-sm outline-hidden focus:border-blue-500 focus:bg-white"
                    />
                </div>

                <!-- Alamat -->
                <div>
                    <label
                        class="mb-1.5 block text-xs font-semibold tracking-wide text-gray-500 uppercase"
                        >Alamat Lengkap (Optional)</label
                    >
                    <textarea
                        v-model="form.alamat"
                        rows="2"
                        placeholder="Alamat domisili"
                        class="w-full resize-none rounded-xl border border-gray-200 bg-gray-50/50 px-3.5 py-2.5 text-sm outline-hidden focus:border-blue-500 focus:bg-white"
                    ></textarea>
                </div>

                <!-- Submit buttons -->
                <div
                    class="flex justify-end gap-3 border-t border-gray-100 pt-4"
                >
                    <button
                        type="button"
                        @click="closeModal"
                        class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-xl bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-gray-800 disabled:opacity-50"
                    >
                        {{ isEditing ? 'Simpan Perubahan' : 'Tambah Anggota' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
        v-if="confirmDelete"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
    >
        <div
            class="w-full max-w-md animate-in overflow-hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-xl duration-200 zoom-in-95 fade-in"
        >
            <div
                class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-50 text-red-600"
            >
                <Trash2 class="h-6 w-6" />
            </div>
            <h3 class="text-lg font-bold text-gray-900">Konfirmasi Hapus</h3>
            <p class="mt-2 text-sm text-gray-500">
                Apakah Anda yakin ingin menghapus data anggota tim
                <strong class="font-semibold text-gray-800">{{
                    selectedMember?.nama
                }}</strong
                >? Tindakan ini tidak dapat dibatalkan.
            </p>
            <div class="mt-6 flex justify-end gap-3">
                <button
                    @click="cancelDelete"
                    class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                >
                    Batal
                </button>
                <button
                    @click="submitDelete"
                    class="rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                >
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>
</template>
