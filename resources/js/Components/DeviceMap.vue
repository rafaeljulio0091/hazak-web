<script setup>
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';

/**
 * Leaflet map with one marker per device, kept in sync with `positions`
 * (deviceId => { latitude, longitude, ... } | null). With `follow`, the
 * map pans to the single device on every update; otherwise it fits all
 * markers once, on the first positions received.
 */
const props = defineProps({
    positions: {
        type: Object,
        required: true,
    },
    labels: {
        type: Object,
        default: () => ({}),
    },
    follow: {
        type: Boolean,
        default: false,
    },
    ariaLabel: {
        type: String,
        default: 'Mapa com a localização em tempo real dos dispositivos',
    },
});

const DEFAULT_CENTER = [-23.55, -46.63];

const mapEl = ref(null);
let map = null;
const markers = new Map();
let fitted = false;

const validEntries = () =>
    Object.entries(props.positions).filter(
        ([, position]) => Number.isFinite(position?.latitude) && Number.isFinite(position?.longitude),
    );

const sync = () => {
    if (!map) return;

    const entries = validEntries();
    const seen = new Set();

    entries.forEach(([deviceId, position]) => {
        const latLng = [position.latitude, position.longitude];
        seen.add(deviceId);

        if (markers.has(deviceId)) {
            markers.get(deviceId).setLatLng(latLng);
        } else {
            const marker = L.marker(latLng).addTo(map);
            marker.bindTooltip(props.labels[deviceId] || deviceId);
            markers.set(deviceId, marker);
        }
    });

    markers.forEach((marker, deviceId) => {
        if (!seen.has(deviceId)) {
            marker.remove();
            markers.delete(deviceId);
        }
    });

    if (!entries.length) return;

    if (props.follow) {
        const [, position] = entries[0];
        map.panTo([position.latitude, position.longitude]);
    } else if (!fitted) {
        map.fitBounds(
            L.latLngBounds(entries.map(([, p]) => [p.latitude, p.longitude])),
            { padding: [40, 40], maxZoom: 15 },
        );
        fitted = true;
    }
};

watch(() => props.positions, sync, { deep: true });

onMounted(() => {
    const [first] = validEntries();

    map = L.map(mapEl.value, {
        center: first ? [first[1].latitude, first[1].longitude] : DEFAULT_CENTER,
        zoom: 15,
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    sync();
});

onBeforeUnmount(() => {
    markers.clear();
    map?.remove();
    map = null;
});
</script>

<template>
    <div ref="mapEl" class="h-[28rem] w-full" role="img" :aria-label="ariaLabel"></div>
</template>
