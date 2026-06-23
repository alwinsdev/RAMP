<?php

declare(strict_types=1);

namespace App\DataProviders\Concerns;

use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Loads and decodes a mock JSON collection from storage/app/mock-data.
 *
 * This trait is the ONLY place in the codebase that touches the mock JSON files
 * (TD-01). Decoded collections are cached per request so repeated reads within a
 * single page render hit the disk once.
 */
trait ReadsMockJson
{
    /** @var array<string, array<int, array<string, mixed>>> */
    private array $jsonCache = [];

    /**
     * Read a collection (e.g. "assets") and return its rows as associative arrays.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function readCollection(string $collection): array
    {
        if (isset($this->jsonCache[$collection])) {
            return $this->jsonCache[$collection];
        }

        $disk = (string) config('ramp.mock_data.disk', 'mock-data');
        $prefix = trim((string) config('ramp.mock_data.path', ''), '/');
        $path = ($prefix === '' ? '' : $prefix.'/').$collection.'.json';

        $storage = Storage::disk($disk);

        // Predictable empties: a missing collection is treated as empty, not fatal (SL-05).
        if (! $storage->exists($path)) {
            return $this->jsonCache[$collection] = [];
        }

        $raw = (string) $storage->get($path);
        $decoded = json_decode($raw, true);

        if (! \is_array($decoded)) {
            throw new RuntimeException("Mock data collection [{$path}] is not valid JSON.");
        }

        /** @var array<int, array<string, mixed>> $decoded */
        return $this->jsonCache[$collection] = $decoded;
    }
}
