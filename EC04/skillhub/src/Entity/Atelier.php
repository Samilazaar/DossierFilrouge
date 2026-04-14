<?php
namespace App\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'ateliers')]
class Atelier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $titre = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $duree = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $capaciteMax = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $placesRestantes = null;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $imageUrl = null;

   #[ORM\ManyToOne(targetEntity: UserFormateur::class, inversedBy: 'ateliers')]
   #[ORM\JoinColumn(nullable: true)]
   private ?UserFormateur $formateur = null;

    #[ORM\OneToMany(targetEntity: Inscription::class, mappedBy: 'atelier')]
    private Collection $inscriptions;

    public function __construct()
    {
        $this->inscriptions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(?string $titre): self
    {
        $this->titre = $titre;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getDuree(): ?string
    {
        return $this->duree;
    }

    public function setDuree(?string $duree): self
    {
        $this->duree = $duree;
        return $this;
    }

    public function getCapaciteMax(): ?int
    {
        return $this->capaciteMax;
    }

    public function setCapaciteMax(?int $capaciteMax): self
    {
        $this->capaciteMax = $capaciteMax;
        return $this;
    }

    public function getPlacesRestantes(): ?int
    {
        return $this->placesRestantes;
    }

    public function setPlacesRestantes(?int $placesRestantes): self
    {
        $this->placesRestantes = $placesRestantes;
        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): self
    {
        $this->imageUrl = $imageUrl;
        return $this;
    }

    public function getFormateur(): ?UserFormateur
    {
        return $this->formateur;
    }
    public function setFormateur(?UserFormateur $formateur): self
    {
        $this->formateur = $formateur;
        return $this;
    }

    public function getInscriptions(): Collection
    {
        return $this->inscriptions;
    }

    public function addInscription(Inscription $inscription): self
    {
        if (!$this->inscriptions->contains($inscription)) {
            $this->inscriptions->add($inscription);
            $inscription->setAtelier($this);
        }

        return $this;
    }

    public function removeInscription(Inscription $inscription): self
    {
        if ($this->inscriptions->removeElement($inscription)) {
            if ($inscription->getAtelier() === $this) {
                $inscription->setAtelier(null);
            }
        }

        return $this;
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

    public function getFormateurNom(): string
    {
        if ($this->formateur) {
            $user = $this->formateur->getUser();
            return $user ? $user->getNom() . ' ' . $user->getPrenom() : 'N/A';
        }
        return 'N/A';
    }


}
