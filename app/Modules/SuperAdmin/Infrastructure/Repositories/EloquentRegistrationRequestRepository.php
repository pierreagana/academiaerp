<?php

namespace App\Modules\SuperAdmin\Infrastructure\Repositories;

use App\Modules\SuperAdmin\Domain\Entities\RegistrationRequest as DomainRegistrationRequest;
use App\Modules\SuperAdmin\Domain\Models\RegistrationRequest as EloquentRegistrationRequest;
use App\Modules\SuperAdmin\Domain\Repositories\RegistrationRequestRepositoryInterface;

class EloquentRegistrationRequestRepository implements RegistrationRequestRepositoryInterface
{
    private array $approvedStatuses = ['approuvé', 'approved', 'validée', 'approuvée'];

    public function getAll(): array
    {
        $requests = EloquentRegistrationRequest::whereNotIn('status', $this->approvedStatuses)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return $requests->map(function ($request) {
            return new DomainRegistrationRequest(
                id: $request->id,
                schoolName: $request->school_name,
                applicantName: $request->applicant_name,
                email: $request->email,
                phone: $request->phone ?? 'N/A',
                region: $request->region ?? 'N/A',
                status: ucfirst($request->status),
                submittedAt: $request->created_at ? 'Il y a ' . $request->created_at->diffForHumans(null, true) : 'N/A',
                packageRequested: $request->plan_requested ?? 'Basic',
                requestCode: '#REQ-' . str_pad($request->id, 4, '0', STR_PAD_LEFT)
            );
        })->toArray();
    }

    public function paginate(
        int $perPage = 10,
        ?string $search = null,
        ?string $status = null,
        ?string $country = null
    ) {
        $query = EloquentRegistrationRequest::whereNotIn('status', $this->approvedStatuses);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('school_name', 'LIKE', '%' . $search . '%')
                  ->orWhere('applicant_name', 'LIKE', '%' . $search . '%')
                  ->orWhere('email', 'LIKE', '%' . $search . '%');
            });
        }

        if ($status && strtolower($status) !== 'tous les statuts' && strtolower($status) !== 'all') {
            $query->where('status', 'LIKE', '%' . strtolower($status) . '%');
        }

        if ($country && strtolower($country) !== 'tous les pays' && strtolower($country) !== 'all') {
            $query->where('region', 'LIKE', '%' . $country . '%');
        }

        $paginator = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
        
        $paginator->getCollection()->transform(function ($request) {
            return new DomainRegistrationRequest(
                id: $request->id,
                schoolName: $request->school_name,
                applicantName: $request->applicant_name,
                email: $request->email,
                phone: $request->phone ?? 'N/A',
                region: $request->region ?? 'N/A',
                status: ucfirst($request->status),
                submittedAt: $request->created_at ? 'Il y a ' . $request->created_at->diffForHumans(null, true) : 'N/A',
                packageRequested: $request->plan_requested ?? 'Basic',
                requestCode: '#REQ-' . str_pad($request->id, 4, '0', STR_PAD_LEFT)
            );
        });
        
        return $paginator;
    }
}
