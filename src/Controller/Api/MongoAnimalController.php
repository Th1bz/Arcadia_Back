<?php

namespace App\Controller\Api;

use App\Document\MongoAnimal;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MongoAnimalController extends AbstractController
{
    public function __construct(private DocumentManager $dm) {}

    #[Route('/api/mongo/animals', methods: ['GET'])]
    public function list(): Response
    {
        try {
            $animals = $this->dm->getRepository(MongoAnimal::class)->findAll();
            return new Response(
                json_encode(['animals' => $animals]),
                Response::HTTP_OK,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            return new Response(
                json_encode(['error' => $e->getMessage()]),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['Content-Type' => 'application/json']
            );
        }
    }

    #[Route('/api/mongo/animals', methods: ['POST'])]
    public function create(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            $animal = new MongoAnimal();
            $animal->setName($data['name']);
            $animal->setHabitat($data['habitat']);
            
            $this->dm->persist($animal);
            $this->dm->flush();
            
            return new Response(
                json_encode([
                    'message' => 'Animal created successfully',
                    'animal' => [
                        'id' => $animal->getId(),
                        'name' => $animal->getName(),
                        'habitat' => $animal->getHabitat()
                    ]
                ]),
                Response::HTTP_CREATED,
                ['Content-Type' => 'application/json']
            );
        } catch (\Exception $e) {
            return new Response(
                json_encode(['error' => $e->getMessage()]),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                ['Content-Type' => 'application/json']
            );
        }
    }
}