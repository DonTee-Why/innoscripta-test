<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Country;
use App\Http\Resources\SchemaResource;
use App\Services\SchemaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class SchemaController extends Controller
{
    public function __construct(
        private readonly SchemaService $schemaService,
    ) {}

    public function show(Request $request, string $stepId): JsonResponse
    {
        $validated = $request->validate([
            'country' => ['required', 'string', Rule::in(Country::toArray())],
        ]);

        $payload = $this->schemaService->getByStepAndCountry(
            stepId: $stepId,
            country: $validated['country'],
        );

        return SchemaResource::make($payload)->response()->setStatusCode(200);
    }
}
