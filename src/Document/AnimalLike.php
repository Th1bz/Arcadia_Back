<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\Document(collection: 'animal_likes')]
class AnimalLike
{
  #[MongoDB\Id]
    protected $id;

    #[MongoDB\Field(type: 'int')]
    protected $animalId;

    #[MongoDB\Field(type: 'int')]
    protected $likes = 0;

    #[MongoDB\Field(type: 'date')]
    protected $lastUpdated;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getAnimalId(): ?int
    {
        return $this->animalId;
    }

    public function setAnimalId(int $animalId): self
    {
        $this->animalId = $animalId;
        return $this;
    }

    public function getLikes(): int
    {
        return $this->likes;
    }

    public function setLikes(int $likes): self
    {
        $this->likes = $likes;
        return $this;
    }

    public function incrementLikes(): self
    {
        $this->likes++;
        $this->lastUpdated = new \DateTime();
        return $this;
    }
}