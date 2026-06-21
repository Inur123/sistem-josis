<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutDashboard, Users, ClipboardList, UserCog, Activity, UserPlus } from '@lucide/vue';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import admin from '@/routes/admin';
import desa from '@/routes/desa';
import kecamatan from '@/routes/kecamatan';
import type { NavItem } from '@/types';

const page = usePage();
const role = computed(() => (page.props.auth as any)?.user?.role ?? '');
const { isMobile, setOpenMobile } = useSidebar();

function handleLinkClick() {
    if (isMobile.value) {
        setOpenMobile(false);
    }
}

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

const dashboardHref = computed(() => {
    if (role.value === 'admin') {
        return admin.dashboard.url();
    }

    if (role.value === 'kecamatan') {
        return kecamatan.dashboard.url();
    }

    if (role.value === 'desa') {
        return desa.dashboard.url();
    }

    return dashboard.url();
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboardHref" @click="handleLinkClick">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
