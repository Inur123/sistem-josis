<script setup lang="ts">
/**
 * PaginationBar — Komponen pagination reusable dengan ellipsis.
 *
 * Tampilkan: < Previous  1  2  ...  6  7  8  ...  11  12  Next >
 */
import { ChevronLeft, ChevronRight } from '@lucide/vue';
import { computed } from 'vue';

const props = defineProps<{
    currentPage: number;
    totalPages: number;
    loading?: boolean;
}>();

const emit = defineEmits<{
    (e: 'go', page: number): void;
}>();

type PageItem = number | '...';

// Buat list nomor halaman dengan ellipsis otomatis
const pageItems = computed<PageItem[]>(() => {
    const current = props.currentPage;
    const total = props.totalPages;

    // Jika total halaman ≤ 7, tampilkan semua tanpa ellipsis
    if (total <= 7) {
        return Array.from({ length: total }, (_, i) => i + 1);
    }

    // Delta = jumlah halaman di sekitar halaman aktif
    const delta = 2;
    const left = current - delta;
    const right = current + delta;

    // Kumpulkan halaman yang perlu ditampilkan: halaman pertama, terakhir, dan sekitar aktif
    const range: number[] = [];

    for (let i = 1; i <= total; i++) {
        if (i === 1 || i === total || (i >= left && i <= right)) {
            range.push(i);
        }
    }

    // Sisipkan '...' di antara angka yang tidak berurutan
    const result: PageItem[] = [];
    let prev: number | undefined;

    for (const i of range) {
        if (prev !== undefined && i - prev > 1) {
            result.push('...');
        }

        result.push(i);
        prev = i;
    }

    return result;
});
</script>

<template>
    <!-- Mobile Pagination (Prev - Page Indicator - Next) -->
    <div
        class="flex items-center justify-between border-t border-gray-100 px-2 pt-4 sm:hidden"
    >
        <button
            :disabled="currentPage === 1 || loading"
            @click="emit('go', currentPage - 1)"
            class="flex items-center gap-1 rounded-xl border border-gray-100 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 transition-all duration-150 active:bg-gray-50"
            :class="
                currentPage === 1 || loading
                    ? 'cursor-not-allowed opacity-50'
                    : 'hover:bg-gray-50'
            "
        >
            <ChevronLeft class="h-3.5 w-3.5" /> Prev
        </button>

        <span class="text-xs font-semibold text-gray-500">
            Hal {{ currentPage }} / {{ totalPages }}
        </span>

        <button
            :disabled="currentPage === totalPages || loading"
            @click="emit('go', currentPage + 1)"
            class="flex items-center gap-1 rounded-xl border border-gray-100 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 transition-all duration-150 active:bg-gray-50"
            :class="
                currentPage === totalPages || loading
                    ? 'cursor-not-allowed opacity-50'
                    : 'hover:bg-gray-50'
            "
        >
            Next <ChevronRight class="h-3.5 w-3.5" />
        </button>
    </div>

    <!-- Desktop Pagination -->
    <div
        class="hidden sm:flex items-center justify-end gap-1 border-t border-gray-100 px-2 pt-4"
    >
        <!-- Previous -->
        <button
            :disabled="currentPage === 1 || loading"
            @click="emit('go', currentPage - 1)"
            class="flex items-center gap-1 px-2 py-2 text-sm font-medium transition-all duration-150"
            :class="
                currentPage === 1 || loading
                    ? 'cursor-not-allowed text-gray-300'
                    : 'text-gray-600 hover:text-gray-900'
            "
        >
            <ChevronLeft class="h-4 w-4" /> Previous
        </button>

        <!-- Nomor Halaman + Ellipsis -->
        <template v-for="(item, idx) in pageItems" :key="idx">
            <!-- Ellipsis -->
            <span
                v-if="item === '...'"
                class="px-1 py-2 text-sm text-gray-400 select-none"
            >
                ...
            </span>

            <!-- Tombol Halaman -->
            <button
                v-else
                @click="emit('go', item)"
                :disabled="loading"
                :class="[
                    'flex min-w-[36px] items-center justify-center rounded-xl px-3 py-2 text-sm font-medium transition-all duration-150',
                    currentPage === item
                        ? 'border border-gray-100 bg-gray-50 text-gray-900'
                        : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900',
                    loading ? 'cursor-not-allowed opacity-50' : '',
                ]"
            >
                {{ item }}
            </button>
        </template>

        <!-- Next -->
        <button
            :disabled="currentPage === totalPages || loading"
            @click="emit('go', currentPage + 1)"
            class="flex items-center gap-1 px-2 py-2 text-sm font-medium transition-all duration-150"
            :class="
                currentPage === totalPages || loading
                    ? 'cursor-not-allowed text-gray-300'
                    : 'text-gray-600 hover:text-gray-900'
            "
        >
            Next <ChevronRight class="h-4 w-4" />
        </button>
    </div>
</template>
