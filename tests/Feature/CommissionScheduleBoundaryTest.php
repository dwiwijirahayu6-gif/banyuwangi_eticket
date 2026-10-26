<?php

namespace Tests\Feature;

use App\Models\CommissionScheme;
use App\Models\Tenant;
use App\Modules\Admin\Services\ChangeCommissionSchedule;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class CommissionScheduleBoundaryTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private CarbonImmutable $switchAt;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        
        CommissionScheme::create([
            'tenant_id' => $this->tenant->id,
            'commission_rate' => 0.15,
            'valid_from' => CarbonImmutable::parse('2026-09-01 00:00:00'),
            'valid_to' => null,
        ]);

        $this->switchAt = CarbonImmutable::parse('2026-10-01 00:00:00');
        app(ChangeCommissionSchedule::class)->handle($this->tenant, 0.20, $this->switchAt);
    }

    private function effectiveAt(CarbonImmutable $instant): Collection
    {
        return CommissionScheme::query()
            ->where('tenant_id', $this->tenant->id)
            ->where('valid_from', '<=', $instant)
            ->where(function ($query) use ($instant) {
                $query->whereNull('valid_to')->orWhere('valid_to', '>', $instant);
            })
            ->get();
    }

    public function test_old_version_ends_exactly_when_new_version_starts(): void
    {
        $schemes = CommissionScheme::where('tenant_id', $this->tenant->id)->orderBy('valid_from')->get();
        $old = $schemes[0];
        $new = $schemes[1];

        $this->assertTrue($old->valid_to->eq($this->switchAt), 'valid_to versi lama harus = waktu efektif');
        $this->assertTrue($new->valid_from->eq($old->valid_to), 'versi baru harus bersambung tanpa celah');
        $this->assertNull($new->valid_to);
    }

    public function test_every_instant_around_the_switch_resolves_exactly_one_scheme(): void
    {
        $expectations = [
            ['instant' => $this->switchAt->subDay(), 'rate' => '0.1500'],
            ['instant' => $this->switchAt->subSecond(), 'rate' => '0.1500'],
            ['instant' => $this->switchAt, 'rate' => '0.2000'],
            ['instant' => $this->switchAt->addSecond(), 'rate' => '0.2000'],
        ];

        foreach ($expectations as $case) {
            $schemes = $this->effectiveAt($case['instant']);
            $this->assertCount(1, $schemes, 'Harus tepat satu skema pada '.$case['instant']->format('Y-m-d H:i:s.u'));
            $this->assertSame($case['rate'], $schemes->first()?->commission_rate);
        }
    }
}