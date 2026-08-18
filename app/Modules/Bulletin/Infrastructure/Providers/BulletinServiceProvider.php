<?php

namespace App\Modules\Bulletin\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;

use App\Modules\Bulletin\Domain\Repositories\BulletinGradeRepositoryInterface;
use App\Modules\Bulletin\Infrastructure\Repositories\EloquentBulletinGradeRepository;

use App\Modules\Bulletin\Domain\Repositories\BulletinPublicationRepositoryInterface;
use App\Modules\Bulletin\Infrastructure\Repositories\EloquentBulletinPublicationRepository;

use App\Modules\Bulletin\Domain\Repositories\BulletinSubjectPublicationRepositoryInterface;
use App\Modules\Bulletin\Infrastructure\Repositories\EloquentBulletinSubjectPublicationRepository;

use App\Modules\Bulletin\Domain\Repositories\BulletinTemplateRepositoryInterface;
use App\Modules\Bulletin\Infrastructure\Repositories\EloquentBulletinTemplateRepository;

use App\Modules\Bulletin\Domain\Repositories\BulletinEvaluationTypeRepositoryInterface;
use App\Modules\Bulletin\Infrastructure\Repositories\EloquentBulletinEvaluationTypeRepository;

class BulletinServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BulletinGradeRepositoryInterface::class, EloquentBulletinGradeRepository::class);
        $this->app->bind(BulletinPublicationRepositoryInterface::class, EloquentBulletinPublicationRepository::class);
        $this->app->bind(BulletinSubjectPublicationRepositoryInterface::class, EloquentBulletinSubjectPublicationRepository::class);
        $this->app->bind(BulletinTemplateRepositoryInterface::class, EloquentBulletinTemplateRepository::class);
        $this->app->bind(BulletinEvaluationTypeRepositoryInterface::class, EloquentBulletinEvaluationTypeRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
