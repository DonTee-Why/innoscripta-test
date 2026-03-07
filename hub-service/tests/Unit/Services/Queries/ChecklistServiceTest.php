<?php

namespace Tests\Unit\Services\Queries;

use App\Checklist\ChecklistEngine;
use App\Infrastructure\Cache\ChecklistCacheRepository;
use App\Infrastructure\Cache\EmployeeCacheRepository;
use App\Services\ChecklistService;
use Mockery;
use Tests\TestCase;

class ChecklistServiceTest extends TestCase
{
    public function test_it_returns_cached_checklist_payload_when_present(): void
    {
        $employeeCache = Mockery::mock(EmployeeCacheRepository::class);
        $checklistCache = Mockery::mock(ChecklistCacheRepository::class);
        $engine = Mockery::mock(ChecklistEngine::class);

        $cached = ['country' => 'USA', 'summary' => ['total_employees' => 1], 'employees' => []];

        $checklistCache->shouldReceive('remember')->once()->with('USA', Mockery::type('callable'))->andReturn($cached);
        $employeeCache->shouldNotReceive('all');
        $engine->shouldNotReceive('evaluate');

        $service = new ChecklistService($employeeCache, $checklistCache, $engine);

        $this->assertSame($cached, $service->getByCountry('USA'));
    }

    public function test_it_computes_checklist_payload_when_cache_is_missing(): void
    {
        $employeeCache = Mockery::mock(EmployeeCacheRepository::class);
        $checklistCache = Mockery::mock(ChecklistCacheRepository::class);
        $engine = Mockery::mock(ChecklistEngine::class);

        $employees = [['id' => 1]];
        $computed = ['country' => 'USA', 'summary' => ['total_employees' => 1], 'employees' => []];

        $checklistCache->shouldReceive('remember')
            ->once()
            ->with('USA', Mockery::type('callable'))
            ->andReturnUsing(fn (string $country, callable $callback) => $callback());

        $employeeCache->shouldReceive('all')->once()->with('USA')->andReturn($employees);
        $engine->shouldReceive('evaluate')->once()->with('USA', $employees)->andReturn($computed);

        $service = new ChecklistService($employeeCache, $checklistCache, $engine);

        $this->assertSame($computed, $service->getByCountry('USA'));
    }

    public function test_it_reads_employees_from_employee_cache_when_cache_is_missing(): void
    {
        $employeeCache = Mockery::mock(EmployeeCacheRepository::class);
        $checklistCache = Mockery::mock(ChecklistCacheRepository::class);
        $engine = Mockery::mock(ChecklistEngine::class);

        $employees = [['id' => 1], ['id' => 2]];

        $checklistCache->shouldReceive('remember')->once()->andReturnUsing(fn (string $country, callable $callback) => $callback());
        $employeeCache->shouldReceive('all')->once()->with('USA')->andReturn($employees);
        $engine->shouldReceive('evaluate')->once()->with('USA', $employees)->andReturn(['country' => 'USA']);

        $service = new ChecklistService($employeeCache, $checklistCache, $engine);
        $service->getByCountry('USA');

        $this->assertTrue(true);
    }

    public function test_it_stores_computed_checklist_payload_in_cache_when_cache_is_missing(): void
    {
        $employeeCache = Mockery::mock(EmployeeCacheRepository::class);
        $checklistCache = Mockery::mock(ChecklistCacheRepository::class);
        $engine = Mockery::mock(ChecklistEngine::class);

        $checklistCache->shouldReceive('remember')->once()->with('USA', Mockery::type('callable'))->andReturnUsing(
            fn (string $country, callable $callback) => $callback()
        );
        $employeeCache->shouldReceive('all')->once()->with('USA')->andReturn([]);
        $engine->shouldReceive('evaluate')->once()->with('USA', [])->andReturn(['country' => 'USA', 'summary' => [], 'employees' => []]);

        $service = new ChecklistService($employeeCache, $checklistCache, $engine);
        $service->getByCountry('USA');

        $this->assertTrue(true);
    }

    public function test_it_returns_computed_payload_after_engine_evaluation(): void
    {
        $employeeCache = Mockery::mock(EmployeeCacheRepository::class);
        $checklistCache = Mockery::mock(ChecklistCacheRepository::class);
        $engine = Mockery::mock(ChecklistEngine::class);

        $computed = ['country' => 'Germany', 'summary' => ['total_employees' => 0], 'employees' => []];

        $checklistCache->shouldReceive('remember')->once()->with('Germany', Mockery::type('callable'))->andReturnUsing(
            fn (string $country, callable $callback) => $callback()
        );
        $employeeCache->shouldReceive('all')->once()->with('Germany')->andReturn([]);
        $engine->shouldReceive('evaluate')->once()->with('Germany', [])->andReturn($computed);

        $service = new ChecklistService($employeeCache, $checklistCache, $engine);

        $this->assertSame($computed, $service->getByCountry('Germany'));
    }
}
