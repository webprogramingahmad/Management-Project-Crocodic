<?php

namespace Tests\Feature\StatusSdm;

use App\Models\Statussdm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsTaskContext;
use Tests\TestCase;

class SdmHealthCheckCommandTest extends TestCase
{
    use BuildsTaskContext;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseReferences();
    }

    public function test_health_check_command_prints_status_summary(): void
    {
        $staffA = $this->makeUser('staff');
        $staffB = $this->makeUser('staff');
        $ready = Statussdm::query()->where('status_sdm', 'Ready')->firstOrFail();
        $notReady = Statussdm::query()->where('status_sdm', 'Not Ready')->firstOrFail();

        $staffA->update(['id_activity_status_sdm' => $ready->id]);
        $staffB->update(['id_activity_status_sdm' => $notReady->id]);

        $this->artisan('sdm:health-check')
            ->expectsOutputToContain('SDM activity status health check')
            ->expectsOutputToContain('Total operational users with activity status: 2')
            ->assertExitCode(0);
    }
}
