<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Country;
use App\Http\Resources\StepsResource;
use App\Services\StepsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class StepsController extends Controller
{
    public function __construct(
        private readonly StepsService $stepsService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'country' => ['required', 'string', Rule::in(Country::toArray())],
        ]);

        $payload = $this->stepsService->getByCountry($validated['country']);

        return StepsResource::make($payload)->response()->setStatusCode(200);
    }
}