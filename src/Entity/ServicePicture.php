<?php

namespace App\Entity;

use App\Repository\ServicePictureRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ServicePictureRepository::class)]
class ServicePicture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'text')]
    private ?string $pictureData = null;

    #[ORM\ManyToOne(targetEntity: Service::class, inversedBy: 'servicePictures')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Service $service = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPictureData(): ?string
    {
        return $this->pictureData;
    }

    public function setPictureData(string $pictureData): self
    {
        $this->pictureData = $pictureData;
        return $this;
    }

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): self
    {
        $this->service = $service;
        return $this;
    }
}
