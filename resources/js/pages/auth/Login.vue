<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { X, Lock, Mail } from '@lucide/vue';
import { onMounted, onUnmounted, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/login';

defineOptions({
    layout: {
        title: 'Selamat Datang',
        description:
            'Silakan masuk menggunakan akun Josis Anda untuk melanjutkan.',
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
    window.removeEventListener(
        'beforeinstallprompt',
        handleBeforeInstallPrompt,
    );
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
    <!-- PWA Install Banner (Premium Floating Banner) -->
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
            class="fixed top-4 left-1/2 z-50 flex w-[calc(100%-2rem)] max-w-md -translate-x-1/2 items-center justify-between gap-4 rounded-2xl border border-amber-100 bg-white/95 p-4 shadow-xl backdrop-blur-md"
        >
            <div class="flex items-center gap-3">
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-yellow-200/50 bg-linear-to-tr from-amber-500 to-yellow-400 p-1 shadow-inner"
                >
                    <img
                        src="/images/pwa-192x192.png"
                        alt="Logo"
                        class="h-full w-full rounded-lg object-contain"
                    />
                </div>
                <div class="text-left">
                    <h4 class="text-sm font-semibold text-gray-900">
                        Pasang Aplikasi Josis
                    </h4>
                    <p class="text-xs text-gray-500">
                        Akses cepat & stabil langsung dari Home Screen
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    @click="installApp"
                    class="rounded-xl bg-gray-900 px-3 py-2 text-xs font-semibold text-white shadow-sm transition-all hover:bg-gray-800"
                >
                    Pasang
                </button>
                <button
                    type="button"
                    @click="dismissBanner"
                    class="rounded-lg p-1.5 text-gray-400 transition-all hover:bg-gray-50 hover:text-gray-600"
                >
                    <X class="h-4 w-4" />
                </button>
            </div>
        </div>
    </Transition>

    <Head title="Login" />

    <div
        v-if="status"
        class="mb-4 rounded-xl border border-green-100 bg-green-50 p-3 text-center text-sm font-semibold text-green-600"
    >
        {{ status }}
    </div>

    <Form
        v-bind="(store as any).form()"
        :reset-on-success="['password']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <div class="grid gap-5">
            <!-- Email Field -->
            <div class="grid gap-2">
                <Label
                    for="email"
                    class="text-xs font-bold tracking-wide text-gray-700 uppercase"
                    >Alamat Email</Label
                >
                <div class="relative">
                    <div
                        class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400"
                    >
                        <Mail class="h-4.5 w-4.5" />
                    </div>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        placeholder="Masukkan email terdaftar"
                        class="h-12! w-full rounded-xl border border-gray-200 bg-gray-50/20 pl-10.5 shadow-none transition-all duration-200 focus-visible:border-blue-500! focus-visible:ring-0! focus-visible:ring-offset-0! focus-visible:outline-none!"
                    />
                </div>
                <InputError :message="errors.email" />
            </div>

            <!-- Password Field -->
            <div class="grid gap-2">
                <div class="flex items-center justify-between">
                    <Label
                        for="password"
                        class="text-xs font-bold tracking-wide text-gray-700 uppercase"
                        >Kata Sandi</Label
                    >
                </div>
                <div class="relative">
                    <div
                        class="pointer-events-none absolute inset-y-0 left-0 z-10 flex items-center pl-3.5 text-gray-400"
                    >
                        <Lock class="h-4.5 w-4.5" />
                    </div>
                    <PasswordInput
                        id="password"
                        name="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        placeholder="Masukkan password Anda"
                        class="border-gray-250 h-12! w-full rounded-xl border bg-gray-50/20 pl-10.5 shadow-none transition-all duration-200 focus-visible:border-blue-500! focus-visible:ring-0! focus-visible:ring-offset-0! focus-visible:outline-none!"
                    />
                </div>
                <InputError :message="errors.password" />
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                class="mt-4 flex w-full transform cursor-pointer items-center justify-center gap-2 rounded-xl border border-[#E0B810]/30 bg-[#FCD116] px-4 py-3 font-bold text-gray-950 shadow-md shadow-yellow-500/10 transition-all duration-300 hover:-translate-y-0.5 hover:bg-[#E0B810] hover:shadow-yellow-500/25 active:translate-y-0"
                :tabindex="3"
                :disabled="processing"
            >
                <Spinner v-if="processing" class="h-5 w-5 text-gray-950" />
                <span>Masuk ke Akun</span>
            </button>
        </div>
    </Form>
</template>

<style scoped>
/* Custom style to override browser autofill background and text color to match Golkar theme */
input:-webkit-autofill,
input:-webkit-autofill:hover,
input:-webkit-autofill:focus,
input:-webkit-autofill:active {
    -webkit-box-shadow: 0 0 0 30px #fffdf0 inset !important; /* soft yellow background */
    -webkit-text-fill-color: #111827 !important;
    border-color: #fcd116 !important; /* Golkar Yellow */
    transition: background-color 5000s ease-in-out 0s;
}
</style>
