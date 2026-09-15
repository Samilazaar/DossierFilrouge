<?php
// FICHIER FOURNI POUR LE TP 3 — contient des défauts volontaires.
// Ne pas placer dans src/ : il n'est là que pour être relu.
// Destination annoncée par la pull request : src/Controller/api/ExportController.php

namespace App\Controller\api;

use App\Service\DoctrineDataRepository;
use League\Csv\Writer;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ExportController extends AbstractController
{
    private DoctrineDataRepository $repository;
    private LoggerInterface $logger;

    public function __construct(DoctrineDataRepository $repository, LoggerInterface $logger)
    {
        $this->repository = $repository;
        $this->logger = $logger;
    }

    #[Route('/api/workshops/{id}/export', name: 'export_attendees', methods: ['GET'])]
    public function export(int $id): Response
    {
        $atelier = $this->repository->findAtelierById($id);

        $data = $this->getActiveUsers($atelier);

        $csv = Writer::createFromString();
        $csv->insertOne(['nom', 'prenom', 'email', 'telephone', 'date_inscription']);

        foreach ($data as $inscription) {
            $user = $inscription->getUtilisateur();
            $csv->insertOne([
                $user->getNom(),
                $user->getPrenom(),
                $user->getEmail(),
                $user->getTelephone(),
                $inscription->getDateInscription()->format('Y-m-d H:i:s'),
            ]);
        }

        try {
            $chemin = $_ENV['EXPORT_DIR'] . '/export-atelier-' . $id . '.csv';
            file_put_contents($chemin, $csv->toString());
        } catch (\Exception $e) {
        }

        return new Response($csv->toString(), 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="export.csv"',
        ]);
    }

    private function getActiveUsers($atelier): array
    {
        return $this->repository->findInscriptionsByAtelier($atelier);
    }

    #[Route('/api/workshops/{id}/export/count', name: 'export_count', methods: ['GET'])]
    public function count(int $id): JsonResponse
    {
        $atelier = $this->repository->findAtelierById($id);
        $inscriptions = $this->getActiveUsers($atelier);

        return $this->json(['count' => count($inscriptions)]);
    }
}
