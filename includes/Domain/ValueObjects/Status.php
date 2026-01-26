<?php
/**
 * Status enum for locations
 *
 * @package Zul\Weather
 */

namespace Zul\Weather\Domain\ValueObjects;

enum Status: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

    public static function fromString(string $value): self
    {
        return match (strtolower($value)) {
            'active' => self::ACTIVE,
            'inactive' => self::INACTIVE,
            default => self::ACTIVE,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
        };
    }

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }
}
