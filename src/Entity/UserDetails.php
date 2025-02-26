<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\UserDetailsRepository;
use App\State\Provider\MeProvider;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: UserDetailsRepository::class)]
#[ApiResource(
    operations: [
        new Get(
            denormalizationContext: ['groups' => ['userDetails:read']],
            validationContext: ['groups' => ['Default']],
            security: "is_granted('ROLE_ADMIN')",
        ),
        new Patch(
            denormalizationContext: ['groups' => ['userDetails:write']],
            validationContext: ['groups' => ['Default']],
            security: "is_granted('ROLE_USER')",
            provider: MeProvider::class,
        ),
    ]
)]
class UserDetails
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['userDetails:read', 'userDetails:write', 'user:read', 'user:write'])]
    private ?string $address = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['userDetails:read', 'userDetails:write', 'user:read', 'user:write'])]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['userDetails:read', 'userDetails:write', 'user:read', 'user:write'])]
    private ?string $country = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['userDetails:read', 'userDetails:write', 'user:read', 'user:write'])]
    private ?string $firstName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['userDetails:read', 'userDetails:write', 'user:read', 'user:write'])]
    private ?string $lastName = null;

    #[ORM\OneToOne(inversedBy: 'userDetails', cascade: ['persist', 'remove'])]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
