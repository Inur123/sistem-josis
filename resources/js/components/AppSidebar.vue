<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import {
    LayoutDashboard,
    Users,
    ClipboardList,
    UserCog,
    Activity,
    UserPlus,
    MapPin,
    X,
} from '@lucide/vue';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    useSidebar,
} from '@/components/ui/sidebar';
import admin from '@/routes/admin';
import desa from '@/routes/desa';
import kecamatan from '@/routes/kecamatan';
import type { NavItem } from '@/types';

const page = usePage();
const role = computed(() => (page.props.auth as any)?.user?.role ?? '');
const { isMobile, setOpenMobile } = useSidebar();


// Nav items per role
const adminNav: NavItem[] = [
    { title: 'Dashboard', href: admin.dashboard.url(), icon: LayoutDashboard },
    {
        title: 'Data Pemilih',
        href: admin.pemilih.index.url(),
        icon: ClipboardList,
    },
    { title: 'Kelola Akun', href: admin.akun.index.url(), icon: UserCog },
    { title: 'Kelola Tim', href: admin.tim.index.url(), icon: Users },
    { title: 'Data Relawan', href: admin.relawan.index.url(), icon: Users },
    { title: 'Data TPS', href: admin.tps.index.url(), icon: MapPin },
    { title: 'Log Aktivitas', href: admin.activityLogs.url(), icon: Activity },
];


const kecamatanNav: NavItem[] = [
    {
        title: 'Dashboard',
        href: kecamatan.dashboard.url(),
        icon: LayoutDashboard,
    },
    {
        title: 'Data Pemilih',
        href: kecamatan.pemilih.index.url(),
        icon: ClipboardList,
    },
    {
        title: 'Data Relawan',
        href: kecamatan.relawan.index.url(),
        icon: Users,
    },
    {
        title: 'TPS',
        icon: MapPin,
        children: [
            { title: 'Data TPS & Suara', href: kecamatan.tps.index.url() },
        ],
    },
];

const desaNav: NavItem[] = [
    { title: 'Dashboard', href: desa.dashboard.url(), icon: LayoutDashboard },
    {
        title: 'Data Pemilih',
        href: desa.pemilih.index.url(),
        icon: ClipboardList,
    },
    { title: 'Tambah Data', href: desa.pemilih.create.url(), icon: UserPlus },
    { title: 'Data Relawan', href: desa.relawan.index.url(), icon: Users },
    {
        title: 'TPS',
        icon: MapPin,
        children: [
            { title: 'Data TPS', href: desa.tps.index.url() },
            { title: 'Data Suara', href: desa.dataSuara.index.url() },
        ],
    },
];

const mainNavItems = computed<NavItem[]>(() => {
    if (role.value === 'admin') {
        return adminNav;
    }

    if (role.value === 'kecamatan') {
        return kecamatanNav;
    }

    if (role.value === 'desa') {
        return desaNav;
    }

    return [];
});

</script>

<template>
    <Sidebar collapsible="icon" variant="sidebar">
        <SidebarHeader class="relative">
            <div class="flex h-12 items-center px-2 py-1.5">
                <AppLogo />
            </div>
            <!-- Mobile Close Button (X) -->
            <button v-if="isMobile" @click="setOpenMobile(false)"
                class="absolute right-4 top-1/2 -translate-y-1/2 flex h-8 w-8 items-center justify-center rounded-lg hover:bg-black/10 text-gray-800 transition active:scale-95"
                title="Tutup menu">
                <X class="h-5 w-5" />
            </button>
        </SidebarHeader>

        <SidebarContent class="relative">
            <!-- Watermark Logo -->
            <div
                class="absolute bottom-6 left-1/2 -translate-x-1/2 w-48 h-48 opacity-10 pointer-events-none select-none z-0">
                <img src="/images/logo_golkar.svg" class="w-full h-full object-contain" alt="Watermark" />
            </div>
            <div class="relative z-10">
                <NavMain :items="mainNavItems" />
            </div>
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
