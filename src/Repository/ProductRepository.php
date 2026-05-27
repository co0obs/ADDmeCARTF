<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function searchAndFilter(?string $keyword, ?string $category, ?int $minStars, ?string $sort, ?float $minPrice = null, ?float $maxPrice = null, int $page = 1, int $limit = 12): Paginator
    {
        $qb = $this->createQueryBuilder('p');

        if ($keyword) {
            $qb->andWhere('p.name LIKE :keyword OR p.description LIKE :keyword')
               ->setParameter('keyword', '%' . $keyword . '%');
        }

        if ($category && $category !== 'all') {
            $qb->andWhere('p.category = :category')
               ->setParameter('category', $category);
        }

        if ($minStars && $minStars > 0) {
            $qb->andWhere('p.starRating >= :minStars')
               ->setParameter('minStars', (float) $minStars);
        }

        // Price range filtering - filter by regular price
        if ($minPrice !== null && $minPrice > 0) {
            $qb->andWhere('p.price >= :minPrice')
               ->setParameter('minPrice', $minPrice);
        }

        if ($maxPrice !== null && $maxPrice > 0) {
            $qb->andWhere('p.price <= :maxPrice')
               ->setParameter('maxPrice', $maxPrice);
        }

        if ($sort === 'price_asc') {
            $qb->orderBy('p.price', 'ASC');
        } elseif ($sort === 'price_desc') {
            $qb->orderBy('p.price', 'DESC');
        } else {
            $qb->orderBy('p.id', 'DESC');
        }

        $qb->setFirstResult(($page - 1) * $limit)
           ->setMaxResults($limit);

        return new Paginator($qb->getQuery(), fetchJoinCollection: false);
    }
}

