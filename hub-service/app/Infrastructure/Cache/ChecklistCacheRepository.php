<?php

declare(strict_types=1);

namespace App\Infrastructure\Cache;

use Illuminate\Support\Facades\Cache;

class ChecklistCacheRepository
{
    private const CHECKLIST_CACHE_PREFIX = 'hub:checklists';

    /**
     * @param string $country
     * @return array<string, mixed>|null
     */
    public function get(string $country): ?array
    {
        $cacheKey = $this->key($country);
        $cached = Cache::get($cacheKey);

        return $cached ?? null;
    }

    /**
     * @param callable(): array<string, mixed> $callback
     * @return array<string, mixed>
     */
    public function remember(string $country, callable $callback, int $ttlMinutes = 10): array
    {
        return Cache::remember(
            $this->key($country),
            now()->addMinutes($ttlMinutes),
            $callback
        );
    }

    /**
     * @param string $country
     * @return void
     */
    public function invalidate(string $country): void
    {
        Cache::forget($this->key($country));
    }

    /**
     * @param string $country
     * @return string
     */
    private function key(string $country): string
    {
        return self::CHECKLIST_CACHE_PREFIX . ':' . strtoupper($country);
    }
}