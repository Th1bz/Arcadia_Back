<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use DateTime;

#[MongoDB\Document]
class VetoReport
{
    #[MongoDB\Id]
    private string $id;

    #[MongoDB\Field(type: 'int')]
    private int $animalId;

    #[MongoDB\Field(type: 'string')]
    private string $username;

    #[MongoDB\Field(type: 'date')]
    private DateTime $visitDate;

    #[MongoDB\Field(type: 'string')]
    private string $comment;

    #[MongoDB\Field(type: 'string')]
    private string $feedType;

    #[MongoDB\Field(type: 'float')]
    private float $feedQuantity;

    #[MongoDB\Field(type: 'string')]
    private string $feedUnit;

    #[MongoDB\Field(type: 'date')]
    private DateTime $createdAt;

    public function __construct()
    {
        $this->createdAt = new DateTime();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getAnimalId(): int
    {
        return $this->animalId;
    }

    public function setAnimalId(int $animalId): self
    {
        $this->animalId = $animalId;
        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }
    
    public function getVisitDate(): DateTime
    {
        return $this->visitDate;
    }

    public function setVisitDate(DateTime $visitDate): self
    {
        $this->visitDate = $visitDate;
        return $this;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    public function getFeedType(): string
    {
        return $this->feedType;
    }

    public function setFeedType(string $feedType): self
    {
        $this->feedType = $feedType;
        return $this;
    }

    public function getFeedQuantity(): float
    {
        return $this->feedQuantity;
    }

    public function setFeedQuantity(float $feedQuantity): self
    {
        $this->feedQuantity = $feedQuantity;
        return $this;
    }

    public function getFeedUnit(): string
    {
        return $this->feedUnit;
    }

    public function setFeedUnit(string $feedUnit): self
    {
        $this->feedUnit = $feedUnit;
        return $this;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    // Pas de setter pour id car il est géré par MongoDB
    // Pas de setter pour createdAt car il est défini dans le constructeur
    // Tous les setters retournent $this pour permettre le chaînage des méthodes
    // Typage strict pour tous les paramètres et retours
    // Utilisation de self pour le type de retour des setters
  
}