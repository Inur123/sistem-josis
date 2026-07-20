<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import { ref, reactive } from 'vue';
import PaginationBar from '@/components/PaginationBar.vue';

interface Causer {
    name: string;
    email: string;
    role: string;
}

interface ActivityLog {
    id: number;
    log_name: string | null;
    description: string;
    event: string | null;
    causer: Causer | null;
    created_at: string;
}

interface PaginatedLogs {
    data: ActivityLog[];
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
    next_page_url: string | null;
    prev_page_url: string | null;
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{
    logs: PaginatedLogs;
}>();

// ─── State ────────────────────────────────────────────────────────────────────
const currentPage = ref(props.logs.current_page);
const totalPages = ref(props.logs.last_page);
const currentData = ref<ActivityLog[]>([...props.logs.data]);
const loading = ref(false);

// Cache halaman yang sudah pernah diambil
const pageCache = reactive<Record<number, ActivityLog[]>>({
    [props.logs.current_page]: [...props.logs.data],
});

// ─── AJAX Pagination ───────────────────────────────────────────────────────────
async function goToPage(page: number) {
    if (
        page < 1 ||
        page > totalPages.value ||
        page === currentPage.value ||
        loading.value
    ) {
        return;
    }

    // Jika sudah ada di cache, langsung tampilkan
    if (pageCache[page]) {
        currentPage.value = page;
        currentData.value = pageCache[page];

        return;
    }

    loading.value = true;

    try {
        const res = await fetch(`/admin/activity-logs?page=${page}`, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!res.ok) {
            throw new Error('Gagal memuat log');
        }

        const json = await res.json();

        // Simpan ke cache dan tampilkan
        pageCache[page] = json.data;
        totalPages.value = json.last_page;
        currentPage.value = page;
        currentData.value = json.data;
    } catch (e) {
        console.error('Error fetching logs:', e);
    } finally {
        loading.value = false;
    }
}

// ─── Real-time via Echo ────────────────────────────────────────────────────────
if (typeof window !== 'undefined') {
    useEcho('admin.activity-logs', 'ActivityLogged', (e: any) => {
        // Tambah log baru di halaman pertama (jika sedang di halaman 1)
        if (currentPage.value === 1) {
            currentData.value.unshift(e.activity);

            if (currentData.value.length > props.logs.per_page) {
                currentData.value.pop();
            }

            // Invalidate cache halaman 1 agar fresh saat kembali
            pageCache[1] = [...currentData.value];
        }

        // Invalidate semua cache selain halaman saat ini karena nomor urut bergeser
        Object.keys(pageCache).forEach((k) => {
            if (Number(k) !== currentPage.value) {
                delete pageCache[Number(k)];
            }
        });
    });
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/admin/dashboard' },
            { title: 'Log Aktivitas', href: '#' },
        ],
    },
});
</script>

<template>
    <Head title="Log Aktivitas" />

    <div class="flex flex-col gap-6 p-6">
        <!-- Header -->
        <div>
            <h2 class="text-xl font-bold text-gray-900">Log Aktivitas</h2>
            <p class="mt-1 text-sm text-gray-500">
                Audit trail dari tindakan yang dilakukan oleh pengguna di sistem
            </p>
        </div>

        <!-- Tabel Log -->
        <div
            class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm"
        >
            <div class="relative overflow-x-auto">
                <!-- Loading overlay -->
                <div
                    v-if="loading"
                    class="absolute inset-0 z-10 flex items-center justify-center bg-white/60"
                >
                    <svg
                        class="h-6 w-6 animate-spin text-blue-600"
                        viewBox="0 0 24 24"
                        fill="none"
                    >
                        <circle
                            class="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-width="4"
                        />
                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8v8H4z"
                        />
                    </svg>
                </div>

                <table class="w-full text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase"
                        >
                            <th class="w-[60px] px-5 py-3">No</th>
                            <th class="w-[150px] px-5 py-3">Waktu (WIB)</th>
                            <th class="w-[120px] px-5 py-3">Aksi</th>
                            <th class="px-5 py-3">Deskripsi</th>
                            <th class="w-[250px] px-5 py-3">Pelaku</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(log, i) in currentData"
                            :key="log.id"
                            class="border-b border-gray-50 last:border-0 hover:bg-gray-50"
                        >
                            <td class="px-5 py-4 text-gray-400">
                                {{
                                    (currentPage - 1) * props.logs.per_page +
                                    i +
                                    1
                                }}
                            </td>
                            <td
                                class="px-5 py-4 font-mono text-xs whitespace-nowrap text-gray-600"
                            >
                                {{ log.created_at }}
                            </td>
                            <td class="px-5 py-4">
                                <span
                                    :class="[
                                        'inline-flex items-center rounded px-2 py-0.5 text-xs font-semibold tracking-wide uppercase',
                                        log.event === 'created'
                                            ? 'border border-green-200 bg-green-50 text-green-700'
                                            : log.event === 'updated'
                                              ? 'border border-blue-200 bg-blue-50 text-blue-700'
                                              : log.event === 'deleted'
                                                ? 'border border-red-200 bg-red-50 text-red-700'
                                                : log.event === 'login'
                                                  ? 'border border-purple-200 bg-purple-50 text-purple-700'
                                                  : log.event === 'logout'
                                                    ? 'border border-orange-200 bg-orange-50 text-orange-700'
                                                    : 'border border-gray-200 bg-gray-50 text-gray-700',
                                    ]"
                                >
                                    {{ log.event || 'System' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 font-medium text-gray-900">
                                {{ log.description }}
                            </td>
                            <td class="px-5 py-4">
                                <div v-if="log.causer" class="flex flex-col">
                                    <span
                                        class="text-xs font-semibold text-gray-900"
                                        >{{ log.causer.name }}</span
                                    >
                                    <span class="text-[10px] text-gray-500">
                                        {{ log.causer.email }} ·
                                        <span class="font-mono capitalize">{{
                                            log.causer.role
                                        }}</span>
                                    </span>
                                </div>
                                <span
                                    v-else
                                    class="text-xs text-gray-400 italic"
                                    >Sistem</span
                                >
                            </td>
                        </tr>
                        <tr v-if="!currentData.length">
                            <td
                                colspan="5"
                                class="px-5 py-12 text-center text-gray-400"
                            >
                                Belum ada aktivitas yang tercatat.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="totalPages > 1" class="px-4">
                <PaginationBar
                    :current-page="currentPage"
                    :total-pages="totalPages"
                    :loading="loading"
                    @go="goToPage"
                />
            </div>
        </div>
    </div>
</template>
