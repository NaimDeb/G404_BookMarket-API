<?php

namespace App\DataPersister;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\ProfessionalDetails;
use App\Entity\User;
use App\Entity\UserDetails;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserDataPersister implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): User
    {
        if ($data instanceof User) {
            if ($data->getPassword()) {
                $hashedPassword = $this->passwordHasher->hashPassword($data, $data->getPassword());
                $data->setPassword($hashedPassword);
            }
            $data->setRoles(['ROLE_USER']);
            // if ($data->getProfessionnalDetails()) {
            //     $this->entityManager->persist($data->getProfessionnalDetails());
            //     $data->setRoles(['ROLE_VENDEUR', 'ROLE_USER']);
            // }


            // Handle UserDetails
            if ($data->getUserDetails()) {
                // If UserDetails already exists in the request, just set the user
                $data->getUserDetails()->setUser($data);
            } else {
                // Create new UserDetails only if none exists
                $userDetails = new UserDetails();
                $userDetails->setUser($data);
                $data->setUserDetails($userDetails);
                $this->entityManager->persist($userDetails);
            }

            // Handle ProfessionalDetails
            if ($data->getProfessionalDetails()) {
                // Set user for existing ProfessionalDetails
                $data->getProfessionalDetails()->setUser($data);
                $data->setRoles(['ROLE_PROFESSIONAL', 'ROLE_USER']);
                $this->entityManager->persist($data->getProfessionalDetails());
            }


            $this->entityManager->persist($data);
            $this->entityManager->flush();
        }

        return $data;
    }
}
