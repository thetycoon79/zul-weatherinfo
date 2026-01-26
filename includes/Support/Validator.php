<?php
/**
 * Input validation helpers
 *
 * @package Zul\Weather
 */

namespace Zul\Weather\Support;

class Validator
{
    private array $errors = [];

    public function required(string $field, $value, ?string $message = null): self
    {
        if (empty($value) && $value !== '0' && $value !== 0 && $value !== 0.0) {
            $this->errors[$field] = $message ?? sprintf('%s is required.', $this->humanize($field));
        }
        return $this;
    }

    public function minLength(string $field, $value, int $length, ?string $message = null): self
    {
        if (!empty($value) && strlen($value) < $length) {
            $this->errors[$field] = $message ?? sprintf('%s must be at least %d characters.', $this->humanize($field), $length);
        }
        return $this;
    }

    public function maxLength(string $field, $value, int $length, ?string $message = null): self
    {
        if (!empty($value) && strlen($value) > $length) {
            $this->errors[$field] = $message ?? sprintf('%s must not exceed %d characters.', $this->humanize($field), $length);
        }
        return $this;
    }

    public function numeric(string $field, $value, ?string $message = null): self
    {
        if (!empty($value) && !is_numeric($value)) {
            $this->errors[$field] = $message ?? sprintf('%s must be a number.', $this->humanize($field));
        }
        return $this;
    }

    public function positiveInt(string $field, $value, ?string $message = null): self
    {
        if ($value !== null && $value !== '' && (!is_numeric($value) || (int) $value < 1)) {
            $this->errors[$field] = $message ?? sprintf('%s must be a positive integer.', $this->humanize($field));
        }
        return $this;
    }

    public function latitude(string $field, $value, ?string $message = null): self
    {
        if ($value !== null && $value !== '') {
            if (!is_numeric($value) || (float) $value < -90 || (float) $value > 90) {
                $this->errors[$field] = $message ?? sprintf('%s must be between -90 and 90.', $this->humanize($field));
            }
        }
        return $this;
    }

    public function longitude(string $field, $value, ?string $message = null): self
    {
        if ($value !== null && $value !== '') {
            if (!is_numeric($value) || (float) $value < -180 || (float) $value > 180) {
                $this->errors[$field] = $message ?? sprintf('%s must be between -180 and 180.', $this->humanize($field));
            }
        }
        return $this;
    }

    public function inArray(string $field, $value, array $allowed, ?string $message = null): self
    {
        if (!empty($value) && !in_array($value, $allowed, true)) {
            $this->errors[$field] = $message ?? sprintf('%s contains an invalid value.', $this->humanize($field));
        }
        return $this;
    }

    public function url(string $field, $value, ?string $message = null): self
    {
        if (!empty($value) && filter_var($value, FILTER_VALIDATE_URL) === false) {
            $this->errors[$field] = $message ?? sprintf('%s must be a valid URL.', $this->humanize($field));
        }
        return $this;
    }

    public function addError(string $field, string $message): self
    {
        $this->errors[$field] = $message;
        return $this;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getError(string $field): ?string
    {
        return $this->errors[$field] ?? null;
    }

    public function reset(): self
    {
        $this->errors = [];
        return $this;
    }

    public function validate(): bool
    {
        return !$this->hasErrors();
    }

    private function humanize(string $field): string
    {
        return ucfirst(str_replace(['_', '-'], ' ', $field));
    }

    public static function sanitizeText(?string $value): string
    {
        return sanitize_text_field($value ?? '');
    }

    public static function sanitizeTextarea(?string $value): string
    {
        return sanitize_textarea_field($value ?? '');
    }

    public static function sanitizeInt($value): int
    {
        return absint($value);
    }

    public static function sanitizeFloat($value): float
    {
        return (float) filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    public static function sanitizeIds(array $ids): array
    {
        return array_filter(array_map('absint', $ids));
    }
}
