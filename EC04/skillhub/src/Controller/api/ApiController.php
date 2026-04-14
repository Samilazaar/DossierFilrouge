<?php
namespace App\Controller\api;

use App\Entity\Atelier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use App\Service\NotificationService;

class ApiController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/api/workshops', name: 'get_all_workshops', methods: ['GET'])]
    #[OA\Get(summary: "Liste les ateliers", description: "Retourne la liste paginée des ateliers avec filtres optionnels")]
    #[OA\Parameter(name: 'page', in: 'query', description: 'Numéro de page', required: false)]
    #[OA\Parameter(name: 'limit', in: 'query', description: 'Nombre de résultats par page (max 50)', required: false)]
    #[OA\Parameter(name: 'teacher', in: 'query', description: 'ID du formateur', required: false)]
    #[OA\Response(response: 200, description: "Liste des ateliers avec métadonnées de pagination")]
    #[OA\Response(response: 400, description: "Paramètre invalide (limit > 50)")]
    public function getAllWorkshops(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);
        $teacher = $request->query->get('teacher');

        if ($limit > 50) {
            return $this->json(['error' => 'Limit too high'], 400);
        }

        $qb = $this->em->getRepository(Atelier::class)->createQueryBuilder('a');

        if ($teacher) {
            $qb->join('a.formateur', 'f')
               ->andWhere('f.id = :teacher')
               ->setParameter('teacher', $teacher);
        }

        $total = (clone $qb)->select('COUNT(a.id)')->getQuery()->getSingleScalarResult();

        $workshops = $qb->setFirstResult(($page - 1) * $limit)
                        ->setMaxResults($limit)
                        ->getQuery()
                        ->getResult();

        $data = array_map(function (Atelier $a) {
            return [
                'id' => $a->getId(),
                'title' => $a->getTitre(),
                'description' => $a->getDescription(),
                'date' => $a->getDate()?->format('Y-m-d H:i'),
                'duration' => $a->getDuree(),
                'capacity' => $a->getCapaciteMax(),
                'remaining' => $a->getPlacesRestantes(),
                'teacher' => $a->getFormateurNom(),
            ];
        }, $workshops);

        return $this->json([
            'data' => $data,
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => (int) $total,
            ],
        ]);
    }

    #[Route('/api/workshops/{id}', name: 'get_one_workshop', methods: ['GET'])]
    #[OA\Get(summary: "Détail d'un atelier", description: "Retourne un atelier par son ID")]
    #[OA\Parameter(name: 'id', in: 'path', description: "ID de l'atelier", required: true)]
    #[OA\Response(response: 200, description: "Atelier trouvé")]
    #[OA\Response(response: 404, description: "Atelier non trouvé")]
    public function getOneWorkshop(int $id): JsonResponse
    {
        $workshop = $this->em->getRepository(Atelier::class)->find($id);

        if (!$workshop) {
            return $this->json(['error' => 'Workshop not found'], 404);
        }

        return $this->json([
            'id' => $workshop->getId(),
            'title' => $workshop->getTitre(),
            'description' => $workshop->getDescription(),
            'date' => $workshop->getDate()?->format('Y-m-d H:i'),
            'duration' => $workshop->getDuree(),
            'capacity' => $workshop->getCapaciteMax(),
            'remaining' => $workshop->getPlacesRestantes(),
            'teacher' => $workshop->getFormateurNom(),
        ]);
    }

    #[Route('/api/workshops', name: 'create_workshop', methods: ['POST'])]
    #[OA\Post(summary: "Créer un atelier", description: "Crée un nouvel atelier")]
    #[OA\RequestBody(description: "Données de l'atelier à créer", required: true)]
    #[OA\Response(response: 201, description: "Atelier créé avec succès")]
    #[OA\Response(response: 422, description: "Données invalides")]
    public function createWorkshop(Request $request, NotificationService $notif): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['title'])) {
            return $this->json(['error' => 'Title is required'], 422);
        }

        $notif->sendEmail('admin@skillhub.com', 'Nouvel atelier créé : ' . $data['title']);

        return $this->json(['id' => 3, 'title' => $data['title']], 201);
    }

    #[Route('/api/workshops/{id}', name: 'delete_workshop', methods: ['DELETE'])]
    #[OA\Delete(summary: "Supprimer un atelier", description: "Supprime un atelier par son ID")]
    #[OA\Parameter(name: 'id', in: 'path', description: "ID de l'atelier", required: true)]
    #[OA\Response(response: 204, description: "Atelier supprimé")]
    #[OA\Response(response: 404, description: "Atelier non trouvé")]
    public function deleteWorkshop(int $id): JsonResponse
    {
        return new JsonResponse(null, 204);
    }

    #[Route('/api/students/{studentId}/workshops', name: 'get_student_workshops', methods: ['GET'])]
    #[OA\Get(summary: "Ateliers d'un étudiant", description: "Retourne les ateliers auxquels un étudiant est inscrit")]
    #[OA\Parameter(name: 'studentId', in: 'path', description: "ID de l'étudiant", required: true)]
    #[OA\Response(response: 200, description: "Liste des ateliers de l'étudiant")]
    #[OA\Response(response: 404, description: "Étudiant non trouvé")]
    public function getStudentWorkshops(int $studentId): JsonResponse
    {
        $workshops = [
            ['id' => 1, 'title' => 'Introduction à PHP'],
        ];

        return $this->json($workshops, 200);
    }
}
