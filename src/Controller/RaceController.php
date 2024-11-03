<?php

namespace App\Controller;

use App\Entity\Race;
use App\Repository\RaceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/race', name: 'api_race_')]
class RaceController extends AbstractController
{
    #[Route('/new', name: 'new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, RaceRepository $raceRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $label = $data['label'] ?? null;

        if (!$label) {
            return new JsonResponse(['error' => 'Le label est requis'], 400);
        }

        // // Vérifier si la race existe déjà
        // $existingRace = $raceRepository->findOneBy(['label' => $label]);
        // if ($existingRace) {
        //     return new JsonResponse([
        //         'message' => 'Race déjà existante',
        //         'race' => [
        //             'id' => $existingRace->getId(),
        //             'label' => $existingRace->getLabel()
        //         ]
        //     ]);
        // }

        // // Créer une nouvelle race
        // $race = new Race();
        // $race->setLabel($label);

        // $entityManager->persist($race);
        // $entityManager->flush();

        // return new JsonResponse([
        //     'message' => 'Race créée avec succès',
        //     'race' => [
        //         'id' => $race->getId(),
        //         'label' => $race->getLabel()
        //     ]
        // ], 201);
    }
} 