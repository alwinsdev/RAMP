import './bootstrap';

// ApexCharts — dashboard health distribution + lifecycle "life consumed" visual.
// Exposed globally so the Alpine components below can instantiate charts.
import ApexCharts from 'apexcharts';
window.ApexCharts = ApexCharts;

// Maps: Leaflet + OpenStreetMap (free, no API key, no billing, no quota).
// markercluster = clustering on the Asset Intelligence Map; heat = the heatmap mode.
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet.markercluster';
import 'leaflet.markercluster/dist/MarkerCluster.css';
import 'leaflet.markercluster/dist/MarkerCluster.Default.css';
import 'leaflet.heat';
window.L = L;

// OpenStreetMap raster tiles + attribution (required by the OSM tile usage policy).
const OSM_TILES = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
const OSM_ATTRIB = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';
const osmLayer = () => L.tileLayer(OSM_TILES, { maxZoom: 19, attribution: OSM_ATTRIB });

// A coloured SVG map pin as a Leaflet divIcon (no external image assets to 404 on).
const pinIcon = (color = '#1A73E8') => L.divIcon({
    className: 'ramp-pin',
    html: `<svg width="30" height="40" viewBox="0 0 24 32" xmlns="http://www.w3.org/2000/svg"><path d="M12 0C5.37 0 0 5.37 0 12c0 8.5 12 20 12 20s12-11.5 12-20C24 5.37 18.63 0 12 0z" fill="${color}"/><circle cx="12" cy="12" r="4.5" fill="#fff"/></svg>`,
    iconSize: [30, 40],
    iconAnchor: [15, 40],
    popupAnchor: [0, -36],
});

document.addEventListener('alpine:init', () => {
    /**
     * Asset location map. cfg = { lat, lng, label, interactive, color }.
     * Sets `failed = true` if the map can't initialise so the Blade shows a fallback.
     */
    window.Alpine.data('assetMap', (cfg) => ({
        failed: false,
        _map: null,
        init() {
            try {
                const lat = Number(cfg.lat);
                const lng = Number(cfg.lng);
                const interactive = cfg.interactive !== false;
                const map = L.map(this.$refs.map, {
                    center: [lat, lng],
                    zoom: interactive ? 15 : 14,
                    zoomControl: interactive,
                    dragging: interactive,
                    scrollWheelZoom: interactive,
                    doubleClickZoom: interactive,
                    boxZoom: interactive,
                    keyboard: interactive,
                    touchZoom: interactive,
                });
                osmLayer().addTo(map);
                L.marker([lat, lng], { icon: pinIcon(cfg.color), title: cfg.label, keyboard: false }).addTo(map);
                this._map = map;
                // Containers that animate / were SPA-swapped can mis-measure; re-measure once laid out.
                requestAnimationFrame(() => map.invalidateSize());
            } catch (e) {
                this.failed = true;
            }
        },
        destroy() {
            this._map?.remove();
        },
    }));

    /**
     * Dashboard health distribution donut. cfg = { series, labels, colors, total }.
     */
    window.Alpine.data('healthDonut', (cfg) => ({
        chart: null,
        init() {
            this.chart = new window.ApexCharts(this.$refs.donut, {
                chart: { type: 'donut', height: 280, fontFamily: 'inherit' },
                series: cfg.series,
                labels: cfg.labels,
                colors: cfg.colors,
                legend: { position: 'bottom', fontSize: '13px', markers: { width: 10, height: 10, radius: 6 } },
                dataLabels: { enabled: false },
                stroke: { width: 2, colors: ['#fff'] },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                value: { color: '#0F172A', fontSize: '28px', fontWeight: 800 },
                                total: { show: true, label: 'Total assets', color: '#8A94A6', fontWeight: 600, formatter: () => String(cfg.total) },
                            },
                        },
                    },
                },
                tooltip: { y: { formatter: (v) => `${v} assets` } },
            });
            this.chart.render();
        },
        destroy() {
            this.chart?.destroy();
        },
    }));

    /**
     * Lifecycle "life consumed" radial gauge. cfg = { percent, color, label }.
     */
    window.Alpine.data('lifecycleGauge', (cfg) => ({
        chart: null,
        init() {
            const percent = Math.max(0, Math.min(100, Math.round(Number(cfg.percent))));
            this.chart = new window.ApexCharts(this.$refs.gauge, {
                chart: { type: 'radialBar', height: 240, sparkline: { enabled: true } },
                series: [percent],
                labels: [cfg.label || 'Life consumed'],
                colors: [cfg.color || '#1A73E8'],
                plotOptions: {
                    radialBar: {
                        hollow: { size: '62%' },
                        track: { background: '#EEF1F5' },
                        dataLabels: {
                            name: { offsetY: 22, color: '#8A94A6', fontSize: '12px', fontWeight: 600 },
                            value: { offsetY: -16, color: '#0F172A', fontSize: '30px', fontWeight: 800, formatter: (v) => `${v}%` },
                        },
                    },
                },
                stroke: { lineCap: 'round' },
            });
            this.chart.render();
        },
        destroy() {
            this.chart?.destroy();
        },
    }));

    /**
     * Asset Intelligence Map — the flagship visualization (Leaflet + OpenStreetMap).
     * cfg = { mapId, embedded, markers: [{id,name,number,category,panchayat,status,color,year,remaining,lat,lng}] }
     * Renders colour-coded markers with clustering, a heatmap mode, popups, and
     * auto-fit to the (already role-scoped, already filtered) marker set.
     */
    window.Alpine.data('assetIntelMap', (cfg) => ({
        failed: false,
        heatmapOn: false,
        _map: null,
        _cluster: null,
        _heat: null,
        _data: cfg.markers || [],

        init() {
            try {
                const map = L.map(this.$refs.map, {
                    center: [11.5, 78.1],
                    zoom: 8,
                    scrollWheelZoom: true,
                });
                osmLayer().addTo(map);
                this._map = map;
                requestAnimationFrame(() => map.invalidateSize());
                this.draw(this._data);
            } catch (e) {
                this.failed = true;
            }
        },

        // Live filter updates from the Livewire screen (matched by mapId), bound in Blade.
        onData(detail) {
            if (detail && detail.mapId === cfg.mapId) {
                this._data = detail.markers || [];
                this.draw(this._data);
            }
        },

        draw(data) {
            if (!this._map) return;

            // Clear previous layers.
            if (this._cluster) { this._map.removeLayer(this._cluster); this._cluster = null; }
            if (this._heat) { this._map.removeLayer(this._heat); this._heat = null; }

            if (this.heatmapOn) {
                this._heat = L.heatLayer(
                    data.map((d) => [Number(d.lat), Number(d.lng), 0.8]),
                    { radius: 28, blur: 20, maxZoom: 17 },
                );
                this._map.addLayer(this._heat);
            } else {
                this._cluster = L.markerClusterGroup({ showCoverageOnHover: false, maxClusterRadius: 50 });
                data.forEach((d) => {
                    const marker = L.circleMarker([Number(d.lat), Number(d.lng)], {
                        radius: 7,
                        fillColor: d.color,
                        fillOpacity: 0.95,
                        color: '#ffffff',
                        weight: 1.5,
                    });
                    marker.bindPopup(this.popup(d), { minWidth: 220 });
                    this._cluster.addLayer(marker);
                });
                this._map.addLayer(this._cluster);
            }

            // Auto-focus: fit the map to the current (filtered) markers.
            if (data.length) {
                this._map.fitBounds(
                    L.latLngBounds(data.map((d) => [Number(d.lat), Number(d.lng)])),
                    { padding: [30, 30], maxZoom: 15 },
                );
            }
        },

        toggleHeatmap() {
            this.heatmapOn = ! this.heatmapOn;
            this.draw(this._data);
        },

        popup(d) {
            const remaining = (d.remaining === null || d.remaining === undefined)
                ? '—'
                : (d.remaining > 0 ? `${d.remaining} yr remaining` : `${Math.abs(d.remaining)} yr past life`);
            return `<div style="font-family:inherit;min-width:210px;padding:2px 2px 4px">
                <div style="display:flex;align-items:center;gap:6px;margin-bottom:6px">
                    <span style="display:inline-block;width:9px;height:9px;border-radius:9999px;background:${d.color}"></span>
                    <span style="font-weight:700;color:${d.color}">${d.status}</span>
                </div>
                <div style="font-weight:700;color:#0f172a">${d.name}</div>
                <div style="font-family:monospace;color:#5a6473;font-size:12px;margin-top:2px">${d.number}</div>
                <div style="font-size:13px;color:#334155;margin-top:6px">${d.category}</div>
                <div style="font-size:13px;color:#334155">${d.panchayat}</div>
                <div style="font-size:13px;color:#334155;margin-top:4px">Built ${d.year || '—'} · ${remaining}</div>
                <a href="/assets/${d.id}" onclick="window.Livewire.navigate('/assets/${d.id}');return false;"
                   style="display:inline-block;margin-top:10px;background:#1a73e8;color:#fff;padding:6px 12px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none">Open Asset</a>
            </div>`;
        },

        destroy() {
            this._map?.remove();
        },
    }));
});
