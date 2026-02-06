<?php

return [
    /*
    |--------------------------------------------------------------------------
    | E-Signature block position on PDF (last page)
    |--------------------------------------------------------------------------
    | All values in MILLIMETRES (mm). Changes here take effect on the next contract sign
    | (no config:clear needed). Any CONTRACT_* value in .env overrides the default below.
    | Y = distance from BOTTOM of page (e.g. 30 = 30mm from bottom). Use positive values;
    |     negative Y can place text off the page.
    | X = distance from LEFT edge (mm).
    */

    'signature' => [
        'margin_left' => (float) env('CONTRACT_SIGNATURE_MARGIN_LEFT', 45),
        'margin_bottom' => (float) env('CONTRACT_SIGNATURE_MARGIN_BOTTOM', -8),
        'block_height' => (float) env('CONTRACT_SIGNATURE_BLOCK_HEIGHT', 35),
        'image_width' => (float) env('CONTRACT_SIGNATURE_IMAGE_WIDTH', 40),
        'image_height' => (float) env('CONTRACT_SIGNATURE_IMAGE_HEIGHT', 15),
        'font_size' => (int) env('CONTRACT_SIGNATURE_FONT_SIZE', 9),

        // Position of each text line in mm (x = from left, y = from bottom of page)
        'signed_by_x' => (float) env('CONTRACT_SIGNED_BY_X', 15.4),
        'signed_by_y_from_bottom' => (float) env('CONTRACT_SIGNED_BY_Y_FROM_BOTTOM',16),
        'date_x' => (float) env('CONTRACT_DATE_X', 50),
        'date_y_from_bottom' => (float) env('CONTRACT_DATE_Y_FROM_BOTTOM', 13),
        'ip_x' => (float) env('CONTRACT_IP_X', 90),
        'ip_y_from_bottom' => (float) env('CONTRACT_IP_Y_FROM_BOTTOM', 13),
    ],
];
