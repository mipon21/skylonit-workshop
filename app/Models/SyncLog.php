<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncLog extends Model
{
    const DIRECTION_ERP_TO_SHEET = 'erp_to_sheet';
    const DIRECTION_SHEET_TO_ERP = 'sheet_to_erp';

    const STATUS_SUCCESS = 'success';
    const STATUS_ERROR = 'error';

    protected $fillable = [
        'direction',
        'entity',
        'erp_project_id',
        'status',
        'message',
    ];
}
