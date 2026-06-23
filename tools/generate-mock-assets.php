<?php

declare(strict_types=1);

/*
 |--------------------------------------------------------------------------
 | Dev-time mock-asset generator (NOT part of the app runtime)
 |--------------------------------------------------------------------------
 | Scales storage/app/mock-data/assets.json up to ~100 assets for a
 | production-scale demo. It PRESERVES the existing hand-authored "anchor"
 | assets (which the test-suite references) and appends realistically-named,
 | geographically-jittered assets across the panchayats.
 |
 | Deterministic (seeded) so re-running produces the same file. Idempotent:
 | it always rebuilds from the anchors, so running twice is safe.
 |
 | Run from the project root:  php tools/generate-mock-assets.php
 */

mt_srand(42); // reproducible output

$root = dirname(__DIR__);
$dataDir = $root.'/storage/app/mock-data';

$read = static fn (string $name): array => json_decode((string) file_get_contents("$dataDir/$name.json"), true, 512, JSON_THROW_ON_ERROR);

$assets = $read('assets');
$panchayats = $read('panchayats');
$zones = array_column($read('zones'), null, 'id');
$districts = array_column($read('districts'), null, 'id');
$categories = array_column($read('categories'), null, 'id');

// ---- Anchor set: the first 28 hand-authored assets are kept verbatim. ----
$anchors = array_slice($assets, 0, 28);

// Panchayat centres (lat, lng). PAN-VEE is intentionally omitted so it keeps
// zero assets — demonstrating the "zero-count category still shown" rule.
$centres = [
    'PAN-ERU' => [11.6643, 78.1460, '636015'],
    'PAN-AMM' => [11.6580, 78.1530, '636014'],
    'PAN-HAS' => [11.6720, 78.1530, '636016'],
    'PAN-SUR' => [11.6700, 78.1300, '636005'],
    'PAN-KON' => [11.6200, 78.1500, '636010'],
    'PAN-MAL' => [11.5500, 78.2500, '636203'],
    'PAN-JAG' => [11.6750, 78.1620, '636302'],
    'PAN-AYO' => [11.7000, 78.2400, '636103'],
    'PAN-ATT' => [11.7600, 78.0500, '637501'],
    'PAN-OMA' => [11.7400, 78.0470, '636455'],
    'PAN-CHI' => [11.4000, 77.6800, '638102'],
    'PAN-PER' => [11.2760, 77.5870, '638053'],
];

$catImages = [
    'CAT-EDU' => ['/asset-images/edu-1.jpg', '/asset-images/edu-2.jpg', '/asset-images/edu-3.jpg'],
    'CAT-HLT' => ['/asset-images/hlt-1.jpg', '/asset-images/hlt-2.jpg', '/asset-images/hlt-3.jpg'],
    'CAT-WAT' => ['/asset-images/wat-1.jpg', '/asset-images/wat-2.jpg', '/asset-images/wat-3.jpg'],
    'CAT-PUB' => ['/asset-images/pub-1.jpg', '/asset-images/pub-2.jpg', '/asset-images/pub-3.png'],
];

$numberPrefix = ['CAT-EDU' => 'EDU', 'CAT-HLT' => 'HLT', 'CAT-WAT' => 'WAT', 'CAT-PUB' => 'PUB'];

// Plausible expected-life choices per category (years).
$lifeChoices = [
    'CAT-EDU' => [25, 30, 40],
    'CAT-HLT' => [20, 30, 40],
    'CAT-WAT' => [8, 15, 25],
    'CAT-PUB' => [35, 45, 50],
];

// Name templates per asset type. {loc} = locality, {n} = ward/sequence number.
$names = [
    'Primary School' => 'Panchayat Union Primary School, {loc} (Ward {n})',
    'Nursery School' => 'Government Nursery School, {loc} (Unit {n})',
    'Government School' => 'Government Higher Secondary School, {loc}',
    'Primary Health Centre' => 'Primary Health Centre, {loc}',
    'Rural Health Facility' => 'Rural Health Sub-Centre, {loc} (Ward {n})',
    'Overhead Water Tank' => 'Overhead Water Tank, {loc} (Ward {n})',
    'Underground Water Tank' => 'Underground Sump, {loc} (Ward {n})',
    'Bore Well' => 'Bore Well #{n}, {loc}',
    'Panchayat Office' => '{loc} Panchayat Office',
    'Community Hall' => 'Community Hall, {loc} (Ward {n})',
];

$streets = ['Main Road', 'Bazaar Street', 'School Road', 'Temple Street', 'Mettur Road', 'North Street', 'Anna Nagar', 'Gandhi Road'];
$captions = ['Front view', 'Side view', 'Building'];

// Distribution of categories to generate per panchayat (a realistic mix).
$mix = ['CAT-EDU', 'CAT-EDU', 'CAT-HLT', 'CAT-WAT', 'CAT-WAT', 'CAT-PUB'];

// Next running ids/numbers, continued from the anchors.
$astSeq = 28;
$phSeq = 28;
$numberSeq = ['EDU' => 9, 'HLT' => 6, 'WAT' => 7, 'PUB' => 6];
$wardCounter = [];

$pad4 = static fn (int $n): string => str_pad((string) $n, 4, '0', STR_PAD_LEFT);
$pick = static fn (array $a) => $a[mt_rand(0, count($a) - 1)];
$jitter = static fn (float $base): float => round($base + (mt_rand(-90, 90) / 10000), 4); // ~±1km

$panchayatById = array_column($panchayats, null, 'id');
$generated = [];

foreach ($centres as $panId => [$lat, $lng, $pin]) {
    $pan = $panchayatById[$panId];
    $zone = $zones[$pan['zone_id']];
    $district = $districts[$zone['district_id']];
    $loc = trim(str_replace('Panchayat', '', $pan['name']));

    foreach ($mix as $catId) {
        $cat = $categories[$catId];
        $type = $pick($cat['sub_types']);
        $wardCounter["$panId-$type"] = ($wardCounter["$panId-$type"] ?? 0) + 1;
        $n = $wardCounter["$panId-$type"];

        $astSeq++;
        $prefix = $numberPrefix[$catId];
        $numberSeq[$prefix]++;
        $assetNumber = "$prefix-".$pad4($numberSeq[$prefix]);

        $constructionYear = mt_rand(1992, 2024);
        $expectedLife = $pick($lifeChoices[$catId]);

        // Photos: usually 1–2 category images; occasionally none.
        $photos = [];
        if (mt_rand(1, 8) !== 1) {
            $imgs = $catImages[$catId];
            $count = mt_rand(1, 2);
            for ($i = 0; $i < $count; $i++) {
                $phSeq++;
                $photos[] = [
                    'id' => 'PH-'.$pad4($phSeq),
                    'url' => $imgs[$i % count($imgs)],
                    'caption' => $captions[$i] ?? 'View',
                    'sequence' => $i + 1,
                ];
            }
        }

        $generated[] = [
            'id' => 'AST-'.$pad4($astSeq),
            'asset_number' => $assetNumber,
            'asset_name' => str_replace(['{loc}', '{n}'], [$loc, (string) $n], $names[$type]),
            'category_id' => $catId,
            'category_name' => $cat['name'],
            'asset_type' => $type,
            'panchayat_id' => $panId,
            'panchayat_name' => $pan['name'],
            'zone_id' => $zone['id'],
            'zone_name' => $zone['name'],
            'district_id' => $district['id'],
            'district_name' => $district['name'],
            'address' => $pick($streets).", $loc, ".$district['name'].", Tamil Nadu $pin",
            'latitude' => $jitter($lat),
            'longitude' => $jitter($lng),
            'construction_year' => $constructionYear,
            'expected_life' => $expectedLife,
            'photos' => $photos,
        ];
    }
}

$all = array_merge($anchors, $generated);

file_put_contents(
    "$dataDir/assets.json",
    json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n",
);

echo 'Generated assets.json with '.count($all).' assets ('.count($anchors).' anchors + '.count($generated)." generated).\n";
