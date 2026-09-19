<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

// Set to false once a real, licensed photo is placed at
// public/images/landing/hero.jpg (see the "pending" note shipped with
// this feature). Until then we fall back to a pure gradient so there is
// never a broken-image icon on the homepage.
const heroImageAvailable = ref(true);
</script>

<template>
    <section class="relative isolate flex min-h-[85vh] items-center overflow-hidden">
        <!-- Background photo (community / city / safety) -->
        <img
            v-show="heroImageAvailable"
            src="/images/landing/hero.jpg"
            alt=""
            class="absolute inset-0 -z-20 h-full w-full object-cover"
            fetchpriority="high"
            decoding="async"
            @error="heroImageAvailable = false"
        />

        <!-- Fallback / always-on treatment: dark + blue gradient wash -->
        <div class="absolute inset-0 -z-10 bg-gradient-to-b from-hazak-black/70 via-hazak-black/85 to-hazak-black"></div>
        <div class="absolute inset-0 -z-10 bg-[radial-gradient(ellipse_at_top,_rgba(0,174,239,0.18),_transparent_60%)]"></div>
        <div v-if="!heroImageAvailable" class="absolute inset-0 -z-20 bg-gradient-to-br from-hazak-graphite via-hazak-black to-[#061024]"></div>

        <div class="mx-auto w-full max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
            <div class="max-w-2xl">
                <span
                    class="inline-flex items-center gap-2 rounded-full border border-hazak-neon/30 bg-hazak-neon/10 px-3 py-1 text-xs font-medium text-hazak-neon-cyan"
                >
                    <span class="h-1.5 w-1.5 rounded-full bg-hazak-neon"></span>
                    Tecnologia a serviço da sua segurança
                </span>

                <h1 class="mt-6 text-4xl font-bold leading-tight tracking-tight text-white sm:text-5xl lg:text-6xl">
                    Tecnologia para uma
                    <span class="bg-gradient-to-r from-hazak-blue to-hazak-neon bg-clip-text text-transparent">
                        comunidade mais segura.
                    </span>
                </h1>

                <p class="mt-6 max-w-xl text-lg leading-relaxed text-slate-300">
                    O Hazak conecta tecnologia, pessoas e recursos de segurança em uma
                    única plataforma, ajudando você a permanecer conectado e protegido
                    onde estiver.
                </p>

                <div class="mt-10 flex flex-col gap-3 sm:flex-row sm:items-center">
                    <Link
                        :href="route('register')"
                        class="inline-flex items-center justify-center rounded-xl bg-hazak-blue px-6 py-3.5 text-sm font-semibold text-white shadow-glow-blue transition duration-150 ease-in-out hover:bg-hazak-blue-hover motion-safe:hover:scale-[1.02]"
                    >
                        Criar minha conta
                    </Link>
                    <a
                        href="#download"
                        class="inline-flex items-center justify-center rounded-xl border border-white/15 bg-white/5 px-6 py-3.5 text-sm font-semibold text-white backdrop-blur transition duration-150 ease-in-out hover:border-white/30 hover:bg-white/10 motion-safe:hover:scale-[1.02]"
                    >
                        Baixar o app
                    </a>
                </div>

                <p class="mt-4 text-sm text-slate-400">Em breve para Android e iOS.</p>
            </div>
        </div>
    </section>
</template>
