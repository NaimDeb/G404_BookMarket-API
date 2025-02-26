<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Delete;
use App\Repository\AnnonceRepository;
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
    private ?int $price = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['annonce:read', 'annonce:write', 'annonce:update'])]
    private ?string $productCondition = null;

    #[ORM\ManyToOne(inversedBy: 'annonces')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['annonce:read', 'annonce:write'])]
    private ?Product $product = null;

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
}
