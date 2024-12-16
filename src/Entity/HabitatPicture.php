<?php

namespace App\Entity;

use App\Repository\HabitatPictureRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HabitatPictureRepository::class)]
class HabitatPicture
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $picture_data = null;

    #[ORM\ManyToOne(inversedBy: 'habitatPictures')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Habitat $habitat = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getPictureData(): ?string
    {
        return $this->picture_data;
    }

    public function setPictureData(string $picture_data): static
    {
        $this->picture_data = $picture_data;

        return $this;
    }

    public function getHabitat(): ?Habitat
    {
        return $this->habitat;
    }

    public function setHabitat(?Habitat $habitat): static
    {
        $this->habitat = $habitat;

        return $this;
    }
}