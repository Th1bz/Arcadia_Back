<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use DateTime;

#[MongoDB\Document]
class Avis
{
    #[MongoDB\Id]
    private ?string $id = null;

    #[MongoDB\Field(type: 'string')]
    private ?string $nom = null;

    #[MongoDB\Field(type: 'string')]
    private ?string $commentaire = null;

    #[MongoDB\Field(type: 'int')]
    private ?int $note = null;

    #[MongoDB\Field(type: 'date')]
    private DateTime $dateCreation;

    #[MongoDB\Field(type: 'bool')]
    private bool $isValid = false;

    public function __construct()
    {
        $this->dateCreation = new \DateTime();
    }

    // Getters
    public function getId(): ?string
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->dateCreation;
    }

    public function getIsValid(): bool
    {
        return $this->isValid;
    }

    // Setters
    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function setCommentaire(string $commentaire): self
    {
        $this->commentaire = $commentaire;
        return $this;
    }

    public function setNote(int $note): self
    {
        $this->note = $note;
        return $this;
    }

    public function setDateCreation(\DateTime $dateCreation): self
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function setIsValid(bool $isValid): self
    {
        $this->isValid = $isValid;
        return $this;
    }

    // Méthode pour convertir l'objet en tableau
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'nom' => $this->getNom(),
            'commentaire' => $this->getCommentaire(),
            'note' => $this->getNote(),
            'dateCreation' => $this->getDateCreation()->format('Y-m-d H:i:s'),
            'isValid' => $this->getIsValid()
        ];
    }
}