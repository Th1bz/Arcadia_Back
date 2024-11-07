<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\Document]
class MongoAnimal
{
    #[MongoDB\Id]
    private $id;

    #[MongoDB\Field(type: 'string')]
    private $name;

    #[MongoDB\Field(type: 'string')]
    private $habitat;

    // Getters and Setters
    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getHabitat(): ?string
    {
        return $this->habitat;
    }

    public function setHabitat(string $habitat): self
    {
        $this->habitat = $habitat;
        return $this;
    }
}