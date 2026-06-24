<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| RAMP — Rural Asset Management Platform configuration
|--------------------------------------------------------------------------
|
| Single source of truth for domain thresholds, hierarchy order, the active
| data provider, and integration keys. Centralizing these here means a future
| rule change (e.g. the near-expiry threshold) is a one-line edit — never a
| magic number scattered through the code (BUSINESS_RULES BR-PR-03 / TD-04).
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Active data provider (THE SEAM)
    |--------------------------------------------------------------------------
    |
    | Selects which implementation sits behind the AssetDataProvider /
    | DashboardDataProvider contracts. Phase 1 = 'mock' (reads JSON). Phase 2+
    | swaps to 'eloquent' with zero UI/Service changes — the whole point of the
    | architecture. Binding happens in DataLayerServiceProvider.
    |
    | Supported: 'mock'
    | Future:    'eloquent'
    */
    'data_provider' => env('RAMP_DATA_PROVIDER', 'mock'),

    /*
    |--------------------------------------------------------------------------
    | Mock data location
    |--------------------------------------------------------------------------
    |
    | Disk + directory the mock providers read from. ONLY the data providers
    | reference this; UI/Services never touch it (DEVELOPMENT_RULES TD-01).
    */
    'mock_data' => [
        'disk' => 'mock-data',      // dedicated disk rooted at storage/app/mock-data (config/filesystems.php)
        'path' => '',               // collections live at the disk root: <collection>.json
    ],

    /*
    |--------------------------------------------------------------------------
    | Lifecycle thresholds
    |--------------------------------------------------------------------------
    |
    | Defined once. The lifecycle engine derives status from Remaining Life:
    |   Healthy     : RL > near_expiry_years
    |   Near Expiry : 0 < RL <= near_expiry_years   (boundary 5 => Near Expiry)
    |   Expired     : RL <= 0                        (boundary 0 => Expired)
    |   Unknown     : inputs missing/invalid
    | See BUSINESS_RULES BR-LC-* / BR-HL-*.
    */
    'lifecycle' => [
        // Every asset uses the same expected life (CR-06). Only construction_year is
        // a stored input; status is derived from it against this fixed life.
        'expected_life' => 25,
        'near_expiry_years' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Administrative hierarchy order (fixed — BR-NV-01)
    |--------------------------------------------------------------------------
    |
    | The backbone of navigation, breadcrumbs, and filtering. Never reorder.
    */
    'hierarchy_order' => [
        'district',
        'zone',
        'panchayat',
        'category',
        'asset',
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Maps JavaScript API
    |--------------------------------------------------------------------------
    |
    | Consumed by the Location View (Sprint 2). Empty key => the view degrades
    | gracefully to the coordinate readout / "location unavailable" state.
    */
    'google_maps_api_key' => env('GOOGLE_MAPS_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Security headers
    |--------------------------------------------------------------------------
    |
    | Toggle the baseline HTTP security headers applied by SecurityHeaders
    | middleware (CSP, X-Frame-Options, nosniff, Referrer-Policy, etc.).
    | Set RAMP_SECURITY_HEADERS=false only to debug a CSP/map issue.
    */
    'security_headers' => (bool) env('RAMP_SECURITY_HEADERS', true),

];
