<?php
namespace App\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
#[ORM\Entity]
#[ORM\Table(name: 'users_formateurs')]
class UserFormateur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;
    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'formateurProfile')]
    private User $user;
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $bio = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $specialite = null;
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $experiences = null;
    #[ORM\OneToMany(targetEntity: Atelier::class, mappedBy: 'formateur')]
    private Collection $ateliers;
    public function __construct()
    {
        $this->ateliers = new ArrayCollection();
    }
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getUser(): User
    {
        return $this->user;
    }
    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }
    public function getBio(): ?string
    {
        return $this->bio;
    }
    public function setBio(?string $bio): self
    {
        $this->bio = $bio;
        return $this;
    }
    public function getSpecialite(): ?string
    {
        return $this->specialite;
    }
    public function setSpecialite(?string $specialite): self
    {
        $this->specialite = $specialite;
        return $this;
    }
    public function getExperiences(): ?string
    {
        return $this->experiences;
    }
    public function setExperiences(?string $experiences): self
    {
        $this->experiences = $experiences;
        return $this;
    }
    public function getAteliers(): Collection
    {
        return $this->ateliers;
    }
    public function addAtelier(Atelier $atelier): self
    {
        if (!$this->ateliers->contains($atelier)) {
            $this->ateliers->add($atelier);
            $atelier->setFormateur($this);
        }
        return $this;
    }
    public function removeAtelier(Atelier $atelier): self
    {
        if ($this->ateliers->removeElement($atelier)) {
            if ($atelier->getFormateur() === $this) {
                $atelier->setFormateur(null);
            }
        }
        return $this;
    }

}
