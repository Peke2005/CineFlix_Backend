<?php

namespace App\Repository;

use App\Entity\ComentarioReacciones;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ComentarioReacciones>
 *
 * @method ComentarioReacciones|null find($id, $lockMode = null, $lockVersion = null)
 * @method ComentarioReacciones|null findOneBy(array $criteria, array $orderBy = null)
 * @method ComentarioReacciones[]    findAll()
 * @method ComentarioReacciones[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ComentarioReaccionesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ComentarioReacciones::class);
    }

}
