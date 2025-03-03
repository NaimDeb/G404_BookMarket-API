<?php

namespace App\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\UserTransactions;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class BuyerTransactionProvider implements ProviderInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        /** @var User */
        $user = $this->security->getUser();

        if (!$user) {
            throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException('Not authenticated');
        }

        return $this->entityManager->getRepository(UserTransactions::class)->findBy(['user' => $user]);
        
        

    }
}