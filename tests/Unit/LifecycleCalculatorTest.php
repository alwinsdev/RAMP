<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\LifecycleStatus;
use App\Support\Lifecycle\LifecycleCalculator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Boundary + worked-example coverage for the single lifecycle engine, now using the
 * FIXED 25-year expected life (CR-06). currentYear is pinned to 2026.
 *
 *   age = 2026 - cy ;  remainingLife = 25 - age
 *   Healthy: RL > 5  ·  Near Expiry: 0 < RL <= 5  ·  Expired: RL <= 0  ·  Unknown: bad input
 */
final class LifecycleCalculatorTest extends TestCase
{
    private const CURRENT_YEAR = 2026;

    private LifecycleCalculator $calc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calc = new LifecycleCalculator(expectedLife: 25, nearExpiryYears: 5);
    }

    #[DataProvider('workedExamples')]
    public function test_compute_matches_documented_examples(
        ?int $constructionYear,
        ?int $expectedAge,
        ?int $expectedRemaining,
        LifecycleStatus $expectedStatus,
    ): void {
        $result = $this->calc->compute($constructionYear, self::CURRENT_YEAR);

        $this->assertSame($expectedStatus, $result->status);
        $this->assertSame($expectedAge, $result->currentAge);
        $this->assertSame($expectedRemaining, $result->remainingLife);
    }

    /**
     * @return array<string, array{0:int|null,1:int|null,2:int|null,3:LifecycleStatus}>
     */
    public static function workedExamples(): array
    {
        return [
            // label => [construction_year, age, remaining, status]
            'Healthy (RL 9)' => [2010, 16, 9, LifecycleStatus::Healthy],
            'Healthy just above boundary (RL 6)' => [2007, 19, 6, LifecycleStatus::Healthy],
            'Near Expiry boundary (RL 5)' => [2006, 20, 5, LifecycleStatus::NearExpiry],
            'Near Expiry lower (RL 1)' => [2002, 24, 1, LifecycleStatus::NearExpiry],
            'Expired boundary (RL 0)' => [2001, 25, 0, LifecycleStatus::Expired],
            'Expired (RL -1)' => [2000, 26, -1, LifecycleStatus::Expired],
            'Built this year (RL 25)' => [2026, 0, 25, LifecycleStatus::Healthy],
            'Unknown — missing construction year' => [null, null, null, LifecycleStatus::Unknown],
        ];
    }

    public function test_future_construction_year_is_unknown(): void
    {
        $this->assertSame(
            LifecycleStatus::Unknown,
            $this->calc->compute(2030, self::CURRENT_YEAR)->status,
        );
    }

    public function test_expected_life_is_fixed(): void
    {
        $this->assertSame(25, $this->calc->expectedLife());
    }

    public function test_defaults_to_runtime_year_when_not_supplied(): void
    {
        $result = $this->calc->compute((int) date('Y'));
        $this->assertSame(0, $result->currentAge);
        $this->assertSame(LifecycleStatus::Healthy, $result->status);
    }
}
