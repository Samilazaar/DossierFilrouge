<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LandingController extends AbstractController
{
    #[Route('/', name: 'app_landing')]
    public function index(): Response
    {
        $titre = "Bienvenue sur SkillHub";
        $features = ['HTML5', 'CSS3', 'JavaScript', 'PHP', 'MySQL'];
        
        return $this->render('landing/index.html.twig', [
            'controller_name' => 'LandingController',
            'page_title' => $titre,
            'features' => $features,
        ]);
    }
}
