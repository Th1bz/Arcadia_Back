<?php

namespace App\Controller;

use App\Document\ZooHours;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class ZooHoursController extends AbstractController
{
    #[Route('/hours', name: 'get_hours', methods: ['GET', 'OPTIONS'])]
    public function getHours(Request $request, DocumentManager $dm): JsonResponse
    {
        if ($request->getMethod() === 'OPTIONS') {
            return $this->corsResponse();
        }

        $hours = $dm->getRepository(ZooHours::class)
            ->findOneBy([], ['updatedAt' => 'DESC']);

        if (!$hours) {
            return $this->corsJsonResponse([
                'hours' => [
                    'week' => [
                        'opening' => '09:00',
                        'closing' => '18:00'
                    ],
                    'weekend' => [
                        'opening' => '10:00',
                        'closing' => '19:00'
                    ]
                ]
            ]);
        }

        return $this->corsJsonResponse([
            'hours' => $hours->getHours()
        ]);
    }

    #[Route('/hours', name: 'update_hours', methods: ['POST', 'OPTIONS'])]
    public function updateHours(Request $request, DocumentManager $dm): JsonResponse
    {
        if ($request->getMethod() === 'OPTIONS') {
            return $this->corsResponse();
        }

        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['hours']) || !isset($data['hours']['week']) || !isset($data['hours']['weekend'])) {
                throw new \Exception('Format de données invalide');
            }

            // Vérifier si les horaires existent déjà
            $hours = $dm->getRepository(ZooHours::class)->findOneBy([], ['updatedAt' => 'DESC']);
            
            if (!$hours) {
                $hours = new ZooHours();
            }
            
            $hours->setHours($data['hours']);
            $hours->setUpdatedAt(new \DateTime());
            
            $dm->persist($hours);
            $dm->flush();

            return $this->corsJsonResponse([
                'message' => 'Horaires mis à jour avec succès',
                'hours' => $hours->getHours()
            ]);
            
        } catch (\Exception $e) {
            return $this->corsJsonResponse([
                'error' => 'Erreur lors de la mise à jour des horaires',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    private function corsResponse(): JsonResponse
    {
        $response = new JsonResponse(null, 204);
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type');
        return $response;
    }

    private function corsJsonResponse(array $data, int $status = 200): JsonResponse
    {
        $response = new JsonResponse($data, $status);
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type');
        return $response;
    }
}