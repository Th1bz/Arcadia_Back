<?php

namespace App\Controller;

use App\Document\VetoReport;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/veto-report', name: 'veto_report_')]
class VetoReportController extends AbstractController
{
    #[Route('/add', name: 'add', methods: ['POST'])]
    public function addReport(Request $request, DocumentManager $dm): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            $report = new VetoReport();
            $report->setAnimalId($data['animalId']);
            $report->setUsername($data['username']);
            $report->setVisitDate(new \DateTime($data['visitDate']));
            $report->setComment($data['comment']);
            $report->setFeedType($data['feedType']);
            $report->setFeedQuantity($data['feedQuantity']);
            $report->setFeedUnit($data['feedUnit']);

            $dm->persist($report);
            $dm->flush();

            return new JsonResponse(['message' => 'Rapport créé avec succès'], 201);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/animal/{id}', name: 'get_by_animal', methods: ['GET'])]
public function getReportsByAnimal(string $id, DocumentManager $dm): JsonResponse
{
    try {
        $reports = $dm->getRepository(VetoReport::class)
            ->findBy(['animalId' => (int)$id], ['visitDate' => 'DESC']);

        $formattedReports = [];
        foreach ($reports as $report) {
            $formattedReports[] = [
                'id' => $report->getId(),
                'animalId' => $report->getAnimalId(),
                'username' => $report->getUsername(),
                'visitDate' => $report->getVisitDate()->format('Y-m-d'),
                'feedType' => $report->getFeedType(),
                'feedQuantity' => $report->getFeedQuantity(),
                'feedUnit' => $report->getFeedUnit(),
                'comment' => $report->getComment()
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
    public function getReportsByUser(string $username, DocumentManager $dm): JsonResponse
    {
        try {
            $reports = $dm->getRepository(VetoReport::class)
                ->findBy(['username' => $username], ['visitDate' => 'DESC']);

        // Convertir les rapports en tableau
        $reportsArray = array_map(function($report) {
            return [
                'id' => $report->getId(),
                'animalId' => $report->getAnimalId(),
                'visitDate' => $report->getVisitDate()->format('Y-m-d'),
                'comment' => $report->getComment(),
                'feedType' => $report->getFeedType(),
                'feedQuantity' => $report->getFeedQuantity(),
                'feedUnit' => $report->getFeedUnit()
            ];
        }, $reports);

            return new JsonResponse(['reports' => $reportsArray]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }


    #[Route('/last/{animalId}', name: 'last_veto_report', methods: ['GET'])]
    public function getLastReport(int $animalId, DocumentManager $dm): JsonResponse
    {
        try {
            $report = $dm->getRepository(VetoReport::class)
                ->findOneBy(
                    ['animalId' => $animalId],
                ['visitDate' => 'DESC']
            );

        if (!$report) {
            return new JsonResponse(null, 204);
        }

        return new JsonResponse([
            'id' => $report->getId(),
            'visitDate' => $report->getVisitDate()->format('Y-m-d'),
            'username' => $report->getUsername(),
            'feedType' => $report->getFeedType(),
            'feedQuantity' => $report->getFeedQuantity(),
            'feedUnit' => $report->getFeedUnit(),
            'comment' => $report->getComment()
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    // Ajouter cette nouvelle route
#[Route('/all', name: 'get_all', methods: ['GET'])]
public function getAllReports(DocumentManager $dm): JsonResponse
{
    try {
        $reports = $dm->getRepository(VetoReport::class)->findBy(
            [], // critères de recherche (vide pour tout récupérer)
            ['visitDate' => 'DESC'] // tri par date de visite décroissante
        );

        $formattedReports = [];
        foreach ($reports as $report) {
            $formattedReports[] = [
                'id' => $report->getId(),
                'animalId' => $report->getAnimalId(),
                'username' => $report->getUsername(),
                'visitDate' => $report->getVisitDate()->format('Y-m-d'),
                'feedType' => $report->getFeedType(),
                'feedQuantity' => $report->getFeedQuantity(),
                'feedUnit' => $report->getFeedUnit(),
                'comment' => $report->getComment(),
                'createdAt' => $report->getCreatedAt()->format('Y-m-d H:i:s')
            ];
        }

        return $this->json([
            'reports' => $formattedReports
        ]);

    } catch (\Exception $e) {
        return new JsonResponse(['error' => $e->getMessage()], 400);
    }
}
}