<?php

namespace Tests\Feature\Checklist;

use App\Services\ChecklistQueryService;
use Mockery\MockInterface;
use Tests\TestCase;

class ChecklistEndpointTest extends TestCase
{
    /** @var ChecklistQueryService&MockInterface */
    private MockInterface $checklistQueryService;

    public function setUp(): void
    {
        parent::setUp();

        $this->checklistQueryService = $this->mock(ChecklistQueryService::class);
    }

    public function test_it_returns_checklist_payload_for_valid_country(): void
    {
        $payload = [
            'country' => 'USA',
            'summary' => [
                'total_employees' => 1,
                'fully_complete_employees' => 1,
                'incomplete_employees' => 0,
                'average_completion_percentage' => 100,
            ],
            'employees' => [],
        ];

        $this->expectChecklistResponse('USA', $payload);

        $response = $this->getJson('/api/checklists?country=USA');

        $response->assertOk()->assertExactJson($payload);
    }

    public function test_it_validates_country_query_parameter_is_required(): void
    {
        $response = $this->getJson('/api/checklists');

        $response->assertStatus(422)->assertJsonValidationErrors(['country']);
    }

    public function test_it_validates_country_query_parameter_is_supported(): void
    {
        $response = $this->getJson('/api/checklists?country=France');

        $response->assertStatus(422)->assertJsonValidationErrors(['country']);
    }

    public function test_it_returns_checklist_summary_and_employee_results_shape(): void
    {
        $payload = [
            'country' => 'USA',
            'summary' => [
                'total_employees' => 1,
                'fully_complete_employees' => 1,
                'incomplete_employees' => 0,
                'average_completion_percentage' => 100,
            ],
            'employees' => [
                [
                    'employee_id' => 1,
                    'checks' => ['ssn' => ['complete' => true, 'message' => 'ok']],
                    'completed_fields' => ['ssn'],
                    'missing_fields' => [],
                    'completion_percentage' => 100,
                    'is_complete' => true,
                ],
            ],
        ];

        $this->expectChecklistResponse('USA', $payload);

        $response = $this->getJson('/api/checklists?country=USA');

        $response->assertOk()
            ->assertJsonStructure([
                'country',
                'summary' => [
                    'total_employees',
                    'fully_complete_employees',
                    'incomplete_employees',
                    'average_completion_percentage',
                ],
                'employees' => [
                    [
                        'employee_id',
                        'checks',
                        'completed_fields',
                        'missing_fields',
                        'completion_percentage',
                        'is_complete',
                    ],
                ],
            ]);
    }

    public function test_it_returns_empty_summary_when_country_has_no_employees(): void
    {
        $payload = [
            'country' => 'Germany',
            'summary' => [
                'total_employees' => 0,
                'fully_complete_employees' => 0,
                'incomplete_employees' => 0,
                'average_completion_percentage' => 0,
            ],
            'employees' => [],
        ];

        $this->expectChecklistResponse('Germany', $payload);

        $response = $this->getJson('/api/checklists?country=Germany');

        $response->assertOk()->assertExactJson($payload);
    }

    private function expectChecklistResponse(string $country, array $payload): void
    {
        $this->checklistQueryService
            ->shouldReceive('getByCountry')
            ->once()
            ->with($country)
            ->andReturn($payload);
    }
}
