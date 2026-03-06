<?php

namespace Tests\Unit\Http\Resources;

use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use Illuminate\Http\Request;
use Tests\TestCase;

class EmployeeResourceTest extends TestCase
{
    public function test_employee_resource_returns_expected_fields_for_usa_employee(): void
    {
        $employee = Employee::where('country', 'USA')->first();
        $resource = new EmployeeResource($employee);
        $request = Request::create('/test');

        $array = $resource->toArray($request);

        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('last_name', $array);
        $this->assertArrayHasKey('full_name', $array);
        $this->assertArrayHasKey('country', $array);
        $this->assertArrayHasKey('salary', $array);
        $this->assertArrayHasKey('ssn', $array);
        $this->assertArrayHasKey('address', $array);
        $this->assertArrayHasKey('tax_id', $array);
        $this->assertArrayHasKey('goal', $array);
        $this->assertArrayHasKey('created_at', $array);
        $this->assertArrayHasKey('updated_at', $array);

        $this->assertEquals('USA', $array['country']);
        $this->assertNotNull($array['ssn']);
        $this->assertNotNull($array['address']);
    }

    public function test_employee_resource_returns_expected_fields_for_germany_employee(): void
    {
        $employee = Employee::where('country', 'Germany')->first();
        $resource = new EmployeeResource($employee);
        $request = Request::create('/test');

        $array = $resource->toArray($request);

        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('last_name', $array);
        $this->assertArrayHasKey('full_name', $array);
        $this->assertArrayHasKey('country', $array);
        $this->assertArrayHasKey('salary', $array);
        $this->assertArrayHasKey('ssn', $array);
        $this->assertArrayHasKey('address', $array);
        $this->assertArrayHasKey('tax_id', $array);
        $this->assertArrayHasKey('goal', $array);
        $this->assertArrayHasKey('created_at', $array);
        $this->assertArrayHasKey('updated_at', $array);

        $this->assertEquals('Germany', $array['country']);
        $this->assertNotNull($array['tax_id']);
        $this->assertNotNull($array['goal']);
    }

    public function test_employee_resource_does_not_omit_required_common_fields(): void
    {
        $employee = Employee::first();
        $resource = new EmployeeResource($employee);
        $request = Request::create('/test');

        $array = $resource->toArray($request);

        $requiredFields = ['id', 'name', 'last_name', 'full_name', 'country', 'salary', 'created_at', 'updated_at'];

        foreach ($requiredFields as $field) {
            $this->assertArrayHasKey($field, $array, "Missing required field: {$field}");
        }
    }
}
