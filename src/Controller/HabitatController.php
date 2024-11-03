<?php

namespace App\Controller;

use App\Entity\Habitat;
use App\Repository\HabitatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/habitat', name: 'api_habitat_')]
class HabitatController extends AbstractController
{
    #[Route('/new', name: 'new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, HabitatRepository $habitatRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $name = $data['name'] ?? null;
        $description = $data['description'] ?? '';
        $commentaire = $data['commentaire_habitat'] ?? '';

        if (!$name) {
            return new JsonResponse(['error' => 'Le nom est requis'], 400);
        }

        // Vérifier si l'habitat existe déjà
        $existingHabitat = $habitatRepository->findOneBy(['name' => $name]);
        if ($existingHabitat) {
            return new JsonResponse([
                'message' => 'Habitat déjà existant',
                'habitat' => [
                    'id' => $existingHabitat->getId(),
                    'name' => $existingHabitat->getName()
                ]
            ]);
        }

        // Créer un nouvel habitat
        $habitat = new Habitat();
        $habitat->setName($name);
        $habitat->setDescription($description);
        $habitat->setCommentaireHabitat($commentaire);

        $entityManager->persist($habitat);
        $entityManager->flush();

        return new JsonResponse([
            'message' => 'Habitat créé avec succès',
            'habitat' => [
                'id' => $habitat->getId(),
                'name' => $habitat->getName(),
                'description' => $habitat->getDescription(),
                'commentaire' => $habitat->getCommentaireHabitat()
            ]
        ], 201);
    }
} 