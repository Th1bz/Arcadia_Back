<?php

namespace App\Controller;

use App\Entity\Service;
use App\Entity\ServicePicture;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Psr\Log\LoggerInterface;


#[Route('/service', name: 'app_service')]
class ServiceController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager, private ServiceRepository $serviceRepository)
    {
    }

    #[Route('/new', name: 'new', methods: ['POST'])]
    public function new(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            // Sanitize Html à la création des données
            $name = htmlspecialchars($data['name'], ENT_QUOTES, 'UTF-8');
            $description = htmlspecialchars($data['description'], ENT_QUOTES, 'UTF-8');

            $service = new Service();
            $service->setName($name);
            $service->setDescription($description);

            // Gestion de l'image
            if (!empty($data['pictureData'])) {
                $imageData = $data['pictureData'];
                
                // Extraire le type MIME et les données
                if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
                    $imageData = substr($imageData, strpos($imageData, ',') + 1);
                    $decodedImage = base64_decode($imageData);
                    
                    // Générer un nom unique
                    $fileName = uniqid() . '.' . $matches[1];
                    $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/services/';
                    
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    // Sauvegarder l'image
                    file_put_contents($uploadDir . $fileName, $decodedImage);
                    
                    // Créer l'entrée dans service_picture
                    $picture = new ServicePicture();
                    $picture->setPictureData('/uploads/services/' . $fileName);
                    $picture->setService($service);
                    
                    $this->manager->persist($picture);
                }
            }

            $this->manager->persist($service);
            $this->manager->flush();

            return new JsonResponse([
                'message' => 'Service créé avec succès',
                'service' => [
                    'id' => $service->getId(),
                    'name' => $service->getName(),
                    'description' => $service->getDescription(),
                    'pictures' => $service->getServicePictures()->map(function($picture) {
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

    #[Route('/list', name: 'list_services', methods: ['GET'])]
    public function listServices(LoggerInterface $logger): JsonResponse
    {
        try {
            $logger->info('Début de la récupération des services');
            
            $services = $this->serviceRepository->findAll();
            $logger->info('Services trouvés:', ['count' => count($services)]);
            
            $data = [];
            foreach ($services as $service) {
                try {
                    $pictures = $service->getServicePictures() ? $service->getServicePictures()->toArray() : [];
                    $logger->info('Images trouvées pour le service ' . $service->getId(), [
                        'count' => count($pictures)
                    ]);
                    
                    $data[] = [
                        'id' => $service->getId(),
                        'name' => $service->getName(),
                        'description' => $service->getDescription(),
                        'pictures' => array_map(function($picture) {
                            return [
                                'id' => $picture->getId(),
                                'path' => $picture->getPictureData()
                            ];
                        }, $pictures)
                    ];
                } catch (\Exception $innerE) {
                    $logger->error('Erreur lors du traitement du service ' . $service->getId(), [
                        'error' => $innerE->getMessage(),
                        'trace' => $innerE->getTraceAsString()
                    ]);
                }
            }

            return new JsonResponse($data, Response::HTTP_OK, [
                'Access-Control-Allow-Origin' => '*'
            ]);

        } catch (\Exception $e) {
            $logger->error('Erreur globale dans listServices', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return new JsonResponse([
                'message' => 'Une erreur est survenue',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR, [
                'Access-Control-Allow-Origin' => '*'
            ]);
        }
    }

    #[Route('/edit/{id}', name: 'edit_service', methods: ['PUT', 'OPTIONS'])]
    public function editService(int $id, Request $request): JsonResponse
    {
        if ($request->getMethod() === 'OPTIONS') {
            $response = new JsonResponse(null, 204);
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'PUT, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type');
            return $response;
        }

        try {
            $service = $this->serviceRepository->find($id);
            if (!$service) {
                throw new \Exception('Service non trouvé');
            }

            $data = json_decode($request->getContent(), true);

            // Mise à jour des champs basiques
            if (isset($data['name'])) {
                $existingService = $this->serviceRepository->findOneBy(['name' => $data['name']]);
                if ($existingService && $existingService->getId() !== $id) {
                    throw new \Exception('Un service avec ce nom existe déjà');
                }
                $name = htmlspecialchars($data['name'], ENT_QUOTES, 'UTF-8');
                $service->setName($name);
            }
            
            if (isset($data['description'])) {
                $description = htmlspecialchars($data['description'], ENT_QUOTES, 'UTF-8');
                $service->setDescription($description);
            }

            // Gestion de la nouvelle image
            if (!empty($data['pictureData'])) {
                // Supprimer l'ancienne image si elle existe
                $oldPictures = $service->getServicePictures();
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
                    $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/services/';
                    
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    file_put_contents($uploadDir . $fileName, $decodedImage);

                    $picture = new ServicePicture();
                    $picture->setPictureData('/uploads/services/' . $fileName);
                    $picture->setService($service);
                    $this->manager->persist($picture);
                }
            }

            $this->manager->flush();

            $response = new JsonResponse([
                'message' => 'Service modifié avec succès',
                'service' => [
                    'id' => $service->getId(),
                    'name' => $service->getName(),
                    'description' => $service->getDescription(),
                    'pictures' => $service->getServicePictures()->map(function($picture) {
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
            error_log('Erreur dans editService: ' . $e->getMessage());
            error_log('Trace: ' . $e->getTraceAsString());

            $response = new JsonResponse([
                'message' => 'Une erreur est survenue lors de la modification du service',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
            
            $response->headers->set('Access-Control-Allow-Origin', '*');
            return $response;
        }
    }

    #[Route('/delete/{id}', name: 'delete_service', methods: ['DELETE', 'OPTIONS'])]
    public function deleteService(int $id): JsonResponse
    {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            $response = new JsonResponse(null, 204);
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'DELETE, OPTIONS');
            return $response;
        }

        try {
            $service = $this->serviceRepository->find($id);
            
            if (!$service) {
                return new JsonResponse([
                    'message' => 'Service non trouvé'
                ], Response::HTTP_NOT_FOUND);
            }

            // Supprimer les images associées
            foreach ($service->getServicePictures() as $picture) {
                // Supprimer le fichier physique
                $imagePath = $this->getParameter('kernel.project_dir') . '/public' . $picture->getPictureData();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
                $this->manager->remove($picture);
            }
            
            $this->manager->remove($service);
            $this->manager->flush();
            
            $response = new JsonResponse([
                'message' => 'Service supprimé avec succès'
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
            $picture = $this->manager->getRepository(ServicePicture::class)->find($id);
            
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

    #[Route('/{id}', name: 'get_service', methods: ['GET'])]
    public function getService(int $id): JsonResponse
    {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            $response = new JsonResponse(null, 204);
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, OPTIONS');
            return $response;
        }
        
        try {
            $service = $this->serviceRepository->find($id);

            if (!$service) {
                $response = new JsonResponse([
                    'message' => 'Service non trouvé'
                ], Response::HTTP_NOT_FOUND);
                $response->headers->set('Access-Control-Allow-Origin', '*');
                return $response;
            }

            $response = new JsonResponse([
                'id' => $service->getId(),
                'name' => $service->getName(),
                'description' => $service->getDescription(),
                'pictures' => $service->getServicePictures()->map(function($picture) {
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