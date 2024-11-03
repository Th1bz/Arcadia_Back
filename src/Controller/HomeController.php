<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
   #[Route('/')]
   public function home() : Response
   {
   return new Response(`Bienvenue sur votre accueil du Zoo Arcadia !`) ;
   }
}