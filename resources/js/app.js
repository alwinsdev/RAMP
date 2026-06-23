import './bootstrap';

// ApexCharts — used by the dashboard health distribution and the lifecycle "life consumed" visual.
// Exposed globally so Alpine x-init blocks (Alpine ships with Livewire 3) can instantiate charts.
import ApexCharts from 'apexcharts';
window.ApexCharts = ApexCharts;
