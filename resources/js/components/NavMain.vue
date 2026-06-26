<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import type { NavItem } from '@/types';

defineProps<{
    items: NavItem[];
}>();

const page = usePage();
const { isCurrentUrl, isCurrentOrParentUrl } = useCurrentUrl();
const { isMobile, setOpenMobile } = useSidebar();

function handleLinkClick() {
    if (isMobile.value) {
        setOpenMobile(false);
    }
}

function isActive(href: any) {
    if (isCurrentUrl(href)) {
        return true;
    }

    const hrefStr = typeof href === 'string' ? href : String(href);
    const currentPath = page.url;

    // Keep "Data Pemilih" active when viewing detail or editing, but not when creating new data
    if (hrefStr.endsWith('/pemilih')) {
        return (
            currentPath.startsWith(hrefStr) &&
            !currentPath.includes('/pemilih/create')
        );
    }

    // For other menus, support subpaths unless it is the dashboard
    if (!hrefStr.endsWith('/dashboard')) {
        return isCurrentOrParentUrl(href);
    }

    return false;
}
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton
                    as-child
                    :is-active="isActive(item.href)"
                    :tooltip="item.title"
                >
                    <Link :href="item.href" @click="handleLinkClick">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
