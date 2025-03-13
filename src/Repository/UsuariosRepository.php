<?php

namespace App\Repository;

use App\Entity\Usuarios;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Carbon\Carbon;

/**
 * @extends ServiceEntityRepository<Usuarios>
 */
class UsuariosRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Usuarios::class);
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


    public function createUser($Name, $Email, $Password, $User, $RegistrationDate)
    {

        $qb = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $qb->insert('usuarios')
            ->setValue("nombre", ":nombre")
            ->setValue("email", ":email")
            ->setValue("contraseña", ":contraseña")
            ->setValue("user", ":usuario")
            ->setValue("fecha_registro", Carbon::now());

        $query = $qb->getSQL();

        $params = [
            'nombre' => $Name,
            'email' => $Email,
            'contraseña' => $Password,
            'usuario' => $User,
            'password' => $Password,
            'fecha_registro' => $RegistrationDate
        ];

        return $this->getEntityManager()->getConnection()->executeQuery($query, $params);
    }
}
