<?php

namespace App\Repository;

use App\Entity\Comentarios;
use App\Entity\Peliculas;
use App\Entity\Usuarios;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @extends ServiceEntityRepository<Usuarios>
 */
class ComentariosRepository extends ServiceEntityRepository
{

    private $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Comentarios::class);
        $this->entityManager = $entityManager; // Aquí inyectamos el EntityManager

    }

    //    /**
    //     * @return Usuarios[] Returns an array of Usuarios objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Usuarios
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function addComment(string $commentMessage, Peliculas $movie, Usuarios $user)
    {
        $comment = new Comentarios();
        $comment->setMensaje($commentMessage);
        $comment->setFechaCreacion(new \DateTime());
        $comment->setPelicula($movie);
        $comment->setUsuario($user);
        $this->entityManager->persist($comment);
        $this->entityManager->flush();
    }
}
