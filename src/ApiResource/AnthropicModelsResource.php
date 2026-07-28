<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\QueryParameter;
use App\Enums\AnthropicEnum;
use App\State\AnthropicModelProvider;

#[ApiResource(
    shortName:"Anthropic Models",
    description: 'Récupère la liste des modèles ANTHROPIC disponibles et exploitables.',
    provider: AnthropicModelProvider::class,
    operations: [
        new GetCollection(
            uriTemplate: '/anthropic_models',
            name: AnthropicEnum::ANTHROPIC_COLLECTION_ALL_MODELS_NAME->value,
            description: 'Récupère la liste de tous les modèles Anthropic disponibles.',
            provider: AnthropicModelProvider::class,
            parameters: [
                'model' => new QueryParameter(
                    key: 'model',
                    schema: ['type' => 'string'],
                    description: 'Filtrer par nom de modèle Anthropic.'
                ),
            ]
        ),
        new GetCollection(
            uriTemplate: '/anthropic_models/usable',
            name: AnthropicEnum::ANTHROPIC_COLLECTION_USABLE_MODELS_NAME->value,
            description: 'Récupère la liste des modèles Anthropic exploitables (utilisables).',
            provider: AnthropicModelProvider::class,
            parameters: [
                'model' => new QueryParameter(
                    key: 'model',
                    schema: ['type' => 'string'],
                    description: 'Filtrer par nom de modèle Anthropic.'
                ),
            ]
        ),
    ]
)]
class AnthropicModelsResource
{}
