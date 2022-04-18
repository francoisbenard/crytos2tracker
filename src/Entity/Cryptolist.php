<?php

namespace App\Entity;

use App\Repository\CryptolistRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CryptolistRepository::class)]
class Cryptolist
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'string', length: 255)]
    private $symbol;

    #[ORM\OneToMany(mappedBy: 'crypto', targetEntity: Mycrypto::class)]
    private $mycryptos;

    public function __construct()
    {
        $this->mycryptos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function setSymbol(string $symbol): self
    {
        $this->symbol = $symbol;

        return $this;
    }

    /**
     * @return Collection<int, Mycrypto>
     */
    public function getMycryptos(): Collection
    {
        return $this->mycryptos;
    }

    public function addMycrypto(Mycrypto $mycrypto): self
    {
        if (!$this->mycryptos->contains($mycrypto)) {
            $this->mycryptos[] = $mycrypto;
            $mycrypto->setCrypto($this);
        }
        return $this;
    }

    public function removeMycrypto(Mycrypto $mycrypto): self
    {
        if ($this->mycryptos->removeElement($mycrypto)) {
            // set the owning side to null (unless already changed)
            if ($mycrypto->getCrypto() === $this) {
                $mycrypto->setCrypto(null);
            }
        }
        return $this;
    }
}
