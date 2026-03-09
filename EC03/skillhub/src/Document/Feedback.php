<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document]
class Feedback {
    #[ODM\Id] private ?string $id = null;
    #[ODM\Field(type: 'string')] private ?string $note = null;
    #[ODM\Field(type: 'string')] private ?string $commentaire = null;
    #[ODM\Field(type: 'date')] private ?\DateTimeInterface $date = null;
    #[ODM\Field(type: 'string')] private ?string $atelierId = null;
    #[ODM\Field(type: 'string')] private ?string $userId = null;



    public function getNote(): ?string 
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;
        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): self
    {
        $this->commentaire = $commentaire;
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

    public function getAtelierId(): ?string
    {
        return $this->atelierId;
    }

    public function setAtelierId(?string $atelierId): self
    {
        $this->atelierId = $atelierId;
        return $this;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(?string $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function getId(): ?string
    {
        return $this->id;
    }


}