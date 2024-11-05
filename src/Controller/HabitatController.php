<?php

namespace App\Controller;

use App\Entity\Habitat;
use App\Entity\HabitatPicture;
use App\Repository\HabitatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/habitat', name: 'app_habitat')]
class HabitatController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager, private HabitatRepository $habitatRepository)
    {
    }

    #[Route('/new', name: 'new', methods: ['POST'])]
    public function new(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            $habitat = new Habitat();
            $habitat->setName($data['name']);
            $habitat->setDescription($data['description']);

            // Gestion de l'image
            if (!empty($data['pictureData'])) {
                $imageData = $data['pictureData'];
                
                // Extraire le type MIME et les données
                if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
                    $imageData = substr($imageData, strpos($imageData, ',') + 1);
                    $decodedImage = base64_decode($imageData);
                    
                    // Générer un nom unique
                    $fileName = uniqid() . '.' . $matches[1];
                    $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/habitats/';
                    
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    // Sauvegarder l'image
                    file_put_contents($uploadDir . $fileName, $decodedImage);
                    
                    // Créer l'entrée dans habitat_picture
                    $picture = new HabitatPicture();
                    $picture->setPictureData('/uploads/habitats/' . $fileName);
                    $picture->setHabitat($habitat);
                    
                    $this->manager->persist($picture);
                }
            }

            $this->manager->persist($habitat);
            $this->manager->flush();

            return new JsonResponse([
                'message' => 'Habitat créé avec succès',
                'habitat' => [
                    'id' => $habitat->getId(),
                    'name' => $habitat->getName(),
                    'description' => $habitat->getDescription(),
                    'pictures' => $habitat->getHabitatPictures()->map(function($picture) {
                        return [
                            'id' => $picture->getId(),
                            'path' => $picture->getPictureData()
                        ];
                    })->toArray()
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/list', name: 'list_habitats', methods: ['GET'])]
    public function listHabitats(): JsonResponse
    {
        try {
            $habitats = $this->habitatRepository->findAll();
            $data = [];

            foreach ($habitats as $habitat) {
                $pictures = $habitat->getHabitatPictures()->map(function($picture) {
                    return [
                        'id' => $picture->getId(),
                        'path' => $picture->getPictureData()
                    ];
                })->toArray();

                $data[] = [
                    'id' => $habitat->getId(),
                    'name' => $habitat->getName(),
                    'description' => $habitat->getDescription(),
                    'pictures' => $pictures
                ];
            }

            $response = new JsonResponse($data);
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

    #[Route('/edit/{id}', name: 'edit_habitat', methods: ['PUT', 'OPTIONS'])]
    public function editHabitat(int $id, Request $request): JsonResponse
    {
        if ($request->getMethod() === 'OPTIONS') {
            $response = new JsonResponse(null, 204);
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'PUT, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type');
            return $response;
        }

        try {
            $habitat = $this->habitatRepository->find($id);
            if (!$habitat) {
                throw new \Exception('Habitat non trouvé');
            }

            $data = json_decode($request->getContent(), true);

            // Mise à jour des champs basiques
            if (isset($data['name'])) {
                $existingHabitat = $this->habitatRepository->findOneBy(['name' => $data['name']]);
                if ($existingHabitat && $existingHabitat->getId() !== $id) {
                    throw new \Exception('Un habitat avec ce nom existe déjà');
                }
                $habitat->setName($data['name']);
            }
            
            if (isset($data['description'])) {
                $habitat->setDescription($data['description']);
            }

            // Gestion de la nouvelle image
            if (!empty($data['pictureData'])) {
                // Supprimer l'ancienne image si elle existe
                $oldPictures = $habitat->getHabitatPictures();
                foreach ($oldPictures as $oldPicture) {
                    // Supprimer le fichier physique
                    $oldImagePath = $this->getParameter('kernel.project_dir') . '/public' . $oldPicture->getPictureData();
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                    
                    $this->manager->remove($oldPicture);
                }
                $this->manager->flush(); // Flush immédiat pour supprimer les anciennes images

                // Créer la nouvelle image
                if (preg_match('/^data:image\/(\w+);base64,/', $data['pictureData'], $matches)) {
                    $imageData = substr($data['pictureData'], strpos($data['pictureData'], ',') + 1);
                    $decodedImage = base64_decode($imageData);
                    
                    $fileName = uniqid() . '.' . $matches[1];
                    $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/habitats/';
                    
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    file_put_contents($uploadDir . $fileName, $decodedImage);

                    $picture = new HabitatPicture();
                    $picture->setPictureData('/uploads/habitats/' . $fileName);
                    $picture->setHabitat($habitat);
                    $this->manager->persist($picture);
                }
            }

            $this->manager->flush();

            $response = new JsonResponse([
                'message' => 'Habitat modifié avec succès',
                'habitat' => [
                    'id' => $habitat->getId(),
                    'name' => $habitat->getName(),
                    'description' => $habitat->getDescription(),
                    'pictures' => $habitat->getHabitatPictures()->map(function($picture) {
                        return [
                            'id' => $picture->getId(),
                            'path' => $picture->getPictureData()
                        ];
                    })->toArray()
                ]
            ]);
            
            $response->headers->set('Access-Control-Allow-Origin', '*');
            return $response;

        } catch (\Exception $e) {
            // Ajouter ces lignes pour plus de détails
            error_log('Erreur dans editHabitat: ' . $e->getMessage());
            error_log('Trace: ' . $e->getTraceAsString());

            $response = new JsonResponse([
                'message' => 'Une erreur est survenue lors de la modification de l\'habitat',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
            
            $response->headers->set('Access-Control-Allow-Origin', '*');
            return $response;
        }
    }

    #[Route('/delete/{id}', name: 'delete_habitat', methods: ['DELETE', 'OPTIONS'])]
    public function deleteHabitat(int $id): JsonResponse
    {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            $response = new JsonResponse(null, 204);
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'DELETE, OPTIONS');
            return $response;
        }

        try {
            $habitat = $this->habitatRepository->find($id);
            
            if (!$habitat) {
                return new JsonResponse([
                    'message' => 'Habitat non trouvé'
                ], Response::HTTP_NOT_FOUND);
            }

            // Vérifier si l'habitat contient des animaux
            if (!$habitat->getAnimals()->isEmpty()) {
                throw new \Exception('Impossible de supprimer cet habitat car il contient des animaux');
            }

            // Supprimer les images associées
            foreach ($habitat->getHabitatPictures() as $picture) {
                // Supprimer le fichier physique
                $imagePath = $this->getParameter('kernel.project_dir') . '/public' . $picture->getPictureData();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
                $this->manager->remove($picture);
            }
            
            $this->manager->remove($habitat);
            $this->manager->flush();
            
            $response = new JsonResponse([
                'message' => 'Habitat supprimé avec succès'
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

    #[Route('/picture/{id}', name: 'get_picture', methods: ['GET'])]
    public function getPicture(int $id): Response
    {
        try {
            $picture = $this->manager->getRepository(HabitatPicture::class)->find($id);
            
            if (!$picture) {
                throw $this->createNotFoundException('Image non trouvée');
            }
            
            $filePath = $this->getParameter('kernel.project_dir') . '/public' . $picture->getPictureData();
            
            if (!file_exists($filePath)) {
                throw $this->createNotFoundException('Fichier image non trouvé');
            }
            
            $fileContent = file_get_contents($filePath);
            $response = new Response($fileContent);
            $mimeType = mime_content_type($filePath);
            
            $response->headers->set('Content-Type', $mimeType);
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Cache-Control', 'public, max-age=3600');
            
            return $response;
            
        } catch (\Exception $e) {
            error_log('Erreur lors de la récupération de l\'image: ' . $e->getMessage());
            throw $this->createNotFoundException('Image non disponible');
        }
    }

    #[Route('/{id}', name: 'get_habitat', methods: ['GET', 'OPTIONS'])]
    public function getHabitat(int $id): JsonResponse
    {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            $response = new JsonResponse(null, 204);
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, OPTIONS');
            return $response;
        }

        try {
            $habitat = $this->habitatRepository->find($id);
            
            if (!$habitat) {
                $response = new JsonResponse([
                    'message' => 'Habitat non trouvé'
                ], Response::HTTP_NOT_FOUND);
                $response->headers->set('Access-Control-Allow-Origin', '*');
                return $response;
            }
            
            $response = new JsonResponse([
                'id' => $habitat->getId(),
                'name' => $habitat->getName(),
                'description' => $habitat->getDescription(),
                'pictures' => $habitat->getHabitatPictures()->map(function($picture) {
                    return [
                        'id' => $picture->getId(),
                        'path' => $picture->getPictureData()
                    ];
                })->toArray()
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