<?php

namespace App\Modules\SuperAdmin\Domain\Repositories;

interface GlobalSettingRepositoryInterface
{
    /**
     * @return \App\Modules\SuperAdmin\Domain\Entities\GlobalSetting[]
     */
    public function getAll(): array;

    /**
     * @param string $key
     * @return \App\Modules\SuperAdmin\Domain\Entities\GlobalSetting|null
     */
    public function findByKey(string $key);
}
