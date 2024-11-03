<?php

namespace App\Controller;

use App\Entity\Service;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('api/zoo', name: 'app_api_zoo')]
class ServiceController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager, private serviceRepository $repository)
    {
        
    }

    #[Route(name: 'new', methods: 'POST')]
   public function new(): Response
   {
        $service = new service();
        $service->setName(name: 'Petit train');
        $service->setDescription(description: 'Trop bien ce petit train !');

        // A stocker en base
        $this->manager->persist($service);
        $this->manager->flush();

        return $this->json(
            ['message' => "Service created with {$service->getId()} id"],
            Response::HTTP_CREATED,
        );
   }


   #[Route('/{id}', name: 'show', methods: 'GET')]
   public function show(int $id): Response
   {
        $service = $this->repository->findOneBy(['id' => $id]);
        if (!$service){
            throw $this->createNotFoundException("No service found for {$id} id");
        }

        return $this->json(['message' => 'Service de ma BDD']);
   }


   #[Route('/{id}', name: 'edit', methods: 'PUT')]
   public function edit(int $id): Response
   {
        $service = $this->repository->findOneBy(['id' => $id]);
        if (!$service){
            throw $this->createNotFoundException("No User found for {$id} id");
        }

        $service->setName('Service name updated');
        $service->setDescription('Service description updated');
        $this->manager->flush();

        return $this->redirectToRoute('app_api_zoo_show', ['id' => $service->getId()]);
   }


   #[Route('/{id}', name: 'delete', methods: 'DELETE')]
   public function delete(int $id): Response
   {
    $service = $this->repository->findOneBy(['id' => $id]);
    if (!$service){
        throw $this->createNotFoundException("No Service found for {$id} id");
    }

    $this->manager->remove($service);
    $this->manager->flush();

    return $this->json(['message' => "User ressource deleted"], Response::HTTP_NO_CONTENT);
   }
}
