<?php

namespace App\Exports;

use App\Models\Lead;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LeadsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Lead::orderByDesc('created_at')->get()->map(function (Lead $lead) {
            return [
                $lead->name,
                $lead->email,
                $lead->phone ?? '',
                $lead->interested_project_type ?? '',
                $lead->message ?? '',
                Lead::statusLabel($lead->status ?? 'new'),
                $lead->created_at->format('Y-m-d H:i:s'),
            ];
        });
    }

    public function headings(): array
    {
        return ['Name', 'Email', 'Phone', 'Interested type', 'Message', 'Status', 'Date'];
    }
}
