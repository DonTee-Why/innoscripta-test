<?php

namespace Tests\Unit\Services;

use App\Enums\Country;
use App\Services\StepsService;
use InvalidArgumentException;
use Tests\TestCase;

class StepsServiceTest extends TestCase
{
    private StepsService $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = new StepsService();
    }

    public function test_it_returns_usa_steps(): void
    {
        $country = Country::USA;
        $result = $this->service->getByCountry($country);

        $this->assertSame($country, $result['country']);
        $this->assertCount(2, $result['steps']);
        $this->assertSame('dashboard', $result['steps'][0]['id']);
        $this->assertSame('employees', $result['steps'][1]['id']);
    }

    public function test_it_returns_germany_steps(): void
    {
        $country = Country::Germany;
        $result = $this->service->getByCountry($country);

        $this->assertSame($country, $result['country']);
        $this->assertCount(3, $result['steps']);
        $this->assertSame('documentation', $result['steps'][2]['id']);
    }

    public function test_it_throws_for_unsupported_country(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported country [France]');

        $this->service->getByCountry('France');
    }
}
