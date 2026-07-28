<?php

namespace App\Services;


use App\Services\BaseAiService;
use App\Services\Utilities\ModelAvailabilityGate;
use Symfony\AI\Platform\Bridge\Anthropic\Factory;
use Symfony\AI\Platform\Bridge\Anthropic\ModelCatalog;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AnthropicService extends BaseAiService
{

    const ANTHROPIC_BASE_URL = 'https://api.anthropic.com/v1/';

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

            foreach ($response as $name => $anthropicModel) {

                $availability = $this->checkAvailability($name);
                if($availability['available']) {
                    $usableModels[] = [
                        'name' => $name,
                        'model' => $anthropicModel,
                        'availability' => $availability
                    ];
                }
            }
            return $usableModels;

        }catch (\Exception $e) {
            // Gérer les erreurs de requête HTTP
            throw new \RuntimeException('Erreur lors de la récupération des modèles ANTHROPIC : ' . $e->getMessage());
        }
    }
}
