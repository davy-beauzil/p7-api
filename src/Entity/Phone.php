<?php

namespace App\Entity;

use App\Repository\PhoneRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PhoneRepository::class)]
class Phone
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column]
    #[Groups(['get:collection', 'get:item'])]
    private string $id;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:collection', 'get:item'])]
    private ?string $brand = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:collection', 'get:item'])]
    private ?string $model = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:item'])]
    private ?string $operatingSystem = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['get:item'])]
    private ?string $processor = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:item'])]
    private ?int $storage = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:item'])]
    private ?int $ram = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:item'])]
    private ?int $screenWidth = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:item'])]
    private ?int $screenHeight = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:item'])]
    private ?float $screenSize = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:item'])]
    private ?bool $bluetooth = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:item'])]
    private ?bool $wifi = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['get:item'])]
    private ?float $priceExclTax = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(?string $brand): self
    {
        $this->brand = $brand;
        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;
        return $this;
    }

    public function getOperatingSystem(): ?string
    {
        return $this->operatingSystem;
    }

    public function setOperatingSystem(string $operatingSystem): self
    {
        $this->operatingSystem = $operatingSystem;
        return $this;
    }

    public function getProcessor(): ?string
    {
        return $this->processor;
    }

    public function setProcessor(string $processor): self
    {
        $this->processor = $processor;
        return $this;
    }

    public function getStorage(): ?int
    {
        return $this->storage;
    }

    public function setStorage(?int $storage): self
    {
        $this->storage = $storage;
        return $this;
    }

    public function getRam(): ?int
    {
        return $this->ram;
    }

    public function setRam(?int $ram): self
    {
        $this->ram = $ram;
        return $this;
    }

    public function getScreenWidth(): ?int
    {
        return $this->screenWidth;
    }

    public function setScreenWidth(?int $screenWidth): self
    {
        $this->screenWidth = $screenWidth;
        return $this;
    }

    public function getScreenHeight(): ?int
    {
        return $this->screenHeight;
    }

    public function setScreenHeight(?int $screenHeight): self
    {
        $this->screenHeight = $screenHeight;
        return $this;
    }

    public function getScreenSize(): ?float
    {
        return $this->screenSize;
    }

    public function setScreenSize(?float $screenSize): self
    {
        $this->screenSize = $screenSize;
        return $this;
    }

    public function isBluetooth(): ?bool
    {
        return $this->bluetooth;
    }

    public function setBluetooth(bool $bluetooth): self
    {
        $this->bluetooth = $bluetooth;
        return $this;
    }

    public function isWifi(): ?bool
    {
        return $this->wifi;
    }

    public function setWifi(bool $wifi): self
    {
        $this->wifi = $wifi;
        return $this;
    }

    public function getPriceExclTax(): ?float
    {
        return $this->priceExclTax;
    }

    public function setPriceExclTax(float $priceExclTax): self
    {
        $this->priceExclTax = $priceExclTax;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}
