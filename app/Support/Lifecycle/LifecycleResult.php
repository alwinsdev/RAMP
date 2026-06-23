<?php

declare(strict_types=1);

namespace App\Support\Lifecycle;

use App\Enums\LifecycleStatus;

/**
 * The immutable result of a lifecycle computation for a single asset.
 *
 * For an Unknown asset (missing/invalid inputs) the figures are null — they are
 * deliberately NOT computed (BR-LC-06). Status is always present.
 */
final readonly class LifecycleResult
{
    public function __construct(
        public LifecycleStatus $status,
        public ?int $currentAge = null,
        public ?int $remainingLife = null,
    ) {
    }

    public static function unknown(): self
    {
        return new self(LifecycleStatus::Unknown);
    }

    public function isUnknown(): bool
    {
        return $this->status === LifecycleStatus::Unknown;
    }
}
