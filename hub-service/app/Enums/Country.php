<?php

declare(strict_types=1);

namespace App\Enums;

class Country
{
    public const USA = 'USA';
    public const Germany = 'Germany';

    /**
     * Return supported countries as an array of values.
     *
     * @return array<string>
     */
    public static function toArray(): array
    {
        return [
            self::USA,
            self::Germany,
        ];
    }
}
