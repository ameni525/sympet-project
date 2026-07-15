<?php

namespace App\Service;

use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\HttpFoundation\Request;

class PaginationService
{
    public function __construct(private PaginatorInterface $paginator)
    {
    }

    /**
     * Pagine n'importe quelle requête Doctrine.
     *
     * @param mixed   $query      Query ou QueryBuilder Doctrine
     * @param Request $request    La requête HTTP (pour lire le numéro de page)
     * @param int     $limit      Nombre d'éléments par page (défaut : 6)
     *
     * @return PaginationInterface
     */
    public function paginate(mixed $query, Request $request, int $limit = 6): PaginationInterface
    {
        return $this->paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            $limit
        );
    }
}