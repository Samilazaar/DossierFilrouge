<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Atelier;
use App\Entity\Inscription;
use App\Entity\UserFormateur;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineDataRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findAllUsers(): array
    {
        return $this->entityManager->getRepository(User::class)->findAll();
    }

    public function findUserByEmail(string $email): ?User
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
    }

    public function findUserById(int $id): ?User
    {
        return $this->entityManager->getRepository(User::class)->find($id);
    }

    public function addUser(User $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function findAllAteliers(): array
    {
        return $this->entityManager->getRepository(Atelier::class)->findAll();
    }

    public function findAtelierById(int $id): ?Atelier
    {
        return $this->entityManager->getRepository(Atelier::class)->find($id);
    }

    public function findAllInscriptions(): array
    {
        return $this->entityManager->getRepository(Inscription::class)->findAll();
    }

    public function findInscriptionsByUser(User $user): array
    {
        return $this->entityManager->getRepository(Inscription::class)->findBy(['utilisateur' => $user]);
    }

    public function findInscriptionsByAtelier(Atelier $atelier): array
    {
        return $this->entityManager->getRepository(Inscription::class)->findBy(['atelier' => $atelier]);
    }

    public function findInscription(User $user, Atelier $atelier): ?Inscription
    {
        return $this->entityManager->getRepository(Inscription::class)->findOneBy([
            'utilisateur' => $user,
            'atelier' => $atelier
        ]);
    }

    public function addInscription(Inscription $inscription): void
    {
        $inscription->getAtelier()->decrementerPlaces();
        $this->entityManager->persist($inscription);
        $this->entityManager->flush();
    }

    public function removeInscription(Inscription $inscription): void
    {
        $inscription->getAtelier()->incrementerPlaces();
        $this->entityManager->remove($inscription);
        $this->entityManager->flush();
    }
    public function findAllFormateurs(): array
    {
        return $this->entityManager->getRepository(UserFormateur::class)->findAll();
    }

    public function findFormateurById(int $id): ?UserFormateur
    {
        return $this->entityManager->getRepository(UserFormateur::class)->find($id);
    }

    public function findFormateurByUser(User $user): ?UserFormateur
    {
        return $this->entityManager->getRepository(UserFormateur::class)
            ->findOneBy(['user' => $user]);
    }

    public function addFormateurProfile(UserFormateur $formateurProfile): void
    {
        $this->entityManager->persist($formateurProfile);
        $this->entityManager->flush();
    }

    public function findAteliersByFormateur(UserFormateur $formateur): array
    {
        return $this->entityManager->getRepository(Atelier::class)
            ->findBy(['formateur' => $formateur]);
    }
    public function addAtelier(Atelier $atelier): void
    {
        $this->entityManager->persist($atelier);
        $this->entityManager->flush();
    }
}
    
