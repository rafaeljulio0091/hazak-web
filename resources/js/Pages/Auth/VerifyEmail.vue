<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Verificação de e-mail" />

        <div class="mb-6">
            <h2 class="text-xl font-bold tracking-tight text-white">Verifique seu e-mail</h2>
            <p class="mt-2 text-sm text-slate-400">
                Obrigado por se cadastrar! Antes de começar, confirme seu e-mail
                clicando no link que acabamos de enviar. Não recebeu? Podemos
                enviar outro.
            </p>
        </div>

        <div
            v-if="verificationLinkSent"
            class="mb-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-2.5 text-sm font-medium text-emerald-400"
        >
            Um novo link de verificação foi enviado para o e-mail cadastrado.
        </div>

        <form @submit.prevent="submit" class="flex items-center justify-between gap-4">
            <PrimaryButton
                :class="{ 'opacity-70': form.processing }"
                :disabled="form.processing"
            >
                Reenviar e-mail de verificação
            </PrimaryButton>

            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="rounded-md text-sm font-medium text-slate-400 underline-offset-4 transition duration-150 ease-in-out hover:text-slate-200 hover:underline focus:outline-none focus:ring-2 focus:ring-hazak-blue/50"
            >
                Sair
            </Link>
        </form>
    </GuestLayout>
</template>
