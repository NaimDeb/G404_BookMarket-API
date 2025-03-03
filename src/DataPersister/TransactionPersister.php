<?php

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Transaction;
use App\Entity\UserTransactions;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use ApiPlatform\Metadata\Operation;


class TransactionPersister implements ProcessorInterface
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): UserTransactions
    {
        if ($data instanceof UserTransactions) {

            $user = $this->security->getUser();
            if (!$user) {
                throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException('Not authenticated');
            }

            $data->setUser($user);
            
            $data->setStatus('pending');

            $data->setTransactionAt(new \DateTimeImmutable());



            $this->entityManager->persist($data);
            $this->entityManager->flush();
        }

        return $data;
    }
}
