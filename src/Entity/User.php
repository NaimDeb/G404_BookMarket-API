<?php

namespace App\Entity;


use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Get;
use App\DataPersister\UserDataPersister;
use App\Repository\UserRepository;
use App\State\Provider\MeProvider;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/register',
            denormalizationContext: ['groups' => ['user:write']],
            validationContext: ['groups' => ['Default']],
            security: "is_granted('PUBLIC_ACCESS')",
            processor: UserDataPersister::class
        ),
        new Get(
            uriTemplate: '/me',
            security: "is_granted('ROLE_USER')",
            provider: MeProvider::class,
            normalizationContext: ['groups' => ['user:read']],
            securityMessage : "You must be logged in",
        )
    ]
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Groups(['user:write', 'user:read'])]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Groups(['user:write'])]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:write', 'user:read'])]
    private ?string $username = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:write', 'user:read'])]
    private ?string $profileDesc = null;


    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    #[Groups(['user:write','user:read', 'userDetails:write', 'userDetails:read'])]
    private ?UserDetails $userDetails = null;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    #[Groups(['user:write','user:read', 'professionalDetails:write', 'professionalDetails:read'])]
    private ?ProfessionalDetails $professionalDetails = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getProfileDesc(): ?string
    {
        return $this->profileDesc;
    }

    public function setProfileDesc(string $profileDesc): static
    {
        $this->profileDesc = $profileDesc;

        return $this;
    }

    public function getUserDetails(): ?UserDetails
    {
        return $this->userDetails;
    }

    public function setUserDetails(?UserDetails $userDetails): static
    {
        // unset the owning side of the relation if necessary
        if ($userDetails === null && $this->userDetails !== null) {
            $this->userDetails->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($userDetails !== null && $userDetails->getUser() !== $this) {
            $userDetails->setUser($this);
        }

        $this->userDetails = $userDetails;

        return $this;
    }

    public function getProfessionalDetails(): ?ProfessionalDetails
    {
        return $this->professionalDetails;
    }

    public function setProfessionalDetails(ProfessionalDetails $professionalDetails): static
    {
        // set the owning side of the relation if necessary
        if ($professionalDetails->getUser() !== $this) {
            $professionalDetails->setUser($this);
        }

        $this->professionalDetails = $professionalDetails;

        return $this;
    }
}
