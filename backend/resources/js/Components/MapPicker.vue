<script setup>
import { ref, onMounted, watch } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Fix Leaflet default marker icon
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
});

const props = defineProps({
    latitude: { type: [Number, String], default: null },
    longitude: { type: [Number, String], default: null },
});

const emit = defineEmits(['update:latitude', 'update:longitude']);

const mapContainer = ref(null);
let map = null;
let marker = null;

const defaultCenter = [-2.5, 118]; // Indonesia
const defaultZoom = 5;

function setMarker(lat, lng) {
    if (marker) {
        marker.setLatLng([lat, lng]);
    } else {
        marker = L.marker([lat, lng], { draggable: true }).addTo(map);
        marker.on('dragend', () => {
            const pos = marker.getLatLng();
            emit('update:latitude', pos.lat);
            emit('update:longitude', pos.lng);
        });
    }
    emit('update:latitude', lat);
    emit('update:longitude', lng);
}

onMounted(() => {
    const hasCoords = props.latitude && props.longitude;
    const center = hasCoords ? [parseFloat(props.latitude), parseFloat(props.longitude)] : defaultCenter;
    const zoom = hasCoords ? 15 : defaultZoom;

    map = L.map(mapContainer.value).setView(center, zoom);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    if (hasCoords) {
        setMarker(parseFloat(props.latitude), parseFloat(props.longitude));
    }

    map.on('click', (e) => {
        setMarker(e.latlng.lat, e.latlng.lng);
    });
});

watch(() => [props.latitude, props.longitude], ([lat, lng]) => {
    if (lat && lng && map) {
        const parsedLat = parseFloat(lat);
        const parsedLng = parseFloat(lng);
        if (marker) {
            const pos = marker.getLatLng();
            if (Math.abs(pos.lat - parsedLat) > 0.0001 || Math.abs(pos.lng - parsedLng) > 0.0001) {
                marker.setLatLng([parsedLat, parsedLng]);
                map.setView([parsedLat, parsedLng], map.getZoom());
            }
        }
    }
});
</script>

<template>
    <div>
        <div ref="mapContainer" class="w-full h-64 rounded-md border border-input z-0"></div>
        <p class="mt-1 text-xs text-muted-foreground">Klik pada peta untuk memilih lokasi, atau geser marker.</p>
    </div>
</template>
