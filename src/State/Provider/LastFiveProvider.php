<?php

namespace App\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Annonce;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class LastFiveProvider implements ProviderInterface
{
    public function __construct( private EntityManagerInterface $entityManager
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        // Get the last five annonces from the database
        $annonces = $this->entityManager->getRepository(Annonce::class)->findBy([], ['createdAt' => 'DESC'], 5);

        return $annonces;

    }
}