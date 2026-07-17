<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Enums\AnthropicEnum;
use App\Services\AnthropicService;

class AnthropicModelProvider implements ProviderInterface
{

    public function __construct(
        private AnthropicService $AnthropicService
    ){ }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $filterName = null;

        /** @var \Symfony\Component\HttpFoundation\Request|null $request */
        $request = $context['request'] ?? null;

        $filterName = $request?->query->get('model') ?? null;

        return match ($operation->getName()) {
            AnthropicEnum::ANTHROPIC_COLLECTION_ALL_MODELS_NAME->value => $this->AnthropicService->getModels($filterName),
            AnthropicEnum::ANTHROPIC_COLLECTION_USABLE_MODELS_NAME->value => $this->AnthropicService->getAvailableModels($filterName),
            default => throw new \InvalidArgumentException("Unsupported operation: {$operation->getName()}"),
        };
    }
}
