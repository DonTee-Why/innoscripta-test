<?php

namespace Tests;

use App\Contracts\EventPublisher;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\EmployeeSeeder;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->mock(EventPublisher::class, function ($mock) {
            $mock->shouldReceive('publish')->andReturnNull();
        });

        $this->seed(EmployeeSeeder::class);
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}
