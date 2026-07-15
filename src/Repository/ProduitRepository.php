<?php

namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Produit>
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }
    public function searchQuery(?string $name, ?string $categoryId)
{
    $qb = $this->createQueryBuilder('p')
        ->join('p.categorie', 'c');

    if ($name) {
        $qb->andWhere('p.nom LIKE :name')
           ->setParameter('name', '%' . $name . '%');
    }

    if ($categoryId) {
        $qb->andWhere('c.id = :cat')
           ->setParameter('cat', $categoryId);
    }

    return $qb->getQuery();
}


}
