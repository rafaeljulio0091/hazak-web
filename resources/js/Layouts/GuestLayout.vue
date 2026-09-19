<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen bg-hazak-black">
        <!-- Brand panel -->
        <div
            class="relative hidden w-full max-w-xl flex-col justify-between overflow-hidden bg-gradient-to-br from-hazak-graphite via-hazak-black to-[#0A1730] p-12 lg:flex"
        >
            <div
                class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-full bg-hazak-blue/20 blur-3xl"
            ></div>
            <div
                class="pointer-events-none absolute -bottom-32 -left-16 h-80 w-80 rounded-full bg-hazak-blue/10 blur-3xl"
            ></div>

            <Link href="/" class="relative flex items-center gap-3">
                <ApplicationLogo class="h-10 w-10 fill-current text-white" />
                <span class="text-xl font-bold tracking-tight text-white">Hazak</span>
            </Link>

            <div class="relative">
                <span
                    class="inline-flex items-center gap-2 rounded-full border border-hazak-blue/30 bg-hazak-blue/10 px-3 py-1 text-xs font-medium text-hazak-blue-hover"
                >
                    <span class="h-1.5 w-1.5 rounded-full bg-hazak-blue"></span>
                    Monitoramento ativo 24/7
                </span>

                <h1 class="mt-6 max-w-md text-3xl font-bold leading-tight tracking-tight text-white">
                    Segurança pessoal com resposta imediata.
                </h1>

                <p class="mt-4 max-w-sm text-sm leading-relaxed text-slate-400">
                    Acompanhe ocorrências, monitore usuários e coordene respostas
                    de emergência em um único painel confiável.
                </p>
            </div>

            <p class="relative text-xs text-slate-500">
                &copy; {{ new Date().getFullYear() }} Hazak. Todos os direitos reservados.
            </p>
        </div>

        <!-- Auth card -->
        <div class="flex w-full flex-1 flex-col items-center justify-center px-6 py-12 sm:px-10">
            <Link href="/" class="mb-8 flex items-center gap-3 lg:hidden">
                <ApplicationLogo class="h-9 w-9 fill-current text-white" />
                <span class="text-lg font-bold tracking-tight text-white">Hazak</span>
            </Link>

            <div
                class="w-full max-w-sm rounded-2xl border border-white/10 bg-hazak-graphite p-6 sm:p-8"
            >
                <slot />
            </div>
        </div>
    </div>
</template>
