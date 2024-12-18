<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Role;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/', name:'app_api_zoo')]
class UserController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager, private UserRepository $userRepository, private RoleRepository $roleRepository, private LoggerInterface $logger)
    {
        
    }
// ---------------------------------Inscription----------------------------------
   #[Route('/signup', methods: 'POST')]
   public function signUpUser(): Response
   {
        $request = Request::createFromGlobals();
        $roleId = $request->getPayload()->get('role');
        if ($roleId === '1') {
            $roleId = 3;
        } elseif ($roleId === '2') {
            $roleId = 2;
        } else {
            return $this->json(
                ['message' => "Wrong Role"],
                Response::HTTP_FORBIDDEN,
            );
        }

        $user = new User();
        $user->setFirstName(firstName: $request->getPayload()->get('firstName'));
        $user->setName(name: $request->getPayload()->get('lastName'));
        $user->setUsername(username: $request->getPayload()->get('email'));
        $user->setPassword(password: $request->getPayload()->get('password'));

        $role = $this->roleRepository->findOneBy(['id' => $roleId]);
        $role->addUser($user);

        // A stocker en base
        $this->manager->persist($user);
        $this->manager->flush();

        return $this->json(
            ['message' => "User created with {$user->getId()} id"],
            Response::HTTP_CREATED,
        );
   }

   
// --------------------------------Connexion--------------------------------------

   #[Route('/login', methods: 'POST')]
   public function loginUser(): Response
   {
        $request = Request::createFromGlobals();
        $username = $request->getPayload()->get('username');
        $password = $request->getPayload()->get('password');

        $user = $this->userRepository->findOneBy(['username' => $username]);
        $roleId = $user->getRole()->getId();
        
        if($password === $user->getPassword()) {
            return $this->json(
                ['message' => "Connexion successful", 'user' => ['username' => $username, 'roleId' => $roleId]],
                Response::HTTP_ACCEPTED,
            );
        } else {
            return $this->json(
                ['message' => "Connexion denied"],
                Response::HTTP_FORBIDDEN,
            );
        }
   }



   #[Route('/api/user/{id}', name: 'show', methods: 'GET')]
   public function show(int $id): Response
   {
        $user = $this->userRepository->findOneBy(['id' => $id]);
        if (!$user){
            throw $this->createNotFoundException("No User found for {$id} id");
        }

        return $this->json(['message' => 'Utilisateur de ma BDD']);
   }


   #[Route('/api/user/{id}', name: 'edit', methods: 'PUT')]
   public function edit(int $id): Response
   {
        $user = $this->userRepository->findOneBy(['id' => $id]);
        if (!$user){
            throw $this->createNotFoundException("No User found for {$id} id");
        }

        $user->setName('User name updated');
        $this->manager->flush();

        return $this->redirectToRoute('app_api_zoo_show', ['id' => $user->getId()]);
   }


   #[Route('/api/user/{id}', name: 'delete', methods: 'DELETE')]
   public function delete(int $id): Response
   {
    $user = $this->userRepository->findOneBy(['id' => $id]);
    if (!$user){
        throw $this->createNotFoundException("No User found for {$id} id");
    }

    $this->manager->remove($user);
    $this->manager->flush();

    return $this->json(['message' => "User ressource deleted"], Response::HTTP_NO_CONTENT);
   }


   #[Route('/init-roles', methods: 'GET')]
   public function initRoles(): Response
   {
        // Vérifier si les rôles existent déjà
        $existingRoles = $this->roleRepository->findAll();
        if (!empty($existingRoles)) {
        return $this->json(
            ['message' => "Les rôles sont déjà initialisés"],
            Response::HTTP_OK
        );
    }

    // Créer les rôles
    $roles = [
        ['id' => 1, 'label' => 'admin'],
        ['id' => 2, 'label' => 'veto'],
        ['id' => 3, 'label' => 'employee']
    ];

    foreach ($roles as $roleData) {
            $role = new Role();
            // Pas besoin de setId car il est auto-incrémenté
            $role->setLabel($roleData['label']);
            $this->manager->persist($role);
        }

        $this->manager->flush();

        return $this->json(
            ['message' => "Rôles initialisés avec succès"],
            Response::HTTP_CREATED
        );
}
}