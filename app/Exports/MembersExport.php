<?php

namespace App\Exports;

use App\Services\MemberQueryService;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MembersExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        protected array $filters,
        protected MemberQueryService $memberQueryService,
    ) {
    }

    public function collection(): Collection
    {
        return $this->memberQueryService
            ->applyFilters($this->memberQueryService->baseQuery(), $this->filters)
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Email',
            'Phone',
            'Status',
            'Referral Code',
            'Referrer',
            'Referrals Count',
            'Created At',
        ];
    }

    public function map($member): array
    {
        return [
            $member->id,
            $member->full_name,
            $member->email,
            $member->phone,
            $member->status,
            $member->referral_code,
            $member->referrer?->full_name,
            $member->referrals_count,
            optional($member->created_at)->toDateTimeString(),
        ];
    }
}
