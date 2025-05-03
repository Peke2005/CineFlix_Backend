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
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Exception;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


final class UserController extends AbstractController
{

    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }


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
            $userRepository = $entityManager->getRepository(Usuarios::class);

            $rutaImagen = 'assets/img/usuario.png';

            $imagenContent = file_get_contents($rutaImagen);

            if ($imagenContent === false) {
                throw new Exception('No se pudo leer la imagen desde la ruta especificada.');
            }

            $userRepository->createUser(
                $usuario["nombre"],
                $usuario["email"],
                $usuario["pass"],
                $imagenContent
            );

            return new JsonResponse(
                ["logError" => "Te has registrado correctamente!"],
                Response::HTTP_CREATED
            );
        } catch (UniqueConstraintViolationException $e) {
            $errorMessage = 'Este correo electrónico ya está registrado.';
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
        }

        return new JsonResponse(
            ["status" => false, "id" => null, "logError" => $errorMessage],
            Response::HTTP_NOT_FOUND
        );
    }

    #[Route('/userLogin', name: 'login_user', methods: ['POST'])]
    public function login(EntityManagerInterface $entityManager, Request $request)
    {
        try {
            $userData = $request->toArray();
            $userRepository = $entityManager->getRepository(Usuarios::class);
            $userFound = $userRepository->findOneBy(["email" => $userData["email"]]);
            if ($userFound) {
                if ($userRepository->verifyPassword($userData['pass'], $userFound->getContraseña())) {
                    $id = $userFound->getIdUsuario();
                    $rol = $userFound->getRol();
                    return new JsonResponse(["status" => true, "rol" => $rol, "id" => $id, "logError" => "Has iniciado sesion correctamente!"], Response::HTTP_OK);
                } else {
                    throw new Exception(message: "Los datos introducidos no coinciden con ningun usuario existente.");
                }
            } else {
                throw new Exception(message: "Los datos introducidos no coinciden con ningun usuario existente.");
            }
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
                $actors = [];

                foreach ($movie->getRelationCategorias() as $category) {
                    $categories[] = $category->getNombreCategoria();
                }

                foreach ($movie->getActores() as $actor) {
                    $actors[] = [
                        'name' => $actor->getNombre(),
                        'birthdate' => $actor->getFechaNacimiento(),
                        'nationality' => $actor->getNacionalidad(),
                        'foto' => $actor->getFoto()
                    ];
                }

                $result[] = [
                    'title' => $movie->getTitulo(),
                    'duration' => $movie->getDuracion(),
                    'year' => $movie->getAño(),
                    'description' => $movie->getDescripcion(),
                    'categories' => $categories,
                    'trailer' => $movie->getTrailer(),
                    'imageUrl' => $movie->getPortada(),
                    'actors' => $actors
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

        $foto = $usuario->getFotoPerfil();
        $userData = [
            'id' => $usuario->getIdUsuario(),
            'nombre' => $usuario->getNombre(),
            'email' => $usuario->getEmail(),
            'contraseña' => $usuario->getContraseña(),
            'imagen' => $foto ? base64_encode(stream_get_contents($foto)) : null,
        ];

        return new JsonResponse(['message' => 'Usuario encontrado', 'data' => $userData], 200);
    }

    #[Route('/updateUser', name: 'app_user_update', methods: ['PUT'])]
    public function updateUser(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $id = $data['id'] ?? null;
        $email = $data['email'] ?? null;
        $contraseña = $data['contraseña'] ?? null;

        if (!$id || !$email || !$contraseña) {
            return new JsonResponse(['message' => 'Faltan datos requeridos.'], 400);
        }

        $usuario = $entityManager->getRepository(Usuarios::class)->find($id);

        if (!$usuario) {
            return new JsonResponse(['message' => 'Usuario no encontrado.'], 404);
        }

        $usuario->setEmail($email);
        $hashedPassword = $passwordHasher->hashPassword($usuario, $contraseña);
        $usuario->setContraseña($hashedPassword);

        $entityManager->persist($usuario);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Datos del usuario actualizados correctamente.']);
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
            foreach ($film->getActores() as $actor) {
                $actors[] = [
                    'name' => $actor->getNombre(),
                    'birthdate' => $actor->getFechaNacimiento(),
                    'nationality' => $actor->getNacionalidad(),
                    'foto' => $actor->getFoto()
                ];
            }

            $result[] = [
                'title' => $film->getTitulo(),
                'duration' => $film->getDuracion(),
                'year' => $film->getAño(),
                'description' => $film->getDescripcion(),
                'categories' => $categories,
                'trailer' => $film->getTrailer(),
                'imageUrl' => $film->getPortada(),
                'actors' => $actors
            ];
        }
        return new JsonResponse(['message' => 'Todas las películas', 'data' => $result]);
    }



    #[Route('/createFilm', name: 'create_pelicula', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $pelicula = new Peliculas();
        $pelicula->setTitulo($data['titulo']);
        $pelicula->setDescripcion($data['descripcion']);
        $pelicula->setAño($data['año']);
        $pelicula->setDuracion((int)$data['duracion']);
        $pelicula->setPortada($data['portada']);
        $pelicula->setTrailer($data['trailer']);
        $em->persist($pelicula);
        $em->flush();

        return $this->json(['message' => 'Película creada'], Response::HTTP_CREATED);
    }

    #[Route('/updateFilm/{id}', name: 'update_pelicula', methods: ['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $pelicula = $em->getRepository(Peliculas::class)->find($id);

        if (!$pelicula) {
            return $this->json(['message' => 'Película no encontrada'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        $pelicula->setTitulo($data['titulo']);
        $pelicula->setDescripcion($data['descripcion']);
        $pelicula->setAño($data['año']);
        $pelicula->setDuracion((int)$data['duracion']);
        $pelicula->setPortada($data['portada']);
        $pelicula->setTrailer($data['trailer']);

        $em->flush();

        return $this->json(['message' => 'Película actualizada']);
    }

    #[Route('/deleteFilm/{id}', name: 'delete_pelicula', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $em): JsonResponse
    {
        $pelicula = $em->getRepository(Peliculas::class)->find($id);

        if (!$pelicula) {
            return $this->json(['message' => 'Película no encontrada'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($pelicula);
        $em->flush();

        return $this->json(['message' => 'Película eliminada']);
    }
    #[Route('/uploadImage', name: 'upload_image', methods: ['POST'])]
    public function uploadImage(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $id = $request->request->get('id');
        $file = $request->files->get('imagen');

        if (!$file || !$id) {
            return new JsonResponse(['message' => 'Faltan datos.'], 400);
        }

        $allowedMimeTypes = ['image/jpeg', 'image/png'];
        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            return new JsonResponse(['message' => 'Solo se permiten imágenes JPG o PNG.'], 400);
        }

        $usuario = $entityManager->getRepository(Usuarios::class)->find($id);
        if (!$usuario) {
            return new JsonResponse(['message' => 'Usuario no encontrado.'], 404);
        }

        $stream = fopen($file->getPathname(), 'rb');
        $contenido = stream_get_contents($stream);
        fclose($stream);

        $usuario->setFotoPerfil($contenido); // ❗ SIN base64_encode()

        $entityManager->persist($usuario);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Imagen actualizada correctamente.'], 200);
    }
}
