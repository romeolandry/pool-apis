<?php

namespace App\State;

use App\Services\GeminiService;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Enums\GeminiEnum;

class GeminiModelsProvider implements ProviderInterface
{
    public function __construct(
        private GeminiService $geminiService
    ){ }
    
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {

        $filterName = null;

        /** @var \Symfony\Component\HttpFoundation\Request|null $request */
        $request = $context['request'] ?? null;

        $filterName = $request?->query->get('model') ?? null;

        return match ($operation->getName()) {
            GeminiEnum::GEMINI_COLLECTION_ALL_MODELS_NAME->value => $this->geminiService->getModels($filterName),
            GeminiEnum::GEMINI_COLLECTION_USABLE_MODELS_NAME->value => $this->geminiService->getAvailableModels($filterName),
            default => throw new \InvalidArgumentException("Unsupported operation: {$operation->getName()}"),
        };
    }
}
