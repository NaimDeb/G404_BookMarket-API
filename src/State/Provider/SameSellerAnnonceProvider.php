<?php

namespace App\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Annonce;
use Doctrine\ORM\EntityManagerInterface;

class SameSellerAnnonceProvider implements ProviderInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $currentAnnonceId = $uriVariables['id'] ?? null;
        if (!$currentAnnonceId) {
            return [];
        }

        $currentAnnonce = $this->entityManager->getRepository(Annonce::class)->find($currentAnnonceId);
        if (!$currentAnnonce) {
            return [];
        }

        $seller = $currentAnnonce->getUser();

        return $this->entityManager->getRepository(Annonce::class)
            ->createQueryBuilder('a')
            ->where('a.user = :seller')
            ->andWhere('a.id != :currentId')
            ->setParameter('seller', $seller)
            ->setParameter('currentId', $currentAnnonceId)
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
    }
}
