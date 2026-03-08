<?php

namespace Tests\Unit\Checklist;

use App\Checklist\ChecklistRulesFactory;
use App\Checklist\Rules\GermanyRules;
use App\Checklist\Rules\UsaRules;
use InvalidArgumentException;
use Tests\TestCase;

class ChecklistRulesFactoryTest extends TestCase
{
    public function test_it_returns_usa_rules_for_usa_country(): void
    {
        $factory = new ChecklistRulesFactory();

        $rules = $factory->make('USA');

        $this->assertInstanceOf(UsaRules::class, $rules);
    }

    public function test_it_returns_germany_rules_for_germany_country(): void
    {
        $factory = new ChecklistRulesFactory();

        $rules = $factory->make('Germany');

        $this->assertInstanceOf(GermanyRules::class, $rules);
    }

    public function test_it_throws_for_non_canonical_country_casing(): void
    {
        $factory = new ChecklistRulesFactory();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported country [UsA]');

        $factory->make('UsA');
    }

    public function test_it_throws_for_unsupported_country(): void
    {
        $factory = new ChecklistRulesFactory();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported country [france]');

        $factory->make('france');
    }
}
