<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import { ChevronLeft, ChevronRight } from '@lucide/vue';

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

interface LinkItem {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedLogs {
    data: ActivityLog[];
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
    next_page_url: string | null;
    prev_page_url: string | null;
    links: LinkItem[];
}

const props = defineProps<{
    logs: PaginatedLogs;
}>();

if (typeof window !== 'undefined') {
    useEcho('admin.activity-logs', 'ActivityLogged', (e: any) => {
        // Prepend new activity log in real-time
        // eslint-disable-next-line vue/no-mutating-props
        props.logs.data.unshift(e.activity);

        // Limit list to pagination size
        if (props.logs.data.length > props.logs.per_page) {
            // eslint-disable-next-line vue/no-mutating-props
            props.logs.data.pop();
        }
    });
}
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
        <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase">
                            <th class="px-5 py-3 w-[60px]">No</th>
                            <th class="px-5 py-3 w-[150px]">Waktu (WIB)</th>
                            <th class="px-5 py-3 w-[120px]">Aksi</th>
                            <th class="px-5 py-3">Deskripsi</th>
                            <th class="px-5 py-3 w-[250px]">Pelaku</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(log, i) in props.logs.data"
                            :key="log.id"
                            class="border-b border-gray-50 last:border-0 hover:bg-gray-50"
                        >
                            <td class="px-5 py-4 text-gray-400">
                                {{ (props.logs.current_page - 1) * props.logs.per_page + i + 1 }}
                            </td>
                            <td class="px-5 py-4 text-gray-600 font-mono text-xs whitespace-nowrap">
                                {{ log.created_at }}
                            </td>
                            <td class="px-5 py-4">
                                <span
                                    :class="[
                                        'inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold uppercase tracking-wide',
                                        log.event === 'created'
                                            ? 'bg-green-50 text-green-700 border border-green-200'
                                            : log.event === 'updated'
                                            ? 'bg-blue-50 text-blue-700 border border-blue-200'
                                            : log.event === 'deleted'
                                            ? 'bg-red-50 text-red-700 border border-red-200'
                                            : log.event === 'login'
                                            ? 'bg-purple-50 text-purple-700 border border-purple-200'
                                            : log.event === 'logout'
                                            ? 'bg-orange-50 text-orange-700 border border-orange-200'
                                            : 'bg-gray-50 text-gray-700 border border-gray-200'
                                    ]"
                                >
                                    {{ log.event || 'System' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-gray-900 font-medium">
                                {{ log.description }}
                            </td>
                            <td class="px-5 py-4">
                                <div v-if="log.causer" class="flex flex-col">
                                    <span class="font-semibold text-gray-900 text-xs">{{ log.causer.name }}</span>
                                    <span class="text-[10px] text-gray-500">{{ log.causer.email }} · <span class="capitalize font-mono">{{ log.causer.role }}</span></span>
                                </div>
                                <span v-else class="text-gray-400 italic text-xs">Sistem</span>
                            </td>
                        </tr>
                        <tr v-if="!props.logs.data.length">
                            <td colspan="5" class="px-5 py-12 text-center text-gray-400">
                                Belum ada aktivitas yang tercatat.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="props.logs.last_page > 1"
                class="flex items-center justify-end gap-1.5 border-t border-gray-100 px-6 py-4"
            >
                <template v-for="(link, index) in props.logs.links" :key="index">
                    <!-- Disabled prev/next or dots -->
                    <span
                        v-if="link.url === null"
                        class="px-2 py-2 text-sm text-gray-400 cursor-not-allowed flex items-center gap-1 select-none font-medium"
                    >
                        <template v-if="link.label.includes('Previous')">
                            <ChevronLeft class="w-4 h-4" /> Previous
                        </template>
                        <template v-else-if="link.label.includes('Next')">
                            Next <ChevronRight class="w-4 h-4" />
                        </template>
                        <template v-else>
                            {{ link.label }}
                        </template>
                    </span>

                    <!-- Active Page or Clickable links -->
                    <Link
                        v-else
                        :href="link.url"
                        :class="[
                            'text-sm transition-all duration-150 flex items-center justify-center font-medium',
                            link.label.includes('Previous') || link.label.includes('Next')
                                ? 'px-2 py-2 text-gray-600 hover:text-gray-900 gap-1'
                                : link.active
                                ? 'bg-gray-50 border border-gray-100 rounded-xl px-4 py-2 text-gray-900'
                                : 'px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-xl'
                        ]"
                    >
                        <template v-if="link.label.includes('Previous')">
                            <ChevronLeft class="w-4 h-4" /> Previous
                        </template>
                        <template v-else-if="link.label.includes('Next')">
                            Next <ChevronRight class="w-4 h-4" />
                        </template>
                        <template v-else>
                            {{ link.label }}
                        </template>
                    </Link>
                </template>
            </div>
        </div>
    </div>
</template>
