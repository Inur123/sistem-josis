import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import type { FlashToast } from '@/types/ui';

interface InertiaPageProps {
    flash: {
        toast: FlashToast | null;
    };
    [key: string]: unknown;
}

export function initializeFlashToast(): void {
    router.on('success', (event) => {
        const pageProps = event.detail.page.props as unknown as InertiaPageProps;
        const flash = pageProps.flash;
        const data = flash?.toast;

        if (!data || !data.type || !data.message) {
            return;
        }

        const type = data.type;

        if (type === 'success') {
            toast.success(data.message);
        } else if (type === 'error') {
            toast.error(data.message);
        } else if (type === 'warning') {
            toast.warning(data.message);
        } else {
            toast.info(data.message);
        }
    });
}
