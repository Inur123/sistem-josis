<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { ChevronDown } from '@lucide/vue';
import { ref, computed } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { SidebarTrigger } from '@/components/ui/sidebar';
import UserInfo from '@/components/UserInfo.vue';
import UserMenuContent from '@/components/UserMenuContent.vue';
import type { BreadcrumbItem } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const page = usePage();
const user = computed(() => page.props.auth.user);
const isDropdownOpen = ref(false);
</script>

<template>
    <header
        class="sticky top-0 z-10 flex h-16 shrink-0 items-center justify-between border-b border-sidebar-border/70 bg-white/95 px-6 backdrop-blur-sm transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4">
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>

        <!-- Right Side: Profile Dropdown -->
        <div class="flex items-center">
            <DropdownMenu @update:open="(open) => isDropdownOpen = open">
                <DropdownMenuTrigger as-child>
                    <button type="button"
                        class="flex items-center gap-2 rounded-lg p-1.5 transition-colors hover:bg-gray-150 cursor-pointer focus:outline-none">
                        <div class="flex items-center gap-2">
                            <!-- AvatarFallback / AvatarImage is inside UserInfo -->
                            <UserInfo :user="user" />
                        </div>
                        <ChevronDown :class="[
                            'h-4 w-4 text-gray-500 shrink-0 transition-transform duration-200',
                            isDropdownOpen ? 'rotate-180 text-amber-500' : ''
                        ]" />
                    </button>
                </DropdownMenuTrigger>
                <DropdownMenuContent class="w-48 rounded-lg" align="end" :side-offset="8">
                    <UserMenuContent :user="user" :show-profile-label="false" />
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    </header>
</template>
