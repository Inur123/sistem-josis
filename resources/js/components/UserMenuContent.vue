<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { LogOut } from '@lucide/vue';
import {
    DropdownMenuItem,
    DropdownMenuLabel,
} from '@/components/ui/dropdown-menu';
import UserInfo from '@/components/UserInfo.vue';
import { logout } from '@/routes';
import type { User } from '@/types';

type Props = {
    user: User;
    showProfileLabel?: boolean;
};

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const props = withDefaults(defineProps<Props>(), {
    showProfileLabel: true,
});

const handleLogout = () => {
    router.flushAll();
};
</script>

<template>
    <DropdownMenuLabel v-if="showProfileLabel" class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" :responsive-mode="false" />
        </div>
    </DropdownMenuLabel>

    <DropdownMenuItem :as-child="true">
        <Link class="block w-full cursor-pointer" :href="logout()" @click="handleLogout" as="button"
            data-test="logout-button">
            <LogOut class="mr-2 h-4 w-4" />
            Log out
        </Link>
    </DropdownMenuItem>
</template>
