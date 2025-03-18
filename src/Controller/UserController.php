<?php

namespace App\Controller;

use App\Entity\Usuarios;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Exception;


final class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/userRegister', name: 'app_user', methods: ['POST'])]
    public function createUser(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        try {
            $usuario = $request->toArray();
            $entityManager->getRepository(Usuarios::class)->createUser($usuario["nombre"], $usuario["email"], $usuario["pass"]);
            return new JsonResponse("Usuario insertados correctamente!", Response::HTTP_CREATED);
        } catch (Exception $e) {
            return new JsonResponse("KO" . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
}
