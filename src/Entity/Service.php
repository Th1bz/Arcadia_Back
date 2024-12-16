<?php

namespace App\Entity;

use App\Repository\ServiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: ServiceRepository::class)]
class Service
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /**
     * @var Collection<int, ServicePicture>
     */
    #[ORM\OneToMany(targetEntity: ServicePicture::class, mappedBy: 'service', orphanRemoval: true)]
    private Collection $servicePictures;

    public function __construct()
    {
        $this->servicePictures = new ArrayCollection();
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
     * @return Collection<int, ServicePicture>
     */
    public function getServicePictures(): Collection
    {
        return $this->servicePictures;
    }

    public function addServicePicture(ServicePicture $servicePicture): static
    {
        if (!$this->servicePictures->contains($servicePicture)) {
            $this->servicePictures->add($servicePicture);
            $servicePicture->setService($this);
        }

        return $this;
    }

    public function removeServicePicture(ServicePicture $servicePicture): static
    {
        if ($this->servicePictures->removeElement($servicePicture)) {

            if ($servicePicture->getService() === $this) {
                $servicePicture->setService(null);
            }
        }

        return $this;
    }
}