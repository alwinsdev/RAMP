<?php

declare(strict_types=1);

namespace App\DataObjects;

/**
 * A category card on the Panchayat Category Dashboard (CR-05). Carries the per-panchayat,
 * role-scoped asset total + health breakdown, computed by CategoryService through the
 * shared LifecycleCalculator (never hard-coded, never inline math). Zero-count
 * categories are still produced (BR-CT-04). The card drills into the filtered Asset List.
 */
final readonly class CategorySummary
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $description,
        public int $total,
        public HealthSummary $health,
    ) {
    }

    public function hasAssets(): bool
    {
        return $this->total > 0;
    }
}
