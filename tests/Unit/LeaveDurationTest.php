<?php

namespace Tests\Unit;

use App\Support\LeaveDuration;
use PHPUnit\Framework\TestCase;

class LeaveDurationTest extends TestCase
{
    public function test_same_start_and_end_is_one_day(): void
    {
        $this->assertSame('1 day', LeaveDuration::label('2026-04-24', '2026-04-24'));
    }

    public function test_date_range_is_inclusive(): void
    {
        $this->assertSame('2 days', LeaveDuration::label('2026-04-01', '2026-04-02'));
    }
}
