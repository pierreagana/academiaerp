<?php

namespace App\Modules\SuperAdmin\Application\UseCases;

use App\Modules\SuperAdmin\Domain\Repositories\GlobalSettingRepositoryInterface;

class GetCmsDataUseCase
{
    public function __construct(
        private GlobalSettingRepositoryInterface $settingRepository
    ) {}

    public function execute(): array
    {
        // Get dynamic platform settings from Domain Repository
        $settings = collect($this->settingRepository->getAll())->mapWithKeys(function ($setting) {
            return [$setting->key => $setting->value];
        });

        $platformName   = $settings->get('platform_name', 'Academia ERP SaaS');
        $supportEmail   = $settings->get('support_email', 'support@academiaerp.com');
        $contactPhone   = $settings->get('contact_phone', '+221 77 000 00 00');

        // CMS Pages structure could eventually come from a CmsPageRepository
        $cmsPages = [
            [
                'id'           => 1,
                'title'        => 'Page d\'Accueil (Landing)',
                'slug'         => '/',
                'status'       => 'published',
                'last_updated' => '12 Oct 2023',
                'editor'       => 'Admin Système',
                'sections'     => 7,
            ],
            [
                'id'           => 2,
                'title'        => 'Politique de Confidentialité',
                'slug'         => '/confidentialite',
                'status'       => 'published',
                'last_updated' => '01 Sep 2023',
                'editor'       => 'Admin Juridique',
                'sections'     => 3,
            ],
        ];

        $faqItems = [
            [
                'id'       => 1,
                'question' => 'Comment configurer le module IA Tuteur pour mes élèves ?',
                'category' => 'Fonctionnalités IA',
                'status'   => 'published',
                'views'    => 248,
            ],
        ];

        $announcements = [
            [
                'id'          => 1,
                'title'       => 'Mise à jour v3.2 — Nouveaux tableaux de bord IA',
                'target'      => 'Tous les établissements',
                'published_at' => '12 Oct 2023',
                'status'      => 'active',
            ],
        ];

        return compact('platformName', 'supportEmail', 'contactPhone', 'cmsPages', 'faqItems', 'announcements');
    }
}
