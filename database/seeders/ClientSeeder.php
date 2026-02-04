<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            ['name' => 'Acme Corp', 'phone' => '+880 1712 000001', 'email' => 'contact@acme.com', 'address' => 'Dhaka, Bangladesh', 'fb_link' => null, 'kyc' => 'NID'],
            ['name' => 'TechStart Ltd', 'phone' => '+880 1812 000002', 'email' => 'info@techstart.com', 'address' => 'Chittagong', 'fb_link' => 'https://facebook.com/techstart', 'kyc' => 'Trade License'],
            ['name' => 'Green Solutions', 'phone' => '+880 1912 000003', 'email' => 'hello@greensolutions.bd', 'address' => 'Sylhet', 'fb_link' => null, 'kyc' => 'NID'],
        ];

        foreach ($clients as $data) {
            Client::updateOrCreate(
                ['name' => $data['name']],
                $data
            );
        }
    }
}
