<script setup>
import { useRevealOnScroll } from '@/Composables/useRevealOnScroll';

const { target, visible } = useRevealOnScroll();

const features = [
    {
        title: 'Conecte-se',
        description: 'Mantenha pessoas importantes próximas através da tecnologia.',
        icon: 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',
    },
    {
        title: 'Compartilhe',
        description: 'Utilize recursos de localização e comunicação quando necessário.',
        icon: 'M15 10.5a3 3 0 11-6 0 3 3 0 016 0z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z',
    },
    {
        title: 'Proteja',
        description: 'Tenha ferramentas de apoio disponíveis em momentos importantes.',
        icon: 'M12 2.25c-.966 0-1.914.075-2.84.219a1.5 1.5 0 00-1.264 1.483v.665c-1.68.298-3.267.824-4.729 1.542a1.5 1.5 0 00-.833 1.34v3.038c0 5.25 3.75 9.75 9.666 11.372 5.917-1.622 9.667-6.122 9.667-11.372V7.5a1.5 1.5 0 00-.834-1.341A18.79 18.79 0 0016 4.617v-.665a1.5 1.5 0 00-1.263-1.483A19.23 19.23 0 0012 2.25z',
    },
    {
        title: 'Tenha controle',
        description: 'Gerencie seus recursos de segurança através de uma interface simples.',
        icon: 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.02-.397-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.28z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
    },
];
</script>

<template>
    <section
        ref="target"
        class="scroll-mt-20 bg-hazak-black py-24 transition-all duration-700"
        :class="visible ? 'translate-y-0 opacity-100' : 'translate-y-6 opacity-0'"
    >
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Como o Hazak ajuda</h2>
                <p class="mt-4 text-slate-400">
                    Recursos de apoio à segurança, pensados para o seu dia a dia.
                </p>
            </div>

            <div class="mt-14 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="feature in features"
                    :key="feature.title"
                    class="group rounded-2xl border border-white/10 bg-hazak-graphite p-6 transition duration-300 ease-in-out motion-safe:hover:scale-[1.02] hover:border-hazak-blue/40 hover:shadow-glow-blue"
                >
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-hazak-blue/10 text-hazak-blue-hover transition duration-300 group-hover:bg-hazak-blue/20">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="feature.icon" />
                        </svg>
                    </span>
                    <h3 class="mt-4 text-base font-semibold text-white">{{ feature.title }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-400">{{ feature.description }}</p>
                </div>
            </div>
        </div>
    </section>
</template>
