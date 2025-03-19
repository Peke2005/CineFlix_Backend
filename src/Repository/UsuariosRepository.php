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


    public function createUser($Name, $Email, $Password)
    {
        $rol = "usuario";
        $RegistrationDate = Carbon::now();
        $qb = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $qb->insert('usuarios')
            ->setValue("nombre", ":nombre")
            ->setValue("email", ":email")
            ->setValue("contraseña", ":pass")
            ->setValue('rol', ':rol')
            ->setValue("fecha_registro", ":fecha_registro");


        $query = $qb->getSQL();

        $params = [
            'nombre' => $Name,
            'email' => $Email,
            'pass' => $Password,
            'rol' => $rol,
            'fecha_registro' => $RegistrationDate
        ];

        return $this->getEntityManager()->getConnection()->executeQuery($query, $params);
    }
}
