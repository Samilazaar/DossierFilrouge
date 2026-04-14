<?php   

namespace App\Service;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class IAService
{
    private const API_URL = 'https://api-inference.huggingface.co/v1/chat/completions';
    public function recommendAtelier(array $competences, array $ateliers): array
    {
        $competencesTexte = implode(', ', $competences);
        $ateliersTexte='';
        foreach ($ateliers as $atelier) {
            $ateliersTexte .= 'Titre:'.$atelier->getTitre(). 'Description:' . $atelier->getDescription() . 'ID Atelier: ' . $atelier->getId() . "\n";
        }
        $ateliersTexte = rtrim($ateliersTexte, ', ');
        $prompt = "L'utilisateur souhaite acquérir des compétences en : $competencesTexte.
        Voici les ateliers disponibles : $ateliersTexte.
        Recommande les ateliers les plus pertinents. Réponds uniquement en JSON avec ce format : {\"texte\": \"ton explication\", \"ids\": [3, 7]}";
        $response = $this->httpClient->request('POST', self::API_URL, [
            'headers' => ['Authorization' => 'Bearer ' . $this->apiKey],
            'json' => [
                'model' => 'meta-llama/Llama-3.1-8B-Instruct',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 500,
            ],
        ]);
        $data = json_decode($response->getContent(), true);
        $jsonData = json_decode($data['choices'][0]['message']['content'], true);
        return $jsonData;
    }

    public function __construct(private string $apiKey, private HttpClientInterface $httpClient)
    {

          
    }
     
    
}
