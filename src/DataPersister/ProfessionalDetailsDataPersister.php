<?php

namespace App\DataPersister;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\ProfessionalDetails;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class ProfessionalDetailsDataPersister implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): User
    {
        if ($data instanceof ProfessionalDetails) {

            $user = $this->security->getUser();
            if (!$user) {
                throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException('Not authenticated');
            }

            if (in_array('ROLE_PROFESSIONAL', $user->getRoles())) {
                throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException('You are already a professional');
            }

            $data->setUser($user);
        }


            $this->entityManager->persist($data);
            $this->entityManager->flush();
        

        return $data;
    }
}
