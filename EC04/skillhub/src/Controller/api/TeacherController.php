<?php
  namespace App\Controller\api;

  use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
  use Symfony\Component\HttpFoundation\JsonResponse;
  use Symfony\Component\Routing\Attribute\Route;
  use Symfony\Component\HttpFoundation\Request;

  class TeacherController extends AbstractController
  {
      // à toi de créer les 5 routes
      #[Route('/api/teachers', name: 'get_all_teachers', methods: ['GET'])]
      public function getAllTeachers(): JsonResponse
      {
          return $this->json(['message' => 'Hello, world!']);
          
      }
      #[Route('/api/teachers/{id}', name: 'get_teacher_by_id', methods: ['GET'])]
      public function getTeacherById(int $id): JsonResponse
      {
          return $this->json(['message' => 'Hello, world!']);
      }
      #[Route('/api/teachers', name: 'create_teacher', methods: ['POST'])]
      public function createTeacher(Request $request): JsonResponse
      {
          $data = json_decode($request->getContent(), true);
    
          if (empty($data['name'])) {
              return $this->json(['error' => 'Name is required'], 422);
          }
    
          return $this->json(['id' => 1, 'name' => $data['name']], 201);
      }
      #[Route('/api/teachers/{id}', name: 'update_teacher', methods: ['PUT'])]
  public function updateTeacher(int $id, Request $request): JsonResponse
  {
      $data = json_decode($request->getContent(), true);
      if (empty($data['name'])) {
          return $this->json(['error' => 'Name is required'], 422);
      }
      return $this->json(['id' => $id, 'name' => $data['name']], 200);
  }

  #[Route('/api/teachers/{id}', name: 'delete_teacher', methods: ['DELETE'])]
  public function deleteTeacher(int $id): JsonResponse
  {
      return new JsonResponse(null, 204);
  }
  }

