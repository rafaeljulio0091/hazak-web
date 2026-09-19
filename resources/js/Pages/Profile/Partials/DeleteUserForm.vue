<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-semibold text-white">
                Excluir conta
            </h2>

            <p class="mt-1 text-sm text-slate-400">
                Após a exclusão, todos os recursos e dados da conta serão
                permanentemente apagados. Baixe qualquer dado que deseje manter
                antes de continuar.
            </p>
        </header>

        <DangerButton @click="confirmUserDeletion">Excluir conta</DangerButton>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-white">
                    Tem certeza que deseja excluir sua conta?
                </h2>

                <p class="mt-1 text-sm text-slate-400">
                    Após a exclusão, todos os recursos e dados da conta serão
                    permanentemente apagados. Digite sua senha para confirmar
                    que deseja excluir a conta permanentemente.
                </p>

                <div class="mt-6">
                    <InputLabel for="password" value="Senha" class="sr-only" />

                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1.5 block w-3/4"
                        placeholder="Senha"
                        @keyup.enter="deleteUser"
                    />

                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="closeModal">Cancelar</SecondaryButton>

                    <DangerButton
                        :class="{ 'opacity-70': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        Excluir conta
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </section>
</template>
