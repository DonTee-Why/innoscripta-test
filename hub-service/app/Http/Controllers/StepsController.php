<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StepsRequest;
use App\Http\Resources\StepsResource;
use App\Services\StepsService;
use Illuminate\Http\JsonResponse;

final class StepsController extends Controller
{
    public function __construct(
        private readonly StepsService $stepsService,
    ) {}

    public function index(StepsRequest $request): JsonResponse
    {
        $payload = $this->stepsService->getByCountry($request->getCountry());

        return StepsResource::make($payload)->response()->setStatusCode(200);
    }
}