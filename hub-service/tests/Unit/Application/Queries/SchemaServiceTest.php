<?php

namespace Tests\Unit\Application\Queries;

use App\Services\SchemaService;
use InvalidArgumentException;
use Tests\TestCase;

class SchemaServiceTest extends TestCase
{
    private SchemaService $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = new SchemaService();
    }

    public function test_it_returns_usa_dashboard_widgets(): void
    {
        $result = $this->service->getByStepAndCountry('dashboard', 'USA');

        $this->assertSame('USA', $result['country']);
        $this->assertSame('dashboard', $result['step_id']);
        $this->assertCount(3, $result['widgets']);
        $this->assertSame('employee_count', $result['widgets'][0]['id']);
        $this->assertSame('completion_rate', $result['widgets'][2]['id']);
    }

    public function test_it_returns_germany_dashboard_widgets(): void
    {
        $result = $this->service->getByStepAndCountry('dashboard', 'Germany');

        $this->assertSame('Germany', $result['country']);
        $this->assertSame('dashboard', $result['step_id']);
        $this->assertCount(2, $result['widgets']);
        $this->assertSame('goal_tracking', $result['widgets'][1]['id']);
    }

    public function test_it_returns_employees_widget_for_employees_step(): void
    {
        $result = $this->service->getByStepAndCountry('employees', 'USA');

        $this->assertSame('employees', $result['step_id']);
        $this->assertCount(1, $result['widgets']);
        $this->assertSame('employees_table', $result['widgets'][0]['id']);
        $this->assertSame('/api/employees?country=USA', $result['widgets'][0]['data_source']);
    }

    public function test_it_returns_documentation_widget_only_for_germany(): void
    {
        $result = $this->service->getByStepAndCountry('documentation', 'Germany');

        $this->assertSame('Germany', $result['country']);
        $this->assertSame('documentation', $result['step_id']);
        $this->assertCount(1, $result['widgets']);
        $this->assertSame('documentation_panel', $result['widgets'][0]['id']);
    }

    public function test_it_returns_empty_documentation_widgets_for_non_germany(): void
    {
        $result = $this->service->getByStepAndCountry('documentation', 'USA');

        $this->assertSame('USA', $result['country']);
        $this->assertSame('documentation', $result['step_id']);
        $this->assertSame([], $result['widgets']);
    }

    public function test_it_throws_for_unsupported_step(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported step [unknown]');

        $this->service->getByStepAndCountry('unknown', 'USA');
    }
}
