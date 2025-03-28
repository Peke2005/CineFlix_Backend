<?php

namespace App\Controller;

use App\Entity\Categorias;
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

    /* {
    "nombre": "Pol Perez",
    "email": "pol.perez@example.com",
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
    public function login(EntityManagerInterface $entityManager, Request $request)
    {
        try {
            $userData = $request->toArray();

            $userFound = $entityManager->getRepository(Usuarios::class)->findOneBy(["email" => $userData["email"], "contraseña" => $userData["pass"]]);

            if ($userFound) {
                $id = $userFound->getIdUsuario();
                return new JsonResponse(["status" => true, "id" => $id, "logError" => "Usuario Encontrado!"], Response::HTTP_OK);
            }

            throw new Exception("Los datos introducidos no coinciden con ningun usuario existente.");
        } catch (Exception $e) {
            return new JsonResponse(["status" => false, "id" => null, "logError" => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    #[Route('/deleteUser', name: 'delete_user', methods: ['DELETE'])]

    public function deleteUser(EntityManagerInterface $entityManager, Request $request)
    {

        try {
            $userData = $request->toArray();
            $userFound = $entityManager->getRepository(Usuarios::class)->findOneBy(["id_usuario" => $userData["id"]]);
            if ($userFound) {
                $entityManager->remove($userFound);

                $entityManager->flush();
                return new JsonResponse("Se ha borrado el usuario correctamente!", Response::HTTP_CREATED);
            }

            throw new Exception("Los datos introducidos no coinciden con ningun usuario existente.");
        } catch (Exception $e) {

            return new JsonResponse("KO" . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/movieSearchTitle', name: 'app_movie_search_title', methods: ['GET'])]
    public function findByTitle(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $title = $request->query->get('title');

        if (empty($title)) {
            return new JsonResponse(['message' => 'Por favor, proporciona un título para la busqueda.'], 400);
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
                    'year' => $movie->getAño(),
                    'description' => $movie->getDescripcion(),
                    'categories' => $categories,
                    'imageUrl' => $movie->getPortada(),
                ];
            }

            return new JsonResponse(['message' => 'Peliculas encontradas', 'data' => $result]);
        } else {
            return new JsonResponse(['message' => 'No se encontro ninguna pelicula.']);
        }
    }

    #[Route('/movieSearchCategory', name: 'app_movie_search_category', methods: ['GET'])]
    public function findByCategory(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $categoryName = $request->query->get('category');

        if (empty($categoryName)) {
            return new JsonResponse(['message' => 'Por favor, proporciona una categoría para la búsqueda.'], 400);
        }

        $category = $entityManager->getRepository(Categorias::class)
            ->findOneBy(['nombre_categoria' => $categoryName]);

        if (!$category) {
            return new JsonResponse(['message' => 'No se encontro la categoria especificada.'], 404);
        }

        $categoryId = $category->getIdCategoria();

        $qb = $entityManager->createQueryBuilder();
        $qb->select('p')
            ->from(Peliculas::class, 'p')
            ->join('p.relationCategorias', 'c')
            ->where('c.id_categoria = :categoryId')
            ->setParameter('categoryId', $categoryId);

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
                    'year' => $movie->getAño(),
                    'description' => $movie->getDescripcion(),
                    'categories' => $categories,
                    'imageUrl' => $movie->getPortada(),
                ];
            }

            return new JsonResponse(['message' => 'Peliculas encontradas', 'data' => $result]);
        } else {
            return new JsonResponse(['message' => 'No se encontro ninguna pelicula en esa categoria.']);
        }
    }
    #[Route('/userSearchById', name: 'app_user_search_by_id', methods: ['GET'])]
    public function findUserById(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $usuarioId = $request->query->get('id');

        if (empty($usuarioId) || !is_numeric($usuarioId)) {
            return new JsonResponse(['message' => 'Por favor, proporciona un ID de usuario valido.'], 400);
        }

        $usuario = $entityManager->getRepository(Usuarios::class)->find($usuarioId);

        if (!$usuario) {
            return new JsonResponse(['message' => 'No se encontro el usuario especificado.'], 404);
        }

        // Devolver información del usuario
        $userData = [
            'id' => $usuario->getIdUsuario(),
            'nombre' => $usuario->getNombre(),
            'email' => $usuario->getEmail(),
            'contrnseña' => $usuario->getContraseña(),
        ];

        return new JsonResponse(['message' => 'Usuario encontrado', 'data' => $userData], 200);
    }

    #[Route('/listFilms', name: 'app_movies', methods: ['GET'])]
    public function listAllFilms(EntityManagerInterface $entityManager): JsonResponse
    {

        $films = $entityManager->getRepository(Peliculas::class)->findAll();
        $result = [];
        foreach ($films as $film) {
            $categories = [];
            foreach ($film->getRelationCategorias() as $category) {
                $categories[] = $category->getNombreCategoria();
            }
            $result[] = [
                'title' => $film->getTitulo(),
                'duration' => $film->getDuracion(),
                'year' => $film->getAño(),
                'description' => $film->getDescripcion(),
                'categories' => $categories,
                'imageUrl' => $film-> getPortada(),
            ];
        }
        return new JsonResponse(['message' => 'Todas las películas', 'data' => $result]);
    }
}
