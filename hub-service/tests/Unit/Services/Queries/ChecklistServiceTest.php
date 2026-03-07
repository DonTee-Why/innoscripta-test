<?php

namespace Tests\Unit\Services\Queries;

use App\Checklist\ChecklistEngine;
use App\Infrastructure\Cache\ChecklistCacheRepository;
use App\Infrastructure\Cache\EmployeeCacheRepository;
use App\Services\ChecklistQueryService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class ChecklistServiceTest extends TestCase
{
    /** @var EmployeeCacheRepository&MockInterface */
    private MockInterface $employeeCacheMock;
    /** @var ChecklistCacheRepository&MockInterface */
    private MockInterface $checklistCacheMock;
    /** @var ChecklistEngine&MockInterface */
    private MockInterface $checklistEngineMock;
    /** @var ChecklistQueryService */
    private ChecklistQueryService $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->employeeCacheMock = $this->mock(EmployeeCacheRepository::class);
        $this->checklistCacheMock = $this->mock(ChecklistCacheRepository::class);
        $this->checklistEngineMock = $this->mock(ChecklistEngine::class);
        $this->service = new ChecklistQueryService($this->employeeCacheMock, $this->checklistCacheMock, $this->checklistEngineMock);
    }

    public function test_it_returns_cached_checklist_payload_when_present(): void
    {
        $cached = ['country' => 'USA', 'summary' => ['total_employees' => 1], 'employees' => []];

        $this->checklistCacheMock->shouldReceive('remember')->once()->with('USA', Mockery::type('callable'))->andReturn($cached);
        $this->employeeCacheMock->shouldNotReceive('all');
        $this->checklistEngineMock->shouldNotReceive('evaluate');

        $this->assertSame($cached, $this->service->getByCountry('USA'));
    }

    public function test_it_computes_checklist_payload_when_cache_is_missing(): void
    {
        $employees = [['id' => 1]];
        $computed = ['country' => 'USA', 'summary' => ['total_employees' => 1], 'employees' => []];

        $this->checklistCacheMock->shouldReceive('remember')
            ->once()
            ->with('USA', Mockery::type('callable'))
            ->andReturnUsing(fn (string $country, callable $callback) => $callback());

        $this->employeeCacheMock->shouldReceive('all')->once()->with('USA')->andReturn($employees);
        $this->checklistEngineMock->shouldReceive('evaluate')->once()->with('USA', $employees)->andReturn($computed);

        $this->assertSame($computed, $this->service->getByCountry('USA'));
    }

    public function test_it_reads_employees_from_employee_cache_when_cache_is_missing(): void
    {
        $employees = [['id' => 1], ['id' => 2]];

        $this->checklistCacheMock->shouldReceive('remember')->once()->andReturnUsing(fn (string $country, callable $callback) => $callback());
        $this->employeeCacheMock->shouldReceive('all')->once()->with('USA')->andReturn($employees);
        $this->checklistEngineMock->shouldReceive('evaluate')->once()->with('USA', $employees)->andReturnUsing(
            fn (): array => ['country' => 'USA']
        );

        $this->service->getByCountry('USA');

        $this->assertTrue(true);
    }

    public function test_it_stores_computed_checklist_payload_in_cache_when_cache_is_missing(): void
    {
        $this->checklistCacheMock->shouldReceive('remember')->once()->with('USA', Mockery::type('callable'))->andReturnUsing(
            fn (string $country, callable $callback) => $callback()
        );
        $this->employeeCacheMock->shouldReceive('all')->once()->with('USA')->andReturn([]);
        $this->checklistEngineMock->shouldReceive('evaluate')->once()->with('USA', [])->andReturn(['country' => 'USA', 'summary' => [], 'employees' => []]);

        $this->service->getByCountry('USA');

        $this->assertTrue(true);
    }

    public function test_it_returns_computed_payload_after_engine_evaluation(): void
    {
        $computed = ['country' => 'Germany', 'summary' => ['total_employees' => 0], 'employees' => []];

        $this->checklistCacheMock->shouldReceive('remember')->once()->with('Germany', Mockery::type('callable'))->andReturnUsing(
            fn (string $country, callable $callback) => $callback()
        );
        $this->employeeCacheMock->shouldReceive('all')->once()->with('Germany')->andReturn([]);
        $this->checklistEngineMock->shouldReceive('evaluate')->once()->with('Germany', [])->andReturn($computed);

        $this->assertSame($computed, $this->service->getByCountry('Germany'));
    }
}
