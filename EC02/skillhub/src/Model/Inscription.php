<?php

namespace App\Model;

class Inscription
{
    private int $id;
    private User $utilisateur;
    private Atelier $atelier;
    private string $dateInscription;

    public function __construct(int $id, User $utilisateur, Atelier $atelier, string $dateInscription = '')
    {
        $this->id = $id;
        $this->utilisateur = $utilisateur;
        $this->atelier = $atelier;
        $this->dateInscription = $dateInscription !== '' ? $dateInscription : date('Y-m-d H:i:s');
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUtilisateur(): User
    {
        return $this->utilisateur;
    }

    public function getAtelier(): Atelier
    {
        return $this->atelier;
    }

    public function getDateInscription(): string
    {
        return $this->dateInscription;
    }
}
