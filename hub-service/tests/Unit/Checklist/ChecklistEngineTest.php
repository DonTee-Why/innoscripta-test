<?php

namespace Tests\Unit\Checklist;

use App\Checklist\ChecklistEngine;
use App\Checklist\ChecklistRulesFactory;
use Tests\TestCase;

class ChecklistEngineTest extends TestCase
{
    private ChecklistEngine $engine;

    public function setUp(): void
    {
        parent::setUp();

        $this->engine = new ChecklistEngine(new ChecklistRulesFactory());
    }

    public function test_it_evaluates_a_fully_complete_usa_employee_correctly(): void
    {
        $result = $this->engine->evaluate('USA', [
            [
                'id' => 1,
                'salary' => 75000,
                'ssn' => '123-45-6789',
                'address' => '123 Main St',
            ],
        ]);

        $employeeResult = $result['employees'][0];

        $this->assertSame('USA', $result['country']);
        $this->assertTrue($employeeResult['is_complete']);
        $this->assertSame(100, $employeeResult['completion_percentage']);
        $this->assertSame(['ssn', 'salary', 'address'], $employeeResult['completed_fields']);
        $this->assertSame([], $employeeResult['missing_fields']);
    }

    public function test_it_marks_usa_employee_ssn_as_missing_when_absent(): void
    {
        $result = $this->engine->evaluate('USA', [
            [
                'id' => 1,
                'salary' => 75000,
                'address' => '123 Main St',
            ],
        ]);

        $employeeResult = $result['employees'][0];

        $this->assertFalse($employeeResult['checks']['ssn']['complete']);
        $this->assertSame(['ssn'], $employeeResult['missing_fields']);
    }

    public function test_it_marks_usa_employee_address_as_missing_when_absent(): void
    {
        $result = $this->engine->evaluate('USA', [
            [
                'id' => 1,
                'salary' => 75000,
                'ssn' => '123-45-6789',
                'address' => '',
            ],
        ]);

        $employeeResult = $result['employees'][0];

        $this->assertFalse($employeeResult['checks']['address']['complete']);
        $this->assertSame(['address'], $employeeResult['missing_fields']);
    }

    public function test_it_marks_usa_employee_salary_as_incomplete_when_zero_or_less(): void
    {
        $result = $this->engine->evaluate('USA', [
            [
                'id' => 1,
                'salary' => 0,
                'ssn' => '123-45-6789',
                'address' => '123 Main St',
            ],
        ]);

        $employeeResult = $result['employees'][0];

        $this->assertFalse($employeeResult['checks']['salary']['complete']);
        $this->assertSame(['salary'], $employeeResult['missing_fields']);
    }

    public function test_it_evaluates_a_fully_complete_germany_employee_correctly(): void
    {
        $result = $this->engine->evaluate('Germany', [
            [
                'id' => 1,
                'salary' => 80000,
                'goal' => 'Improve delivery',
                'tax_id' => 'DE123456789',
            ],
        ]);

        $employeeResult = $result['employees'][0];

        $this->assertSame('Germany', $result['country']);
        $this->assertTrue($employeeResult['is_complete']);
        $this->assertSame(['salary', 'goal', 'tax_id'], $employeeResult['completed_fields']);
    }

    public function test_it_marks_germany_employee_goal_as_missing_when_absent(): void
    {
        $result = $this->engine->evaluate('Germany', [
            [
                'id' => 1,
                'salary' => 80000,
                'goal' => '',
                'tax_id' => 'DE123456789',
            ],
        ]);

        $employeeResult = $result['employees'][0];

        $this->assertFalse($employeeResult['checks']['goal']['complete']);
        $this->assertSame(['goal'], $employeeResult['missing_fields']);
    }

    public function test_it_marks_germany_employee_salary_as_incomplete_when_zero_or_less(): void
    {
        $result = $this->engine->evaluate('Germany', [
            [
                'id' => 1,
                'salary' => 0,
                'goal' => 'Improve delivery',
                'tax_id' => 'DE123456789',
            ],
        ]);

        $employeeResult = $result['employees'][0];

        $this->assertFalse($employeeResult['checks']['salary']['complete']);
        $this->assertSame(['salary'], $employeeResult['missing_fields']);
    }

    public function test_it_marks_germany_employee_tax_id_as_incomplete_when_format_is_invalid(): void
    {
        $result = $this->engine->evaluate('Germany', [
            [
                'id' => 1,
                'salary' => 80000,
                'goal' => 'Improve delivery',
                'tax_id' => '12345',
            ],
        ]);

        $employeeResult = $result['employees'][0];

        $this->assertFalse($employeeResult['checks']['tax_id']['complete']);
        $this->assertSame(['tax_id'], $employeeResult['missing_fields']);
    }

    public function test_it_returns_completed_fields_for_each_employee(): void
    {
        $result = $this->engine->evaluate('USA', [
            [
                'id' => 1,
                'salary' => 50000,
                'ssn' => '123-45-6789',
                'address' => '',
            ],
        ]);

        $this->assertSame(['ssn', 'salary'], $result['employees'][0]['completed_fields']);
    }

    public function test_it_returns_missing_fields_for_each_employee(): void
    {
        $result = $this->engine->evaluate('USA', [
            [
                'id' => 1,
                'salary' => 50000,
                'ssn' => null,
                'address' => '',
            ],
        ]);

        $this->assertSame(['ssn', 'address'], $result['employees'][0]['missing_fields']);
    }

    public function test_it_returns_completion_percentage_for_each_employee(): void
    {
        $result = $this->engine->evaluate('USA', [
            [
                'id' => 1,
                'salary' => 50000,
                'ssn' => '123-45-6789',
                'address' => '',
            ],
        ]);

        $this->assertSame(67, $result['employees'][0]['completion_percentage']);
    }

    public function test_it_marks_employee_as_complete_when_all_checks_pass(): void
    {
        $result = $this->engine->evaluate('USA', [
            [
                'id' => 1,
                'salary' => 50000,
                'ssn' => '123-45-6789',
                'address' => '123 Main St',
            ],
        ]);

        $this->assertTrue($result['employees'][0]['is_complete']);
    }

    public function test_it_marks_employee_as_incomplete_when_any_check_fails(): void
    {
        $result = $this->engine->evaluate('USA', [
            [
                'id' => 1,
                'salary' => 50000,
                'ssn' => null,
                'address' => '123 Main St',
            ],
        ]);

        $this->assertFalse($result['employees'][0]['is_complete']);
    }

    public function test_it_aggregates_summary_for_multiple_employees(): void
    {
        $result = $this->engine->evaluate('USA', [
            [
                'id' => 1,
                'salary' => 75000,
                'ssn' => '123-45-6789',
                'address' => '123 Main St',
            ],
            [
                'id' => 2,
                'salary' => 50000,
                'ssn' => null,
                'address' => '456 Side St',
            ],
        ]);

        $this->assertSame(2, $result['summary']['total_employees']);
        $this->assertSame(84, $result['summary']['average_completion_percentage']);
    }

    public function test_it_counts_fully_complete_employees_correctly(): void
    {
        $result = $this->engine->evaluate('USA', [
            ['id' => 1, 'salary' => 1, 'ssn' => '123-45-6789', 'address' => 'x'],
            ['id' => 2, 'salary' => 1, 'ssn' => null, 'address' => 'x'],
            ['id' => 3, 'salary' => 1, 'ssn' => '123-45-6789', 'address' => 'x'],
        ]);

        $this->assertSame(2, $result['summary']['fully_complete_employees']);
    }

    public function test_it_counts_incomplete_employees_correctly(): void
    {
        $result = $this->engine->evaluate('USA', [
            ['id' => 1, 'salary' => 1, 'ssn' => '123-45-6789', 'address' => 'x'],
            ['id' => 2, 'salary' => 1, 'ssn' => null, 'address' => 'x'],
            ['id' => 3, 'salary' => 1, 'ssn' => null, 'address' => 'x'],
        ]);

        $this->assertSame(2, $result['summary']['incomplete_employees']);
    }

    public function test_it_calculates_average_completion_percentage_correctly(): void
    {
        $result = $this->engine->evaluate('USA', [
            ['id' => 1, 'salary' => 1, 'ssn' => '123-45-6789', 'address' => 'x'], // 100
            ['id' => 2, 'salary' => 1, 'ssn' => null, 'address' => 'x'], // 67
            ['id' => 3, 'salary' => 1, 'ssn' => null, 'address' => null], // 33
        ]);

        $this->assertSame(67, $result['summary']['average_completion_percentage']);
    }

    public function test_it_returns_zeroed_summary_when_no_employees_exist(): void
    {
        $result = $this->engine->evaluate('USA', []);

        $this->assertSame(0, $result['summary']['total_employees']);
        $this->assertSame(0, $result['summary']['fully_complete_employees']);
        $this->assertSame(0, $result['summary']['incomplete_employees']);
        $this->assertSame(0, $result['summary']['average_completion_percentage']);
    }

    public function test_it_returns_empty_employee_results_when_no_employees_exist(): void
    {
        $result = $this->engine->evaluate('USA', []);

        $this->assertSame([], $result['employees']);
    }
}
