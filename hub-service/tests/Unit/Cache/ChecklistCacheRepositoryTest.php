<?php

namespace Tests\Unit\Cache;

use App\Infrastructure\Cache\ChecklistCacheRepository;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ChecklistCacheRepositoryTest extends TestCase
{
    private ChecklistCacheRepository $repository;

    public function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        $this->repository = new ChecklistCacheRepository();
    }

    public function test_it_puts_checklist_payload_in_cache(): void
    {
        $payload = ['country' => 'USA', 'summary' => ['total_employees' => 1]];

        $this->repository->remember('USA', fn () => $payload);

        $this->assertSame($payload, Cache::get('hub:checklists:USA'));
    }

    public function test_it_gets_checklist_payload_from_cache(): void
    {
        $payload = ['country' => 'USA'];
        Cache::put('hub:checklists:USA', $payload, now()->addMinutes(10));

        $this->assertSame($payload, $this->repository->get('USA'));
    }

    public function test_it_returns_null_when_checklist_cache_is_missing(): void
    {
        $this->assertNull($this->repository->get('USA'));
    }

    public function test_it_invalidates_checklist_cache_for_country(): void
    {
        Cache::put('hub:checklists:USA', ['ok' => true], now()->addMinutes(10));

        $this->repository->invalidate('USA');

        $this->assertNull(Cache::get('hub:checklists:USA'));
    }

    public function test_it_remember_caches_callback_result_on_cache_miss(): void
    {
        $calls = 0;

        $result = $this->repository->remember('USA', function () use (&$calls): array {
            $calls++;

            return ['computed' => true];
        });

        $this->assertSame(['computed' => true], $result);
        $this->assertSame(1, $calls);
        $this->assertSame(['computed' => true], Cache::get('hub:checklists:USA'));
    }

    public function test_it_remember_returns_cached_result_without_recomputing_callback(): void
    {
        $calls = 0;
        Cache::put('hub:checklists:USA', ['cached' => true], now()->addMinutes(10));

        $result = $this->repository->remember('USA', function () use (&$calls): array {
            $calls++;

            return ['computed' => true];
        });

        $this->assertSame(['cached' => true], $result);
        $this->assertSame(0, $calls);
    }
}
