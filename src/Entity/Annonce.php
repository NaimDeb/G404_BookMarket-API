<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Delete;
use App\DataPersister\AnnonceDataPersister;
use App\Repository\AnnonceRepository;
use App\State\Provider\LastFiveProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: AnnonceRepository::class)]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['annonce:read']]),
        new Post(
            denormalizationContext: ['groups' => ['annonce:write']],
            processor: AnnonceDataPersister::class
        ),


        new GetCollection(
            uriTemplate: '/annonces/last-five',
            normalizationContext: ['groups' => ['annonce:read']],
            provider: LastFiveProvider::class,
            name: 'getLastFive'
        ),



        new Patch(
            denormalizationContext: ['groups' => ['annonce:update']],
            security: "is_granted('ROLE_USER') and object.user == user",
            securityMessage: "Vous n'avez pas accès à cette annonce",
            processor: AnnonceDataPersister::class
        ),


        new Delete(
            security: "is_granted('ROLE_USER') and object.user == user",
            securityMessage: "Vous n'avez pas accès à cette annonce"
        )
    ]
)]
class Annonce
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['annonce:read'])]
    private ?int $id = null;

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
    #[Groups(['annonce:read', 'annonce:write'])]
    private Collection $images;

    /**
     * @var Collection<int, UserTransactions>
     */
    #[ORM\OneToMany(targetEntity: UserTransactions::class, mappedBy: 'annonce')]
    private Collection $userTransactions;


    #[Groups(['annonce:read'])]
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
