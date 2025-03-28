<?php

namespace App\DataPersister;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Annonce;
use App\Entity\User;
use App\Entity\UserDetails;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AnnonceDataPersister implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Annonce
    {
        if ($data instanceof Annonce) {

            $user = $this->security->getUser();
            if (!$user) {
                throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException('Not authenticated');
            }

            if ($data->getUser() && $data->getCreatedAt()) {
                $data->setUpdatedAt(new \DateTimeImmutable());
            } else {
                $data->setUser($user);
                $data->setCreatedAt(new \DateTimeImmutable());
            }





            $this->entityManager->persist($data);
            $this->entityManager->flush();
        }

        return $data;
    }
}
