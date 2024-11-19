<?php

namespace App\Controller;

use App\Document\Avis;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/avis', name: 'api_avis_')]
class AvisController extends AbstractController
{
    private DocumentManager $documentManager;

    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!$data) {
                throw new \Exception('Données JSON invalides');
            }

            // Sanitize Html à la création des données (pas de lecture des balises html)
            $nom = htmlspecialchars($data['nom'], ENT_QUOTES, 'UTF-8');
            $commentaire = htmlspecialchars($data['commentaire'], ENT_QUOTES, 'UTF-8');

            $avis = new Avis();
            $avis->setNom($nom);
            $avis->setCommentaire($commentaire);
            $avis->setNote((int) ($data['note'] ?? 0));
            $avis->setIsValid(false); // Par défaut, l'avis n'est pas validé

            $this->documentManager->persist($avis);
            $this->documentManager->flush();

            return $this->json(['success' => true, 'id' => $avis->getId()], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/valided/{id}', name: 'valider', methods: ['PUT'])]
    public function valider(string $id): JsonResponse
    {
        try {
            $avis = $this->documentManager->getRepository(Avis::class)->find($id);
            
            if (!$avis) {
                throw new \Exception('Avis non trouvé');
            }

            $avis->setIsValid(true); // L'employé valide l'avis
            $this->documentManager->flush();

            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    #[Route('/{id}', name: 'supprimer', methods: ['DELETE'])]
    public function supprimer(string $id): JsonResponse
    {
        try {
            $avis = $this->documentManager->getRepository(Avis::class)->find($id);
            
            if (!$avis) {
                throw new \Exception('Avis non trouvé');
            }

            $this->documentManager->remove($avis);
            $this->documentManager->flush();

            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    #[Route('/valides', name: 'get_valides', methods: ['GET'])]
    public function getAvisValides(): JsonResponse
    {
        try {
            $avisValides = $this->documentManager->getRepository(Avis::class)
                ->findBy(['isValid' => true], ['dateCreation' => 'DESC']);

            $avisArray = array_map(function(Avis $avis) {
                return $avis->toArray();
            }, $avisValides);

            return $this->json(['avis' => $avisArray]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/all', name: 'get_all', methods: ['GET'])]
    public function getAllAvis(): JsonResponse
    {
        try {
            $tousAvis = $this->documentManager->getRepository(Avis::class)
                ->findBy([], ['dateCreation' => 'DESC']);

            $avisArray = array_map(function(Avis $avis) {
                return $avis->toArray();
            }, $tousAvis);

            return $this->json(['avis' => $avisArray]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/not-valid', name: 'get_not_valid', methods: ['GET'])]
    public function getAvisNotValid(): JsonResponse
    {
        try {
            $avisNotValid = $this->documentManager->getRepository(Avis::class)
                ->findBy(['isValid' => false], ['dateCreation' => 'DESC']);

            $avisArray = array_map(function(Avis $avis) {
                return $avis->toArray();
            }, $avisNotValid);

            return $this->json(['avis' => $avisArray]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}