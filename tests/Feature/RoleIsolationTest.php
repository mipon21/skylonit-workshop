<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleIsolationTest extends TestCase
{
    use RefreshDatabase;

    private function createClientUser(): User
    {
        $user = User::factory()->create(['role' => 'client']);
        Client::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
        return $user;
    }

    private function createProject(): Project
    {
        $client = Client::create([
            'user_id' => null,
            'name' => 'Test Client',
            'email' => 'client@test.com',
        ]);
        return Project::create([
            'client_id' => $client->id,
            'project_name' => 'Test Project',
            'project_code' => 'SLN-000001',
            'contract_amount' => 10000,
            'status' => 'Pending',
        ]);
    }

    public function test_client_cannot_access_developers_list(): void
    {
        $user = $this->createClientUser();

        $response = $this->actingAs($user)->get(route('developers.index'));

        $response->assertStatus(403);
    }

    public function test_client_cannot_access_sales_list(): void
    {
        $user = $this->createClientUser();

        $response = $this->actingAs($user)->get(route('sales.index'));

        $response->assertStatus(403);
    }

    public function test_developer_cannot_access_admin_only_routes(): void
    {
        $user = User::factory()->create(['role' => 'developer']);

        $this->actingAs($user)->get(route('clients.index'))->assertStatus(403);
        $this->actingAs($user)->get(route('revenue.index'))->assertStatus(403);
        $this->actingAs($user)->get(route('developers.index'))->assertStatus(403);
        $this->actingAs($user)->get(route('sales.index'))->assertStatus(403);
    }

    public function test_sales_cannot_access_admin_only_routes(): void
    {
        $user = User::factory()->create(['role' => 'sales']);

        $this->actingAs($user)->get(route('clients.index'))->assertStatus(403);
        $this->actingAs($user)->get(route('revenue.index'))->assertStatus(403);
        $this->actingAs($user)->get(route('developers.index'))->assertStatus(403);
        $this->actingAs($user)->get(route('sales.index'))->assertStatus(403);
    }

    public function test_developer_sees_only_assigned_projects(): void
    {
        $developer = User::factory()->create(['role' => 'developer']);
        $projectAssigned = $this->createProject();
        $projectAssigned->developers()->attach($developer->id);
        $projectNotAssigned = $this->createProject();
        $projectNotAssigned->update(['project_name' => 'Other Project', 'project_code' => 'SLN-000002']);

        $response = $this->actingAs($developer)->get(route('projects.index'));

        $response->assertStatus(200);
        $response->assertSee($projectAssigned->project_name);
        $response->assertDontSee($projectNotAssigned->project_name);
    }

    public function test_developer_cannot_view_project_they_are_not_assigned_to(): void
    {
        $developer = User::factory()->create(['role' => 'developer']);
        $project = $this->createProject();

        $response = $this->actingAs($developer)->get(route('projects.show', $project));

        $response->assertStatus(403);
    }

    public function test_developer_can_view_project_they_are_assigned_to(): void
    {
        $developer = User::factory()->create(['role' => 'developer']);
        $project = $this->createProject();
        $project->developers()->attach($developer->id);

        $response = $this->actingAs($developer)->get(route('projects.show', $project));

        $response->assertStatus(200);
        $response->assertSee($project->project_name);
    }

    public function test_sales_sees_only_assigned_projects(): void
    {
        $sales = User::factory()->create(['role' => 'sales']);
        $projectAssigned = $this->createProject();
        $projectAssigned->sales()->attach($sales->id);
        $projectNotAssigned = $this->createProject();
        $projectNotAssigned->update(['project_name' => 'Other Project', 'project_code' => 'SLN-000002']);

        $response = $this->actingAs($sales)->get(route('projects.index'));

        $response->assertStatus(200);
        $response->assertSee($projectAssigned->project_name);
        $response->assertDontSee($projectNotAssigned->project_name);
    }
}
