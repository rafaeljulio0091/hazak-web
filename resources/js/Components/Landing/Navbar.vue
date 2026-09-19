<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    canLogin: {
        type: Boolean,
        default: true,
    },
    canRegister: {
        type: Boolean,
        default: true,
    },
});

const mobileOpen = ref(false);

const navLinks = [
    { label: 'Home', href: '/' },
    { label: 'Sobre nós', href: '#sobre' },
    { label: 'Baixar o app', href: '#download' },
];

const closeMobile = () => {
    mobileOpen.value = false;
};
</script>

<template>
    <header class="sticky top-0 z-40 border-b border-white/10 bg-hazak-black/80 backdrop-blur">
        <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8" aria-label="Navegação principal">
            <Link href="/" class="flex items-center gap-2.5">
                <ApplicationLogo class="h-8 w-8 fill-current text-hazak-blue" />
                <span class="text-lg font-bold tracking-tight text-white">HAZAK</span>
            </Link>

            <div class="hidden items-center gap-8 md:flex">
                <a
                    v-for="link in navLinks"
                    :key="link.label"
                    :href="link.href"
                    class="text-sm font-medium text-slate-300 transition duration-150 ease-in-out hover:text-white"
                >
                    {{ link.label }}
                </a>
            </div>

            <div class="hidden items-center gap-3 md:flex">
                <Link
                    v-if="canLogin"
                    :href="route('login')"
                    class="rounded-xl border border-white/15 px-4 py-2 text-sm font-semibold text-slate-200 transition duration-150 ease-in-out hover:border-white/30 hover:bg-white/5 focus:outline-none focus:ring-2 focus:ring-hazak-blue/50"
                >
                    Entrar
                </Link>
                <Link
                    v-if="canRegister"
                    :href="route('register')"
                    class="rounded-xl bg-hazak-blue px-4 py-2 text-sm font-semibold text-white shadow-glow-blue transition duration-150 ease-in-out hover:bg-hazak-blue-hover motion-safe:hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-hazak-blue/50"
                >
                    Criar conta
                </Link>
            </div>

            <button
                type="button"
                class="flex h-10 w-10 items-center justify-center rounded-xl text-slate-300 transition duration-150 ease-in-out hover:bg-white/5 hover:text-white focus:outline-none focus:ring-2 focus:ring-hazak-blue/50 md:hidden"
                :aria-expanded="mobileOpen"
                aria-controls="landing-mobile-menu"
                :aria-label="mobileOpen ? 'Fechar menu' : 'Abrir menu'"
                @click="mobileOpen = !mobileOpen"
            >
                <svg v-if="!mobileOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                </svg>
                <svg v-else class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </nav>

        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-2"
        >
            <div
                v-show="mobileOpen"
                id="landing-mobile-menu"
                class="border-t border-white/10 bg-hazak-black px-4 py-4 md:hidden"
            >
                <div class="flex flex-col gap-1">
                    <a
                        v-for="link in navLinks"
                        :key="link.label"
                        :href="link.href"
                        class="rounded-xl px-3 py-2.5 text-sm font-medium text-slate-300 transition duration-150 ease-in-out hover:bg-white/5 hover:text-white"
                        @click="closeMobile"
                    >
                        {{ link.label }}
                    </a>
                </div>

                <div class="mt-4 flex flex-col gap-2 border-t border-white/10 pt-4">
                    <Link
                        v-if="canLogin"
                        :href="route('login')"
                        class="rounded-xl border border-white/15 px-4 py-2.5 text-center text-sm font-semibold text-slate-200 transition duration-150 ease-in-out hover:bg-white/5"
                    >
                        Entrar
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="rounded-xl bg-hazak-blue px-4 py-2.5 text-center text-sm font-semibold text-white shadow-glow-blue transition duration-150 ease-in-out hover:bg-hazak-blue-hover"
                    >
                        Criar conta
                    </Link>
                </div>
            </div>
        </Transition>
    </header>
</template>
