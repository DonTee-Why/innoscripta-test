<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\SchemaShowRequest;
use App\Http\Resources\SchemaResource;
use App\Services\SchemaService;
use Illuminate\Http\JsonResponse;

final class SchemaController extends Controller
{
    public function __construct(
        private readonly SchemaService $schemaService,
    ) {}

    public function show(SchemaShowRequest $request, string $stepId): JsonResponse
    {
        $payload = $this->schemaService->getByStepAndCountry(
            stepId: $stepId,
            country: $request->getCountry(),
        );

        return SchemaResource::make($payload)->response()->setStatusCode(200);
    }
}
