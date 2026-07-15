<?php

namespace App\Repository;

use App\Entity\Commande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commande>
 */
class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }

    public function getRevenueTotal(): float
    {
        return (float) $this->createQueryBuilder('c')
            ->select('COALESCE(SUM(c.total), 0)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getMonthlyRevenue(): array
    {
        return $this->getEntityManager()->getConnection()->fetchAllAssociative(
            'SELECT DATE_FORMAT(date_commande, "%Y-%m") AS month, SUM(total) AS total
             FROM commande
             GROUP BY month
             ORDER BY month ASC'
        );
    }
}
