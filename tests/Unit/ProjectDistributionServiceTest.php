<?php

namespace Tests\Unit;

use App\Models\Client;
use App\Models\Payment;
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

    public function test_cash_base_is_paid_minus_expenses_min_zero(): void
    {
        $client = Client::create(['name' => 'Test Client']);
        $project = Project::create([
            'client_id' => $client->id,
            'project_name' => 'Cash Base Project',
            'contract_amount' => 25000,
            'status' => 'Pending',
            'developer_sales_mode' => false,
            'sales_commission_enabled' => true,
            'sales_percentage' => 25,
            'developer_percentage' => 40,
        ]);
        $project->expenses()->create(['amount' => 2750, 'note' => 'Code Purchase']);
        $project->payments()->create([
            'amount' => 5000,
            'payment_status' => Payment::PAYMENT_STATUS_PAID,
            'note' => 'First',
        ]);
        $project->payments()->create([
            'amount' => 15000,
            'payment_status' => Payment::PAYMENT_STATUS_PAID,
            'note' => 'Middle',
        ]);

        $cashBase = $this->service->getCashBase($project);
        $this->assertSame(17250.0, $cashBase);

        $realized = $this->service->getRealizedBreakdown($project);
        $this->assertSame(3450.0, $realized['overhead']);
        $this->assertSame(4312.50, $realized['sales']);
        $this->assertSame(6900.0, $realized['developer']);
        $this->assertSame(2587.50, $realized['profit']);

        $sum = $realized['overhead'] + $realized['sales'] + $realized['developer'] + $realized['profit'];
        $this->assertSame($cashBase, round($sum, 2));
    }

    public function test_cash_base_zero_when_paid_less_than_expenses(): void
    {
        $client = Client::create(['name' => 'Test Client']);
        $project = Project::create([
            'client_id' => $client->id,
            'project_name' => 'Negative Cash Base',
            'contract_amount' => 25000,
            'status' => 'Pending',
            'developer_sales_mode' => false,
            'sales_commission_enabled' => true,
            'sales_percentage' => 25,
            'developer_percentage' => 40,
        ]);
        $project->expenses()->create(['amount' => 30000, 'note' => 'Large expense']);
        $project->payments()->create([
            'amount' => 10000,
            'payment_status' => Payment::PAYMENT_STATUS_PAID,
            'note' => 'Partial',
        ]);

        $cashBase = $this->service->getCashBase($project);
        $this->assertSame(0.0, $cashBase);

        $realized = $this->service->getRealizedBreakdown($project);
        $this->assertEquals(0.0, $realized['overhead']);
        $this->assertEquals(0.0, $realized['sales']);
        $this->assertEquals(0.0, $realized['developer']);
        $this->assertEquals(0.0, $realized['profit']);
    }
}
