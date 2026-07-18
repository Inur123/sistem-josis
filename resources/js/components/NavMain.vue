<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronRight } from '@lucide/vue';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
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
    if (!href) {
        return false;
    }

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

function isGroupActive(item: NavItem): boolean {
    if (!item.children) {
return false;
}

    return item.children.some((child) => child.href && isActive(child.href));
}
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu>
            <template v-for="item in items" :key="item.title">
                <!-- Item dengan children (dropdown) -->
                <Collapsible
                    v-if="item.children && item.children.length > 0"
                    as-child
                    :default-open="isGroupActive(item)"
                    class="group/collapsible"
                >
                    <SidebarMenuItem>
                        <CollapsibleTrigger as-child>
                            <SidebarMenuButton
                                :is-active="isGroupActive(item)"
                                :tooltip="item.title"
                                class="cursor-pointer"
                            >
                                <component :is="item.icon" v-if="item.icon" />
                                <span>{{ item.title }}</span>
                                <ChevronRight
                                    class="ml-auto h-4 w-4 transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90"
                                />
                            </SidebarMenuButton>
                        </CollapsibleTrigger>
                        <CollapsibleContent>
                            <SidebarMenuSub>
                                <SidebarMenuSubItem
                                    v-for="child in item.children"
                                    :key="child.title"
                                >
                                    <SidebarMenuSubButton
                                        as-child
                                        :is-active="child.href ? isActive(child.href) : false"
                                    >
                                        <Link
                                            :href="child.href ?? '#'"
                                            @click="handleLinkClick"
                                        >
                                            <component :is="child.icon" v-if="child.icon" />
                                            <span>{{ child.title }}</span>
                                        </Link>
                                    </SidebarMenuSubButton>
                                </SidebarMenuSubItem>
                            </SidebarMenuSub>
                        </CollapsibleContent>
                    </SidebarMenuItem>
                </Collapsible>

                <!-- Item biasa (tanpa children) -->
                <SidebarMenuItem v-else>
                    <SidebarMenuButton
                        as-child
                        :is-active="item.href ? isActive(item.href) : false"
                        :tooltip="item.title"
                    >
                        <Link :href="item.href ?? '#'" @click="handleLinkClick">
                            <component :is="item.icon" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </template>
        </SidebarMenu>
    </SidebarGroup>
</template>
