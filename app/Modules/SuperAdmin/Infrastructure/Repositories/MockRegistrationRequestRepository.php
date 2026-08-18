<?php

namespace App\Modules\SuperAdmin\Infrastructure\Repositories;

use App\Modules\SuperAdmin\Domain\Entities\RegistrationRequest;
use App\Modules\SuperAdmin\Domain\Repositories\RegistrationRequestRepositoryInterface;

class MockRegistrationRequestRepository implements RegistrationRequestRepositoryInterface
{
    public function getAll(): array
    {
        return [
            new RegistrationRequest(
                id: '#REQ-2023-089',
                schoolName: 'Lycée Moderne de Cocody',
                applicantName: 'Kouassi Jean',
                email: 'direction@lmc-edu.ci',
                phone: '+225 01 23 45 67 89',
                region: 'Abidjan, Côte d\'Ivoire',
                status: 'En attente',
                submittedAt: 'Aujourd\'hui, 10:45',
                packageRequested: 'Premium'
            ),
            new RegistrationRequest(
                id: '#REQ-2023-088',
                schoolName: 'Complexe Scolaire Les Anges',
                applicantName: 'Mme. Diallo Aminata',
                email: 'contact@lesanges.sn',
                phone: '+221 77 123 45 67',
                region: 'Dakar, Sénégal',
                status: 'En cours d\'analyse',
                submittedAt: 'Hier, 15:30',
                packageRequested: 'Starter'
            ),
            new RegistrationRequest(
                id: '#REQ-2023-087',
                schoolName: 'Institut Polytechnique',
                applicantName: 'Dr. Talla',
                email: 'admin@polytech.cm',
                phone: '+237 6 12 34 56 78',
                region: 'Douala, Cameroun',
                status: 'Validée',
                submittedAt: '24 Oct 2026',
                packageRequested: 'Enterprise'
            ),
            new RegistrationRequest(
                id: '#REQ-2023-086',
                schoolName: 'Groupe Scolaire Bilingue',
                applicantName: 'M. Ouedraogo',
                email: 'info@gs-bilingue.bf',
                phone: '+226 70 00 11 22',
                region: 'Ouagadougou, B. Faso',
                status: 'Rejetée',
                submittedAt: '22 Oct 2026',
                packageRequested: 'Starter'
            )
        ];
    }
}
