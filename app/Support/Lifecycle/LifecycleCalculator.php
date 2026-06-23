<?php

declare(strict_types=1);

namespace App\Support\Lifecycle;

use App\Enums\LifecycleStatus;

/**
 * The single, shared lifecycle computation for the whole application.
 *
 * Every consumer (dashboard, lists, asset detail, lifecycle view, status filter)
 * derives age / remaining life / status through this one class — there is no
 * inline duplication anywhere (BUSINESS_RULES BR-LC-05 / BR-PR-02).
 *
 * Status is ALWAYS computed and NEVER stored (BR-LC-04). Only the raw inputs
 * (construction_year, expected_life) live in data.
 *
 * Rules implemented exactly (BR-LC-* / BR-HL-*):
 *   currentAge    = currentYear - constructionYear
 *   remainingLife = expectedLife - currentAge          (negative is valid, BR-LC-09)
 *
 *   Decision order (first match wins):
 *     1. inputs missing/invalid           -> Unknown   (BR-LC-06/07/08, BR-HL-04)
 *     2. remainingLife <= 0               -> Expired   (BR-HL-03/06, boundary 0)
 *     3. remainingLife <= nearExpiryYears -> Near Expiry (BR-HL-02/05, boundary 5)
 *     4. otherwise                        -> Healthy   (BR-HL-01)
 */
final class LifecycleCalculator
{
    /** Fallback when no configured threshold is injected (kept in sync with config/ramp.php). */
    public const DEFAULT_NEAR_EXPIRY_YEARS = 5;

    public function __construct(
        private readonly int $nearExpiryYears = self::DEFAULT_NEAR_EXPIRY_YEARS,
    ) {
    }

    /**
     * Compute age, remaining life, and status from the stored inputs.
     *
     * @param  int|null  $currentYear  Defaults to the system/runtime year (BR-LC-01).
     *                                 Inject an explicit value in tests for determinism.
     */
    public function compute(?int $constructionYear, ?int $expectedLife, ?int $currentYear = null): LifecycleResult
    {
        $currentYear ??= (int) date('Y');

        // --- validate inputs (BR-LC-06/07/08) -> Unknown ---
        if ($constructionYear === null || $expectedLife === null) {
            return LifecycleResult::unknown();
        }
        if ($expectedLife <= 0) {                       // non-positive expected life is invalid (BR-LC-08)
            return LifecycleResult::unknown();
        }
        if ($constructionYear > $currentYear) {         // a future construction year is invalid (BR-LC-07)
            return LifecycleResult::unknown();
        }

        // --- compute figures (BR-LC-02/03) ---
        $currentAge = $currentYear - $constructionYear;
        $remainingLife = $expectedLife - $currentAge;   // negative is valid (BR-LC-09)

        return new LifecycleResult(
            status: $this->statusFromRemainingLife($remainingLife),
            currentAge: $currentAge,
            remainingLife: $remainingLife,
        );
    }

    /**
     * Map a (valid) remaining life to a health status. Inputs are assumed already
     * validated; Unknown is handled upstream in compute().
     */
    public function statusFromRemainingLife(int $remainingLife): LifecycleStatus
    {
        if ($remainingLife <= 0) {
            return LifecycleStatus::Expired;            // covers exactly 0 (BR-HL-06)
        }

        if ($remainingLife <= $this->nearExpiryYears) {
            return LifecycleStatus::NearExpiry;         // covers exactly 5 (BR-HL-05)
        }

        return LifecycleStatus::Healthy;                // remaining life > threshold (BR-HL-01)
    }
}
