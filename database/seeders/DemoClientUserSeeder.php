<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoClientUserSeeder extends Seeder
{
    /**
     * Create a demo client portal user and link to a client.
     * Run after migrations (e.g. php artisan db:seed --class=DemoClientUserSeeder).
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'client@example.com'],
            [
                'name' => 'Demo Client',
                'password' => Hash::make('password'),
                'role' => 'client',
            ]
        );

        if ($user->role !== 'client') {
            $user->update(['role' => 'client']);
        }

        $client = Client::where('user_id', $user->id)->first()
            ?? Client::where('email', 'client@example.com')->first()
            ?? Client::first();

        if (! $client) {
            $client = Client::create([
                'name' => 'Demo Client',
                'email' => 'client@example.com',
                'user_id' => $user->id,
            ]);
        } else {
            $client->update([
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        }

        $this->command->info('Demo client portal account ready:');
        $this->command->info('  Email: client@example.com');
        $this->command->info('  Password: password');
        $this->command->info('  Client: ' . $client->name . ' (ID ' . $client->id . ')');
    }
}
