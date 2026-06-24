import './bootstrap';

// ApexCharts — dashboard health distribution + lifecycle "life consumed" visual.
// Exposed globally so the Alpine components below can instantiate charts.
import ApexCharts from 'apexcharts';
window.ApexCharts = ApexCharts;

// Marker clustering for the Asset Intelligence Map.
import { MarkerClusterer } from '@googlemaps/markerclusterer';

/**
 * Google Maps JS API loader — a single shared promise so the script is injected
 * at most once per page. Resolves with google.maps; rejects if the script fails
 * (e.g. an invalid/blocked key) so callers can fall back gracefully.
 */
window.loadGoogleMaps = (key) => {
    if (window.__gmapsPromise) {
        return window.__gmapsPromise;
    }

    window.__gmapsPromise = new Promise((resolve, reject) => {
        if (window.google && window.google.maps && window.google.maps.Map) {
            resolve(window.google.maps);
            return;
        }
        if (!key) {
            reject(new Error('Missing Google Maps API key'));
            return;
        }

        // Google calls this when the key is invalid or the referrer isn't allowed
        // (e.g. 127.0.0.1 not whitelisted). Surface a graceful fallback instead of
        // a blank canvas. The components listen for this event and show the fallback.
        window.gm_authFailure = () => {
            window.__gmapsAuthFailed = true;
            window.dispatchEvent(new CustomEvent('gmaps-auth-failure'));
        };

        // With loading=async the script's onload can fire BEFORE google.maps.Map is
        // ready — creating a map then renders blank. The official callback= param
        // resolves only once the API is fully initialised, so we always wait for it.
        window.__gmapsReady = () => resolve(window.google.maps);

        const script = document.createElement('script');
        // visualization library is required for the heatmap layer on the intelligence map.
        script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(key)}&libraries=visualization&loading=async&callback=__gmapsReady`;
        script.async = true;
        script.onerror = () => reject(new Error('Failed to load Google Maps'));
        document.head.appendChild(script);
    });

    return window.__gmapsPromise;
};

document.addEventListener('alpine:init', () => {
    /**
     * Asset location map. cfg = { lat, lng, key, label }.
     * Sets `failed = true` when the map can't load so the Blade shows a fallback.
     */
    window.Alpine.data('assetMap', (cfg) => ({
        failed: false,
        async init() {
            if (window.__gmapsAuthFailed) { this.failed = true; return; }
            window.addEventListener('gmaps-auth-failure', () => { this.failed = true; });
            try {
                await window.loadGoogleMaps(cfg.key);
                const position = { lat: Number(cfg.lat), lng: Number(cfg.lng) };
                // interactive === false -> a read-only preview (no controls, no gestures).
                const interactive = cfg.interactive !== false;
                const map = new google.maps.Map(this.$refs.map, {
                    center: position,
                    zoom: interactive ? 15 : 14,
                    mapTypeControl: false,
                    streetViewControl: false,
                    fullscreenControl: interactive,
                    zoomControl: interactive,
                    disableDefaultUI: ! interactive,
                    gestureHandling: interactive ? 'auto' : 'none',
                    keyboardShortcuts: interactive,
                });
                new google.maps.Marker({ position, map, title: cfg.label });
            } catch (e) {
                this.failed = true;
            }
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
     * Asset Intelligence Map — the flagship visualization.
     * cfg = { key, mapId, embedded, markers: [{id,name,number,category,panchayat,status,color,year,remaining,lat,lng}] }
     * Renders colour-coded markers with clustering, a heatmap mode, info windows,
     * and auto-fit to the (already role-scoped, already filtered) marker set.
     */
    window.Alpine.data('assetIntelMap', (cfg) => ({
        failed: false,
        heatmapOn: false,
        _map: null,
        _info: null,
        _markers: [],
        _cluster: null,
        _heat: null,
        _data: cfg.markers || [],

        async init() {
            if (!cfg.key || window.__gmapsAuthFailed) { this.failed = true; return; }
            window.addEventListener('gmaps-auth-failure', () => { this.failed = true; });
            try {
                await window.loadGoogleMaps(cfg.key);
            } catch (e) {
                this.failed = true;
                return;
            }

            this._map = new google.maps.Map(this.$refs.map, {
                center: { lat: 11.5, lng: 78.1 },
                zoom: 8,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: ! cfg.embedded,
            });
            this._info = new google.maps.InfoWindow();
            this.draw(this._data);
        },

        // Live filter updates from the Livewire screen (matched by mapId), bound in Blade.
        onData(detail) {
            if (detail && detail.mapId === cfg.mapId) {
                this._data = detail.markers || [];
                this.draw(this._data);
            }
        },

        draw(data) {
            // Clear previous layers.
            if (this._cluster) { this._cluster.clearMarkers(); this._cluster = null; }
            this._markers.forEach((m) => m.setMap(null));
            this._markers = [];
            if (this._heat) { this._heat.setMap(null); this._heat = null; }

            this._markers = data.map((d) => {
                const marker = new google.maps.Marker({
                    position: { lat: Number(d.lat), lng: Number(d.lng) },
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        scale: 7,
                        fillColor: d.color,
                        fillOpacity: 0.95,
                        strokeColor: '#ffffff',
                        strokeWeight: 1.5,
                    },
                    title: d.name,
                });
                marker.addListener('click', () => {
                    this._info.setContent(this.popup(d));
                    this._info.open(this._map, marker);
                });
                return marker;
            });

            if (this.heatmapOn) {
                this._heat = new google.maps.visualization.HeatmapLayer({
                    data: data.map((d) => new google.maps.LatLng(Number(d.lat), Number(d.lng))),
                    map: this._map,
                    radius: 32,
                    opacity: 0.7,
                });
            } else {
                this._cluster = new MarkerClusterer({ map: this._map, markers: this._markers });
            }

            // Auto-focus: fit the map to the current (filtered) markers.
            if (data.length) {
                const bounds = new google.maps.LatLngBounds();
                data.forEach((d) => bounds.extend({ lat: Number(d.lat), lng: Number(d.lng) }));
                this._map.fitBounds(bounds);
                google.maps.event.addListenerOnce(this._map, 'idle', () => {
                    if (this._map.getZoom() > 15) this._map.setZoom(15);
                });
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
            if (this._cluster) this._cluster.clearMarkers();
            if (this._heat) this._heat.setMap(null);
        },
    }));
});
