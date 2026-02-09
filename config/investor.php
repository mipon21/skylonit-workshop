<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Profit split: Founder vs Investors (of total profit pool)
    |--------------------------------------------------------------------------
    | Founder keeps this % of profit; the remainder is the investor pool (40%).
    | Only the investor pool is shared among active investors (by risk tier).
    */
    'founder_percent' => 5,
    'investor_pool_percent' => 95,

    /*
    |--------------------------------------------------------------------------
    | Partner pool split: Shareholders vs Investors (of the 40% partner pool)
    |--------------------------------------------------------------------------
    | When both shareholders and investors exist, this split applies.
    | partner_shareholders_percent + partner_investors_percent must equal 100.
    | If only shareholders exist → 100% to shareholders. If only investors → 100% to investors.
    */
    'partner_shareholders_percent' => 50,
    'partner_investors_percent' => 50,

    /*
    |--------------------------------------------------------------------------
    | Investor risk tiers: share of the 40% investor pool + return cap
    |--------------------------------------------------------------------------
    | share_percent = this investor's weight within the 40% pool (e.g. Low 25%
    | means 25% of the 40% = 10% of total profit when alone; with others, split
    | proportionally). cap_multiplier = return cap (e.g. 2 = 2× invested amount).
    */
    'risk_tiers' => [
        'low' => [
            'share_percent' => 25,
            'cap_multiplier' => 2,
        ],
        'medium' => [
            'share_percent' => 30,
            'cap_multiplier' => 2.5,
        ],
        'high' => [
            'share_percent' => 40,
            'cap_multiplier' => 3,
        ],
    ],
];
