<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            EmailTemplateSeeder::class,
            ClientSeeder::class,
            ProjectSeeder::class,
            PaymentExpenseSeeder::class,
            UddoktaPayDemoPaymentsSeeder::class,
            TaskSeeder::class,
            BugSeeder::class,
            DocumentSeeder::class,
            ProjectNoteSeeder::class,
            ProjectActivitySeeder::class,
            ClientNotificationSeeder::class,
            ContractSeeder::class,
            SyncLogSeeder::class,
        ]);
    }
}
