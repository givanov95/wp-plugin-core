<?php

declare(strict_types=1);

namespace WpPluginCore\Tests;

use PHPUnit\Framework\TestCase;
use WpPluginCore\Enums\ValidationRule;

final class SmokeTest extends TestCase
{
    public function test_validation_rule_enum_is_intact(): void
    {
        $this->assertSame('email', ValidationRule::EMAIL->value);
        $this->assertCount(6, ValidationRule::cases());
    }
}
