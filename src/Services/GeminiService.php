<?php

namespace App\Services;

use App\Services\BaseAiService;
use Symfony\AI\Platform\PlatformInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeminiService extends BaseAiService
{
    const GEMINI_BASE_URL = 'https://generativelanguage.googleapis.com/v1beta/';

    // public function __construct(
    //     private readonly PlatformInterface $aiPlatform,
    //     private readonly HttpClientInterface $httpClient,
    //     private readonly string $geminiApiKey,
    //     private readonly string $geminiModel,
    // ) { }

    // public function getConfiguration(): array
    // {
    //     return [
    //         'api_key' => $this->geminiApiKey,
    //         'model' => $this->geminiModel,
    //     ];
    // }

    /**
     * Récupère et filtre les modèles Gemini disponibles et exploitables.
     *
     * @return array Liste des identifiants de modèles (ex: ["models/gemini-2.5-flash", ...])
     */
    public function getModels(?string $filterName = null): array
    {
        try {

            $response = $this->httpClient->request('GET', self::GEMINI_BASE_URL . 'models', [
                'query' => [
                    'key' => $this->geminiApiKey,
                ]
            ]);

            $data = $response->toArray();

            if (!isset($data['models'])) {
                return [];
            }

            $usableModels = [];

            foreach ($data['models'] as $model) {
                $name = $model['name'] ?? '';

                $name = explode('/', $name)[1] ?? '';

                $methods = $model['supportedGenerationMethods'] ?? [];

                if (!in_array('generateContent', $methods, true)) {
                    continue;
                }

                if ($filterName !== null && !str_contains($name,$filterName)) {
                    continue;
                }

                $usableModels[] = $name;
            }

            return $usableModels;

        }catch (\Exception $e) {
            // Gérer les erreurs de requête HTTP
            throw new \RuntimeException('Erreur lors de la récupération des modèles Gemini : ' . $e->getMessage());
        }
    }

    public function getModelsBis(?string $filterName = null): array
    {
        try {

            $response = $this->httpClient->request('GET', self::GEMINI_BASE_URL . 'models', [
                'query' => [
                    'key' => $this->geminiApiKey,
                ]
            ]);

            $data = $response->toArray();

            if (!isset($data['models'])) {
                return [];
            }

            $usableModels = [];

            foreach ($data['models'] as $model) {
                $name = $model['name'] ?? '';

                $name = explode('/', $name)[1] ?? '';

                $methods = $model['supportedGenerationMethods'] ?? [];

                if (!in_array('generateContent', $methods, true)) {
                    continue;
                }

                if ($filterName !== null && !str_contains($name,$filterName)) {
                    continue;
                }

                $usableModels[] = $name;
            }

            return $usableModels;

        }catch (\Exception $e) {
            // Gérer les erreurs de requête HTTP
            throw new \RuntimeException('Erreur lors de la récupération des modèles Gemini : ' . $e->getMessage());
        }
    }

    /**
     * Récupère et filtre les modèles Gemini disponibles et exploitables.
     *
     * @return array Liste des identifiants de modèles (ex: ["models/gemini-2.5-flash", ...])
     */
    public function getAvailableModels(?string $filterName = null): array
    {
        try{

            $response = $this->getModels($filterName);

            $usableModels = [];

            foreach ($response as $model_name) {

                $availability = $this->checkAvailability($model_name);

                if($availability['available']) {
                    $usableModels[] = [
                        'name' => $model_name,
                        'availability' => $availability
                    ];
                }
            }
            return $usableModels;

        }catch (\Exception $e) {
            // Gérer les erreurs de requête HTTP
            throw new \RuntimeException('Erreur lors de la récupération des modèles Gemini : ' . $e->getMessage());
        }
    }
}
