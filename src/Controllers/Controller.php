<?php

namespace WpPluginCore\Controllers;

use InvalidArgumentException;
use WpPluginCore\Enums\ValidationRule;

class Controller
{
    /**
     * Validate input data against a rules definition.
     *
     * @param array $data
     * @param array<string, array{required?: bool, rule?: ValidationRule}> $rules
     * @return array
     */
    protected function validate(array $data, array $rules): array
    {
        $validated = [];

        foreach ($rules as $key => $config) {
            $required = $config['required'] ?? false;
            $rule     = $config['rule'] ?? ValidationRule::STRING;

            if (!array_key_exists($key, $data)) {
                if ($required) {
                    throw new InvalidArgumentException("Field '{$key}' is required.");
                }
                continue;
            }

            $validated[$key] = $this->singleValidate($data[$key], $required, $rule, $key);
        }

        return $validated;
    }

    protected function singleValidate(mixed $value, bool $required, ValidationRule $rule, string $fieldName): mixed
    {
        if ($value === null || $value === '') {
            if ($required) {
                throw new InvalidArgumentException("Field '{$fieldName}' is required.");
            }
            return null;
        }

        switch ($rule) {
            case ValidationRule::EMAIL:
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    throw new InvalidArgumentException("Field '{$fieldName}' must be a valid email.");
                }
                return $value;

            case ValidationRule::URL:
                if (!filter_var($value, FILTER_VALIDATE_URL)) {
                    throw new InvalidArgumentException("Field '{$fieldName}' must be a valid URL.");
                }
                return $value;

            case ValidationRule::INT:
                if (filter_var($value, FILTER_VALIDATE_INT) === false) {
                    throw new InvalidArgumentException("Field '{$fieldName}' must be an integer.");
                }
                return (int) $value;

            case ValidationRule::BOOL:
                $boolVal = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($boolVal === null) {
                    throw new InvalidArgumentException("Field '{$fieldName}' must be boolean.");
                }
                return $boolVal;

            case ValidationRule::RAW:
            case ValidationRule::STRING:
            default:
                return $value;
        }
    }

    /**
     * Sanitize input data using WordPress helpers.
     *
     * @param array $data
     * @param array<string, array{rule?: ValidationRule}> $rules
     * @return array
     */
    protected function sanitize(array $data, array $rules): array
    {
        $sanitized = [];

        foreach ($rules as $key => $config) {
            if (!array_key_exists($key, $data)) {
                continue;
            }

            $rule = $config['rule'] ?? ValidationRule::STRING;
            $sanitized[$key] = $this->singleSanitize($data[$key], $rule);
        }

        return $sanitized;
    }

    protected function singleSanitize(mixed $value, ValidationRule $rule): mixed
    {
        return match ($rule) {
            ValidationRule::EMAIL  => sanitize_email($value),
            ValidationRule::URL    => esc_url_raw($value),
            ValidationRule::INT    => (int) $value,
            ValidationRule::BOOL   => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            ValidationRule::RAW    => wp_kses_post($value),
            default                => sanitize_text_field($value),
        };
    }

    protected function json(mixed $data, int $status = 200): void
    {
        wp_send_json($data, $status);
    }

    protected function success(mixed $data = null, int $status = 200): void
    {
        wp_send_json(['success' => true, 'data' => $data], $status);
    }

    protected function error(string $message, string $code = 'error', int $status = 400, mixed $data = null): void
    {
        wp_send_json([
            'success' => false,
            'error'   => array_filter([
                'code'    => $code,
                'message' => $message,
                'data'    => $data,
            ], static fn ($v) => $v !== null),
        ], $status);
    }
}
