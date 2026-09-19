<script setup>
import { onMounted, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import EmptyState from '@/Components/EmptyState.vue';
import StatCard from '@/Components/StatCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head } from '@inertiajs/vue3';

const loading = ref(true);

const metrics = ref([
    { label: 'Ocorrências ativas', value: '12', tone: 'critical', trend: 'up', trendLabel: '+3 na última hora' },
    { label: 'Usuários monitorados', value: '1.284', hint: 'em 6 regiões' },
    { label: 'Tempo médio de resposta', value: '3m 42s', trend: 'down', trendLabel: '-18s vs. ontem' },
    { label: 'Alertas nas últimas 24h', value: '47', hint: '8 críticos' },
]);

const occurrenceColumns = [
    { key: 'id', label: 'ID' },
    { key: 'user', label: 'Usuário' },
    { key: 'type', label: 'Tipo' },
    { key: 'location', label: 'Local' },
    { key: 'time', label: 'Horário' },
    { key: 'status', label: 'Status' },
];

const occurrences = ref([
    { id: '#4821', user: 'Marina Alves', type: 'Botão de pânico', location: 'Zona Sul, SP', time: '09:42', status: 'critical' },
    { id: '#4820', user: 'João Pereira', type: 'Rota fora do padrão', location: 'Centro, RJ', time: '09:31', status: 'warning' },
    { id: '#4819', user: 'Camila Ribeiro', type: 'Check-in atrasado', location: 'Zona Norte, SP', time: '09:15', status: 'active' },
    { id: '#4818', user: 'Rafael Souza', type: 'Queda detectada', location: 'Barra, RJ', time: '08:58', status: 'resolved' },
    { id: '#4817', user: 'Beatriz Lima', type: 'Botão de pânico', location: 'Pinheiros, SP', time: '08:40', status: 'resolved' },
]);

const activity = ref([
    { id: 1, text: 'Equipe despachada para a ocorrência #4821', time: 'agora' },
    { id: 2, text: 'Camila Ribeiro confirmou check-in de segurança', time: 'há 4 min' },
    { id: 3, text: 'Ocorrência #4818 marcada como resolvida', time: 'há 12 min' },
    { id: 4, text: 'Novo usuário monitorado adicionado: Beatriz Lima', time: 'há 26 min' },
]);

onMounted(() => {
    setTimeout(() => {
        loading.value = false;
    }, 800);
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold tracking-tight text-white">Dashboard</h2>
                    <p class="mt-1 text-sm text-slate-400">
                        Visão geral das ocorrências e do monitoramento em tempo real.
                    </p>
                </div>
                <span class="hidden items-center gap-2 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-xs font-medium text-emerald-400 sm:inline-flex">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                    Sistema operacional
                </span>
            </div>
        </template>

        <div class="space-y-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <StatCard
                    v-for="metric in metrics"
                    :key="metric.label"
                    v-bind="metric"
                    :loading="loading"
                />
            </div>

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                <div class="rounded-2xl border border-white/10 bg-hazak-graphite xl:col-span-2">
                    <div class="flex items-center justify-between border-b border-white/10 px-5 py-4">
                        <h3 class="text-sm font-semibold text-white">Ocorrências recentes</h3>
                        <button
                            type="button"
                            class="text-xs font-medium text-hazak-blue-hover transition duration-150 ease-in-out hover:text-hazak-blue focus:outline-none focus:ring-2 focus:ring-hazak-blue/50 focus:ring-offset-2 focus:ring-offset-hazak-graphite rounded-md"
                        >
                            Ver todas
                        </button>
                    </div>

                    <DataTable
                        :columns="occurrenceColumns"
                        :rows="occurrences"
                        :loading="loading"
                        empty-title="Nenhuma ocorrência recente"
                        empty-description="Novas ocorrências aparecerão aqui assim que forem registradas."
                    >
                        <template #cell-status="{ row }">
                            <StatusBadge :status="row.status" />
                        </template>
                    </DataTable>
                </div>

                <div class="flex flex-col rounded-2xl border border-white/10 bg-hazak-graphite">
                    <div class="flex items-center justify-between border-b border-white/10 px-5 py-4">
                        <h3 class="text-sm font-semibold text-white">Atividade em tempo real</h3>
                        <span class="flex items-center gap-1.5 text-xs text-slate-500">
                            <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-hazak-blue"></span>
                            ao vivo
                        </span>
                    </div>

                    <div v-if="loading" class="space-y-4 px-5 py-4" aria-hidden="true">
                        <div v-for="n in 4" :key="n" class="animate-pulse space-y-2">
                            <div class="h-3 w-full rounded bg-white/10"></div>
                            <div class="h-2.5 w-16 rounded bg-white/10"></div>
                        </div>
                    </div>

                    <ul v-else-if="activity.length" class="flex-1 divide-y divide-white/5 overflow-y-auto">
                        <li v-for="item in activity" :key="item.id" class="px-5 py-3.5">
                            <p class="text-sm text-slate-300">{{ item.text }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ item.time }}</p>
                        </li>
                    </ul>

                    <EmptyState
                        v-else
                        title="Sem atividade no momento"
                        description="Assim que algo acontecer, você verá aqui em tempo real."
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
