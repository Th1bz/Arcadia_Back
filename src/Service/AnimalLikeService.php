<?php

namespace App\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use App\Document\AnimalLike;

class AnimalLikeService
{
    private $documentManager;

    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    public function incrementLike(int $animalId): void
    {
        $likeDoc = $this->documentManager
            ->getRepository(AnimalLike::class)
            ->findOneBy(['animalId' => $animalId]);

        if (!$likeDoc) {
            $likeDoc = new AnimalLike();
            $likeDoc->setAnimalId($animalId);
            $likeDoc->setLikes(0);
        }

        $likeDoc->incrementLikes();
        $this->documentManager->persist($likeDoc);
        $this->documentManager->flush();
    }
}