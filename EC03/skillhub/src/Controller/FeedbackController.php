<?php

namespace App\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Document\Feedback;


class FeedbackController extends AbstractController
{
    private DocumentManager $documentManager;

    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    #[Route('/feedback', name: 'feedback')]
    public function index(): Response
    {
        return $this->render('feedback/index.html.twig', [
            'page_title' => 'Feedback',
            'feedbacks' => $this->documentManager->getRepository(Feedback::class)->findAll()

        ]);
    }


    #[Route('/feedback/create/{atelierId}', name: 'create_feedback', methods: ['POST'])]
    public function create(Request $request, SessionInterface $session, string $atelierId): Response
    {
        $userId = $session->get('user_id');
        if (!$userId) {
            return $this->redirectToRoute('app_connexion');
        }

        $feedback = new Feedback();
        $feedback->setNote($request->request->get('note'));
        $feedback->setCommentaire($request->request->get('commentaire'));
        $feedback->setDate(new \DateTime());
        $feedback->setAtelierId($atelierId);
        $feedback->setUserId((string) $userId);
        $this->documentManager->persist($feedback);
        $this->documentManager->flush();
        return $this->redirectToRoute('app_atelier_detail', ['id' => $atelierId]);
    }

}