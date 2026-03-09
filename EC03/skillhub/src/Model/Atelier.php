<?php

namespace App\Model;

class Atelier
{
    private int $id;
    private string $titre;
    private string $description;
    private string $date;
    private string $duree;
    private int $capaciteMax;
    private int $placesRestantes;
    private string $imageUrl;
    private string $formateur;

    public function __construct(int $id, string $titre, string $description, string $date, string $duree, int $capaciteMax, string $imageUrl, string $formateur)
    {
        $this->id = $id;
        $this->titre = $titre;
        $this->description = $description;
        $this->date = $date;
        $this->duree = $duree;
        $this->capaciteMax = $capaciteMax;
        $this->placesRestantes = $capaciteMax;
        $this->imageUrl = $imageUrl;
        $this->formateur = $formateur;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function getDuree(): string
    {
        return $this->duree;
    }

    public function getCapaciteMax(): int
    {
        return $this->capaciteMax;
    }

    public function getPlacesRestantes(): int
    {
        return $this->placesRestantes;
    }

    public function setPlacesRestantes(int $placesRestantes): void
    {
        $this->placesRestantes = $placesRestantes;
    }

    public function getImageUrl(): string
    {
        return $this->imageUrl;
    }

    public function getFormateur(): string
    {
        return $this->formateur;
    }

    public function estComplet(): bool
    {
        return $this->placesRestantes <= 0;
    }

    public function getPlacesDisponibles(): int
    {
        return max(0, $this->placesRestantes);
    }

    public function decrementerPlaces(): void
    {
        if ($this->placesRestantes > 0) {
            $this->placesRestantes--;
        }
    }

    public function incrementerPlaces(): void
    {
        if ($this->placesRestantes < $this->capaciteMax) {
            $this->placesRestantes++;
        }
    }
}
