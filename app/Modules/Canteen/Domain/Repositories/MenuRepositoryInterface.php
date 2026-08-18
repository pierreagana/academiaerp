<?php

namespace App\Modules\Canteen\Domain\Repositories;

interface MenuRepositoryInterface
{
    public function itemsForWeek(string $weekStartDate);

    public function findItem($id);

    public function saveItem(array $data);

    public function deleteItem($id);

    public function weekFor(string $weekStartDate);

    public function publishWeek(string $weekStartDate);
}
