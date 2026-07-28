<?php

declare(strict_types=1);

namespace App\Enums;

enum PlatformEnum: string
{
    case OPENAI = 'openai';
    case ANTHROPIC = 'anthropic';
    case GEMINI = 'gemini';
}
