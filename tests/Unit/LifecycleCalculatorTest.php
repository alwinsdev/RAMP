<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\LifecycleStatus;
use App\Support\Lifecycle\LifecycleCalculator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Boundary + worked-example coverage for the single lifecycle engine.
 *
 * Pure unit test (no Laravel app) — proving the rules in BUSINESS_RULES BR-LC-* /
 * BR-HL-* hold exactly. currentYear is pinned to 2026 to match the documented
 * worked-example tables (docs/07 §2, docs/08 §7.1).
 */
final class LifecycleCalculatorTest extends TestCase
{
    private const CURRENT_YEAR = 2026;

    private LifecycleCalculator $calc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calc = new LifecycleCalculator(nearExpiryYears: 5);
    }

    /**
     * @param  int|null  $constructionYear
     * @param  int|null  $expectedLife
     */
    #[DataProvider('workedExamples')]
    public function test_compute_matches_documented_examples(
        ?int $constructionYear,
        ?int $expectedLife,
        ?int $expectedAge,
        ?int $expectedRemaining,
        LifecycleStatus $expectedStatus,
    ): void {
        $result = $this->calc->compute($constructionYear, $expectedLife, self::CURRENT_YEAR);

        $this->assertSame($expectedStatus, $result->status);
        $this->assertSame($expectedAge, $result->currentAge);
        $this->assertSame($expectedRemaining, $result->remainingLife);
    }

    /**
     * @return array<string, array{0:int|null,1:int|null,2:int|null,3:int|null,4:LifecycleStatus}>
     */
    public static function workedExamples(): array
    {
        return [
            // label => [construction_year, expected_life, age, remaining, status]
            'Healthy (RL 14)' => [2010, 30, 16, 14, LifecycleStatus::Healthy],
            'Near Expiry boundary (RL 5)' => [2016, 15, 10, 5, LifecycleStatus::NearExpiry],
            'Expired (RL -1)' => [2000, 25, 26, -1, LifecycleStatus::Expired],
            'Expired boundary (RL 0)' => [2021, 5, 5, 0, LifecycleStatus::Expired],
            'Expired (RL -3)' => [1998, 25, 28, -3, LifecycleStatus::Expired],
            'Healthy (RL 22)' => [2008, 40, 18, 22, LifecycleStatus::Healthy],
            'Near Expiry (RL 2)' => [2003, 25, 23, 2, LifecycleStatus::NearExpiry],
            'Healthy (RL 36)' => [2012, 50, 14, 36, LifecycleStatus::Healthy],

            // Unknown — figures intentionally not computed (BR-LC-06)
            'Unknown — missing construction year' => [null, 30, null, null, LifecycleStatus::Unknown],
            'Unknown — missing expected life' => [2010, null, null, null, LifecycleStatus::Unknown],
        ];
    }

    public function test_healthy_just_above_threshold(): void
    {
        // RL = 6 -> Healthy (just above the inclusive near-expiry boundary)
        $result = $this->calc->compute(2020, 12, self::CURRENT_YEAR); // age 6, RL 6
        $this->assertSame(LifecycleStatus::Healthy, $result->status);
        $this->assertSame(6, $result->remainingLife);
    }

    public function test_near_expiry_lower_boundary(): void
    {
        // RL = 1 -> Near Expiry
        $result = $this->calc->compute(2020, 7, self::CURRENT_YEAR); // age 6, RL 1
        $this->assertSame(LifecycleStatus::NearExpiry, $result->status);
    }

    public function test_zero_expected_life_is_unknown(): void
    {
        $this->assertSame(
            LifecycleStatus::Unknown,
            $this->calc->compute(2010, 0, self::CURRENT_YEAR)->status,
        );
    }

    public function test_negative_expected_life_is_unknown(): void
    {
        $this->assertSame(
            LifecycleStatus::Unknown,
            $this->calc->compute(2010, -5, self::CURRENT_YEAR)->status,
        );
    }

    public function test_future_construction_year_is_unknown(): void
    {
        $this->assertSame(
            LifecycleStatus::Unknown,
            $this->calc->compute(2030, 20, self::CURRENT_YEAR)->status,
        );
    }

    public function test_construction_year_equal_to_current_year_is_valid(): void
    {
        // Built this year: age 0, RL = expected life -> Healthy when above threshold
        $result = $this->calc->compute(self::CURRENT_YEAR, 10, self::CURRENT_YEAR);
        $this->assertSame(0, $result->currentAge);
        $this->assertSame(10, $result->remainingLife);
        $this->assertSame(LifecycleStatus::Healthy, $result->status);
    }

    public function test_defaults_to_runtime_year_when_not_supplied(): void
    {
        // Built this calendar year with a generous life -> Healthy, age 0.
        $result = $this->calc->compute((int) date('Y'), 30);
        $this->assertSame(0, $result->currentAge);
        $this->assertSame(LifecycleStatus::Healthy, $result->status);
    }
}
