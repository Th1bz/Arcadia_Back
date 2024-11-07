<?php

namespace App\Entity;

use App\Repository\HabitatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HabitatRepository::class)]
class Habitat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'habitat', targetEntity: Animal::class)]
    private Collection $animals;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    /**
     * @var Collection<int, HabitatPicture>
     */
    #[ORM\OneToMany(targetEntity: HabitatPicture::class, mappedBy: 'habitat', orphanRemoval: true)]
    private Collection $habitatPictures;

    public function __construct()
    {
        $this->animals = new ArrayCollection();
        $this->habitatPictures = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return Collection<int, Animal>
     */
    public function getAnimals(): Collection
    {
        return $this->animals;
    }

    public function addAnimal(Animal $animal): static
    {
        if (!$this->animals->contains($animal)) {
            $this->animals->add($animal);
            $animal->setHabitat($this);
        }
        return $this;
    }

    public function removeAnimal(Animal $animal): static
    {
        if ($this->animals->removeElement($animal)) {
            if ($animal->getHabitat() === $this) {
                $animal->setHabitat(null);
            }
        }
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, HabitatPicture>
     */
    public function getHabitatPictures(): Collection
    {
        return $this->habitatPictures;
    }

    public function addHabitatPicture(HabitatPicture $habitatPicture): static
    {
        if (!$this->habitatPictures->contains($habitatPicture)) {
            $this->habitatPictures->add($habitatPicture);
            $habitatPicture->setHabitat($this);
        }

        return $this;
    }

    public function removeHabitatPicture(HabitatPicture $habitatPicture): static
    {
        if ($this->habitatPictures->removeElement($habitatPicture)) {

            if ($habitatPicture->getHabitat() === $this) {
                $habitatPicture->setHabitat(null);
            }
        }

        return $this;
    }
}
