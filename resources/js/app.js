import './bootstrap';

// ApexCharts — dashboard health distribution + lifecycle "life consumed" visual.
// Exposed globally so the Alpine components below can instantiate charts.
import ApexCharts from 'apexcharts';
window.ApexCharts = ApexCharts;

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
        if (window.google && window.google.maps) {
            resolve(window.google.maps);
            return;
        }
        if (!key) {
            reject(new Error('Missing Google Maps API key'));
            return;
        }
        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(key)}&loading=async`;
        script.async = true;
        script.defer = true;
        script.onload = () => resolve(window.google.maps);
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
            try {
                await window.loadGoogleMaps(cfg.key);
                const position = { lat: Number(cfg.lat), lng: Number(cfg.lng) };
                const map = new google.maps.Map(this.$refs.map, {
                    center: position,
                    zoom: 15,
                    mapTypeControl: false,
                    streetViewControl: false,
                    fullscreenControl: false,
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
});
