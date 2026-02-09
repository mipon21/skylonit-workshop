<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Investment;
use App\Models\Payment;
use App\Models\Project;
use App\Models\User;
use App\Services\ProfitPoolService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Middleware\VerifyCsrfToken;
use Tests\TestCase;

class ProfitPoolDistributionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->client = Client::create(['name' => 'Test Client']);
    }

    protected function createProjectWithPaidPayment(int $year, int $month, float $contractAmount = 100000, float $paymentAmount = 50000): Project
    {
        $project = Project::create([
            'client_id' => $this->client->id,
            'project_name' => 'Test Project',
            'contract_amount' => $contractAmount,
            'status' => 'Pending',
            'developer_sales_mode' => false,
            'sales_commission_enabled' => true,
            'sales_percentage' => 25,
            'developer_percentage' => 40,
        ]);

        $paidAt = \Carbon\Carbon::createFromDate($year, $month, 15)->startOfDay();
        Payment::create([
            'project_id' => $project->id,
            'amount' => $paymentAmount,
            'payment_type' => 'first',
            'payment_status' => Payment::PAYMENT_STATUS_PAID,
            'status' => Payment::STATUS_COMPLETED,
            'paid_at' => $paidAt,
        ]);

        return $project->fresh();
    }

    public function test_partners_include_active_investors_and_shareholders(): void
    {
        $this->createProjectWithPaidPayment(2025, 1);

        $investor = Investment::create([
            'category' => Investment::CATEGORY_INVESTOR,
            'investor_name' => 'Test Investor',
            'amount' => 10000,
            'invested_at' => now(),
            'risk_level' => 'low',
            'profit_share_percent' => 50,
            'return_cap_multiplier' => 2,
            'return_cap_amount' => 20000,
            'returned_amount' => 0,
            'status' => Investment::STATUS_ACTIVE,
        ]);

        $shareholder = Investment::create([
            'category' => Investment::CATEGORY_SHAREHOLDER,
            'share_percent' => 100,
            'investor_name' => 'Test Shareholder',
            'amount' => 10000,
            'invested_at' => now(),
            'risk_level' => 'medium',
            'profit_share_percent' => 0,
            'return_cap_multiplier' => 0,
            'return_cap_amount' => 999999999.99,
            'returned_amount' => 0,
            'status' => Investment::STATUS_ACTIVE,
        ]);

        $service = app(ProfitPoolService::class);
        $results = $service->runDistributionForMonth(2025, 1);

        $this->assertArrayHasKey('distributions', $results);
        $this->assertCount(2, $results['distributions']);

        $investor->refresh();
        $shareholder->refresh();

        $this->assertGreaterThan(0, $investor->returned_amount);
        $this->assertGreaterThan(0, $shareholder->returned_amount);
    }

    public function test_investor_exits_when_cap_reached_shareholder_does_not(): void
    {
        $this->createProjectWithPaidPayment(2025, 1, 100000, 100000);

        $investor = Investment::create([
            'category' => Investment::CATEGORY_INVESTOR,
            'investor_name' => 'Capped Investor',
            'amount' => 1000,
            'invested_at' => now(),
            'risk_level' => 'low',
            'profit_share_percent' => 50,
            'return_cap_multiplier' => 0.1,
            'return_cap_amount' => 100,
            'returned_amount' => 0,
            'status' => Investment::STATUS_ACTIVE,
        ]);

        $shareholder = Investment::create([
            'category' => Investment::CATEGORY_SHAREHOLDER,
            'share_percent' => 100,
            'investor_name' => 'Uncapped Shareholder',
            'amount' => 1000,
            'invested_at' => now(),
            'risk_level' => 'medium',
            'profit_share_percent' => 0,
            'return_cap_multiplier' => 0,
            'return_cap_amount' => 999999999.99,
            'returned_amount' => 0,
            'status' => Investment::STATUS_ACTIVE,
        ]);

        $service = app(ProfitPoolService::class);
        $results = $service->runDistributionForMonth(2025, 1);

        $investor->refresh();
        $shareholder->refresh();

        $this->assertEquals(Investment::STATUS_EXITED, $investor->status);
        $this->assertGreaterThanOrEqual(100, $investor->returned_amount);
        $this->assertTrue($investor->hasReachedCap());

        $this->assertEquals(Investment::STATUS_ACTIVE, $shareholder->status);
        $this->assertFalse($shareholder->hasReachedCap());
        $this->assertGreaterThan(0, $shareholder->returned_amount);
    }

    public function test_shareholder_receives_entire_partner_pool_when_no_active_investors(): void
    {
        $this->createProjectWithPaidPayment(2025, 2);

        $exitedInvestor = Investment::create([
            'category' => Investment::CATEGORY_INVESTOR,
            'investor_name' => 'Exited Investor',
            'amount' => 1000,
            'invested_at' => now(),
            'risk_level' => 'low',
            'profit_share_percent' => 50,
            'return_cap_multiplier' => 0.01,
            'return_cap_amount' => 10,
            'returned_amount' => 10,
            'status' => Investment::STATUS_EXITED,
        ]);

        $shareholder = Investment::create([
            'category' => Investment::CATEGORY_SHAREHOLDER,
            'share_percent' => 100,
            'investor_name' => 'Sole Shareholder',
            'amount' => 1000,
            'invested_at' => now(),
            'risk_level' => 'medium',
            'profit_share_percent' => 0,
            'return_cap_multiplier' => 0,
            'return_cap_amount' => 999999999.99,
            'returned_amount' => 0,
            'status' => Investment::STATUS_ACTIVE,
        ]);

        $service = app(ProfitPoolService::class);
        $results = $service->runDistributionForMonth(2025, 2);

        $this->assertCount(1, $results['distributions']);
        $this->assertEquals('Sole Shareholder', $results['distributions'][0]['investor_name']);

        $shareholder->refresh();
        $partnerPoolPercent = config('investor.investor_pool_percent', 95) / 100;
        $partnerPool = $results['profit_pool'] * $partnerPoolPercent;
        $this->assertEqualsWithDelta($partnerPool, $shareholder->returned_amount, 0.02);
    }

    public function test_investors_split_partner_pool_when_no_shareholders(): void
    {
        $this->createProjectWithPaidPayment(2025, 3);

        Investment::create([
            'category' => Investment::CATEGORY_INVESTOR,
            'investor_name' => 'Investor A',
            'amount' => 10000,
            'invested_at' => now(),
            'risk_level' => 'low',
            'profit_share_percent' => 50,
            'return_cap_multiplier' => 10,
            'return_cap_amount' => 100000,
            'returned_amount' => 0,
            'status' => Investment::STATUS_ACTIVE,
        ]);

        Investment::create([
            'category' => Investment::CATEGORY_INVESTOR,
            'investor_name' => 'Investor B',
            'amount' => 10000,
            'invested_at' => now(),
            'risk_level' => 'medium',
            'profit_share_percent' => 50,
            'return_cap_multiplier' => 10,
            'return_cap_amount' => 100000,
            'returned_amount' => 0,
            'status' => Investment::STATUS_ACTIVE,
        ]);

        $service = app(ProfitPoolService::class);
        $results = $service->runDistributionForMonth(2025, 3);

        $this->assertCount(2, $results['distributions']);
        $partnerPoolPercent = config('investor.investor_pool_percent', 95) / 100;
        $partnerPool = $results['profit_pool'] * $partnerPoolPercent;
        $totalPaid = array_sum(array_column($results['distributions'], 'payout'));
        $this->assertEqualsWithDelta($partnerPool, $totalPaid, 0.02);
    }

    public function test_multiple_shareholders_weighted_distribution(): void
    {
        $this->createProjectWithPaidPayment(2025, 4, 100000, 100000);

        Investment::create([
            'category' => Investment::CATEGORY_SHAREHOLDER,
            'share_percent' => 60,
            'investor_name' => 'Shareholder A',
            'amount' => 5000,
            'invested_at' => now(),
            'risk_level' => 'medium',
            'profit_share_percent' => 0,
            'return_cap_multiplier' => 0,
            'return_cap_amount' => 999999999.99,
            'returned_amount' => 0,
            'status' => Investment::STATUS_ACTIVE,
        ]);

        Investment::create([
            'category' => Investment::CATEGORY_SHAREHOLDER,
            'share_percent' => 40,
            'investor_name' => 'Shareholder B',
            'amount' => 5000,
            'invested_at' => now(),
            'risk_level' => 'medium',
            'profit_share_percent' => 0,
            'return_cap_multiplier' => 0,
            'return_cap_amount' => 999999999.99,
            'returned_amount' => 0,
            'status' => Investment::STATUS_ACTIVE,
        ]);

        $service = app(ProfitPoolService::class);
        $results = $service->runDistributionForMonth(2025, 4);

        $this->assertCount(2, $results['distributions']);
        $partnerPoolPercent = config('investor.investor_pool_percent', 95) / 100;
        $partnerPool = $results['profit_pool'] * $partnerPoolPercent;
        $payoutA = collect($results['distributions'])->firstWhere('investor_name', 'Shareholder A')['payout'];
        $payoutB = collect($results['distributions'])->firstWhere('investor_name', 'Shareholder B')['payout'];

        $expectedA = $partnerPool * 0.6;
        $expectedB = $partnerPool * 0.4;
        $this->assertEqualsWithDelta($expectedA, $payoutA, 0.02);
        $this->assertEqualsWithDelta($expectedB, $payoutB, 0.02);
    }

    public function test_mixed_shareholders_and_investors_partner_pool_split(): void
    {
        $this->createProjectWithPaidPayment(2025, 5, 100000, 100000);

        Investment::create([
            'category' => Investment::CATEGORY_SHAREHOLDER,
            'share_percent' => 100,
            'investor_name' => 'Shareholder',
            'amount' => 5000,
            'invested_at' => now(),
            'risk_level' => 'medium',
            'profit_share_percent' => 0,
            'return_cap_multiplier' => 0,
            'return_cap_amount' => 999999999.99,
            'returned_amount' => 0,
            'status' => Investment::STATUS_ACTIVE,
        ]);

        Investment::create([
            'category' => Investment::CATEGORY_INVESTOR,
            'investor_name' => 'Investor',
            'amount' => 100000,
            'invested_at' => now(),
            'risk_level' => 'low',
            'profit_share_percent' => 100,
            'return_cap_multiplier' => 10,
            'return_cap_amount' => 1000000,
            'returned_amount' => 0,
            'status' => Investment::STATUS_ACTIVE,
        ]);

        $service = app(ProfitPoolService::class);
        $results = $service->runDistributionForMonth(2025, 5);

        $this->assertCount(2, $results['distributions']);
        $partnerPoolPercent = config('investor.investor_pool_percent', 95) / 100;
        $partnerShareholdersPercent = (float) (\App\Models\Setting::get('investor_partner_shareholders_percent') ?? config('investor.partner_shareholders_percent', 50)) / 100;
        $partnerInvestorsPercent = (float) (\App\Models\Setting::get('investor_partner_investors_percent') ?? config('investor.partner_investors_percent', 50)) / 100;
        $partnerPool = $results['profit_pool'] * $partnerPoolPercent;
        $shareholderPool = $partnerPool * $partnerShareholdersPercent;
        $investorPool = $partnerPool * $partnerInvestorsPercent;

        $shPayout = collect($results['distributions'])->firstWhere('investor_name', 'Shareholder')['payout'];
        $invPayout = collect($results['distributions'])->firstWhere('investor_name', 'Investor')['payout'];

        $this->assertEqualsWithDelta($shareholderPool, $shPayout, 0.02);
        $this->assertEqualsWithDelta($investorPool, $invPayout, 0.02);
    }
}
