<?php

namespace App\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Annonce;
use App\Entity\UserTransactions;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class SellerTransactionProvider implements ProviderInterface
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


        // Need to test
        //     $qb = $this->entityManager->getRepository(UserTransactions::class)->createQueryBuilder('t')
        //     ->innerJoin('t.annonce', 'a')
        //     ->where('a.user = :user')
        //     ->setParameter('user', $user);

        // return $qb->getQuery()->getResult();

        $annonces = $this->entityManager->getRepository(Annonce::class)->findBy(['user' => $user]);
        $transactions = [];
        foreach ($annonces as $annonce) {
            $transactions = array_merge($transactions, $this->entityManager->getRepository(UserTransactions::class)->findBy(['annonce' => $annonce]));
        }
        return $transactions;
        
        

    }
}