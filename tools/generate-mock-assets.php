<?php

declare(strict_types=1);

/*
 |--------------------------------------------------------------------------
 | Dev-time mock-asset generator (NOT part of the app runtime)
 |--------------------------------------------------------------------------
 | Builds storage/app/mock-data/assets.json: ~100 assets across the 10 category
 | model (CR-05) and the 12 populated panchayats (Veerapandi kept empty to
 | demonstrate zero-count categories). The first few assets are FIXED anchors the
 | test-suite references; the rest are generated deterministically (seeded).
 |
 | No `expected_life` field — every asset uses the fixed 25-year life (CR-06); only
 | construction_year is stored. Each asset carries a synthetic created_at for the
 | dashboard's "Recent Assets" section.
 |
 | Run from the project root:  php tools/generate-mock-assets.php
 */

mt_srand(42);

$root = dirname(__DIR__);
$dataDir = $root.'/storage/app/mock-data';
$read = static fn (string $n): array => json_decode((string) file_get_contents("$dataDir/$n.json"), true, 512, JSON_THROW_ON_ERROR);

$panchayats = array_column($read('panchayats'), null, 'id');
$zones = array_column($read('zones'), null, 'id');
$districts = array_column($read('districts'), null, 'id');

// Panchayat centres [lat, lng, pincode]. PAN-VEE omitted on purpose (stays empty).
$centres = [
    'PAN-ERU' => [11.6643, 78.1460, '636015'], 'PAN-AMM' => [11.6580, 78.1530, '636014'],
    'PAN-HAS' => [11.6720, 78.1530, '636016'], 'PAN-SUR' => [11.6700, 78.1300, '636005'],
    'PAN-KON' => [11.6200, 78.1500, '636010'], 'PAN-MAL' => [11.5500, 78.2500, '636203'],
    'PAN-JAG' => [11.6750, 78.1620, '636302'], 'PAN-AYO' => [11.7000, 78.2400, '636103'],
    'PAN-ATT' => [11.7600, 78.0500, '637501'], 'PAN-OMA' => [11.7400, 78.0470, '636455'],
    'PAN-CHI' => [11.4000, 77.6800, '638102'], 'PAN-PER' => [11.2760, 77.5870, '638053'],
];

// category id => [prefix, asset_type, name template, image list]
$cats = [
    'CAT-PRI' => ['PRI', 'Primary School', 'Government Primary School, {loc}', ['edu-1', 'edu-3', 'edu-2']],
    'CAT-NUR' => ['NUR', 'Nursery School', 'Government Nursery School, {loc}', ['edu-2', 'edu-1']],
    'CAT-PLY' => ['PLY', 'Play School', 'Anganwadi Play School, {loc} (Ward {n})', ['edu-2', 'edu-3']],
    'CAT-TOI' => ['TOI', 'Toilet Building', 'Public Toilet Block, {loc} (Ward {n})', ['toi-1', 'toi-2']],
    'CAT-OHT' => ['OHT', 'Overhead Water Tank', 'Overhead Water Tank, {loc} (Ward {n})', ['wat-1', 'wat-2']],
    'CAT-UGT' => ['UGT', 'Underground Water Tank', 'Underground Water Tank, {loc} (Ward {n})', ['wat-3', 'wat-2']],
    'CAT-RAT' => ['RAT', 'Ration Shop', 'Fair Price Ration Shop, {loc} (Ward {n})', ['rat-1']],
    'CAT-PAN' => ['PAN', 'Panchayat Office', '{loc} Panchayat Office', ['pub-1', 'pub-2']],
    'CAT-FUN' => ['FUN', 'Function Hall', 'Community Function Hall, {loc}', ['fun-1', 'fun-2']],
    'CAT-BOR' => ['BOR', 'Bore Well', 'Public Bore Well #{n}, {loc}', ['bor-1', 'wat-1']],
];
$catNames = [
    'CAT-PRI' => 'Primary Schools', 'CAT-NUR' => 'Nursery Schools', 'CAT-PLY' => 'Play Schools',
    'CAT-TOI' => 'Toilet Buildings', 'CAT-OHT' => 'Overhead Water Tanks (OHT)', 'CAT-UGT' => 'Underground Water Tanks (UGT)',
    'CAT-RAT' => 'Ration Shops', 'CAT-PAN' => 'Panchayat Offices', 'CAT-FUN' => 'Function Halls', 'CAT-BOR' => 'Bore Wells',
];

$pad4 = static fn (int $n): string => str_pad((string) $n, 4, '0', STR_PAD_LEFT);
$pick = static fn (array $a) => $a[mt_rand(0, count($a) - 1)];
$jitter = static fn (float $b): float => round($b + (mt_rand(-90, 90) / 10000), 4);
$createdAt = static fn (int $daysAgo): string => date('Y-m-d', strtotime("2026-06-01 -{$daysAgo} days"));

/** Build a fully-resolved asset record. */
$make = function (string $id, string $catId, string $panId, ?int $cy, array $photos, ?array $coords, int $daysAgo, string $assetNumber, string $name) use ($cats, $catNames, $panchayats, $zones, $districts): array {
    [, $type] = $cats[$catId];
    $pan = $panchayats[$panId];
    $zone = $zones[$pan['zone_id']];
    $district = $districts[$zone['district_id']];

    return [
        'id' => $id,
        'asset_number' => $assetNumber,
        'asset_name' => $name,
        'category_id' => $catId,
        'category_name' => $catNames[$catId],
        'asset_type' => $type,
        'panchayat_id' => $panId,
        'panchayat_name' => $pan['name'],
        'zone_id' => $zone['id'],
        'zone_name' => $zone['name'],
        'district_id' => $district['id'],
        'district_name' => $district['name'],
        'address' => $pan['name'].', '.$district['name'].', Tamil Nadu',
        'latitude' => $coords[0] ?? null,
        'longitude' => $coords[1] ?? null,
        'construction_year' => $cy,
        'created_at' => date('Y-m-d', strtotime("2026-06-01 -{$daysAgo} days")),
        'photos' => $photos,
    ];
};

$img = static fn (string $f, string $cap, int $seq, int $ph): array => ['id' => 'PH-'.str_pad((string) $ph, 4, '0', STR_PAD_LEFT), 'url' => "/asset-images/{$f}.jpg", 'caption' => $cap, 'sequence' => $seq];

// ---- Fixed anchor assets (referenced by tests) ----
$assets = [
    $make('AST-0001', 'CAT-PRI', 'PAN-ERU', 2010, [
        $img('edu-1', 'Front view', 1, 1), $img('edu-3', 'Classroom block', 2, 2), $img('edu-2', 'Assembly ground', 3, 3),
    ], [11.6643, 78.1460], 12, 'PRI-0001', 'Government Primary School, Erumapalayam'),
    $make('AST-0002', 'CAT-NUR', 'PAN-ERU', 2016, [$img('edu-2', 'Front view', 1, 4)], [11.6651, 78.1472], 30, 'NUR-0001', 'Government Nursery School, Erumapalayam'),
    $make('AST-0003', 'CAT-PRI', 'PAN-ERU', 2000, [], [11.6660, 78.1455], 60, 'PRI-0002', 'North Government Primary School, Erumapalayam'),
    $make('AST-0004', 'CAT-BOR', 'PAN-AMM', 2021, [$img('bor-1', 'Hand pump', 1, 5)], [11.6502, 78.1387], 8, 'BOR-0001', 'Public Bore Well #3, Ammapet'),
    $make('AST-0005', 'CAT-PAN', 'PAN-KON', 2012, [$img('pub-1', 'Office front', 1, 6)], [11.6201, 78.1502], 90, 'PAN-0001', 'Kondalampatti Panchayat Office'),
    $make('AST-0006', 'CAT-FUN', 'PAN-JAG', null, [], [null, null], 200, 'FUN-0001', 'Jagirammapalayam Community Function Hall'),
];

// ---- Generated assets ----
$astSeq = 6;
$phSeq = 6;
$numberSeq = ['PRI' => 2, 'NUR' => 1, 'PLY' => 0, 'TOI' => 0, 'OHT' => 0, 'UGT' => 0, 'RAT' => 0, 'PAN' => 1, 'FUN' => 1, 'BOR' => 1];
$ward = [];
$catCycle = array_keys($cats);
$target = 100;
$dayCursor = 5;

$panIds = array_keys($centres);
$ci = 0;
while (count($assets) < $target) {
    foreach ($panIds as $panId) {
        if (count($assets) >= $target) {
            break;
        }
        $catId = $catCycle[$ci % count($catCycle)];
        $ci++;
        [$prefix, $type, $tpl, $imgs] = $cats[$catId];

        $ward["$panId-$catId"] = ($ward["$panId-$catId"] ?? 0) + 1;
        $n = $ward["$panId-$catId"];

        $astSeq++;
        $numberSeq[$prefix]++;
        $assetNumber = "$prefix-".$pad4($numberSeq[$prefix]);

        [$lat, $lng, $pin] = $centres[$panId];
        $loc = trim(str_replace('Panchayat', '', $panchayats[$panId]['name']));
        $cy = mt_rand(1996, 2024);

        $photos = [];
        if (mt_rand(1, 9) !== 1) {
            $count = mt_rand(1, min(2, count($imgs)));
            for ($i = 0; $i < $count; $i++) {
                $phSeq++;
                $photos[] = $img($imgs[$i % count($imgs)], ['Front view', 'Side view'][$i] ?? 'View', $i + 1, $phSeq);
            }
        }

        $name = str_replace(['{loc}', '{n}'], [$loc, (string) $n], $tpl);
        $dayCursor += mt_rand(2, 9);

        $assets[] = $make("AST-".$pad4($astSeq), $catId, $panId, $cy, $photos, [$jitter($lat), $jitter($lng)], $dayCursor, $assetNumber, $name);
    }
}

file_put_contents(
    "$dataDir/assets.json",
    json_encode($assets, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n",
);

echo 'Generated assets.json with '.count($assets)." assets across 10 categories.\n";
