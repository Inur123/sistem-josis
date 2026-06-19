<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import adminRoutes from '@/routes/admin';

interface User {
    id: string;
    name: string;
    email: string;
    role: string;
    kecamatan_id: string | null;
    kecamatan: string | null;
    desa_id: string | null;
    desa: string | null;
}

interface DropdownItem {
    id: string;
    nama: string;
    kecamatan_id?: string;
}

const props = defineProps<{
    users: User[];
    kecamatans: DropdownItem[];
    desas: DropdownItem[];
}>();

const showModal = ref(false);
const editingUser = ref<User | null>(null);

const form = useForm({
    name: '',
    email: '',
    password: '',
    role: 'desa',
    kecamatan_id: '',
    desa_id: '',
});

// Watch role changes to reset fields
watch(
    () => form.role,
    (newRole) => {
        if (newRole === 'admin') {
            form.kecamatan_id = '';
            form.desa_id = '';
        } else if (newRole === 'kecamatan') {
            form.desa_id = '';
        }
    },
);

// Watch kecamatan selection changes to reset desa selection
watch(
    () => form.kecamatan_id,
    () => {
        form.desa_id = '';
    },
);

// Filter desas depending on selected kecamatan in the form
const formDesas = computed(() => {
    if (!form.kecamatan_id) {
return [];
}

    return props.desas.filter((d) => d.kecamatan_id === form.kecamatan_id);
});

function openCreateModal() {
    editingUser.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
}

function openEditModal(user: User) {
    editingUser.value = user;
    form.clearErrors();
    form.name = user.name;
    form.email = user.email;
    form.password = '';
    form.role = user.role;
    form.kecamatan_id = user.kecamatan_id ?? '';
    form.desa_id = user.desa_id ?? '';
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    editingUser.value = null;
    form.reset();
}

function submitForm() {
    if (editingUser.value) {
        form.put(adminRoutes.akun.update.url(editingUser.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(adminRoutes.akun.store.url(), {
            onSuccess: () => closeModal(),
        });
    }
}

const confirmDeleteId = ref<string | null>(null);

function handleDelete(id: string) {
    if (confirmDeleteId.value !== id) {
        confirmDeleteId.value = id;

        return;
    }

    router.delete(adminRoutes.akun.destroy.url(id), {
        onSuccess: () => {
            confirmDeleteId.value = null;
        },
    });
}

function cancelDelete() {
    confirmDeleteId.value = null;
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: adminRoutes.dashboard.url() },
            { title: 'Kelola Akun', href: adminRoutes.akun.index.url() },
        ],
    },
});
</script>

<template>
    <Head title="Kelola Akun (Admin)" />
    <div class="flex flex-col gap-5 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900">
                    Kelola Akun Pengguna
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Total {{ props.users.length }} akun terdaftar dalam
                    platform.
                </p>
            </div>
            <button
                @click="openCreateModal"
                class="flex cursor-pointer items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700"
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
                Tambah Akun
            </button>
        </div>

        <!-- Users Table -->
        <div
            class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm"
        >
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50 text-left text-xs font-semibold tracking-wide text-gray-500 uppercase"
                        >
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Role</th>
                            <th class="px-4 py-3">Wilayah Akses</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr
                            v-for="user in props.users"
                            :key="user.id"
                            class="hover:bg-gray-50"
                        >
                            <td class="px-4 py-3 font-medium text-gray-900">
                                {{ user.name }}
                            </td>
                            <td class="px-4 py-3 text-gray-500">
                                {{ user.email }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    :class="[
                                        'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold uppercase',
                                        user.role === 'admin'
                                            ? 'bg-purple-100 text-purple-700'
                                            : user.role === 'kecamatan'
                                              ? 'bg-amber-100 text-amber-700'
                                              : 'bg-green-100 text-green-700',
                                    ]"
                                >
                                    {{ user.role }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                <span v-if="user.role === 'admin'"
                                    >Seluruh Kabupaten</span
                                >
                                <span v-else-if="user.role === 'kecamatan'"
                                    >Kec. {{ user.kecamatan }}</span
                                >
                                <span v-else-if="user.role === 'desa'"
                                    >Kec. {{ user.kecamatan }} &raquo;
                                    {{ user.desa }}</span
                                >
                            </td>
                            <td class="px-4 py-3">
                                <div
                                    class="flex items-center justify-center gap-2"
                                >
                                    <button
                                        @click="openEditModal(user)"
                                        class="cursor-pointer rounded-md bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600 hover:bg-gray-200"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        @click="handleDelete(user.id)"
                                        :class="[
                                            'cursor-pointer rounded-md px-2.5 py-1 text-xs font-medium transition-colors',
                                            confirmDeleteId === user.id
                                                ? 'bg-red-500 text-white'
                                                : 'bg-red-50 text-red-600 hover:bg-red-100',
                                        ]"
                                    >
                                        {{
                                            confirmDeleteId === user.id
                                                ? 'Yakin?'
                                                : 'Hapus'
                                        }}
                                    </button>
                                    <button
                                        v-if="confirmDeleteId === user.id"
                                        @click="cancelDelete"
                                        class="cursor-pointer rounded-md bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-500"
                                    >
                                        Batal
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div
            v-if="showModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        >
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-lg">
                <h3 class="mb-4 text-lg font-bold text-gray-900">
                    {{
                        editingUser
                            ? 'Edit Akun Pengguna'
                            : 'Tambah Akun Pengguna Baru'
                    }}
                </h3>

                <form @submit.prevent="submitForm" class="flex flex-col gap-4">
                    <!-- Nama -->
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-gray-700"
                            >Nama</label
                        >
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
                        />
                        <span
                            v-if="form.errors.name"
                            class="text-xs text-red-500"
                            >{{ form.errors.name }}</span
                        >
                    </div>

                    <!-- Email -->
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-gray-700"
                            >Email</label
                        >
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            class="rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
                        />
                        <span
                            v-if="form.errors.email"
                            class="text-xs text-red-500"
                            >{{ form.errors.email }}</span
                        >
                    </div>

                    <!-- Password -->
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-gray-700">
                            Password
                            <span
                                v-if="editingUser"
                                class="font-normal text-gray-400"
                                >(kosongkan jika tidak diubah)</span
                            >
                        </label>
                        <input
                            v-model="form.password"
                            type="password"
                            :required="!editingUser"
                            class="rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
                        />
                        <span
                            v-if="form.errors.password"
                            class="text-xs text-red-500"
                            >{{ form.errors.password }}</span
                        >
                    </div>

                    <!-- Role -->
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-gray-700"
                            >Role</label
                        >
                        <select
                            v-model="form.role"
                            class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
                        >
                            <option value="admin">Admin</option>
                            <option value="kecamatan">Kecamatan</option>
                            <option value="desa">Desa / Kelurahan</option>
                        </select>
                        <span
                            v-if="form.errors.role"
                            class="text-xs text-red-500"
                            >{{ form.errors.role }}</span
                        >
                    </div>

                    <!-- Kecamatan (untuk role kecamatan dan desa) -->
                    <div
                        v-if="form.role !== 'admin'"
                        class="flex flex-col gap-1"
                    >
                        <label class="text-xs font-semibold text-gray-700"
                            >Kecamatan</label
                        >
                        <select
                            v-model="form.kecamatan_id"
                            required
                            class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
                        >
                            <option value="">Pilih Kecamatan</option>
                            <option
                                v-for="kec in props.kecamatans"
                                :key="kec.id"
                                :value="kec.id"
                            >
                                Kec. {{ kec.nama }}
                            </option>
                        </select>
                        <span
                            v-if="form.errors.kecamatan_id"
                            class="text-xs text-red-500"
                            >{{ form.errors.kecamatan_id }}</span
                        >
                    </div>

                    <!-- Desa (untuk role desa) -->
                    <div
                        v-if="form.role === 'desa'"
                        class="flex flex-col gap-1"
                    >
                        <label class="text-xs font-semibold text-gray-700"
                            >Desa / Kelurahan</label
                        >
                        <select
                            v-model="form.desa_id"
                            required
                            :disabled="!form.kecamatan_id"
                            class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none disabled:bg-gray-50 disabled:text-gray-400"
                        >
                            <option value="">Pilih Desa / Kelurahan</option>
                            <option
                                v-for="desa in formDesas"
                                :key="desa.id"
                                :value="desa.id"
                            >
                                {{ desa.nama }}
                            </option>
                        </select>
                        <span
                            v-if="form.errors.desa_id"
                            class="text-xs text-red-500"
                            >{{ form.errors.desa_id }}</span
                        >
                    </div>

                    <!-- Buttons -->
                    <div class="mt-4 flex items-center justify-end gap-3">
                        <button
                            type="button"
                            @click="closeModal"
                            class="cursor-pointer rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-200"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="cursor-pointer rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                        >
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
