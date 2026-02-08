<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Middleware\VerifyCsrfToken;
use Tests\TestCase;

class ProjectDistributionTest extends TestCase
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

    public function test_project_creation_blocked_when_distribution_exceeds_100_percent(): void
    {
        $response = $this->actingAs($this->admin)->post(route('projects.store'), [
            'client_id' => $this->client->id,
            'project_name' => 'Invalid Distribution',
            'contract_amount' => 100000,
            'status' => 'Pending',
            'developer_sales_mode' => '0',
            'sales_commission_enabled' => '1',
            'sales_percentage' => 45,
            'developer_percentage' => 45,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('distribution');
        $this->assertDatabaseMissing('projects', ['project_name' => 'Invalid Distribution']);
    }

    public function test_project_creation_succeeds_with_valid_distribution(): void
    {
        $response = $this->actingAs($this->admin)->post(route('projects.store'), [
            'client_id' => $this->client->id,
            'project_name' => 'Valid Project',
            'contract_amount' => 100000,
            'status' => 'Pending',
            'developer_sales_mode' => '0',
            'sales_commission_enabled' => '1',
            'sales_percentage' => 25,
            'developer_percentage' => 40,
        ]);

        $response->assertRedirect(route('projects.index'));
        $response->assertSessionHas('success');

        $project = Project::where('project_name', 'Valid Project')->first();
        $this->assertNotNull($project);
        $this->assertFalse($project->developer_sales_mode);
        $this->assertTrue($project->sales_commission_enabled);
        $this->assertSame(25.0, (float) $project->sales_percentage);
        $this->assertSame(40.0, (float) $project->developer_percentage);
    }

    public function test_project_creation_succeeds_with_developer_sales_mode(): void
    {
        $response = $this->actingAs($this->admin)->post(route('projects.store'), [
            'client_id' => $this->client->id,
            'project_name' => 'Dev-Sales Project',
            'contract_amount' => 100000,
            'status' => 'Pending',
            'developer_sales_mode' => '1',
            'sales_commission_enabled' => '1',
            'sales_percentage' => 25,
            'developer_percentage' => 40,
        ]);

        $response->assertRedirect(route('projects.index'));

        $project = Project::where('project_name', 'Dev-Sales Project')->first();
        $this->assertNotNull($project);
        $this->assertTrue($project->developer_sales_mode);
        $this->assertSame(0.0, $project->overhead);
        $this->assertSame(25000.0, $project->sales);
        $this->assertSame(75000.0, $project->developer);
        $this->assertSame(0.0, $project->profit);
    }

    public function test_project_update_blocked_when_distribution_exceeds_100_percent(): void
    {
        $project = Project::create([
            'client_id' => $this->client->id,
            'project_name' => 'Existing',
            'contract_amount' => 100000,
            'status' => 'Pending',
            'developer_sales_mode' => false,
            'sales_commission_enabled' => true,
            'sales_percentage' => 25,
            'developer_percentage' => 40,
        ]);

        $response = $this->actingAs($this->admin)->put(route('projects.update', $project), [
            'client_id' => $this->client->id,
            'project_name' => 'Existing',
            'contract_amount' => 100000,
            'status' => 'Pending',
            'developer_sales_mode' => '0',
            'sales_commission_enabled' => '1',
            'sales_percentage' => 50,
            'developer_percentage' => 50,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('distribution');
        $project->refresh();
        $this->assertSame(25.0, (float) $project->sales_percentage);
    }
}
