<?php

namespace App\Controller;

use App\entity\Opinion;
use App\Repository\OpinionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('api/zoo', name: 'app_api_zoo')]
class OpinionController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager, private OpinionRepository $repository)
    {
        
    }

    #[Route(name: 'new', methods: 'POST')]
   public function new(): Response
   {
        $opinion = new Opinion();
        $opinion->setPseudo(pseudo: 'JohnDoe');
        $opinion->setCommentary(commentary: 'Très belle journée au parc');
        // $opinion->setIsVisible(isVisible: true);
        

        // A stocker en base
        $this->manager->persist($opinion);
        $this->manager->flush();

        return $this->json(
            ['message' => "Opinion created with {$opinion->getId()} id"],
            Response::HTTP_CREATED,
        );
   }


   #[Route('/{id}', name: 'show', methods: 'GET')]
   public function show(int $id): Response
   {
        $opinion = $this->repository->findOneBy(['id' => $id]);
        if (!$opinion){
            throw $this->createNotFoundException("No Opinion found for {$id} id");
        }

        return $this->json(['message' => 'Opinion de ma BDD']);
   }


   #[Route('/{id}', name: 'edit', methods: 'PUT')]
   public function edit(int $id): Response
   {
        $opinion = $this->repository->findOneBy(['id' => $id]);
        if (!$opinion){
            throw $this->createNotFoundException("No Opinion found for {$id} id");
        }

        $opinion->setPseudo('Opinion pseudo updated');
        $opinion->setCommentary('Opinion commentary updated');
        $this->manager->flush();

        return $this->redirectToRoute('app_api_zoo_show', ['id' => $opinion->getId()]);
   }


   #[Route('/{id}', name: 'delete', methods: 'DELETE')]
   public function delete(int $id): Response
   {
    $opinion = $this->repository->findOneBy(['id' => $id]);
    if (!$opinion){
        throw $this->createNotFoundException("No Opinion found for {$id} id");
    }

    $this->manager->remove($opinion);
    $this->manager->flush();

    return $this->json(['message' => "Opinion ressource deleted"], Response::HTTP_NO_CONTENT);
   }
}
