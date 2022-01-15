<?php

namespace App\Entity;

use App\Repository\PurchaseOrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PurchaseOrderRepository::class)]
class PurchaseOrder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $purchaseOrderId;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'purchaseOrders')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\OneToMany(mappedBy: 'purchaseOrder', targetEntity: PurchaseOrderLine::class, cascade: ['persist'], orphanRemoval: true)]
    private $purchaseOrderLines;

    #[ORM\Column(type: 'float')]
    private $totalPrice;

    #[ORM\ManyToOne(targetEntity: Restaurant::class, inversedBy: 'purchaseOrders')]
    #[ORM\JoinColumn(nullable: false)]
    private $restaurant;

    public function __construct()
    {
        $this->purchaseOrderLines = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPurchaseOrderId(): ?string
    {
        return $this->purchaseOrderId;
    }

    public function setPurchaseOrderId(string $purchaseOrderId): self
    {
        $this->purchaseOrderId = $purchaseOrderId;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection|PurchaseOrderLine[]
     */
    public function getPurchaseOrderLines(): Collection
    {
        return $this->purchaseOrderLines;
    }

    public function addPurchaseOrderLine(PurchaseOrderLine $purchaseOrderLine): self
    {
        if (!$this->purchaseOrderLines->contains($purchaseOrderLine)) {
            $this->purchaseOrderLines[] = $purchaseOrderLine;
            $purchaseOrderLine->setPurchaseOrder($this);
        }

        return $this;
    }

    public function removePurchaseOrderLine(PurchaseOrderLine $purchaseOrderLine): self
    {
        if ($this->purchaseOrderLines->removeElement($purchaseOrderLine)) {
            // set the owning side to null (unless already changed)
            if ($purchaseOrderLine->getPurchaseOrder() === $this) {
                $purchaseOrderLine->setPurchaseOrder(null);
            }
        }

        return $this;
    }

    public function getTotalPrice(): ?float
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(float $totalPrice): self
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    public function getRestaurant(): ?Restaurant
    {
        return $this->restaurant;
    }

    public function setRestaurant(?Restaurant $restaurant): self
    {
        $this->restaurant = $restaurant;

        return $this;
    }
}
