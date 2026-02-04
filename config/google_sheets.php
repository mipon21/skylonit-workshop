<?php

return [
    'spreadsheet_id' => env('GOOGLE_SHEETS_SPREADSHEET_ID', ''),
    'credentials_path' => (function () {
        $path = env('GOOGLE_SHEETS_CREDENTIALS', storage_path('app/google-credentials.json'));
        return str_starts_with($path, '/') || preg_match('#^[A-Za-z]:\\\\#', $path) ? $path : base_path($path);
    })(),
    'enabled' => (bool) env('GOOGLE_SHEETS_SYNC_ENABLED', false),

    'tabs' => [
        'projects' => 'Projects',
        'payments' => 'Payments',
        'expenses' => 'Expenses',
        'documents' => 'Documents',
        'tasks' => 'Tasks',
        'bugs' => 'Bugs',
        'notes' => 'Notes',
    ],
];
