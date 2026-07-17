<?php

declare(strict_types=1);

namespace App\Enums;

enum AnthropicEnum: string
{
    case ANTHROPIC_COLLECTION_ALL_MODELS_NAME = 'anthropic_get_all_models';
    case ANTHROPIC_COLLECTION_USABLE_MODELS_NAME = 'anthropic_get_usable_models';
    case ANTHROPIC_COLLECTION_DISABLED_MODELS_NAME = 'anthropic_get_disabled_models';
}
