<?php

namespace App\Entity;

use App\Repository\MycryptoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MycryptoRepository::class)]
class Mycrypto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'float')]
    private $price;

    #[ORM\Column(type: 'integer')]
    private $quantity;

    #[ORM\ManyToOne(targetEntity: Cryptolist::class, inversedBy: 'Mycryptos')]
    private $crypto;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getCrypto(): ?Cryptolist
    {
        return $this->crypto;
    }

    public function setCrypto(?Cryptolist $crypto): self
    {
        $this->crypto = $crypto;

        return $this;
    }
}
