<?php

namespace App\Controller;

use App\Entity\Animal;
use App\Entity\Habitat;
use App\Entity\Race;
use App\Entity\Picture;
use App\Repository\AnimalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;


#[Route('/animal', name: 'app_animal')]
class AnimalController extends AbstractController
{

    public function __construct(private EntityManagerInterface $manager, private AnimalRepository $animalRepository)
    {
        
    }

    #[Route('/add', name: 'add_animal', methods: ['POST', 'OPTIONS'])]
    public function addAnimal(Request $request): JsonResponse
    {
        if ($request->getMethod() === 'OPTIONS') {
            $response = new JsonResponse(null, 204);
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'POST, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type');
            return $response;
        }

        try {
            $data = json_decode($request->getContent(), true);
            
            $animal = new Animal();
            $animal->setFirstName($data['firstName']);
            $animal->setRace($this->manager->getRepository(Race::class)->find($data['race']));
            $animal->setHabitat($this->manager->getRepository(Habitat::class)->find($data['habitat']));
            $animal->setStatus($data['status'] ?? 'En bonne santé');

            // Gestion de l'image si présente
            if (!empty($data['pictureData'])) {
                $imageData = base64_decode($data['pictureData']);
                
                // Générer un nom unique pour l'image
                $fileName = uniqid() . '.jpg';
                
                // Définir le chemin de sauvegarde
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/animals/';
                
                // Créer le dossier s'il n'existe pas
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                // Sauvegarder l'image
                file_put_contents($uploadDir . $fileName, $imageData);
                
                // Créer l'entrée dans la base de données avec le chemin
                $picture = new Picture();
                $picture->setPictureData('/uploads/animals/' . $fileName);
                $picture->setAnimal($animal);
                $this->manager->persist($picture);
            }

            $this->manager->persist($animal);
            $this->manager->flush();

            $response = new JsonResponse([
                'message' => 'Animal créé avec succès',
                'animal' => [
                    'id' => $animal->getId(),
                    'firstName' => $animal->getFirstName(),
                    'status' => $animal->getStatus(),
                    'race' => [
                        'id' => $animal->getRace()->getId(),
                        'label' => $animal->getRace()->getLabel()
                    ],
                    'habitat' => [
                        'id' => $animal->getHabitat()->getId(),
                        'name' => $animal->getHabitat()->getName()
                    ],
                    'picture' => $animal->getPicture() ? [
                        'id' => $animal->getPicture()->getId(),
                        'url' => $animal->getPicture()->getPictureData()
                    ] : null
                ]
            ], JsonResponse::HTTP_CREATED);
            
            $response->headers->set('Access-Control-Allow-Origin', '*');
            return $response;

        } catch (\Exception $e) {
            $response = new JsonResponse([
                'message' => 'Erreur lors de la création',
                'error' => $e->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
            
            $response->headers->set('Access-Control-Allow-Origin', '*');
            return $response;
        }
    }

    #[Route('/list', name: 'list_animals', methods: ['GET'])]
    public function listAnimals(): JsonResponse
    {
        try {
            $animals = $this->animalRepository->findAll();
            $data = [];

            foreach ($animals as $animal) {
                $data[] = [
                    'id' => $animal->getId(),
                    'firstName' => $animal->getFirstName(),
                    'status' => $animal->getStatus(),
                    'race' => [
                        'id' => $animal->getRace()->getId(),
                        'label' => $animal->getRace()->getLabel()
                    ],
                    'habitat' => [
                        'id' => $animal->getHabitat()->getId(),
                        'name' => $animal->getHabitat()->getName()
                    ],
                    'picture' => $animal->getPicture() ? [
                        'id' => $animal->getPicture()->getId(),
                        'url' => $animal->getPicture()->getPictureData()
                    ] : null
                ];
            }

            $response = new JsonResponse($data);
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'GET');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type');
            
            return $response;

        } catch (\Exception $e) {
            // Log l'erreur
            error_log('Erreur dans listAnimals: ' . $e->getMessage());
            
            $response = new JsonResponse([
                'message' => 'Une erreur est survenue lors de la récupération des animaux',
                'error' => $e->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
            
            $response->headers->set('Access-Control-Allow-Origin', '*');
            return $response;
        }
    }

    #[Route('/delete/{id}', name: 'delete_animal', methods: ['DELETE', 'OPTIONS'])]
    public function deleteAnimal(int $id): JsonResponse
    {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            $response = new JsonResponse(null, 204);
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'DELETE, OPTIONS');
            return $response;
        }

        try {
            $animal = $this->animalRepository->find($id);
            
            if (!$animal) {
                return new JsonResponse([
                    'message' => 'Animal non trouvé'
                ], Response::HTTP_NOT_FOUND);
            }

            // Supprimer l'image physique si elle existe
            $picture = $animal->getPicture();
            if ($picture) {
                $imagePath = $this->getParameter('kernel.project_dir') . '/public' . $picture->getPictureData();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            // Supprimer l'animal (et sa photo en cascade grâce à la relation)
            $this->manager->remove($animal);
            $this->manager->flush();
            
            $response = new JsonResponse([
                'message' => 'Animal supprimé avec succès'
            ], Response::HTTP_OK);
            
            $response->headers->set('Access-Control-Allow-Origin', '*');
            return $response;
            
        } catch (\Exception $e) {
            $response = new JsonResponse([
                'message' => 'Une erreur est survenue lors de la suppression de l\'animal',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
            
            $response->headers->set('Access-Control-Allow-Origin', '*');
            return $response;
        }
    }

    #[Route('/edit/{id}', name: 'edit_animal', methods: ['PUT', 'OPTIONS'])]
    public function editAnimal(int $id, Request $request): JsonResponse
    {
        if ($request->getMethod() === 'OPTIONS') {
            $response = new JsonResponse(null, 204);
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'PUT, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type');
            return $response;
        }

        try {
            $animal = $this->animalRepository->find($id);
            if (!$animal) {
                throw new \Exception('Animal non trouvé');
            }

            $data = json_decode($request->getContent(), true);

            // Mise à jour des champs basiques
            if (isset($data['firstName'])) {
                $animal->setFirstName($data['firstName']);
            }
            if (isset($data['race'])) {
                $race = $this->manager->getRepository(Race::class)->find($data['race']);
                if ($race) {
                    $animal->setRace($race);
                }
            }
            if (isset($data['status'])) {
                $animal->setStatus($data['status']);
            }

            // Gestion de la nouvelle image
            if (!empty($data['pictureData'])) {
                // Supprimer l'ancienne image si elle existe
                $oldPicture = $animal->getPicture();
                if ($oldPicture) {
                    $oldImagePath = $this->getParameter('kernel.project_dir') . '/public' . $oldPicture->getPictureData();
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                    $this->manager->remove($oldPicture);
                }

                // Créer la nouvelle image
                $imageData = base64_decode($data['pictureData']);
                $fileName = uniqid() . '.jpg';
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/animals/';
                
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                file_put_contents($uploadDir . $fileName, $imageData);

                $picture = new Picture();
                $picture->setPictureData('/uploads/animals/' . $fileName);
                $picture->setAnimal($animal);
                $this->manager->persist($picture);
            }

            $this->manager->flush();

            $response = new JsonResponse([
                'message' => 'Animal modifié avec succès',
                'animal' => [
                    'id' => $animal->getId(),
                    'firstName' => $animal->getFirstName(),
                    'status' => $animal->getStatus(),
                    'race' => [
                        'id' => $animal->getRace()->getId(),
                        'label' => $animal->getRace()->getLabel()
                    ],
                    'habitat' => [
                        'id' => $animal->getHabitat()->getId(),
                        'name' => $animal->getHabitat()->getName()
                    ],
                    'picture' => $animal->getPicture() ? [
                        'id' => $animal->getPicture()->getId(),
                        'url' => $animal->getPicture()->getPictureData()
                    ] : null
                ]
            ]);
            
            $response->headers->set('Access-Control-Allow-Origin', '*');
            return $response;

        } catch (\Exception $e) {
            $response = new JsonResponse([
                'message' => 'Une erreur est survenue lors de la modification de l\'animal',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
            
            $response->headers->set('Access-Control-Allow-Origin', '*');
            return $response;
        }
    }

    #[Route('/picture/{id}', name: 'get_picture', methods: ['GET'])]
    public function getPicture(int $id): Response
    {
        try {
            $picture = $this->manager->getRepository(Picture::class)->find($id);
            
            if (!$picture) {
                throw $this->createNotFoundException('Image non trouvée');
            }
            
            // Obtenir le chemin complet du fichier
            $filePath = $this->getParameter('kernel.project_dir') . '/public' . $picture->getPictureData();
            
            // Vérifier si le fichier existe
            if (!file_exists($filePath)) {
                throw $this->createNotFoundException('Fichier image non trouvé');
            }
            
            // Lire le contenu du fichier
            $fileContent = file_get_contents($filePath);
            
            // Créer la réponse
            $response = new Response($fileContent);
            
            // Déterminer le type MIME
            $mimeType = mime_content_type($filePath);
            
            // Configurer les en-têtes
            $response->headers->set('Content-Type', $mimeType);
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Cache-Control', 'public, max-age=3600');
            
            return $response;
            
        } catch (\Exception $e) {
            // Log l'erreur
            error_log('Erreur lors de la récupération de l\'image: ' . $e->getMessage());
            
            // Retourner une image par défaut ou une erreur 404
            throw $this->createNotFoundException('Image non disponible');
        }
    }

    #[Route('/{id}', name: 'get_animal', methods: ['GET', 'OPTIONS'])]
    public function getAnimal(int $id): JsonResponse
    {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            $response = new JsonResponse(null, 204);
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, OPTIONS');
            return $response;
        }

        try {
            $animal = $this->animalRepository->find($id);
            
            if (!$animal) {
                $response = new JsonResponse([
                    'message' => 'Animal non trouvé'
                ], Response::HTTP_NOT_FOUND);
                $response->headers->set('Access-Control-Allow-Origin', '*');
                return $response;
            }
            
            $response = new JsonResponse([
                'id' => $animal->getId(),
                'firstName' => $animal->getFirstName(),
                'status' => $animal->getStatus(),
                'race' => [
                    'id' => $animal->getRace()->getId(),
                    'label' => $animal->getRace()->getLabel()
                ],
                'habitat' => [
                    'id' => $animal->getHabitat()->getId(),
                    'name' => $animal->getHabitat()->getName()
                ],
                'picture' => $animal->getPicture() ? [
                    'id' => $animal->getPicture()->getId(),
                    'url' => $animal->getPicture()->getPictureData()
                ] : null
            ]);
            
            $response->headers->set('Access-Control-Allow-Origin', '*');
            return $response;
            
        } catch (\Exception $e) {
            $response = new JsonResponse([
                'message' => 'Une erreur est survenue',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
            
            $response->headers->set('Access-Control-Allow-Origin', '*');
            return $response;
        }
    }

}