<?php

namespace Database\Seeders;

use App\Models\Investment;
use Illuminate\Database\Seeder;

class InvestmentSeeder extends Seeder
{
    public function run(): void
    {
        $tiers = config('investor.risk_tiers', [
            'low' => ['share_percent' => 25, 'cap_multiplier' => 2],
            'medium' => ['share_percent' => 30, 'cap_multiplier' => 2.5],
            'high' => ['share_percent' => 40, 'cap_multiplier' => 3],
        ]);

        $demos = [
            [
                'investor_name' => 'Demo Investor (Low Risk)',
                'amount' => 100000,
                'invested_at' => now()->subMonths(3),
                'risk_level' => 'low',
                'notes' => 'Demo seed. Low risk: 25% share, 2× cap.',
            ],
            [
                'investor_name' => 'Demo Investor (Medium Risk)',
                'amount' => 150000,
                'invested_at' => now()->subMonths(2),
                'risk_level' => 'medium',
                'notes' => 'Demo seed. Medium risk: 30% share, 2.5× cap.',
            ],
        ];

        foreach ($demos as $data) {
            $risk = $data['risk_level'];
            $sharePercent = $tiers[$risk]['share_percent'] ?? 25;
            $capMultiplier = $tiers[$risk]['cap_multiplier'] ?? 2;
            $amount = $data['amount'];
            $returnCapAmount = round($amount * $capMultiplier, 2);

            Investment::updateOrCreate(
                ['investor_name' => $data['investor_name']],
                [
                    'amount' => $amount,
                    'invested_at' => $data['invested_at'],
                    'risk_level' => $risk,
                    'profit_share_percent' => $sharePercent,
                    'return_cap_multiplier' => $capMultiplier,
                    'return_cap_amount' => $returnCapAmount,
                    'returned_amount' => 0,
                    'status' => Investment::STATUS_ACTIVE,
                    'notes' => $data['notes'] ?? null,
                ]
            );
        }
    }
}
