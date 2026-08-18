<?php

namespace App\Modules\SuperAdmin\Infrastructure\Repositories;

use App\Modules\SuperAdmin\Domain\Entities\School;
use App\Modules\SuperAdmin\Domain\Repositories\SchoolRepositoryInterface;

class MockSchoolRepository implements SchoolRepositoryInterface
{
    public function getAll(): array
    {
        return [
            new School(
                id: '#SCH-001',
                name: 'Lycée d\'Excellence',
                logo: 'https://ui-avatars.com/api/?name=LE&background=0F3294&color=fff',
                region: 'Dakar, Sénégal',
                status: 'Actif',
                studentsCount: 1250,
                package: 'Premium',
                renewalDate: '12 Déc 2026'
            ),
            new School(
                id: '#SCH-002',
                name: 'Complexe Scolaire Les Leaders',
                logo: 'https://ui-avatars.com/api/?name=CS&background=10B981&color=fff',
                region: 'Abidjan, CI',
                status: 'Actif',
                studentsCount: 840,
                package: 'Starter',
                renewalDate: '05 Nov 2026'
            ),
            new School(
                id: '#SCH-003',
                name: 'Institut Saint-Jean',
                logo: 'https://ui-avatars.com/api/?name=IS&background=F59E0B&color=fff',
                region: 'Yaoundé, Cameroun',
                status: 'Inactif',
                studentsCount: 320,
                package: 'Enterprise',
                renewalDate: 'Expiré (Il y a 2j)'
            ),
            new School(
                id: '#SCH-004',
                name: 'Groupe Scolaire Aminata',
                logo: 'https://ui-avatars.com/api/?name=GS&background=6366F1&color=fff',
                region: 'Bamako, Mali',
                status: 'En attente',
                studentsCount: 0,
                package: 'Premium',
                renewalDate: '-'
            ),
            new School(
                id: '#SCH-005',
                name: 'Collège Notre-Dame',
                logo: 'https://ui-avatars.com/api/?name=CN&background=EC4899&color=fff',
                region: 'Libreville, Gabon',
                status: 'Actif',
                studentsCount: 2100,
                package: 'Enterprise',
                renewalDate: '24 Jan 2027'
            )
        ];
    }
}
