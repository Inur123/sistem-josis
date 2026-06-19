import type { ComputedRef, Ref } from 'vue';
import { computed, ref } from 'vue';
import type { Appearance, ResolvedAppearance } from '@/types';

export type { Appearance, ResolvedAppearance };

export type UseAppearanceReturn = {
    appearance: Ref<Appearance>;
    resolvedAppearance: ComputedRef<ResolvedAppearance>;
    updateAppearance: () => void;
};

export function updateTheme(): void {
    if (typeof window === 'undefined') {
        return;
    }

    // Always force light mode — remove dark class
    document.documentElement.classList.remove('dark');
}

export function initializeTheme(): void {
    if (typeof window === 'undefined') {
        return;
    }

    // Force light mode — clear any saved preference and remove dark class
    localStorage.setItem('appearance', 'light');
    document.documentElement.classList.remove('dark');
}

const appearance = ref<Appearance>('light');

export function useAppearance(): UseAppearanceReturn {
    const resolvedAppearance = computed<ResolvedAppearance>(() => 'light');

    function updateAppearance() {
        // Always stay on light mode
        appearance.value = 'light';
        localStorage.setItem('appearance', 'light');
        document.documentElement.classList.remove('dark');
    }

    return {
        appearance,
        resolvedAppearance,
        updateAppearance,
    };
}
