<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ProfessionalDetailsRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\DataPersister\ProfessionalDetailsDataPersister;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ProfessionalDetailsRepository::class)]
#[ApiResource(
    operations: [
        new Post(
            denormalizationContext: ['groups' => ['proDetails:write']],
            validationContext: ['groups' => ['Default']],
            security: "is_granted('ROLE_ADMIN')",
            processor: ProfessionalDetailsDataPersister::class
        ),
        new Get(
            denormalizationContext: ['groups' => ['proDetails:read']],
            validationContext: ['groups' => ['Default']],
            security: "is_granted('ROLE_USER')",
        ),
        new Patch(
            denormalizationContext: ['groups' => ['proDetails:write']],
            validationContext: ['groups' => ['Default']],
            security: "is_granted('ROLE_PROFESSIONAL')",
        ),
    ]
)]
class ProfessionalDetails
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['proDetails:read', 'proDetails:write', 'user:read', 'user:write'])]
    private ?string $companyName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['proDetails:read', 'proDetails:write', 'user:read', 'user:write'])]
    private ?string $companyAddress = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['proDetails:read', 'proDetails:write', 'user:read', 'user:write'])]
    private ?string $companyPhone = null;

    #[ORM\OneToOne(inversedBy: 'professionalDetails', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(?string $companyName): static
    {
        $this->companyName = $companyName;

        return $this;
    }

    public function getCompanyAddress(): ?string
    {
        return $this->companyAddress;
    }

    public function setCompanyAddress(?string $companyAddress): static
    {
        $this->companyAddress = $companyAddress;

        return $this;
    }

    public function getCompanyPhone(): ?string
    {
        return $this->companyPhone;
    }

    public function setCompanyPhone(?string $companyPhone): static
    {
        $this->companyPhone = $companyPhone;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
