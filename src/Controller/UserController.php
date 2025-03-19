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

    /* {
    "nombre": "Juan Perez",
    "email": "juan.perez@example.com",
    "pass": "miContraseña123"
} */
    #[Route('/userRegister', name: 'register_user', methods: ['POST'])]
    public function createUser(EntityManagerInterface $entityManager, Request $request)
    {
        try {
            $usuario = $request->toArray();
            $entityManager->getRepository(Usuarios::class)->createUser($usuario["nombre"], $usuario["email"], $usuario["pass"]);
            return new JsonResponse("Usuario Insertado Correctamente!", Response::HTTP_CREATED);
        } catch (Exception $e) {
            return new JsonResponse(["status" => false, "id" => null, "logError" => $e->getMessage()], Response::HTTP_NOT_FOUND);

        }
    }

    #[Route('/userLogin', name: 'login_user', methods: ['POST'])]
    public function loginUser(EntityManagerInterface $entityManager, Request $request)
    {
        try {
            $userData = $request->toArray();

            $userFound = $entityManager->getRepository(Usuarios::class)->findOneBy(["email" => $userData["email"], "contraseña" => $userData["pass"]]);
            
            if ($userFound) {
                $id = $userFound->getIdUsuario();
                dd($userFound->getIdUsuario());
                return new JsonResponse(["status" => true, "id" => $id, "logError" => null], Response::HTTP_OK);
            }

            throw new Exception("Los datos introducidos no coinciden con ningun usuario existente.");
        } catch (Exception $e) {
            return new JsonResponse(["status" => false, "id" => null, "logError" => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    #[Route('/deleteUser', name: 'login_user', methods: ['DELETE'])]

    public function deleteUser(EntityManagerInterface $entityManager, Request $request)
    {

        try {
            $userData = $request->toArray();
            $userFound = $entityManager->getRepository(Usuarios::class)->findOneBy(["id" => $userData["id"]]);
            if ($userFound) {
                $id = $userFound->getIdUsuario();
                return new JsonResponse("Se ha borrado el usuario correctamente!", Response::HTTP_CREATED);
            }

            throw new Exception("Los datos introducidos no coinciden con ningun usuario existente.");
        } catch (Exception $e) {

            return new JsonResponse("KO" . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
}
