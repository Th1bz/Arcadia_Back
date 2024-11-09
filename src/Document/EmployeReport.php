<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use DateTime;

#[MongoDB\Document]
class EmployeReport
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
    private string $visitTime;

    #[MongoDB\Field(type: 'string')]
    private string $feedConsumedType;

    #[MongoDB\Field(type: 'float')]
    private float $feedConsumedQuantity;

    #[MongoDB\Field(type: 'string')]
    private string $feedConsumedUnit;

    #[MongoDB\Field(type: 'string')]
    private string $feedGivenType;

    #[MongoDB\Field(type: 'float')]
    private float $feedGivenQuantity;

    #[MongoDB\Field(type: 'string')]
    private string $feedGivenUnit;

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

    public function getVisitTime(): string
    {
        return $this->visitTime;
    }

    public function setVisitTime(string $visitTime): self
    {
        $this->visitTime = $visitTime;
        return $this;
    }

    public function getFeedConsumedType(): string
    {
        return $this->feedConsumedType;
    }

    public function setFeedConsumedType(string $feedConsumedType): self
    {
        $this->feedConsumedType = $feedConsumedType;
        return $this;
    }

    public function getFeedConsumedQuantity(): float
    {
        return $this->feedConsumedQuantity;
    }

    public function setFeedConsumedQuantity(float $feedConsumedQuantity): self
    {
        $this->feedConsumedQuantity = $feedConsumedQuantity;
        return $this;
    }

    public function getFeedConsumedUnit(): string
    {
        return $this->feedConsumedUnit;
    }

    public function setFeedConsumedUnit(string $feedConsumedUnit): self
    {
        $this->feedConsumedUnit = $feedConsumedUnit;
        return $this;
    }

    public function getFeedGivenType(): string
    {
        return $this->feedGivenType;
    }

    public function setFeedGivenType(string $feedGivenType): self
    {
        $this->feedGivenType = $feedGivenType;
        return $this;
    }

    public function getFeedGivenQuantity(): float
    {
        return $this->feedGivenQuantity;
    }

    public function setFeedGivenQuantity(float $feedGivenQuantity): self
    {
        $this->feedGivenQuantity = $feedGivenQuantity;
        return $this;
    }

    public function getFeedGivenUnit(): string
    {
        return $this->feedGivenUnit;
    }

    public function setFeedGivenUnit(string $feedGivenUnit): self
    {
        $this->feedGivenUnit = $feedGivenUnit;
        return $this;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }
}