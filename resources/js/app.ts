import { createInertiaApp } from '@inertiajs/vue3';
import { configureEcho } from '@laravel/echo-vue';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

if (typeof window !== 'undefined') {
    // Prevent Echo crashes if connection is blocked or misconfigured
    const mockChannel = () => ({
        listen: () => mockChannel(),
        listenToAll: () => mockChannel(),
        notification: () => mockChannel(),
        whisper: () => mockChannel(),
    });
    (window as any).Echo = (window as any).Echo || {
        channel: mockChannel,
        private: mockChannel,
        join: mockChannel,
        leave: () => {},
        leaveChannel: () => {},
        connector: { options: {} }
    };

    const broadcaster = import.meta.env.VITE_BROADCAST_CONNECTION === 'pusher' ? 'pusher' : 'reverb';

    try {
        configureEcho({
            broadcaster: broadcaster,
        });
    } catch (e) {
        console.warn('Laravel Echo configuration failed, using fallback mock Echo.', e);
    }
}

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === 'Welcome':
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            default:
                return AppLayout;
        }
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();
