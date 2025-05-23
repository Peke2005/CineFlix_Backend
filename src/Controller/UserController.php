<?php

namespace App\Controller;

use App\Entity\Categorias;
use App\Entity\Peliculas;
use App\Entity\Actores;
use App\Entity\Comentarios;
use App\Entity\Historiales;
use App\Entity\Respuestas;
use App\Entity\Usuarios;
use App\Repository\HistorialesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Exception;
use App\Entity\ComentarioReacciones;
use App\Entity\RespuestaReacciones;
use App\Entity\Valoraciones;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Repository\ValoracionesRepository;
use App\Repository\UsuariosRepository;
use App\Repository\PeliculasRepository;


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
                    $name = $userFound->getNombre();
                    $foto = $userFound->getFotoPerfil();
                    $image_perfil = base64_encode(stream_get_contents($foto));
                    return new JsonResponse(["status" => true, "rol" => $rol, "id" => $id, 'name' => $name, 'foto_perfil' =>  $image_perfil, "logError" => "Has iniciado sesion correctamente!"], Response::HTTP_OK);
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

    #[Route('/deleteUser', name: 'deleteUser', methods: ['DELETE'])]
    public function deleteUser(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        try {
            $id = $request->query->get('id');

            if (!$id) {
                throw new Exception("No se proporcionó el ID del usuario.");
            }

            $userFound = $entityManager->getRepository(Usuarios::class)->find($id);

            if (!$userFound) {
                throw new Exception("El usuario con ID {$id} no existe.");
            }

            $entityManager->remove($userFound);
            $entityManager->flush();

            return new JsonResponse("Se ha borrado el usuario correctamente!", Response::HTTP_OK);
        } catch (Exception $e) {
            return new JsonResponse("KO: " . $e->getMessage(), Response::HTTP_BAD_REQUEST);
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
        $qb->select('p, cat, a')
            ->from(Peliculas::class, 'p')
            ->leftJoin('p.relationCategorias', 'cat')
            ->leftJoin('p.actores', 'a')
            ->where('p.titulo LIKE :title')
            ->setParameter('title', '%' . $title . '%');

        $movies = $qb->getQuery()->getResult();

        if (!empty($movies)) {
            $result = [];
            foreach ($movies as $movie) {
                $categories = [];
                $actors = [];
                $comentarios = [];

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
                    'id_pelicula' => $movie->getIdPelicula(),
                    'title' => $movie->getTitulo(),
                    'duration' => $movie->getDuracion(),
                    'year' => $movie->getAño(),
                    'description' => $movie->getDescripcion(),
                    'categories' => $categories,
                    'trailer' => $movie->getTrailer(),
                    'imageUrl' => $movie->getPortada(),
                    'actors' => $actors,
                ];
            }

            return new JsonResponse(['message' => 'Películas encontradas', 'data' => $result]);
        }

        return new JsonResponse(['message' => 'No se encontró ninguna película.'], 404);
    }

    #[Route('/comments', name: 'app_movie_comments', methods: ['GET'])]
    public function getComments(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $id = $request->query->get('idFilm');
        $userId = $request->query->get('idUser');

        $movie = $entityManager->getRepository(Peliculas::class)->find($id);

        if (!$movie) {
            return new JsonResponse(['message' => 'Película no encontrada.'], 404);
        }

        $qb = $entityManager->createQueryBuilder();
        $qb->select('c, r, u')
            ->from(Comentarios::class, 'c')
            ->leftJoin('c.relacionRespuestas', 'r')
            ->leftJoin('c.usuario', 'u')
            ->where('c.pelicula = :movie')
            ->setParameter('movie', $movie)
            ->orderBy('c.fechaCreacion', 'DESC');

        $comments = $qb->getQuery()->getResult();

        $result = [];
        foreach ($comments as $comment) {
            $entityManager->refresh($comment->getUsuario());

            $likes = $entityManager->getRepository(ComentarioReacciones::class)->count([
                'comentario' => $comment,
                'tipo' => 'like',
            ]);

            $dislikes = $entityManager->getRepository(ComentarioReacciones::class)->count([
                'comentario' => $comment,
                'tipo' => 'dislike',
            ]);

            $userReaction = null;
            if ($userId) {
                $user = $entityManager->getRepository(Usuarios::class)->find($userId);
                $reaccion = $entityManager->getRepository(ComentarioReacciones::class)->findOneBy([
                    'comentario' => $comment,
                    'usuario' => $user,
                ]);
                if ($reaccion) {
                    $userReaction = $reaccion->getTipo();
                }
            }

            $commentData = $comment->toArray(false);
            $commentData['likes'] = $likes;
            $commentData['dislikes'] = $dislikes;
            $commentData['userReaction'] = $userReaction;

            $respuestasFinal = [];
            foreach ($comment->getRelacionRespuestas() as $respuesta) {
                $entityManager->refresh($respuesta->getUsuario());

                $likesRes = $entityManager->getRepository(RespuestaReacciones::class)->count([
                    'respuesta' => $respuesta,
                    'tipo' => 'like',
                ]);

                $dislikesRes = $entityManager->getRepository(RespuestaReacciones::class)->count([
                    'respuesta' => $respuesta,
                    'tipo' => 'dislike',
                ]);

                $userReactionRes = null;
                if ($userId) {
                    $user = $entityManager->getRepository(Usuarios::class)->find($userId);
                    $reaccionRes = $entityManager->getRepository(RespuestaReacciones::class)->findOneBy([
                        'respuesta' => $respuesta,
                        'usuario' => $user,
                    ]);
                    if ($reaccionRes) {
                        $userReactionRes = $reaccionRes->getTipo();
                    }
                }

                $resData = $respuesta->toArray();
                $resData['likes'] = $likesRes;
                $resData['dislikes'] = $dislikesRes;
                $resData['userReaction'] = $userReactionRes;

                $respuestasFinal[] = $resData;
            }

            $commentData['respuestas'] = $respuestasFinal;

            $result[] = $commentData;
        }

        return new JsonResponse([
            'message' => 'Comentarios encontrados',
            'movieId' => $id,
            'data' => $result
        ]);
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
                    'id_pelicula' => $movie->getIdPelicula(),
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
        $actors = [];
            $categories = [];
            foreach ($film->getRelationCategorias() as $category) {
                $categories[] = $category->getNombreCategoria();
            }
            foreach ($film->getActores() as $actor) {
                $actors[] = [
                    'id_actor' => $actor->getIdActor(),
                    'name' => $actor->getNombre(),
                    'birthdate' => $actor->getFechaNacimiento(),
                    'nationality' => $actor->getNacionalidad(),
                    'foto' => $actor->getFoto()
                ];
            }

            $result[] = [
                'id_pelicula' => $film->getIdPelicula(),
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

    #[Route('/historial', name: 'api_historial_usuario', methods: ['GET'])]
    public function getHistorialUsuario(
        Request $request,
        EntityManagerInterface $em,
        HistorialesRepository $historialesRepository
    ): JsonResponse {
        $usuarioId = $request->query->get('id');
        $usuario = $em->getRepository(Usuarios::class)->find($usuarioId);
        if (!$usuario) {
            return new JsonResponse(['error' => 'Usuario no encontrado'], 404);
        }


        $historiales = $historialesRepository->findBy(
            ['usuario' => $usuario],
            ['fechaVista' => 'DESC'],
        );

        $total = $historialesRepository->count(['usuario' => $usuario]);

        $peliculas = [];
        foreach ($historiales as $historial) {
            $pelicula = $historial->getPelicula();
            $categories = [];
            foreach ($pelicula->getRelationCategorias() as $category) {
                $categories[] = $category->getNombreCategoria();
            }

            $peliculas[] = [
                'id_pelicula' => $pelicula->getIdPelicula(),
                'titulo' => $pelicula->getTitulo(),
                'descripcion' => $pelicula->getDescripcion(),
                'categories' => $categories,
                'año' => $pelicula->getAño(),
                'duracion' => $pelicula->getDuracion(),
                'portada' => $pelicula->getPortada(),
                'fecha_vista' => $historial->getFechaVista()->format('Y-m-d H:i:s'),
            ];
        }

        return new JsonResponse([
            'peliculas' => $peliculas,
        ], 200);
    }

    #[Route('/historial', name: 'api_historial_add', methods: ['POST'])]
    public function addHistorial(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?? [];
        $usuarioId = $data['usuarioId'] ?? $request->query->get('usuarioId');
        $peliculaId = $data['peliculaId'] ?? $request->query->get('peliculaId');
        $fechaVista = $data['fechaVista'] ?? null;

        if (!$usuarioId || !$peliculaId) {
            return new JsonResponse(['error' => 'Faltan usuarioId o peliculaId'], 400);
        }

        $usuario = $em->getRepository(Usuarios::class)->find($usuarioId);
        if (!$usuario) {
            return new JsonResponse(['error' => 'Usuario no encontrado'], 404);
        }

        $pelicula = $em->getRepository(Peliculas::class)->find($peliculaId);
        if (!$pelicula) {
            return new JsonResponse(['error' => 'Película no encontrada'], 404);
        }

        try {
            $fechaVistaDate = $fechaVista ? new \DateTime($fechaVista) : new \DateTime();
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Formato de fechaVista inválido. Usa formato Y-m-d H:i:s'], 400);
        }

        $existingHistorial = $em->getRepository(Historiales::class)->findOneBy([
            'usuario' => $usuario,
            'pelicula' => $pelicula,
        ]);

        if ($existingHistorial) {
            return new JsonResponse(['message' => 'La película ya está en el historial del usuario'], 200);
        }

        $historial = new Historiales();
        $historial->setUsuario($usuario);
        $historial->setPelicula($pelicula);
        $historial->setFechaVista($fechaVistaDate);

        $em->persist($historial);
        $em->flush();

        return new JsonResponse([
            'message' => 'Película añadida al historial correctamente',
        ], 201);
    }

    #[Route('/actores', name: 'get_actores', methods: ['GET'])]
    public function getActores(EntityManagerInterface $em): JsonResponse
    {
        $actores = $em->getRepository(Actores::class)->findAll();
        $result = [];

        foreach ($actores as $actor) {
            $result[] = [
                'id_actor' => $actor->getIdActor(),
                'name' => $actor->getNombre(),
                'birthdate' => $actor->getFechaNacimiento() ? $actor->getFechaNacimiento()->format('Y-m-d') : null,
                'nationality' => $actor->getNacionalidad(),
                'photo' => $actor->getFoto()
            ];
        }

        return $this->json(['message' => 'Lista de actores', 'data' => $result]);
    }


    #[Route('/createFilm', name: 'create_pelicula', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $pelicula = new Peliculas();
        $pelicula->setTitulo($data['title']);
        $pelicula->setDescripcion($data['description']);
        $pelicula->setAño($data['year']);
        $pelicula->setDuracion((int) $data['duration']);
        $pelicula->setPortada($data['imageUrl']);
        $pelicula->setTrailer($data['trailer']);

        if (!empty($data['actors'])) {
            foreach ($data['actors'] as $actorId) {
                $actor = $em->getRepository(Actores::class)->find($actorId);
                if ($actor) {
                    $pelicula->addActor($actor);
                }
            }
        }

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

        $pelicula->setTitulo($data['title']);
        $pelicula->setDescripcion($data['description']);
        $pelicula->setAño($data['year']);
        $pelicula->setDuracion((int) $data['duration']);
        $pelicula->setPortada($data['imageUrl']);
        $pelicula->setTrailer($data['trailer']);

        $pelicula->getActores()->clear();
        if (!empty($data['actors'])) {
            foreach ($data['actors'] as $actorId) {
                $actor = $em->getRepository(Actores::class)->find($actorId);
                if ($actor) {
                    $pelicula->addActor($actor);
                }
            }
        }

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

        $usuario->setFotoPerfil($contenido);

        $entityManager->persist($usuario);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Imagen actualizada correctamente.', 'foto_perfil' => base64_encode($contenido)], 200);
    }

    #[Route('/uploadComentario', name: 'upload_comentario', methods: ['POST'])]
    public function uploadComentario(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $userId = $data['userId'] ?? null;
            $movieId = $data['movieId'] ?? null;
            $commentMessage = $data['commentMessage'] ?? null;

            if (!$userId || !$movieId || !$commentMessage) {
                return new JsonResponse(['message' => 'Faltan datos requeridos.'], Response::HTTP_BAD_REQUEST);
            }

            $user = $entityManager->getRepository(Usuarios::class)->find($userId);
            if (!$user) {
                return new JsonResponse(['message' => 'Usuario no encontrado.'], Response::HTTP_NOT_FOUND);
            }
            if ($user) {
                $entityManager->refresh($user);
            }

            $movie = $entityManager->getRepository(Peliculas::class)->find($movieId);
            if (!$movie) {
                return new JsonResponse(['message' => 'Película no encontrada.'], Response::HTTP_NOT_FOUND);
            }

            $commentRepository = $entityManager->getRepository(Comentarios::class);

            $commentRepository->addComment($commentMessage, $movie, $user);

            return new JsonResponse(['message' => 'Comentario añadido con éxito'], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return new JsonResponse(['message' => 'Error al agregar el comentario: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/uploadCommentResponse', name: 'upload_comment_response', methods: ['POST'])]
    public function uploadCommentResponse(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $userId = $data['userId'] ?? null;
            $commentId = $data['commentId'] ?? null;
            $responseMessage = $data['responseMessage'] ?? null;

            if (!$userId || !$commentId || !$responseMessage) {
                return new JsonResponse(['message' => 'Faltan datos requeridos.'], Response::HTTP_BAD_REQUEST);
            }


            $user = $entityManager->getRepository(Usuarios::class)->find($userId);
            if (!$user) {
                return new JsonResponse(['message' => 'Usuario no encontrado.'], Response::HTTP_NOT_FOUND);
            }
            if ($user) {
                $entityManager->refresh($user);
            }

            $comment = $entityManager->getRepository(Comentarios::class)->find($commentId);
            if (!$comment) {
                return new JsonResponse(['message' => 'Comentario no encontrado.'], Response::HTTP_NOT_FOUND);
            }

            $commentRepository = $entityManager->getRepository(Respuestas::class);

            $commentRepository->addResponse($responseMessage, $comment, $user);

            return new JsonResponse(['message' => 'Respuesta añadida con éxito'], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return new JsonResponse(['message' => 'Error al agregar la respuesta: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/comentario/reaccion', name: 'comentario_reaccion', methods: ['POST'])]
    public function reaccionComentario(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $comentarioId = $request->request->get('comentario_id');
        $usuarioId = $request->request->get('usuario_id');
        $tipo = $request->request->get('tipo');

        if (!$comentarioId || !$usuarioId || !$tipo) {
            return new JsonResponse(['error' => 'Faltan parámetros'], 400);
        }

        if (!in_array($tipo, ['like', 'dislike'], true)) {
            return new JsonResponse(['error' => 'Tipo inválido'], 400);
        }

        $comentario = $em->getRepository(Comentarios::class)->find($comentarioId);
        $usuario = $em->getRepository(Usuarios::class)->find($usuarioId);

        if (!$comentario || !$usuario) {
            return new JsonResponse(['error' => 'Comentario o usuario no encontrado'], 404);
        }

        $repo = $em->getRepository(ComentarioReacciones::class);
        $reaccion = $repo->findOneBy(['comentario' => $comentario, 'usuario' => $usuario]);

        if ($reaccion) {
            if ($reaccion->getTipo() === $tipo) {
                $em->remove($reaccion);
            } else {
                $reaccion->setTipo($tipo);
            }
        } else {
            $reaccion = new ComentarioReacciones();
            $reaccion->setComentario($comentario);
            $reaccion->setUsuario($usuario);
            $reaccion->setTipo($tipo);
            $em->persist($reaccion);
        }

        $em->flush();

        $likes = $repo->count(['comentario' => $comentario, 'tipo' => 'like']);
        $dislikes = $repo->count(['comentario' => $comentario, 'tipo' => 'dislike']);

        return new JsonResponse([
            'likes' => $likes,
            'dislikes' => $dislikes,
            'status' => 'updated'
        ]);
    }

    #[Route('/respuesta/reaccion', name: 'respuesta_reaccion', methods: ['POST'])]
    public function reaccionarRespuesta(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $respuestaId = $request->request->get('respuesta_id');
        $usuarioId = $request->request->get('usuario_id');
        $tipo = $request->request->get('tipo');

        if (!in_array($tipo, ['like', 'dislike'])) {
            return new JsonResponse(['message' => 'Tipo inválido.'], 400);
        }

        $respuesta = $em->getRepository(Respuestas::class)->find($respuestaId);
        $usuario = $em->getRepository(Usuarios::class)->find($usuarioId);

        if (!$respuesta || !$usuario) {
            return new JsonResponse(['message' => 'Respuesta o usuario no encontrado.'], 404);
        }

        $repo = $em->getRepository(RespuestaReacciones::class);
        $reaccionExistente = $repo->findOneBy(['respuesta' => $respuesta, 'usuario' => $usuario]);

        if ($reaccionExistente) {
            if ($reaccionExistente->getTipo() === $tipo) {
                $em->remove($reaccionExistente);
            } else {
                $reaccionExistente->setTipo($tipo);
            }
        } else {
            $nueva = new RespuestaReacciones();
            $nueva->setRespuesta($respuesta);
            $nueva->setUsuario($usuario);
            $nueva->setTipo($tipo);
            $em->persist($nueva);
        }

        $em->flush();

        $likes = $repo->count(['respuesta' => $respuesta, 'tipo' => 'like']);
        $dislikes = $repo->count(['respuesta' => $respuesta, 'tipo' => 'dislike']);

        $userReaction = null;
        $reaccionFinal = $repo->findOneBy(['respuesta' => $respuesta, 'usuario' => $usuario]);
        if ($reaccionFinal) {
            $userReaction = $reaccionFinal->getTipo();
        }

        return new JsonResponse([
            'likes' => $likes,
            'dislikes' => $dislikes,
            'userReaction' => $userReaction,
        ]);
    }

    #[Route('/rateMovie', name: 'rate_movie', methods: ['POST'])]
    public function rateMovie(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $userId = $request->request->get('userId');  
        $movieId = $request->request->get('movieId');
        $valor   = $request->request->get('valor');  

        if (!$userId || !$movieId || !$valor) {
            return new JsonResponse(['error' => 'Faltan datos.'], 400);
        }

        $usuario = $entityManager->getRepository(Usuarios::class)->find($userId);
        $pelicula = $entityManager->getRepository(Peliculas::class)->find($movieId);

        if (!$usuario || !$pelicula) {
            return new JsonResponse(['error' => 'Usuario o película no encontrada.'], 404);
        }

        $valoracion = $entityManager->getRepository(Valoraciones::class)->findOneBy([
            'usuario' => $usuario,
            'pelicula' => $pelicula
        ]);

        if (!$valoracion) {
            $valoracion = new \App\Entity\Valoraciones();
            $valoracion->setUsuario($usuario);
            $valoracion->setPelicula($pelicula);
        }

        $valoracion->setValor((int) $valor);
        $entityManager->persist($valoracion);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Valoración guardada con éxito']);
    }

    #[Route('/getUserRating', name: 'get_user_rating', methods: ['GET'])]
    public function getUserRating(
        Request $request,
        ValoracionesRepository $valoracionesRepository,
        UsuariosRepository $usuariosRepository,
        PeliculasRepository $peliculasRepository
    ): JsonResponse {
        $userId = $request->query->get('userId');
        $movieId = $request->query->get('movieId');

        $user = $usuariosRepository->find($userId);
        $movie = $peliculasRepository->find($movieId);

        if (!$user || !$movie) {
            return new JsonResponse(['valor' => 0]);
        }

        $valoracion = $valoracionesRepository->findOneBy([
            'usuario' => $user,
            'pelicula' => $movie,
        ]);

        $valor = $valoracion ? $valoracion->getValor() : 0;

        return new JsonResponse(['valor' => $valor]);
    }
}
