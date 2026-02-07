<?php

namespace Database\Seeders;

use App\Models\SyncLog;
use Illuminate\Database\Seeder;

class SyncLogSeeder extends Seeder
{
    public function run(): void
    {
        if (SyncLog::exists()) {
            return;
        }

        SyncLog::create([
            'direction' => SyncLog::DIRECTION_ERP_TO_SHEET,
            'entity' => 'project',
            'erp_project_id' => 1,
            'status' => SyncLog::STATUS_SUCCESS,
            'message' => 'Synced (demo)',
        ]);
        SyncLog::create([
            'direction' => SyncLog::DIRECTION_SHEET_TO_ERP,
            'entity' => 'project',
            'erp_project_id' => 1,
            'status' => SyncLog::STATUS_SUCCESS,
            'message' => 'Updated from sheet (demo)',
        ]);
    }
}
