<script setup>
import { ref } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

defineEmits(['toggle-sidebar']);

const user = usePage().props.auth.user;

const systemStatuses = {
    operational: { label: 'Operacional', dot: 'bg-emerald-400' },
    degraded: { label: 'Instabilidade', dot: 'bg-amber-400' },
    critical: { label: 'Crítico', dot: 'bg-hazak-critical' },
};

const systemStatus = ref('operational');

const notifications = ref([
    { id: 1, title: 'Novo alerta SOS', time: 'há 2 min', unread: true },
    { id: 2, title: 'Ocorrência #4821 atualizada', time: 'há 18 min', unread: true },
    { id: 3, title: 'Usuário monitorado offline', time: 'há 1 h', unread: false },
]);
</script>

<template>
    <header
        class="sticky top-0 z-20 flex h-16 shrink-0 items-center gap-3 border-b border-white/10 bg-hazak-graphite/80 px-4 backdrop-blur sm:px-6"
    >
        <button
            type="button"
            class="-ml-1 flex h-9 w-9 items-center justify-center rounded-xl text-slate-400 transition duration-150 ease-in-out hover:bg-white/5 hover:text-slate-100 focus:outline-none focus:ring-2 focus:ring-hazak-blue/50 lg:hidden"
            aria-label="Abrir menu de navegação"
            @click="$emit('toggle-sidebar')"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
            </svg>
        </button>

        <div class="relative hidden flex-1 max-w-md sm:block">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
            </span>
            <input
                type="search"
                placeholder="Buscar ocorrências, usuários..."
                aria-label="Buscar"
                class="w-full rounded-xl border border-white/10 bg-hazak-black/60 py-2 pl-9 pr-3 text-sm text-slate-100 placeholder-slate-500 transition duration-150 ease-in-out focus:border-hazak-blue focus:outline-none focus:ring-2 focus:ring-hazak-blue/40"
            />
        </div>

        <div class="ml-auto flex items-center gap-2 sm:gap-3">
            <label class="relative hidden sm:block">
                <span class="sr-only">Status do sistema</span>
                <select
                    v-model="systemStatus"
                    class="appearance-none rounded-xl border border-white/10 bg-hazak-black/60 py-2 pl-7 pr-8 text-xs font-medium text-slate-200 transition duration-150 ease-in-out focus:border-hazak-blue focus:outline-none focus:ring-2 focus:ring-hazak-blue/40"
                >
                    <option v-for="(meta, key) in systemStatuses" :key="key" :value="key">
                        {{ meta.label }}
                    </option>
                </select>
                <span
                    class="pointer-events-none absolute left-2.5 top-1/2 h-2 w-2 -translate-y-1/2 rounded-full"
                    :class="systemStatuses[systemStatus].dot"
                    aria-hidden="true"
                ></span>
            </label>

            <Dropdown align="right" width="72" content-classes="py-2 bg-hazak-graphite">
                <template #trigger>
                    <button
                        type="button"
                        class="relative flex h-9 w-9 items-center justify-center rounded-xl text-slate-400 transition duration-150 ease-in-out hover:bg-white/5 hover:text-slate-100 focus:outline-none focus:ring-2 focus:ring-hazak-blue/50"
                        aria-label="Notificações"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"
                            />
                        </svg>
                        <span
                            v-if="notifications.some((n) => n.unread)"
                            class="absolute right-2 top-2 h-2 w-2 rounded-full bg-hazak-critical"
                            aria-hidden="true"
                        ></span>
                    </button>
                </template>

                <template #content>
                    <div class="w-72 max-w-[80vw]">
                        <div class="border-b border-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Notificações
                        </div>
                        <ul>
                            <li
                                v-for="notification in notifications"
                                :key="notification.id"
                                class="flex items-start gap-2 px-4 py-2.5 text-sm hover:bg-white/5"
                            >
                                <span
                                    class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full"
                                    :class="notification.unread ? 'bg-hazak-blue' : 'bg-transparent'"
                                    aria-hidden="true"
                                ></span>
                                <div>
                                    <p class="text-slate-200">{{ notification.title }}</p>
                                    <p class="text-xs text-slate-500">{{ notification.time }}</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </template>
            </Dropdown>

            <Dropdown align="right" width="48">
                <template #trigger>
                    <button
                        type="button"
                        class="flex items-center gap-2 rounded-xl border border-white/10 px-2 py-1.5 transition duration-150 ease-in-out hover:bg-white/5 focus:outline-none focus:ring-2 focus:ring-hazak-blue/50"
                    >
                        <span
                            class="flex h-7 w-7 items-center justify-center rounded-full bg-hazak-blue/20 text-xs font-semibold text-hazak-blue-hover"
                            aria-hidden="true"
                        >
                            {{ user.name?.charAt(0).toUpperCase() }}
                        </span>
                        <span class="hidden text-sm font-medium text-slate-200 sm:block">{{ user.name }}</span>
                        <svg class="hidden h-4 w-4 text-slate-500 sm:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                </template>

                <template #content>
                    <DropdownLink :href="route('profile.edit')">Perfil</DropdownLink>
                    <DropdownLink :href="route('logout')" method="post" as="button">Sair</DropdownLink>
                </template>
            </Dropdown>
        </div>
    </header>
</template>
