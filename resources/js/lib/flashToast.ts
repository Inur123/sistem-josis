import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import type { FlashToast } from '@/types/ui';

interface InertiaPageProps {
    flash: {
        toast: FlashToast | null;
    };
    [key: string]: unknown;
}

let lastShownToast: { type: string; message: string } | null = null;

export function initializeFlashToast(): void {
    router.on('success', (event) => {
        const pageProps = event.detail.page
            .props as unknown as InertiaPageProps;
        const flash = pageProps.flash;
        const data = flash?.toast;

        if (!data || !data.type || !data.message) {
            // Jika flash kosong, reset data toast terakhir agar notifikasi yang sama di masa depan tetap muncul
            lastShownToast = null;

            return;
        }

        // Abaikan jika notifikasi ini sama persis dengan yang baru saja ditampilkan
        if (lastShownToast && lastShownToast.type === data.type && lastShownToast.message === data.message) {
            return;
        }

        // Catat sebagai toast terakhir yang ditampilkan
        lastShownToast = { type: data.type, message: data.message };

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
