<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\UserTransactionsRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\DataPersister\TransactionPersister;
use App\State\Provider\BuyerTransactionProvider;
use App\State\Provider\SellerTransactionProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: UserTransactionsRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: 'user_transactions/sales',
            normalizationContext: ['groups' => 'transaction:read'],
            security: "is_granted('ROLE_USER')",
            provider: SellerTransactionProvider::class
        ),
        new Get(
            uriTemplate: 'user_transactions/sales/{id}',
            normalizationContext: ['groups' => 'transaction:read'],
            security: "is_granted('ROLE_USER')",
            provider: SellerTransactionProvider::class
        ),
        new GetCollection(
            uriTemplate: 'user_transactions/purchases',
            normalizationContext: ['groups' => 'transaction:read'],
            security: "is_granted('ROLE_USER')",
            provider: BuyerTransactionProvider::class
        ),
        new Get(
            uriTemplate: 'user_transactions/purchases/{id}',
            normalizationContext: ['groups' => 'transaction:read'],
            security: "is_granted('ROLE_USER')",
            provider: BuyerTransactionProvider::class
        ),
        new Post(
            uriTemplate: 'user_transactions/buy',
            denormalizationContext: ['groups' => 'transaction:write'],
            security: "is_granted('ROLE_USER')",
            processor: TransactionPersister::class,
            securityMessage: 'Only users can buy products'
        )
    ]
)]
class UserTransactions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['transaction:read'])]
    private ?\DateTimeImmutable $transactionAt = null;

    #[ORM\Column(length: 255)]
    #[Groups(['transaction:read'])]
    private ?string $status = null;

    #[ORM\ManyToOne(inversedBy: 'userTransactions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['transaction:read', 'transaction:write'])]
    private ?Annonce $annonce = null;

    #[ORM\ManyToOne(inversedBy: 'userTransactions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['transaction:read'])]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTransactionAt(): ?\DateTimeImmutable
    {
        return $this->transactionAt;
    }

    public function setTransactionAt(\DateTimeImmutable $transactionAt): static
    {
        $this->transactionAt = $transactionAt;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getAnnonce(): ?Annonce
    {
        return $this->annonce;
    }

    public function setAnnonce(?Annonce $annonce): static
    {
        $this->annonce = $annonce;

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
