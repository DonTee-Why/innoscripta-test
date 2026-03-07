<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ChecklistIndexRequest;
use App\Http\Resources\ChecklistResource;
use App\Services\ChecklistService;
use Illuminate\Http\JsonResponse;

class ChecklistController extends Controller
{
    public function index(ChecklistIndexRequest $request, ChecklistService $checklistService): JsonResponse
    {
        return ChecklistResource::make(
            $checklistService->getByCountry($request->getCountry())
        )->response()->setStatusCode(200);
    }
}
