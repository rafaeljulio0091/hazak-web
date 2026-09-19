<script setup>
import { useRevealOnScroll } from '@/Composables/useRevealOnScroll';

const { target, visible } = useRevealOnScroll();

// Set these once the app is actually published; until then the badges
// render as a disabled "Em breve" state instead of a broken link.
const playStoreUrl = null;
const appStoreUrl = null;

const stores = [
    {
        name: 'Google Play',
        url: playStoreUrl,
        icon: 'M3 3l10.5 9L3 21V3z M13.5 12l3.5-2 3.11 1.8c.86.5.86 1.9 0 2.4L17 16.2l-3.5-2v-2.2z M13.5 12L3 3l14 6.5-3.5 2.5z M13.5 12L3 21l14-6.5-3.5-2.5z',
    },
    {
        name: 'App Store',
        url: appStoreUrl,
        icon: 'M16.365 1.43c0 1.14-.493 2.27-1.177 3.08-.744.9-1.99 1.57-2.987 1.57-.12 0-.23-.02-.3-.03-.017-.12-.052-.5-.052-.86 0-1.14.572-2.27 1.207-2.98.744-.85 2.03-1.5 3.07-1.55.02.17.03.3.03.43M20.5 17.02c-.6 1.38-.9 2-1.6 3.22-.98 1.7-2.36 3.8-4.08 3.82-1.53.02-1.92-1-3.99-1-2.08 0-2.5.98-4 1-1.65.02-2.9-1.85-3.9-3.55C.5 17.13-.5 12.3 1.14 9.02c.9-1.8 2.5-2.94 4.24-2.96 1.51-.03 2.94 1.02 3.87 1.02.92 0 2.65-1.26 4.47-1.08.76.03 2.9.31 4.28 2.33-.11.07-2.55 1.5-2.53 4.46.03 3.54 3.1 4.72 3.13 4.73-.03.1-.5 1.7-1.6 3.5',
    },
];
</script>

<template>
    <section
        id="download"
        ref="target"
        class="scroll-mt-20 bg-hazak-black py-24 transition-all duration-700"
        :class="visible ? 'translate-y-0 opacity-100' : 'translate-y-6 opacity-0'"
    >
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Baixe o Hazak</h2>
            <p class="mt-4 text-slate-400">Leve a segurança do Hazak para o seu bolso.</p>

            <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <template v-for="store in stores" :key="store.name">
                    <a
                        v-if="store.url"
                        :href="store.url"
                        class="flex w-64 items-center gap-3 rounded-xl border border-white/15 bg-white/5 px-5 py-3.5 text-left transition duration-150 ease-in-out hover:border-hazak-neon/30 hover:bg-white/10 motion-safe:hover:scale-[1.02]"
                    >
                        <svg class="h-7 w-7 shrink-0 text-white" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path :d="store.icon" />
                        </svg>
                        <span>
                            <span class="block text-xs text-slate-400">Disponível em breve na</span>
                            <span class="block text-sm font-semibold text-white">{{ store.name }}</span>
                        </span>
                    </a>

                    <span
                        v-else
                        class="flex w-64 cursor-not-allowed items-center gap-3 rounded-xl border border-white/10 bg-white/[0.02] px-5 py-3.5 text-left opacity-60"
                        :aria-label="`${store.name} — em breve`"
                    >
                        <svg class="h-7 w-7 shrink-0 text-slate-500" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path :d="store.icon" />
                        </svg>
                        <span>
                            <span class="block text-xs text-slate-500">Em breve na</span>
                            <span class="block text-sm font-semibold text-slate-300">{{ store.name }}</span>
                        </span>
                    </span>
                </template>
            </div>
        </div>
    </section>
</template>
