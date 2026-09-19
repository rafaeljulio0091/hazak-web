<script setup>
import { useRevealOnScroll } from '@/Composables/useRevealOnScroll';
import { ref } from 'vue';

const { target, visible } = useRevealOnScroll();

// Real screenshot of the Hazak dashboard rendered at mobile width — not a
// fabricated UI. Falls back to a plain phone-frame outline if missing.
const previewAvailable = ref(true);
</script>

<template>
    <section
        id="app"
        ref="target"
        class="scroll-mt-20 relative overflow-hidden border-t border-white/5 bg-hazak-graphite py-24 transition-all duration-700"
        :class="visible ? 'translate-y-0 opacity-100' : 'translate-y-6 opacity-0'"
    >
        <div class="pointer-events-none absolute right-0 top-1/2 -z-10 h-96 w-96 -translate-y-1/2 rounded-full bg-hazak-neon/10 blur-3xl"></div>

        <div class="mx-auto grid max-w-7xl grid-cols-1 items-center gap-12 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                    Segurança ao seu alcance
                </h2>
                <p class="mt-6 text-lg leading-relaxed text-slate-300">
                    Uma experiência desenvolvida para funcionar de maneira simples,
                    rápida e intuitiva diretamente no seu smartphone.
                </p>
            </div>

            <div class="flex justify-center">
                <div class="relative w-64 rounded-[2.5rem] border-4 border-white/10 bg-hazak-black p-2 shadow-glow-neon sm:w-72">
                    <div class="overflow-hidden rounded-[2rem] border border-white/10 bg-hazak-black">
                        <img
                            v-show="previewAvailable"
                            src="/images/landing/app-preview.png"
                            alt="Tela do painel Hazak exibida em um smartphone"
                            class="aspect-[9/19.5] w-full object-cover object-top"
                            loading="lazy"
                            decoding="async"
                            @error="previewAvailable = false"
                        />
                        <div
                            v-if="!previewAvailable"
                            class="flex aspect-[9/19.5] w-full items-center justify-center bg-gradient-to-b from-hazak-graphite to-hazak-black text-xs text-slate-500"
                        >
                            Pré-visualização do app
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
