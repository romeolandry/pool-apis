<?php

namespace App\Services;



use Symfony\AI\Platform\Bridge\Anthropic\Factory;
use Symfony\AI\Platform\Bridge\Anthropic\ModelCatalog;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AnthropicService
{
    private $ANTHROPICFactory;

    const ANTHROPIC_BASE_URL = 'https://api.anthropic.com/v1/';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $anthropicApikey,
        private readonly string $anthropicModel,
        private readonly string $anthropicVersion
    ) { 
        $this->ANTHROPICFactory = Factory::createPlatform(
            apiKey: $this->anthropicApikey
        );    
    }

    /**
     * Récupère et filtre les modèles ANTHROPIC disponibles et exploitables.
     *
     * @return array Liste des identifiants de modèles (ex: ["models/ANTHROPIC-2.5-flash", ...])
     */
    public function getModels(?string $filterName = null): array
    {
        try {

           $catalog = new ModelCatalog();
           $models =  $catalog->getModels();

            foreach ($models as $model) {

                if ($filterName !== null && str_contains($model,$filterName)) {
                    return [$model];
                }

            }

            return $models;

        }catch (\Exception $e) {
            // Gérer les erreurs de requête HTTP
            throw new \RuntimeException('Erreur lors de la récupération des modèles ANTHROPIC : ' . $e->getMessage());
        }
    }

    /**
     * Récupère et filtre les modèles ANTHROPIC disponibles et exploitables.
     *
     * @return array Liste des identifiants de modèles (ex: ["models/ANTHROPIC-2.5-flash", ...])
     */
    public function getAvailableModels(?string $filterName = null): array
    {
        try{

            $response = $this->getModels($filterName);

            $usableModels = [];

            foreach ($response as $model_name) {
                
                if (!str_contains($model_name, 'pro')) {
                    $usableModels[] = $model_name;
                }
            }
            return $usableModels;

        }catch (\Exception $e) {
            // Gérer les erreurs de requête HTTP
            throw new \RuntimeException('Erreur lors de la récupération des modèles ANTHROPIC : ' . $e->getMessage());
        }
    }
}
