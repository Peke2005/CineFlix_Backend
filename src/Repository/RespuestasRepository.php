<?php

namespace App\Repository;

use App\Entity\Respuestas;
use App\Entity\Usuarios;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Carbon\Carbon;

/**
 * @extends ServiceEntityRepository<Usuarios>
 */
class RespuestasRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Respuestas::class);
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

    public function verifyPassword($plainPass, $hashedPass)
    {
        $factory = new PasswordHasherFactory([
            'common' => ['algorithm' => 'bcrypt'],
        ]);
        $hasher = $factory->getPasswordHasher('common');
        return $hasher->verify($hashedPass, $plainPass);
    }

    public function hashPassword($Password)
    {
        $factory = new PasswordHasherFactory([
            'common' => ['algorithm' => 'bcrypt'],
        ]);
        $hasher = $factory->getPasswordHasher('common');

        return $hasher->hash($Password);
    }
    public function createUser($Name, $Email, $Password, $imagen)
    {
        $rol = "usuario";
        $RegistrationDate = Carbon::now();
        $qb = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $qb->insert('usuarios')
            ->setValue("nombre", ":nombre")
            ->setValue("email", ":email")
            ->setValue("contraseña", ":pass")
            ->setValue('rol', ':rol')
            ->setValue("fecha_registro", ":fecha_registro")
            ->setValue("foto_perfil", ":foto_perfil");

        $query = $qb->getSQL();
        $params = [
            'nombre' => $Name,
            'email' => $Email,
            'pass' => $this->hashPassword($Password),
            'rol' => $rol,
            'fecha_registro' => $RegistrationDate,
            'foto_perfil' => $imagen
        ];

        return $this->getEntityManager()->getConnection()->executeQuery($query, $params);
    }
}
