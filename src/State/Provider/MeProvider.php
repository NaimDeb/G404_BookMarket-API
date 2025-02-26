<?php

namespace App\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Symfony\Bundle\SecurityBundle\Security;

class MeProvider implements ProviderInterface
{
    public function __construct(
        private Security $security
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        /** @var User */
        $user = $this->security->getUser();
        // Load relations explicitly to ensure correct data
        if ($user->getUserDetails()) {
            $user->getUserDetails()->getUser();  // Force load of user relation
        }
        if ($user->getProfessionalDetails()) {
            $user->getProfessionalDetails()->getUser(); // Force load of user relation
        }
        return $user;

    }
}