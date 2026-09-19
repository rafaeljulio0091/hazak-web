<script setup>
import { ref } from 'vue';
import Sidebar from '@/Components/Sidebar.vue';
import Topbar from '@/Components/Topbar.vue';

const sidebarCollapsed = ref(false);
const mobileSidebarOpen = ref(false);
</script>

<template>
    <div class="flex min-h-screen bg-hazak-black">
        <Sidebar
            :collapsed="sidebarCollapsed"
            :open="mobileSidebarOpen"
            @toggle-collapse="sidebarCollapsed = !sidebarCollapsed"
            @close="mobileSidebarOpen = false"
        />

        <div class="flex min-w-0 flex-1 flex-col">
            <Topbar @toggle-sidebar="mobileSidebarOpen = true" />

            <header v-if="$slots.header" class="border-b border-white/10 bg-hazak-graphite/40 px-4 py-5 sm:px-6 lg:px-8">
                <slot name="header" />
            </header>

            <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                <slot />
            </main>
        </div>
    </div>
</template>
