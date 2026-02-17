<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Company founded year (for "Years Experience" on marketing site)
    |--------------------------------------------------------------------------
    |
    | Used to compute years of experience as: current year - founded_year.
    | Updates automatically each year. Set in .env as COMPANY_FOUNDED_YEAR.
    |
    */

    'founded_year' => (int) env('COMPANY_FOUNDED_YEAR', date('Y') - 5),

];
