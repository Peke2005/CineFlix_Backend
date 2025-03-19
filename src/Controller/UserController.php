<?php

namespace App\Controller;

use App\Entity\Peliculas;
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

    #[Route('/userRegister', name: 'app_user')]
    public function createUser(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        try {
            $entityManager->getRepository(Usuarios::class)->createUser($request->get("name"), $request->get("email"), $request->get("contraseña"), $request->get("rol"), $request->get("fecha_registro"));
            return new JsonResponse("OK", Response::HTTP_CREATED);
        } catch (Exception $e) {
            return new JsonResponse("KO". $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/movieSearchTitle', name: 'app_movie_search_title', methods: ['GET'])]
    public function findByTitle(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $title = $request->query->get('title');

        if (empty($title)) {
            return new JsonResponse(['message' => 'Por favor, proporciona un título para la búsqueda.'], 400);
        }

        $qb = $entityManager->createQueryBuilder();
        $qb->select('p')
           ->from(Peliculas::class, 'p')
           ->where('p.titulo LIKE :title')
           ->setParameter('title', '%' . $title . '%');

        $query = $qb->getQuery();
        $movies = $query->getResult();

        if (!empty($movies)) {
            $result = [];
            foreach ($movies as $movie) {
                $categories = [];
                foreach ($movie->getRelationCategorias() as $category) {
                    $categories[] = $category->getNombreCategoria();
                }

                $result[] = [
                    'title' => $movie->getTitulo(),
                    'duration' => $movie->getDuracion(),
                    'description' => $movie->getDescripcion(),
                    'categories' => $categories,
                ];
            }

            return new JsonResponse(['message' => 'Peliculas encontradas', 'data' => $result]);
        } else {
            return new JsonResponse(['message' => 'No se encontró ninguna película.']);
        }
    }
}
