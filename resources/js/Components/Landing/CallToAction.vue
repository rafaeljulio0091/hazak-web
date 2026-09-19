<script setup>
import { Link } from '@inertiajs/vue3';
import { useRevealOnScroll } from '@/Composables/useRevealOnScroll';

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

const { target, visible } = useRevealOnScroll();
</script>

<template>
    <section
        ref="target"
        class="relative overflow-hidden border-t border-white/5 bg-hazak-graphite py-24 transition-all duration-700"
        :class="visible ? 'translate-y-0 opacity-100' : 'translate-y-6 opacity-0'"
    >
        <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-full bg-[radial-gradient(ellipse_at_center,_rgba(30,107,255,0.14),_transparent_65%)]"></div>

        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Comece agora com o Hazak</h2>
            <p class="mt-4 text-lg text-slate-300">
                Crie sua conta e tenha acesso aos recursos disponíveis na plataforma.
            </p>

            <div class="mt-10 flex flex-col justify-center gap-3 sm:flex-row">
                <Link
                    v-if="canRegister"
                    :href="route('register')"
                    class="inline-flex items-center justify-center rounded-xl bg-hazak-blue px-6 py-3.5 text-sm font-semibold text-white shadow-glow-blue transition duration-150 ease-in-out hover:bg-hazak-blue-hover motion-safe:hover:scale-[1.02]"
                >
                    Criar conta
                </Link>
                <Link
                    v-if="canLogin"
                    :href="route('login')"
                    class="inline-flex items-center justify-center rounded-xl border border-white/15 bg-white/5 px-6 py-3.5 text-sm font-semibold text-white backdrop-blur transition duration-150 ease-in-out hover:border-white/30 hover:bg-white/10 motion-safe:hover:scale-[1.02]"
                >
                    Entrar
                </Link>
            </div>
        </div>
    </section>
</template>
