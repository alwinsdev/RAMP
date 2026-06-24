<?php

declare(strict_types=1);

namespace App\Support\Lifecycle;

use App\Enums\LifecycleStatus;

/**
 * The single, shared lifecycle computation for the whole application.
 *
 * Every consumer (dashboard, lists, asset information, asset health) derives age /
 * remaining life / status through this one class — no inline duplication
 * (BUSINESS_RULES BR-LC-05 / BR-PR-02). Status is ALWAYS computed and NEVER stored.
 *
 * Per CR-06, **every asset uses the same expected life** (default 25 years), so the
 * only stored input is `construction_year`:
 *
 *   currentAge    = currentYear - constructionYear
 *   remainingLife = expectedLife - currentAge          (negative is valid)
 *
 *   Decision order (first match wins):
 *     1. construction year missing / future  -> Unknown
 *     2. remainingLife <= 0                   -> Expired   (boundary 0)
 *     3. remainingLife <= nearExpiryYears     -> Near Expiry (boundary 5)
 *     4. otherwise                            -> Healthy
 */
final class LifecycleCalculator
{
    public const DEFAULT_EXPECTED_LIFE = 25;
    public const DEFAULT_NEAR_EXPIRY_YEARS = 5;

    public function __construct(
        private readonly int $expectedLife = self::DEFAULT_EXPECTED_LIFE,
        private readonly int $nearExpiryYears = self::DEFAULT_NEAR_EXPIRY_YEARS,
    ) {
    }

    /**
     * Compute age, remaining life, and status from the construction year.
     *
     * @param  int|null  $currentYear  Defaults to the system/runtime year (BR-LC-01).
     */
    public function compute(?int $constructionYear, ?int $currentYear = null): LifecycleResult
    {
        $currentYear ??= (int) date('Y');

        // --- validate input -> Unknown (BR-LC-06/07) ---
        if ($constructionYear === null || $constructionYear > $currentYear) {
            return LifecycleResult::unknown();
        }

        // --- compute figures (BR-LC-02/03) ---
        $currentAge = $currentYear - $constructionYear;
        $remainingLife = $this->expectedLife - $currentAge; // negative is valid

        return new LifecycleResult(
            status: $this->statusFromRemainingLife($remainingLife),
            currentAge: $currentAge,
            remainingLife: $remainingLife,
        );
    }

    /** The fixed expected life applied to every asset. */
    public function expectedLife(): int
    {
        return $this->expectedLife;
    }

    /**
     * Map a remaining life to a health status. Input is assumed valid; Unknown is
     * handled upstream in compute().
     */
    public function statusFromRemainingLife(int $remainingLife): LifecycleStatus
    {
        if ($remainingLife <= 0) {
            return LifecycleStatus::Expired;            // covers exactly 0 (BR-HL-06)
        }

        if ($remainingLife <= $this->nearExpiryYears) {
            return LifecycleStatus::NearExpiry;         // covers exactly 5 (BR-HL-05)
        }

        return LifecycleStatus::Healthy;
    }
}
