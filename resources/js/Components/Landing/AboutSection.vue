<script setup>
import { useRevealOnScroll } from '@/Composables/useRevealOnScroll';

const { target, visible } = useRevealOnScroll();

const pillars = [
    {
        label: 'Proteção',
        icon: 'M12 2.25c-.966 0-1.914.075-2.84.219a1.5 1.5 0 00-1.264 1.483v.665c-1.68.298-3.267.824-4.729 1.542a1.5 1.5 0 00-.833 1.34v3.038c0 5.25 3.75 9.75 9.666 11.372 5.917-1.622 9.667-6.122 9.667-11.372V7.5a1.5 1.5 0 00-.834-1.341A18.79 18.79 0 0016 4.617v-.665a1.5 1.5 0 00-1.263-1.483A19.23 19.23 0 0012 2.25z',
    },
    {
        label: 'Conectividade',
        icon: 'M8.288 15.038a5.25 5.25 0 017.424 0M5.106 11.856a9.75 9.75 0 0113.788 0M1.924 8.674a14.25 14.25 0 0120.152 0M12 20.25h.008v.008H12v-.008z',
    },
    {
        label: 'Tecnologia',
        icon: 'M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 20.25a48.25 48.25 0 01-8.135-.687c-1.718-.293-2.3-2.379-1.067-3.61L5 14.5',
    },
    {
        label: 'Comunidade',
        icon: 'M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z',
    },
];
</script>

<template>
    <section
        id="sobre"
        ref="target"
        class="scroll-mt-20 border-t border-white/5 bg-hazak-graphite py-24 transition-all duration-700"
        :class="visible ? 'translate-y-0 opacity-100' : 'translate-y-6 opacity-0'"
    >
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                    Segurança através da tecnologia
                </h2>
                <p class="mt-6 text-lg leading-relaxed text-slate-300">
                    O Hazak foi criado para utilizar tecnologia e conectividade como
                    ferramentas de apoio à segurança e proteção das pessoas. Criamos uma
                    experiência simples e acessível, reunindo recursos que ajudam
                    usuários a permanecerem conectados com pessoas importantes nos
                    momentos em que isso mais importa.
                </p>
            </div>

            <div class="mx-auto mt-14 grid max-w-4xl grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
                <div
                    v-for="pillar in pillars"
                    :key="pillar.label"
                    class="flex flex-col items-center gap-3 rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-6 text-center transition duration-150 ease-in-out motion-safe:hover:scale-[1.02] hover:border-hazak-neon/30"
                >
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-hazak-neon/10 text-hazak-neon-cyan">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="pillar.icon" />
                        </svg>
                    </span>
                    <p class="text-sm font-semibold text-white">{{ pillar.label }}</p>
                </div>
            </div>
        </div>
    </section>
</template>
