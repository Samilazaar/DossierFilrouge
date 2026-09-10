<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document(collection: 'visit_logs')]
class VisitLog
{
    #[ODM\Id]
    private ?string $id = null;

    #[ODM\Field(type: 'string')]
    private ?string $userId = null;

    #[ODM\Field(type: 'string')]
    private ?string $atelierId = null;

    #[ODM\Field(type: 'string')]
    private ?string $action = null;

    #[ODM\Field(type: 'date')]
    private ?\DateTimeInterface $timestamp = null;

    #[ODM\Field(type: 'string', nullable: true)]
    private ?string $ipAddress = null;

    #[ODM\Field(type: 'string', nullable: true)]
    private ?string $userAgent = null;

    public function getId(): ?string
    {
        return $this->id;
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

    public function getAtelierId(): ?string
    {
        return $this->atelierId;
    }

    public function setAtelierId(?string $atelierId): self
    {
        $this->atelierId = $atelierId;
        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(?string $action): self
    {
        $this->action = $action;
        return $this;
    }

    public function getTimestamp(): ?\DateTimeInterface
    {
        return $this->timestamp;
    }

    public function setTimestamp(?\DateTimeInterface $timestamp): self
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): self
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): self
    {
        $this->userAgent = $userAgent;
        return $this;
    }
}
