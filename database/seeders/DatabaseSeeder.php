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
            ClientSeeder::class,
            ProjectSeeder::class,
            PaymentExpenseSeeder::class,
            TaskSeeder::class,
            BugSeeder::class,
            DocumentSeeder::class,
            ProjectNoteSeeder::class,
        ]);
    }
}
