<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="block w-full rounded-xl border border-white/10 bg-hazak-black/60 px-3.5 py-2.5 text-sm text-slate-100 placeholder-slate-500 shadow-sm transition duration-150 ease-in-out focus:border-hazak-blue focus:outline-none focus:ring-2 focus:ring-hazak-blue/40"
        v-model="model"
        ref="input"
    />
</template>
