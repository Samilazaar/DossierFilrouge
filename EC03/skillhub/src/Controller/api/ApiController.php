<?php
namespace App\Controller\api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ApiController extends AbstractController
{
    #[Route('/api/data', name: 'get_data', methods: ['GET'])]
    public function getData(): JsonResponse
    {
        $data = [
            'id' => 1,
            'status' => 'success',
            'message' => 'This is your backend response'
        ];

        return $this->json($data);
    }
}