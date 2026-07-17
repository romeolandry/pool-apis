<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\QueryParameter;
use App\Enums\GeminiEnum;
use App\State\GeminiModelsProvider;

#[ApiResource(
    shortName:"Gemin Models",
    description: 'Récupère la liste des modèles Gemini disponibles et exploitables.',
    provider: GeminiModelsProvider::class,
    operations: [
        new GetCollection(
            uriTemplate: '/gemini_models',
            name: GeminiEnum::GEMINI_COLLECTION_ALL_MODELS_NAME->value,
            description: 'Récupère la liste de tous les modèles Gemini disponibles.',
            provider: GeminiModelsProvider::class,
            parameters: [
                'model' => new QueryParameter(
                    key: 'model',
                    schema: ['type' => 'string'],
                    description: 'Filtrer par nom de modèle Gemini.'
                ),
            ]
        ),
        new GetCollection(
            uriTemplate: '/gemini_models/usable',
            name: GeminiEnum::GEMINI_COLLECTION_USABLE_MODELS_NAME->value,
            description: 'Récupère la liste des modèles Gemini exploitables (utilisables).',
            provider: GeminiModelsProvider::class,
            parameters: [
                'model' => new QueryParameter(
                    key: 'model',
                    schema: ['type' => 'string'],
                    description: 'Filtrer par nom de modèle Gemini.'
                ),
            ]
        ),
    ]
)]
class GeminiModelsResource
{}
