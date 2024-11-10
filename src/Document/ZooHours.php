<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\Document]
class ZooHours
{
    #[MongoDB\Id]
    private $id;

    #[MongoDB\Field(type: 'hash')]
    private $hours = [
        'week' => [
            'opening' => '09:00',
            'closing' => '18:00'
        ],
        'weekend' => [
            'opening' => '10:00',
            'closing' => '19:00'
        ]
    ];

    #[MongoDB\Field(type: 'date')]
    private $updatedAt;

    public function __construct()
    {
        $this->updatedAt = new \DateTime();
    }

    // Getters et Setters
    public function getId() { return $this->id; }
    
    public function getHours() { return $this->hours; }
    public function setHours(array $hours): self
    {
        $this->hours = $hours;
        return $this;
    }

    public function getUpdatedAt() { return $this->updatedAt; }
    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}