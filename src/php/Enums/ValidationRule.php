<?php

namespace WpPluginCore\Enums;

enum ValidationRule: string
{
    case STRING = 'string';
    case INT = 'int';
    case BOOL = 'bool';
    case EMAIL = 'email';
    case URL = 'url';
    case RAW = 'raw';
}
