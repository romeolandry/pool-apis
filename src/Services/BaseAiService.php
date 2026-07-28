<?php

namespace App\Services;


use App\Services\Utilities\ModelAvailabilityGate;
use Symfony\AI\Platform\PlatformInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\AI\Platform\Bridge\Anthropic\Factory as AnthropicFactory;
// use Symfony\AI\Platform\Bridge\OpenAi\Factory as OpenAiFactory;
use Symfony\AI\Platform\Bridge\Gemini\Factory as GeminiFactory;
use Symfony\AI\Platform\Platform;
use App\Enums\PlatformEnum;

abstract class BaseAiService
{
    protected $platform;

    public function __construct(
        // private readonly OpenAiFactory $openAiFactory,
        protected readonly AnthropicFactory $anthropicFactory,
        protected readonly GeminiFactory $geminiFactory,
        protected readonly HttpClientInterface $httpClient,
        protected readonly string $geminiApiKey,
        protected readonly string $anthropicApikey    
    ) { 
        $this->platform = new Platform([
            // $this->openAiFactory->createProvider(apiKey: 'OPENAI_API_KEY'),
            $this->anthropicFactory->createProvider(apiKey: $anthropicApikey),
            $this->geminiFactory->createProvider(apiKey: $geminiApiKey)
        ]);
    }

    protected function buildPlatform(string $platformName): PlatformInterface
    {
        return match ($platformName) {
            PlatformEnum::ANTHROPIC->value => $this->anthropicFactory->createPlatform(apiKey: $this->anthropicApikey),
            PlatformEnum::GEMINI->value => $this->geminiFactory->createPlatform(apiKey: $this->geminiApiKey),
            default => throw new \InvalidArgumentException("Unsupported platform: {$platformName}")
        };
    }


    protected function checkAvailability(string $model, string $platform="gemini"): array
    {
        try {

            $availability = [
                'available' => false,
                'description' => '',
            ];

            $gate = new ModelAvailabilityGate(
                platform: $this->buildPlatform($platform),
                cache: new FilesystemAdapter(namespace: 'ai_model_cooldown'),
                maxWaitSeconds: 60,
                defaultCooldownSeconds: 30
            );

            if (!$gate->isAvailable($model)) {
                $availability['available'] = false;
                $availability['description'] = sprintf("%s is cooling down, %d second(s) left.", $model, $gate->waitTime($model));
            } else {
                $availability['available'] = true;
                $availability['description'] = sprintf("%s is available.", $model);
            }

            return $availability;
        } catch (\Exception $e) {
            // Gérer les erreurs de requête HTTP
            throw new \RuntimeException('Erreur lors de la vérification de disponibilité du modèle ANTHROPIC : ' . $e->getMessage());
        }
    }
}