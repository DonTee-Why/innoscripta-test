<?php

declare(strict_types=1);

namespace App\Services;

use App\Checklist\ChecklistEngine;
use App\Infrastructure\Cache\ChecklistCacheRepository;
use App\Infrastructure\Cache\EmployeeCacheRepository;

final class ChecklistService
{
    public function __construct(
        private readonly EmployeeCacheRepository $employeeCacheRepository,
        private readonly ChecklistCacheRepository $checklistCacheRepository,
        private readonly ChecklistEngine $checklistEngine,
    ) {}

    public function getByCountry(string $country): array
    {
        return $this->checklistCacheRepository->remember(
            $country,
            function () use ($country): array {
                $employees = $this->employeeCacheRepository->all($country);

                return $this->checklistEngine->evaluate($country, array_values($employees));
            }
        );
    }
}
