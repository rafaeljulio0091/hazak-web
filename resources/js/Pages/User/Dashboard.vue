<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeviceMap from '@/Components/DeviceMap.vue';
import EmptyState from '@/Components/EmptyState.vue';
import StatCard from '@/Components/StatCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { useDevicesTracking } from '@/Composables/useDeviceTracking';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({
    devices: {
        type: Array,
        required: true, // [{ id, label, position }]
    },
    firebaseToken: {
        type: String,
        required: true,
    },
});

// A device is considered online when it reported within this window.
const ONLINE_WINDOW_MS = 5 * 60 * 1000;

const refreshToken = async () => {
    const { data } = await axios.post(route('tracking.token'));
    return data.token;
};

const { positions, errors, status, start } = useDevicesTracking(
    props.devices.map((device) => device.id),
    {
        token: props.firebaseToken,
        refreshToken,
        initialPositions: Object.fromEntries(props.devices.map((device) => [device.id, device.position])),
    },
);

const labels = computed(() =>
    Object.fromEntries(props.devices.map((device) => [device.id, device.label || device.id])),
);

const now = ref(Date.now());
let clock = null;

const isOnline = (position) => Boolean(position?.updatedAt) && now.value - position.updatedAt <= ONLINE_WINDOW_MS;

const rows = computed(() =>
    props.devices.map((device) => {
        const position = positions.value[device.id] ?? null;

        let badge = 'idle';
        if (errors.value[device.id]) badge = 'critical';
        else if (isOnline(position)) badge = 'active';
        else if (position) badge = 'warning';

        return {
            id: device.id,
            label: labels.value[device.id],
            position,
            badge,
            lastUpdate: position?.updatedAt ? new Date(position.updatedAt).toLocaleString('pt-BR') : '—',
        };
    }),
);

const onlineCount = computed(() => rows.value.filter((row) => row.badge === 'active').length);

const lastUpdateLabel = computed(() => {
    const latest = Math.max(0, ...rows.value.map((row) => row.position?.updatedAt ?? 0));
    return latest ? new Date(latest).toLocaleTimeString('pt-BR') : '—';
});

const badgeLabel = {
    active: 'Online',
    warning: 'Sem sinal recente',
    critical: 'Sem permissão',
    idle: 'Sem posição',
};

onMounted(() => {
    start();
    clock = setInterval(() => {
        now.value = Date.now();
    }, 30 * 1000);
});

onBeforeUnmount(() => clearInterval(clock));
</script>

<template>
    <Head title="Meus dispositivos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold tracking-tight text-white">Meus dispositivos</h2>
                    <p class="mt-1 text-sm text-slate-400">
                        Localização em tempo real dos dispositivos compartilhados com você.
                    </p>
                </div>
                <span
                    v-if="devices.length"
                    class="hidden items-center gap-2 rounded-full border px-3 py-1 text-xs font-medium sm:inline-flex"
                    :class="status === 'error'
                        ? 'border-hazak-critical/30 bg-hazak-critical/10 text-hazak-critical'
                        : 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400'"
                >
                    <span class="h-1.5 w-1.5 rounded-full bg-current" :class="{ 'animate-pulse': status === 'connecting' }"></span>
                    {{ status === 'error' ? 'Falha na conexão' : status === 'live' ? 'Ao vivo' : 'Conectando...' }}
                </span>
            </div>
        </template>

        <div v-if="!devices.length" class="rounded-2xl border border-white/10 bg-hazak-graphite">
            <EmptyState
                title="Nenhum dispositivo compartilhado"
                description="Quando alguém compartilhar a localização de um dispositivo com você, ele aparecerá aqui."
            />
        </div>

        <div v-else class="space-y-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <StatCard label="Dispositivos compartilhados" :value="devices.length" />
                <StatCard label="Online agora" :value="onlineCount" hint="posição nos últimos 5 min" />
                <StatCard label="Última atualização" :value="lastUpdateLabel" />
            </div>

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                <div class="overflow-hidden rounded-2xl border border-white/10 xl:col-span-2">
                    <DeviceMap :positions="positions" :labels="labels" />
                </div>

                <div class="flex flex-col rounded-2xl border border-white/10 bg-hazak-graphite">
                    <div class="border-b border-white/10 px-5 py-4">
                        <h3 class="text-sm font-semibold text-white">Dispositivos</h3>
                    </div>

                    <ul class="flex-1 divide-y divide-white/5 overflow-y-auto">
                        <li v-for="row in rows" :key="row.id">
                            <Link
                                :href="route('my.devices.show', row.id)"
                                class="flex items-center justify-between gap-3 px-5 py-3.5 transition duration-150 ease-in-out hover:bg-white/[0.03] focus:outline-none focus:ring-2 focus:ring-inset focus:ring-hazak-blue/50"
                            >
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-slate-200">{{ row.label }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ row.lastUpdate }}</p>
                                </div>
                                <StatusBadge class="shrink-0" :status="row.badge" :label="badgeLabel[row.badge]" />
                            </Link>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
