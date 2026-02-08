<?php

namespace Tests\Unit;

use App\Models\Client;
use App\Models\Project;
use App\Services\ProjectDistributionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectDistributionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ProjectDistributionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ProjectDistributionService::class);
    }

    public function test_developer_sales_mode_returns_75_25_no_overhead_no_profit(): void
    {
        $client = Client::create(['name' => 'Test Client']);
        $project = Project::create([
            'client_id' => $client->id,
            'project_name' => 'Dev-Sales Project',
            'contract_amount' => 100000,
            'status' => 'Pending',
            'developer_sales_mode' => true,
            'sales_commission_enabled' => true,
            'sales_percentage' => 25,
            'developer_percentage' => 40,
        ]);

        $breakdown = $this->service->getBreakdown($project);

        $this->assertTrue($breakdown['developer_sales_mode']);
        $this->assertSame(100000.0, $breakdown['base']);
        $this->assertSame(0.0, $breakdown['overhead']);
        $this->assertSame(25000.0, $breakdown['sales']);
        $this->assertSame(75000.0, $breakdown['developer']);
        $this->assertSame(0.0, $breakdown['profit']);
    }

    public function test_standard_mode_applies_20_overhead_and_custom_percentages(): void
    {
        $client = Client::create(['name' => 'Test Client']);
        $project = Project::create([
            'client_id' => $client->id,
            'project_name' => 'Standard Project',
            'contract_amount' => 100000,
            'status' => 'Pending',
            'developer_sales_mode' => false,
            'sales_commission_enabled' => true,
            'sales_percentage' => 25,
            'developer_percentage' => 40,
        ]);

        $breakdown = $this->service->getBreakdown($project);

        $this->assertFalse($breakdown['developer_sales_mode']);
        $this->assertSame(100000.0, $breakdown['base']);
        $this->assertSame(20000.0, $breakdown['overhead']);
        $this->assertSame(25000.0, $breakdown['sales']);
        $this->assertSame(40000.0, $breakdown['developer']);
        $this->assertSame(15000.0, $breakdown['profit']);
    }

    public function test_standard_mode_sales_disabled_zero_sales_flows_to_profit(): void
    {
        $client = Client::create(['name' => 'Test Client']);
        $project = Project::create([
            'client_id' => $client->id,
            'project_name' => 'No Sales Project',
            'contract_amount' => 100000,
            'status' => 'Pending',
            'developer_sales_mode' => false,
            'sales_commission_enabled' => false,
            'sales_percentage' => 25,
            'developer_percentage' => 40,
        ]);

        $breakdown = $this->service->getBreakdown($project);

        $this->assertSame(0.0, $breakdown['sales']);
        $this->assertSame(20000.0, $breakdown['overhead']);
        $this->assertSame(40000.0, $breakdown['developer']);
        $this->assertSame(40000.0, $breakdown['profit']);
    }

    public function test_base_uses_contract_minus_expenses(): void
    {
        $client = Client::create(['name' => 'Test Client']);
        $project = Project::create([
            'client_id' => $client->id,
            'project_name' => 'With Expenses',
            'contract_amount' => 100000,
            'status' => 'Pending',
            'developer_sales_mode' => false,
            'sales_commission_enabled' => true,
            'sales_percentage' => 25,
            'developer_percentage' => 40,
        ]);
        $project->expenses()->create(['amount' => 10000, 'note' => 'Expense']);

        $breakdown = $this->service->getBreakdown($project);

        $this->assertSame(90000.0, $breakdown['base']);
        $this->assertSame(18000.0, $breakdown['overhead']);
        $this->assertSame(22500.0, $breakdown['sales']);
        $this->assertSame(36000.0, $breakdown['developer']);
        $this->assertSame(13500.0, $breakdown['profit']);
    }

    public function test_validate_distribution_blocks_when_sales_plus_developer_plus_20_exceeds_100(): void
    {
        $errors = $this->service->validateDistribution(false, 40.0, 50.0);
        $this->assertNotEmpty($errors);
        $this->assertArrayHasKey('distribution', $errors);
    }

    public function test_validate_distribution_passes_when_developer_sales_mode_on(): void
    {
        $errors = $this->service->validateDistribution(true, 40.0, 50.0);
        $this->assertEmpty($errors);
    }

    public function test_validate_distribution_passes_when_within_limit(): void
    {
        $errors = $this->service->validateDistribution(false, 25.0, 40.0);
        $this->assertEmpty($errors);
    }
}
