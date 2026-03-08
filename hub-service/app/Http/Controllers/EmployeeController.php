<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeIndexRequest;
use App\Http\Resources\EmployeeResource;
use App\Services\EmployeeQueryService;
use Illuminate\Http\JsonResponse;

final class EmployeeController extends Controller
{
    public function __construct(
        private readonly EmployeeQueryService $employeeQueryService,
    ) {}

    public function index(EmployeeIndexRequest $request): JsonResponse
    {
        return EmployeeResource::make(
            $this->employeeQueryService->getByCountry(
                country: $request->getCountry(),
                page: $request->getPage(),
                perPage: $request->getPerPage(),
            )
        )->response();
    }
}
