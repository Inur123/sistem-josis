<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import { Loader2, Activity, PlusCircle, CheckCircle, XCircle, LogIn, LogOut } from '@lucide/vue';
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

interface LogStats {
    total: number;
    created: number;
    updated: number;
    deleted: number;
    login: number;
    logout: number;
}

const props = defineProps<{
    logs: PaginatedLogs;
    stats: LogStats;
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
    useEcho('admin.activity-logs', 'ActivityLogged', () => {
        // Reload all data so stats update correctly in real time
        router.reload({
            only: ['logs', 'stats'],
            onSuccess: () => {
                currentPage.value = props.logs.current_page;
                totalPages.value = props.logs.last_page;
                currentData.value = [...props.logs.data];
                // Reset cache
                Object.keys(pageCache).forEach((k) => delete pageCache[Number(k)]);
                pageCache[props.logs.current_page] = [...props.logs.data];
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

        <!-- Statistik Cards Grid -->
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
            <!-- Total Logs -->
            <div class="rounded-xl border border-amber-100/50 bg-white p-4 shadow-xs">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-gray-500 uppercase">Total Log</span>
                    <div class="rounded-lg bg-amber-50 p-1.5 text-amber-600">
                        <Activity class="h-4 w-4" />
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-1">
                    <span class="text-2xl font-bold text-gray-950">{{ stats.total }}</span>
                </div>
            </div>

            <!-- Created -->
            <div class="rounded-xl border border-amber-100/50 bg-white p-4 shadow-xs">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-gray-500 uppercase">Created</span>
                    <div class="rounded-lg bg-green-50 p-1.5 text-green-600">
                        <PlusCircle class="h-4 w-4" />
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-1">
                    <span class="text-2xl font-bold text-gray-950">{{ stats.created }}</span>
                </div>
            </div>

            <!-- Updated -->
            <div class="rounded-xl border border-amber-100/50 bg-white p-4 shadow-xs">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-gray-500 uppercase">Updated</span>
                    <div class="rounded-lg bg-blue-50 p-1.5 text-blue-600">
                        <CheckCircle class="h-4 w-4" />
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-1">
                    <span class="text-2xl font-bold text-gray-950">{{ stats.updated }}</span>
                </div>
            </div>

            <!-- Deleted -->
            <div class="rounded-xl border border-amber-100/50 bg-white p-4 shadow-xs">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-gray-500 uppercase">Deleted</span>
                    <div class="rounded-lg bg-red-50 p-1.5 text-red-600">
                        <XCircle class="h-4 w-4" />
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-1">
                    <span class="text-2xl font-bold text-gray-950">{{ stats.deleted }}</span>
                </div>
            </div>

            <!-- Login -->
            <div class="rounded-xl border border-amber-100/50 bg-white p-4 shadow-xs">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-gray-500 uppercase">Login</span>
                    <div class="rounded-lg bg-purple-50 p-1.5 text-purple-600">
                        <LogIn class="h-4 w-4" />
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-1">
                    <span class="text-2xl font-bold text-gray-950">{{ stats.login }}</span>
                </div>
            </div>

            <!-- Logout -->
            <div class="rounded-xl border border-amber-100/50 bg-white p-4 shadow-xs">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-gray-500 uppercase">Logout</span>
                    <div class="rounded-lg bg-orange-50 p-1.5 text-orange-600">
                        <LogOut class="h-4 w-4" />
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-1">
                    <span class="text-2xl font-bold text-gray-950">{{ stats.logout }}</span>
                </div>
            </div>
        </div>

        <!-- Tabel Log -->
        <div class="overflow-hidden rounded-xl border border-amber-100/50 bg-white shadow-sm">
            <div class="relative overflow-x-auto">
                <!-- Loading overlay -->
                <div v-if="loading" class="absolute inset-0 z-10 flex items-center justify-center bg-white/60">
                    <Loader2 class="h-6 w-6 animate-spin text-amber-600" />
                </div>

                <table class="w-full text-sm">
                    <thead>
                        <tr
                            class="border-b border-amber-100/30 bg-amber-50/30 text-left text-xs font-semibold text-amber-800 uppercase">
                            <th class="w-[60px] px-5 py-3">No</th>
                            <th class="w-[150px] px-5 py-3">Waktu (WIB)</th>
                            <th class="w-[120px] px-5 py-3">Aksi</th>
                            <th class="px-5 py-3">Deskripsi</th>
                            <th class="w-[250px] px-5 py-3">Pelaku</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(log, i) in currentData" :key="log.id"
                            class="border-b border-amber-50/40 last:border-0 hover:bg-amber-50/20">
                            <td class="px-5 py-4 text-gray-400">
                                {{
                                    (currentPage - 1) * props.logs.per_page +
                                    i +
                                    1
                                }}
                            </td>
                            <td class="px-5 py-4 font-mono text-xs whitespace-nowrap text-gray-600">
                                {{ log.created_at }}
                            </td>
                            <td class="px-5 py-4">
                                <span :class="[
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
                                ]">
                                    {{ log.event || 'System' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 font-medium text-gray-900">
                                {{ log.description }}
                            </td>
                            <td class="px-5 py-4">
                                <div v-if="log.causer" class="flex flex-col">
                                    <span class="text-xs font-semibold text-gray-900">{{ log.causer.name }}</span>
                                    <span class="text-[10px] text-gray-500">
                                        {{ log.causer.email }} ·
                                        <span class="font-mono capitalize">{{
                                            log.causer.role
                                        }}</span>
                                    </span>
                                </div>
                                <span v-else class="text-xs text-gray-400 italic">Sistem</span>
                            </td>
                        </tr>
                        <tr v-if="!currentData.length">
                            <td colspan="5" class="px-5 py-12 text-center text-gray-400">
                                Belum ada aktivitas yang tercatat.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="totalPages > 1" class="px-4">
                <PaginationBar :current-page="currentPage" :total-pages="totalPages" :loading="loading"
                    @go="goToPage" />
            </div>
        </div>
    </div>
</template>
