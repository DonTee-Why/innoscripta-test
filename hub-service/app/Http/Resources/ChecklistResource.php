<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ChecklistResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'country' => $this->resource['country'] ?? null,
            'summary' => $this->resource['summary'] ?? [],
            'employees' => $this->resource['employees'] ?? [],
        ];
    }
}
