<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    collapsed: {
        type: Boolean,
        default: false,
    },
    open: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['toggle-collapse', 'close']);

const navItems = [
    {
        label: 'Dashboard',
        route: 'dashboard',
        icon: 'M3.75 6a2.25 2.25 0 012.25-2.25h12A2.25 2.25 0 0120.25 6v12a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18V6zM3.75 10.5h16.5M10.5 6v14.25',
    },
    {
        label: 'Ocorrências',
        icon: 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-8.25 3.75h.008v.008h-.008v-.008z',
        disabled: true,
    },
    {
        label: 'Usuários',
        icon: 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
        disabled: true,
    },
    {
        label: 'Alertas',
        icon: 'M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0',
        disabled: true,
    },
    {
        label: 'Perfil',
        route: 'profile.edit',
        icon: 'M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z',
    },
];
</script>

<template>
    <div
        v-show="open"
        class="fixed inset-0 z-40 bg-hazak-black/70 backdrop-blur-sm lg:hidden"
        @click="$emit('close')"
    ></div>

    <aside
        class="fixed inset-y-0 left-0 z-50 flex h-full w-64 flex-col border-r border-white/10 bg-hazak-graphite transition-all duration-200 ease-in-out lg:sticky lg:top-0 lg:z-30 lg:h-screen lg:translate-x-0"
        :class="[open ? 'translate-x-0' : '-translate-x-full', collapsed ? 'lg:w-20' : 'lg:w-64']"
    >
        <div class="flex h-16 shrink-0 items-center gap-3 border-b border-white/10 px-4">
            <Link :href="route('dashboard')" class="flex items-center gap-3 overflow-hidden">
                <ApplicationLogo class="h-8 w-8 shrink-0 fill-current text-hazak-blue" />
                <span
                    class="whitespace-nowrap text-lg font-bold tracking-tight text-white transition-opacity duration-150"
                    :class="collapsed ? 'lg:hidden' : ''"
                >
                    Hazak
                </span>
            </Link>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
            <template v-for="item in navItems" :key="item.label">
                <Link
                    v-if="!item.disabled"
                    :href="route(item.route)"
                    class="group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition duration-150 ease-in-out"
                    :class="
                        route().current(item.route)
                            ? 'bg-hazak-blue/10 text-white'
                            : 'text-slate-400 hover:bg-white/5 hover:text-slate-100'
                    "
                >
                    <span
                        v-if="route().current(item.route)"
                        class="absolute inset-y-1 left-0 w-0.5 rounded-full bg-hazak-blue"
                        aria-hidden="true"
                    ></span>

                    <svg
                        class="h-5 w-5 shrink-0"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="1.5"
                        aria-hidden="true"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                    </svg>

                    <span class="whitespace-nowrap" :class="collapsed ? 'lg:hidden' : ''">
                        {{ item.label }}
                    </span>
                </Link>

                <span
                    v-else
                    class="flex cursor-not-allowed items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600"
                    :title="`${item.label} — em breve`"
                >
                    <svg
                        class="h-5 w-5 shrink-0"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="1.5"
                        aria-hidden="true"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                    </svg>

                    <span class="flex flex-1 items-center justify-between whitespace-nowrap" :class="collapsed ? 'lg:hidden' : ''">
                        {{ item.label }}
                        <span class="rounded-full bg-white/5 px-2 py-0.5 text-[10px] uppercase tracking-wide text-slate-500">
                            Em breve
                        </span>
                    </span>
                </span>
            </template>
        </nav>

        <div class="shrink-0 border-t border-white/10 p-3">
            <button
                type="button"
                class="hidden w-full items-center justify-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-400 transition duration-150 ease-in-out hover:bg-white/5 hover:text-slate-100 focus:outline-none focus:ring-2 focus:ring-hazak-blue/50 lg:flex"
                :aria-label="collapsed ? 'Expandir menu' : 'Recolher menu'"
                @click="$emit('toggle-collapse')"
            >
                <svg
                    class="h-5 w-5 shrink-0 transition-transform duration-200"
                    :class="{ 'rotate-180': collapsed }"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="1.5"
                    aria-hidden="true"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                </svg>
                <span :class="collapsed ? 'lg:hidden' : ''">Recolher</span>
            </button>
        </div>
    </aside>
</template>
