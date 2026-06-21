<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { X } from '@lucide/vue';
import { onMounted, onUnmounted, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/login';

defineOptions({
    layout: {
        title: 'Masuk ke akun Anda',
        description: 'Masukkan email dan password untuk login',
    },
});

defineProps<{
    status?: string;
}>();

// PWA Install Prompt State
const deferredPrompt = ref<any>(null);
const showBanner = ref(false);

const handleBeforeInstallPrompt = (e: Event) => {
    e.preventDefault();
    deferredPrompt.value = e;
    showBanner.value = true;
};

onMounted(() => {
    window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
    window.addEventListener('appinstalled', () => {
        deferredPrompt.value = null;
        showBanner.value = false;
    });
});

onUnmounted(() => {
    window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
});

async function installApp() {
    if (!deferredPrompt.value) {
        return;
    }

    deferredPrompt.value.prompt();

    const { outcome } = await deferredPrompt.value.userChoice;

    if (outcome === 'accepted') {
        deferredPrompt.value = null;
        showBanner.value = false;
    }
}

function dismissBanner() {
    showBanner.value = false;
}
</script>

<template>
    <!-- PWA Install Banner (Top-Down Pop-up) -->
    <Transition
        enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="-translate-y-full opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="-translate-y-full opacity-0"
    >
        <div
            v-if="showBanner"
            class="fixed top-4 left-1/2 -translate-x-1/2 z-50 w-[calc(100%-2rem)] max-w-md bg-white border border-gray-100 rounded-2xl shadow-xl p-4 flex items-center justify-between gap-4"
        >
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 flex-shrink-0 rounded-xl bg-gray-50 flex items-center justify-center p-1 border border-gray-100">
                    <img src="/pwa-192x192.png" alt="Logo" class="h-full w-full object-contain rounded-lg" />
                </div>
                <div class="text-left">
                    <h4 class="text-sm font-semibold text-gray-900">Pasang Aplikasi Josis</h4>
                    <p class="text-xs text-gray-500">Instal di HP untuk akses lebih cepat</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    @click="installApp"
                    class="bg-gray-900 hover:bg-gray-800 text-white text-xs font-semibold px-3 py-2 rounded-xl transition-all shadow-sm"
                >
                    Pasang
                </button>
                <button
                    type="button"
                    @click="dismissBanner"
                    class="text-gray-400 hover:text-gray-600 p-1.5 hover:bg-gray-50 rounded-lg transition-all"
                >
                    <X class="h-4.5 w-4.5" />
                </button>
            </div>
        </div>
    </Transition>

    <Head title="Login" />

    <div
        v-if="status"
        class="mb-4 text-center text-sm font-medium text-green-600"
    >
        {{ status }}
    </div>

    <Form
        v-bind="(store as any).form()"
        :reset-on-success="['password']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <div class="grid gap-6">
            <div class="grid gap-2">
                <Label for="email">Email</Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="email"
                    placeholder="email@example.com"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="password">Password</Label>
                <PasswordInput
                    id="password"
                    name="password"
                    required
                    :tabindex="2"
                    autocomplete="current-password"
                    placeholder="Password"
                />
                <InputError :message="errors.password" />
            </div>

            <Button
                type="submit"
                class="mt-4 w-full"
                :tabindex="3"
                :disabled="processing"
                data-test="login-button"
            >
                <Spinner v-if="processing" />
                Login
            </Button>
        </div>
    </Form>
</template>
