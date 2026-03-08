<?php

namespace Tests\Unit\Services\Queries;

use App\Infrastructure\Cache\EmployeeCacheRepository;
use App\Services\EmployeeQueryService;
use InvalidArgumentException;
use Mockery\MockInterface;
use Tests\TestCase;

class EmployeesQueryServiceTest extends TestCase
{
    /** @var EmployeeCacheRepository&MockInterface */
    private MockInterface $employeeCacheRepository;

    private EmployeeQueryService $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->employeeCacheRepository = $this->mock(EmployeeCacheRepository::class);
        $this->service = new EmployeeQueryService($this->employeeCacheRepository);
    }

    public function test_it_returns_usa_columns_for_usa_country(): void
    {
        $this->employeeCacheRepository
            ->shouldReceive('paginate')
            ->once()
            ->with('USA', 1, 15)
            ->andReturn([
                'data' => [],
                'meta' => ['page' => 1, 'per_page' => 15, 'total' => 0, 'last_page' => 0],
            ]);

        $result = $this->service->getByCountry('USA');

        $this->assertSame('USA', $result['country']);
        $this->assertSame(
            ['name', 'last_name', 'salary', 'ssn'],
            array_column($result['columns'], 'key')
        );
        $this->assertSame('masked-text', $result['columns'][3]['type']);
    }

    public function test_it_returns_germany_columns_for_germany_country(): void
    {
        $this->employeeCacheRepository
            ->shouldReceive('paginate')
            ->once()
            ->with('Germany', 1, 15)
            ->andReturn([
                'data' => [],
                'meta' => ['page' => 1, 'per_page' => 15, 'total' => 0, 'last_page' => 0],
            ]);

        $result = $this->service->getByCountry('Germany');

        $this->assertSame('Germany', $result['country']);
        $this->assertSame(
            ['name', 'last_name', 'salary', 'goal'],
            array_column($result['columns'], 'key')
        );
    }

    public function test_it_masks_ssn_for_usa_employees(): void
    {
        $this->employeeCacheRepository
            ->shouldReceive('paginate')
            ->once()
            ->with('USA', 1, 15)
            ->andReturn([
                'data' => [
                    [
                        'id' => 1,
                        'name' => 'John',
                        'last_name' => 'Doe',
                        'salary' => 120000,
                        'ssn' => '123-45-6789',
                        'country' => 'USA',
                    ],
                ],
                'meta' => ['page' => 1, 'per_page' => 15, 'total' => 1, 'last_page' => 1],
            ]);

        $result = $this->service->getByCountry('USA');

        $this->assertSame('***-**-6789', $result['data'][0]['ssn']);
    }

    public function test_it_returns_goal_for_germany_employees(): void
    {
        $this->employeeCacheRepository
            ->shouldReceive('paginate')
            ->once()
            ->with('Germany', 1, 15)
            ->andReturn([
                'data' => [
                    [
                        'id' => 2,
                        'name' => 'Anna',
                        'last_name' => 'Meyer',
                        'salary' => 90000,
                        'goal' => 'Increase enterprise sales',
                        'country' => 'Germany',
                    ],
                ],
                'meta' => ['page' => 1, 'per_page' => 15, 'total' => 1, 'last_page' => 1],
            ]);

        $result = $this->service->getByCountry('Germany');

        $this->assertSame('Increase enterprise sales', $result['data'][0]['goal']);
        $this->assertArrayNotHasKey('ssn', $result['data'][0]);
    }

    public function test_it_throws_for_unsupported_country(): void
    {
        $this->employeeCacheRepository
            ->shouldReceive('paginate')
            ->once()
            ->with('France', 1, 15)
            ->andReturn([
                'data' => [],
                'meta' => ['page' => 1, 'per_page' => 15, 'total' => 0, 'last_page' => 0],
            ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported country [France]');

        $this->service->getByCountry('France');
    }
}
