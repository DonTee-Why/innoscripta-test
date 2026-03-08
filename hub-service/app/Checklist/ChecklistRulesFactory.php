<?php

declare(strict_types=1);

namespace App\Checklist;

use App\Enums\Country;
use App\Checklist\Rules\GermanyRules;
use App\Checklist\Rules\UsaRules;
use App\Contracts\CountryChecklistRulesInterface;
use InvalidArgumentException;

final class ChecklistRulesFactory
{
    public function make(string $country): CountryChecklistRulesInterface
    {
        return match ($country) {
            Country::USA => app(UsaRules::class),
            Country::Germany => app(GermanyRules::class),
            default => throw new InvalidArgumentException("Unsupported country [{$country}]"),
        };
    }
}
