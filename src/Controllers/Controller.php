<?php

namespace WpPluginCore\Controllers;

use InvalidArgumentException;
use WpPluginCore\Enums\ValidationRule;

class Controller
{
    protected function validate(array $data, array $rules): array
    {
        $validated = [];

        foreach ($rules as $key => $config) {
            $required = $config['required'] ?? false;
            $rule = $config['rule'] ?? ValidationRule::STRING;

            if (!array_key_exists($key, $data)) {
                if ($required) {
                    throw new InvalidArgumentException("Field '{$key}' is required.");
                } else {
                    continue;
                }
            }

            $value = $data[$key];

            $validated[$key] = $this->singleValidate($value, $required, $rule, $key);
        }

        return $validated;
    }

    /**
     * Validate fields
     *
     * @param  array $data
     * @param  array $rules
     * @return array
     */
    protected function singleValidate(mixed $value, bool $required, ValidationRule $rule, string $fieldName): mixed
    {
        if ($required && ($value === null || $value === '')) {
            throw new InvalidArgumentException("Field '{$fieldName}' is required.");
        }

        if (!$required && ($value === null || $value === '')) {
            return null;
        }


        switch ($rule) {
            case ValidationRule::EMAIL:
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    throw new InvalidArgumentException("Field '{$fieldName}' must be a valid email.");
                }
                break;

            case ValidationRule::URL:
                if (!filter_var($value, FILTER_VALIDATE_URL)) {
                    throw new InvalidArgumentException("Field '{$fieldName}' must be a valid URL.");
                }
                break;

            case ValidationRule::INT:
                if (filter_var($value, FILTER_VALIDATE_INT) === false) {
                    throw new InvalidArgumentException("Field '{$fieldName}' must be an integer.");
                }
                return (int) $value;
                break;

            case ValidationRule::BOOL:
                $boolVal = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($boolVal === null) {
                    throw new InvalidArgumentException("Field '{$fieldName}' must be boolean.");
                }
                break;

            case ValidationRule::RAW:
            case ValidationRule::STRING:
            default:
                // Тук няма допълнителна проверка, приемаме всичко
                break;
        }

        return $value;
    }

    /**
     * Sanitize fields array
     *
     * @param  array $data
     * @param  array $rules
     * @return array
     */
    protected function sanitize(array $data, array $rules): array
    {
        $sanitized = [];

        foreach ($rules as $key => $config) {
            if (!array_key_exists($key, $data)) {
                continue;
            }

            $value = $data[$key];
            $rule = $config['rule'] ?? ValidationRule::STRING;

            $sanitized[$key] = $this->singleSanitize($value, $rule);
        }

        return $sanitized;
    }

    /**
     * Single field sanitize
     *
     * @param  mixed                        $value
     * @param  \App\Core\Enums\ValidationRule $rule
     * @return mixed
     */
    protected function singleSanitize(mixed $value, ValidationRule $rule): mixed
    {
        return match ($rule) {
            ValidationRule::EMAIL  => sanitize_email($value),
            ValidationRule::URL    => esc_url_raw($value),
            ValidationRule::INT    => (int) $value,
            ValidationRule::BOOL   => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            ValidationRule::RAW    => wp_kses_post($value),
            default              => sanitize_text_field($value),
        };
    }
    protected function json($data, int $status = 200)
    {
        return wp_send_json($data, $status);
    }
}
