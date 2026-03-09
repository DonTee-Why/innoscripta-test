<?php

namespace Tests\Unit\Cache;

use App\Infrastructure\Cache\EmployeeCacheRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class EmployeeCacheRepositoryTest extends TestCase
{
    private EmployeeCacheRepository $repository;

    /**
     * @var array<string, array<string, string>>
     */
    private array $redisHashes = [];

    /**
     * @var array<string, array<string, bool>>
     */
    private array $redisSets = [];

    public function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        $this->repository = new EmployeeCacheRepository();
        $this->redisHashes = [];
        $this->redisSets = [];

        Redis::shouldReceive('command')->byDefault()->andReturnUsing(function (string $command, array $args) {
            return match ($command) {
                'hset' => $this->fakeHset($args[0], $args[1], $args[2]),
                'hdel' => $this->fakeHdel($args[0], $args[1]),
                'hgetall' => $this->redisHashes[$args[0]] ?? [],
                'hget' => $this->redisHashes[$args[0]][$args[1]] ?? null,
                'sadd' => $this->fakeSadd($args[0], $args[1]),
                'smembers' => array_keys($this->redisSets[$args[0]] ?? []),
                'del' => $this->fakeDel($args[0]),
                default => null,
            };
        });
    }

    public function test_it_upserts_employee_snapshot_into_country_hash(): void
    {
        $this->repository->upsert('USA', 10, ['id' => 10, 'name' => 'John']);

        $this->assertArrayHasKey('hub:employees:USA', $this->redisHashes);
        $this->assertArrayHasKey('10', $this->redisHashes['hub:employees:USA']);
    }

    public function test_it_finds_employee_snapshot_by_country_and_id(): void
    {
        $this->repository->upsert('USA', 10, ['id' => 10, 'name' => 'John']);

        $found = $this->repository->find('USA', 10);

        $this->assertSame(['id' => 10, 'name' => 'John'], $found);
    }

    public function test_it_returns_null_when_employee_snapshot_is_missing(): void
    {
        $this->assertNull($this->repository->find('USA', 10));
    }

    public function test_it_returns_all_employee_snapshots_for_a_country(): void
    {
        $this->repository->upsert('USA', 1, ['id' => 1, 'name' => 'A']);
        $this->repository->upsert('USA', 2, ['id' => 2, 'name' => 'B']);

        $all = array_values($this->repository->all('USA'));

        $this->assertCount(2, $all);
        $this->assertSame(['id' => 1, 'name' => 'A'], $all[0]);
        $this->assertSame(['id' => 2, 'name' => 'B'], $all[1]);
    }

    public function test_it_returns_empty_array_when_no_employee_snapshots_exist_for_country(): void
    {
        $this->assertSame([], $this->repository->all('USA'));
    }

    public function test_it_deletes_employee_snapshot_from_country_hash(): void
    {
        $this->repository->upsert('USA', 1, ['id' => 1]);
        $this->repository->delete('USA', 1);

        $this->assertNull($this->repository->find('USA', 1));
    }

    public function test_it_paginates_employee_snapshots_correctly(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->repository->upsert('USA', $i, ['id' => $i]);
        }

        $pageTwo = $this->repository->paginate('USA', page: 2, perPage: 2);

        $this->assertSame([['id' => 3], ['id' => 2]], $pageTwo['data']);
    }

    public function test_it_returns_paginated_meta_information_correctly(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->repository->upsert('USA', $i, ['id' => $i]);
        }

        $pageTwo = $this->repository->paginate('USA', page: 2, perPage: 2);

        $this->assertSame([
            'page' => 2,
            'per_page' => 2,
            'total' => 5,
            'last_page' => 3,
        ], $pageTwo['meta']);
    }

    public function test_it_caches_paginated_employee_list(): void
    {
        $this->repository->upsert('USA', 1, ['id' => 1]);

        $payload = $this->repository->paginate('USA', page: 1, perPage: 1);

        $this->assertSame($payload, Cache::get('hub:employees:list:USA:1:1'));
    }

    public function test_it_returns_cached_paginated_employee_list_on_subsequent_calls(): void
    {
        $this->repository->upsert('USA', 1, ['id' => 1]);
        $this->repository->paginate('USA', page: 1, perPage: 10);

        $this->repository->upsert('USA', 2, ['id' => 2]);

        $cached = $this->repository->paginate('USA', page: 1, perPage: 10);

        $this->assertCount(1, $cached['data']);
    }

    public function test_it_registers_paginated_cache_key_in_registry_set(): void
    {
        $this->repository->upsert('USA', 1, ['id' => 1]);

        $this->repository->paginate('USA', page: 1, perPage: 10);

        $this->assertArrayHasKey('hub:employees:list:keys:USA', $this->redisSets);
        $this->assertArrayHasKey('hub:employees:list:USA:1:10', $this->redisSets['hub:employees:list:keys:USA']);
    }

    public function test_it_invalidates_all_paginated_employee_lists_for_country(): void
    {
        $this->repository->upsert('USA', 1, ['id' => 1]);
        $this->repository->paginate('USA', page: 1, perPage: 10);
        $this->repository->paginate('USA', page: 2, perPage: 10);

        $this->repository->invalidatePaginatedLists('USA');

        $this->assertNull(Cache::get('hub:employees:list:USA:1:10'));
        $this->assertNull(Cache::get('hub:employees:list:USA:2:10'));
        $this->assertArrayNotHasKey('hub:employees:list:keys:USA', $this->redisSets);
    }

    public function test_it_flushes_country_employee_hash_and_paginated_lists(): void
    {
        $this->repository->upsert('USA', 1, ['id' => 1]);
        $this->repository->paginate('USA', page: 1, perPage: 10);

        $this->repository->flushCountry('USA');

        $this->assertSame([], $this->repository->all('USA'));
        $this->assertNull(Cache::get('hub:employees:list:USA:1:10'));
        $this->assertArrayNotHasKey('hub:employees:list:keys:USA', $this->redisSets);
    }

    private function fakeHset(string $key, string $field, string $value): int
    {
        if (!isset($this->redisHashes[$key])) {
            $this->redisHashes[$key] = [];
        }

        $this->redisHashes[$key][$field] = $value;

        return 1;
    }

    private function fakeHdel(string $key, string $field): int
    {
        unset($this->redisHashes[$key][$field]);

        return 1;
    }

    private function fakeSadd(string $key, string $member): int
    {
        if (!isset($this->redisSets[$key])) {
            $this->redisSets[$key] = [];
        }

        $this->redisSets[$key][$member] = true;

        return 1;
    }

    private function fakeDel(string $key): int
    {
        unset($this->redisHashes[$key], $this->redisSets[$key]);

        return 1;
    }
}
