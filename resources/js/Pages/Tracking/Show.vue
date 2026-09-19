<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { useDeviceTracking } from '@/Composables/useDeviceTracking';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
    deviceId: {
        type: String,
        required: true,
    },
    firebaseToken: {
        type: String,
        required: true,
    },
    initialPosition: {
        type: Object,
        default: null,
    },
});

const refreshToken = async () => {
    const { data } = await axios.post(route('tracking.token'));
    return data.token;
};

const { position, status, error, start } = useDeviceTracking(props.deviceId, {
    token: props.firebaseToken,
    refreshToken,
    initialPosition: props.initialPosition,
});

const mapEl = ref(null);
let map = null;
let marker = null;

const statusMeta = computed(() => {
    if (status.value === 'live') return { badge: 'active', label: 'Recebendo posição' };
    if (status.value === 'error') return { badge: 'critical', label: 'Falha na conexão' };
    return { badge: 'warning', label: 'Conectando...' };
});

const lastUpdateLabel = computed(() => {
    if (!position.value?.updatedAt) return '—';
    return new Date(position.value.updatedAt).toLocaleTimeString('pt-BR');
});

watch(position, (value) => {
    if (!value || !map) return;

    const latLng = [value.latitude, value.longitude];

    if (!marker) {
        marker = L.marker(latLng).addTo(map);
    } else {
        marker.setLatLng(latLng);
    }

    map.panTo(latLng);
});

onMounted(() => {
    map = L.map(mapEl.value, {
        center: props.initialPosition
            ? [props.initialPosition.latitude, props.initialPosition.longitude]
            : [-23.55, -46.63],
        zoom: 15,
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    if (props.initialPosition) {
        marker = L.marker([props.initialPosition.latitude, props.initialPosition.longitude]).addTo(map);
    }

    start();
});

onBeforeUnmount(() => {
    map?.remove();
    map = null;
});
</script>

<template>
    <Head :title="`Rastreamento — ${deviceId}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold tracking-tight text-white">Rastreamento em tempo real</h2>
                    <p class="mt-1 text-sm text-slate-400">Dispositivo {{ deviceId }}</p>
                </div>
                <StatusBadge :status="statusMeta.badge" />
            </div>
        </template>

        <div class="space-y-4">
            <div v-if="error" class="rounded-xl border border-hazak-critical/30 bg-hazak-critical/10 px-4 py-3 text-sm text-hazak-critical">
                Não foi possível conectar ao Firebase. Verifique o token e as regras de segurança do Realtime Database.
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-white/10 bg-hazak-graphite p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Status</p>
                    <p class="mt-1 text-sm font-semibold text-slate-200">{{ statusMeta.label }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-hazak-graphite p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Última atualização</p>
                    <p class="mt-1 text-sm font-semibold text-slate-200">{{ lastUpdateLabel }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-hazak-graphite p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Coordenadas</p>
                    <p class="mt-1 text-sm font-semibold text-slate-200">
                        {{ position ? `${position.latitude.toFixed(5)}, ${position.longitude.toFixed(5)}` : '—' }}
                    </p>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-white/10">
                <div ref="mapEl" class="h-[28rem] w-full" role="img" aria-label="Mapa com a localização em tempo real do dispositivo"></div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
