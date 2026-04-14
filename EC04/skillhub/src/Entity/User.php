<?php
namespace App\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $nom = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $prenom = null;
    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: true)]
    private ?string $email = null;
    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $telephone = null;
    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $password = null;
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $competences = null;
    #[ORM\OneToOne(targetEntity: UserFormateur::class, mappedBy: 'user')]
    private ?UserFormateur $formateurProfile = null;
    #[ORM\OneToMany(targetEntity: Inscription::class, mappedBy: 'utilisateur')]
    private Collection $inscriptions;
    public function __construct()
    {
        $this->inscriptions = new ArrayCollection();
    }
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getNom(): ?string
    {
        return $this->nom;
    }
    public function setNom(?string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }
    public function getPrenom(): ?string
    {
        return $this->prenom;
    }
    public function setPrenom(?string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }
    public function getEmail(): ?string
    {
        return $this->email;
    }
    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }
    public function getTelephone(): ?string
    {
        return $this->telephone;
    }
    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;
        return $this;
    }
    public function getPassword(): ?string
    {
        return $this->password;
    }
    public function setPassword(?string $password): self
    {
        $this->password = $password;
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
            $inscription->setUtilisateur($this);
        }
        return $this;
    }
    public function removeInscription(Inscription $inscription): self
    {
        if ($this->inscriptions->removeElement($inscription)) {
            if ($inscription->getUtilisateur() === $this) {
                $inscription->setUtilisateur(null);
            }
        }
        return $this;
    }
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }
    public function getFormateurProfile(): ?UserFormateur
    {
        return $this->formateurProfile;
    }
    public function setFormateurProfile(?UserFormateur $formateurProfile): self
    {
        $this->formateurProfile = $formateurProfile;
        return $this;
    }
    public function isFormateur(): bool
    {
        return $this->formateurProfile !== null;
    }

    public function getCompetences(): ?array
    {
        return $this->competences;
    }
    public function setCompetences(?array $competences): self
    {
        $this->competences = $competences;
        return $this;
    }
}

