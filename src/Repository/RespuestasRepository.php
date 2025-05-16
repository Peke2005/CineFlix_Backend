<?php

namespace App\Repository;

use App\Entity\Respuestas;
use App\Entity\Comentarios;
use App\Entity\Usuarios;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Respuestas>
 */
class RespuestasRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Respuestas::class);
    }

    public function addResponse(string $responseMessage, Comentarios $parentComment, Usuarios $user): void
    {
        $response = new Respuestas();
        $response->setMensaje($responseMessage);
        $response->setUsuario($user);
        $response->setComentario($parentComment);
        $response->setFechaCreacion(new \DateTime());

        $this->getEntityManager()->persist($response);
        $this->getEntityManager()->flush();
    }
}