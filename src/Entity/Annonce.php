<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Delete;
use App\Repository\AnnonceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: AnnonceRepository::class)]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['annonce:read']]),
        new Post(denormalizationContext: ['groups' => ['annonce:write']],),
        new Patch(denormalizationContext: ['groups' => ['annonce:update']]),
        new Delete()
    ]
)]
class Annonce
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['annonce:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::BIGINT)]
    #[Groups(['annonce:read', 'annonce:write', 'annonce:update'])]
    private ?string $type = null;

    #[ORM\Column]
    #[Groups(['annonce:read', 'annonce:write', 'annonce:update'])]
    private ?int $price = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['annonce:read', 'annonce:write', 'annonce:update'])]
    private ?string $productCondition = null;

    #[ORM\ManyToOne(inversedBy: 'annonces')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['annonce:read', 'annonce:write'])]
    private ?Product $product = null;

    #[ORM\ManyToOne(inversedBy: 'annonces')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * @var Collection<int, Image>
     */
    #[ORM\ManyToMany(targetEntity: Image::class)]
    private Collection $images;

    /**
     * @var Collection<int, UserTransactions>
     */
    #[ORM\OneToMany(targetEntity: UserTransactions::class, mappedBy: 'annonce')]
    private Collection $userTransactions;

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->userTransactions = new ArrayCollection();
    }


    public function getId(): ?int
    
    {
        return $this->id;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getProductCondition(): ?string
    {
        return $this->productCondition;
    }

    public function setProductCondition(?string $productCondition): static
    {
        $this->productCondition = $productCondition;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

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

    /**
     * @return Collection<int, Image>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
        }

        return $this;
    }

    public function removeImage(Image $image): static
    {
        $this->images->removeElement($image);

        return $this;
    }

    /**
     * @return Collection<int, UserTransactions>
     */
    public function getUserTransactions(): Collection
    {
        return $this->userTransactions;
    }

    public function addUserTransaction(UserTransactions $userTransaction): static
    {
        if (!$this->userTransactions->contains($userTransaction)) {
            $this->userTransactions->add($userTransaction);
            $userTransaction->setAnnonce($this);
        }

        return $this;
    }

    public function removeUserTransaction(UserTransactions $userTransaction): static
    {
        if ($this->userTransactions->removeElement($userTransaction)) {
            // set the owning side to null (unless already changed)
            if ($userTransaction->getAnnonce() === $this) {
                $userTransaction->setAnnonce(null);
            }
        }

        return $this;
    }
}
