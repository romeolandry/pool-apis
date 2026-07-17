<?php

declare(strict_types=1);

namespace App\Enums;

enum GeminiEnum: string
{
    case GEMINI_COLLECTION_ALL_MODELS_NAME = 'get_all_models';
    case GEMINI_COLLECTION_USABLE_MODELS_NAME = 'get_usable_models';
    case GEMINI_COLLECTION_DISABLED_MODELS_NAME = 'get_disabled_models';
}
