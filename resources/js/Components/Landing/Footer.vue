<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';

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

const year = new Date().getFullYear();
</script>

<template>
    <footer class="border-t border-white/10 bg-hazak-black">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-10 sm:grid-cols-3">
                <div>
                    <Link href="/" class="flex items-center gap-2.5">
                        <ApplicationLogo class="h-7 w-7 fill-current text-hazak-blue" />
                        <span class="text-base font-bold tracking-tight text-white">HAZAK</span>
                    </Link>
                    <p class="mt-4 max-w-xs text-sm text-slate-400">
                        Tecnologia conectando pessoas e contribuindo para uma comunidade
                        mais segura.
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Navegação</p>
                    <ul class="mt-4 space-y-2 text-sm">
                        <li><a href="/" class="text-slate-400 transition hover:text-white">Home</a></li>
                        <li><a href="#sobre" class="text-slate-400 transition hover:text-white">Sobre nós</a></li>
                        <li><a href="#download" class="text-slate-400 transition hover:text-white">Baixar o app</a></li>
                        <li v-if="canLogin"><Link :href="route('login')" class="text-slate-400 transition hover:text-white">Entrar</Link></li>
                        <li v-if="canRegister"><Link :href="route('register')" class="text-slate-400 transition hover:text-white">Criar conta</Link></li>
                    </ul>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Legal</p>
                    <ul class="mt-4 space-y-2 text-sm">
                        <li class="flex items-center gap-2 text-slate-500">
                            Política de Privacidade
                            <span class="rounded-full bg-white/5 px-2 py-0.5 text-[10px] uppercase tracking-wide text-slate-500">Em breve</span>
                        </li>
                        <li class="flex items-center gap-2 text-slate-500">
                            Termos de Uso
                            <span class="rounded-full bg-white/5 px-2 py-0.5 text-[10px] uppercase tracking-wide text-slate-500">Em breve</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="mt-12 border-t border-white/10 pt-6 text-center text-xs text-slate-500">
                &copy; {{ year }} Hazak. Todos os direitos reservados.
            </div>
        </div>
    </footer>
</template>
