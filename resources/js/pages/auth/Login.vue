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
        description: 'Silakan masuk menggunakan akun Josis Anda untuk melanjutkan.',
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
            class="fixed top-4 left-1/2 -translate-x-1/2 z-50 w-[calc(100%-2rem)] max-w-md bg-white/95 border border-amber-100 backdrop-blur-md rounded-2xl shadow-xl p-4 flex items-center justify-between gap-4"
        >
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 shrink-0 rounded-xl bg-linear-to-tr from-amber-500 to-yellow-400 flex items-center justify-center p-1 border border-yellow-200/50 shadow-inner">
                    <img src="/images/pwa-192x192.png" alt="Logo" class="h-full w-full object-contain rounded-lg" />
                </div>
                <div class="text-left">
                    <h4 class="text-sm font-semibold text-gray-900">Pasang Aplikasi Josis</h4>
                    <p class="text-xs text-gray-500">Akses cepat & stabil langsung dari Home Screen</p>
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
                    <X class="h-4 w-4" />
                </button>
            </div>
        </div>
    </Transition>

    <Head title="Login" />

    <div
        v-if="status"
        class="mb-4 text-center text-sm font-semibold text-green-600 bg-green-50 border border-green-100 rounded-xl p-3"
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
                <Label for="email" class="text-xs font-bold text-gray-700 tracking-wide uppercase">Alamat Email</Label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
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
                        class="w-full pl-10.5 h-12! rounded-xl border border-gray-200 focus-visible:border-blue-500! focus-visible:ring-0! focus-visible:ring-offset-0! focus-visible:outline-none! transition-all duration-200 bg-gray-50/20 shadow-none"
                    />
                </div>
                <InputError :message="errors.email" />
            </div>

            <!-- Password Field -->
            <div class="grid gap-2">
                <div class="flex items-center justify-between">
                    <Label for="password" class="text-xs font-bold text-gray-700 tracking-wide uppercase">Kata Sandi</Label>
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 z-10">
                        <Lock class="h-4.5 w-4.5" />
                    </div>
                    <PasswordInput
                        id="password"
                        name="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        placeholder="Masukkan password Anda"
                        class="w-full pl-10.5 h-12! rounded-xl border border-gray-250 focus-visible:border-blue-500! focus-visible:ring-0! focus-visible:ring-offset-0! focus-visible:outline-none! transition-all duration-200 bg-gray-50/20 shadow-none"
                    />
                </div>
                <InputError :message="errors.password" />
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                class="mt-4 w-full bg-[#FCD116] hover:bg-[#E0B810] text-gray-950 font-bold py-3 px-4 rounded-xl transition-all duration-300 transform hover:-translate-y-0.5 shadow-md shadow-yellow-500/10 hover:shadow-yellow-500/25 active:translate-y-0 flex items-center justify-center gap-2 border border-[#E0B810]/30 cursor-pointer"
                :tabindex="3"
                :disabled="processing"
            >
                <Spinner v-if="processing" class="text-gray-950 h-5 w-5" />
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
    border-color: #FCD116 !important; /* Golkar Yellow */
    transition: background-color 5000s ease-in-out 0s;
}
</style>


