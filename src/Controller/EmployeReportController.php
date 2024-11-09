<?php

namespace App\Controller;

use App\Document\EmployeReport;
use DateTime;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/employe-report', name: 'employe_report_')]
class EmployeReportController extends AbstractController
{
    #[Route('/add', name: 'add', methods: ['POST'])]
    public function add(Request $request, DocumentManager $dm): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $report = new EmployeReport();
            $report->setAnimalId($data['animalId'])
                ->setUsername($data['username'])
                ->setVisitDate(new DateTime($data['visitDate']))
                ->setVisitTime($data['visitTime'])
                ->setFeedConsumedType($data['feedConsumed']['type'])
                ->setFeedConsumedQuantity($data['feedConsumed']['quantity'])
                ->setFeedConsumedUnit($data['feedConsumed']['unit'])
                ->setFeedGivenType($data['feedGiven']['type'])
                ->setFeedGivenQuantity($data['feedGiven']['quantity'])
                ->setFeedGivenUnit($data['feedGiven']['unit']);

            $dm->persist($report);
            $dm->flush();

            return $this->json([
                'message' => 'Rapport créé avec succès',
                'id' => $report->getId()
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la création du rapport',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/animal/{id}', name: 'get_by_animal', methods: ['GET'])]
    public function getReportsByAnimal(string $id, DocumentManager $dm): JsonResponse
    {
        try {
            $reports = $dm->getRepository(EmployeReport::class)
                ->findBy(['animalId' => (int)$id], ['visitDate' => 'DESC']);
    
            $formattedReports = [];
            foreach ($reports as $report) {
                $formattedReports[] = [
                    'id' => $report->getId(),
                    'animalId' => $report->getAnimalId(),
                    'username' => $report->getUsername(),
                    'visitDate' => $report->getVisitDate()->format('Y-m-d'),
                    'visitTime' => $report->getVisitTime(),
                    'feedConsumedType' => $report->getFeedConsumedType(),
                    'feedConsumedQuantity' => $report->getFeedConsumedQuantity(),
                    'feedConsumedUnit' => $report->getFeedConsumedUnit(),
                    'feedGivenType' => $report->getFeedGivenType(),
                    'feedGivenQuantity' => $report->getFeedGivenQuantity(),
                    'feedGivenUnit' => $report->getFeedGivenUnit()
                ];
            }
    
            return $this->json([
                'reports' => $formattedReports
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }


    #[Route('/user/{username}', name: 'get_by_user', methods: ['GET'])]
    public function getByUser(string $username, DocumentManager $dm): JsonResponse
    {
        try {
            $reports = $dm->getRepository(EmployeReport::class)->findBy(
                ['username' => $username],
                ['visitDate' => 'DESC']
            );

            $formattedReports = [];
            foreach ($reports as $report) {
                $formattedReports[] = [
                    'id' => $report->getId(),
                    'animalId' => $report->getAnimalId(),
                    'visitDate' => $report->getVisitDate()->format('Y-m-d'),
                    'visitTime' => $report->getVisitTime(),
                    'feedConsumed' => [
                        'type' => $report->getFeedConsumedType(),
                        'quantity' => $report->getFeedConsumedQuantity(),
                        'unit' => $report->getFeedConsumedUnit()
                    ],
                    'feedGiven' => [
                        'type' => $report->getFeedGivenType(),
                        'quantity' => $report->getFeedGivenQuantity(),
                        'unit' => $report->getFeedGivenUnit()
                    ],
                    'createdAt' => $report->getCreatedAt()->format('Y-m-d H:i:s')
                ];
            }

            return $this->json([
                'reports' => $formattedReports
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la récupération des rapports',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/all', name: 'get_all', methods: ['GET'])]
    public function getAllReports(DocumentManager $dm): JsonResponse
    {
    try {
        $reports = $dm->getRepository(EmployeReport::class)->findBy(
            [],
            ['visitDate' => 'DESC'] // tri par date de visite décroissante
        );

        $formattedReports = [];
        foreach ($reports as $report) {
            $formattedReports[] = [
                'id' => $report->getId(),
                'animalId' => $report->getAnimalId(),
                'username' => $report->getUsername(),
                'visitDate' => $report->getVisitDate()->format('Y-m-d'),
                'visitTime' => $report->getVisitTime(),
                'feedConsumedType' => $report->getFeedConsumedType(),
                'feedConsumedQuantity' => $report->getFeedConsumedQuantity(),
                'feedConsumedUnit' => $report->getFeedConsumedUnit(),
                'feedGivenType' => $report->getFeedGivenType(),
                'feedGivenQuantity' => $report->getFeedGivenQuantity(),
                'feedGivenUnit' => $report->getFeedGivenUnit(),
                'createdAt' => $report->getCreatedAt()->format('Y-m-d H:i:s')
            ];
        }

        return $this->json([
            'reports' => $formattedReports
        ]);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la récupération des rapports',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'get_one', methods: ['GET'])]
    public function getOne(string $id, DocumentManager $dm): JsonResponse
    {
        try {
            $report = $dm->getRepository(EmployeReport::class)->find($id);

            if (!$report) {
                return $this->json([
                    'error' => 'Rapport non trouvé'
                ], Response::HTTP_NOT_FOUND);
            }

            return $this->json([
                'id' => $report->getId(),
                'animalId' => $report->getAnimalId(),
                'visitDate' => $report->getVisitDate()->format('Y-m-d'),
                'visitTime' => $report->getVisitTime(),
                'feedConsumed' => [
                    'type' => $report->getFeedConsumedType(),
                    'quantity' => $report->getFeedConsumedQuantity(),
                    'unit' => $report->getFeedConsumedUnit()
                ],
                'feedGiven' => [
                    'type' => $report->getFeedGivenType(),
                    'quantity' => $report->getFeedGivenQuantity(),
                    'unit' => $report->getFeedGivenUnit()
                ],
                'createdAt' => $report->getCreatedAt()->format('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la récupération du rapport',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/last/{animalId}', name: 'last_veto_report', methods: ['GET'])]
    public function getLastReport(int $animalId, DocumentManager $dm): JsonResponse
    {
        try {
            $report = $dm->getRepository(EmployeReport::class)
                ->findOneBy(
                    ['animalId' => $animalId],
                ['visitDate' => 'DESC']
            );

        if (!$report) {
            return new JsonResponse(null, 204);
        }

        return $this->json([
            'id' => $report->getId(),
            'animalId' => $report->getAnimalId(),
            'visitDate' => $report->getVisitDate()->format('Y-m-d'),
            'visitTime' => $report->getVisitTime(),
            'feedConsumed' => [
                'type' => $report->getFeedConsumedType(),
                'quantity' => $report->getFeedConsumedQuantity(),
                'unit' => $report->getFeedConsumedUnit()
            ],
            'feedGiven' => [
                'type' => $report->getFeedGivenType(),
                'quantity' => $report->getFeedGivenQuantity(),
                'unit' => $report->getFeedGivenUnit()
            ],
            'createdAt' => $report->getCreatedAt()->format('Y-m-d H:i:s')
        ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }
}