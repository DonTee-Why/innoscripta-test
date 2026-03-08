<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ChecklistIndexRequest;
use App\Http\Resources\ChecklistResource;
use App\Services\ChecklistQueryService;
use Illuminate\Http\JsonResponse;

class ChecklistsController extends Controller
{
    public function __construct(
        private readonly ChecklistQueryService $checklistQueryService,
    ) {}

    public function index(ChecklistIndexRequest $request): JsonResponse
    {
        return ChecklistResource::make(
            $this->checklistQueryService->getByCountry($request->getCountry())
        )->response()->setStatusCode(200);
    }
}
