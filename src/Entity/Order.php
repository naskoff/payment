<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\OrderRepository;

/**
 * @ORM\Table(name="orders")
 * @ORM\Entity(repositoryClass=OrderRepository::class)
 */
class Order
{
    const CURRENCY_BGR = 'BGR';
    const CURRENCY_EUR = 'EUR';
    const CURRENCY_USD = 'USD';

    const STATUS_PENDING = 'PENDING';
    const STATUS_CONFIRM = 'CONFIRM';
    const STATUS_REJECT = 'REJECT';

    const PROVIDER_PAYPAL = 'PAYPAL';
    const PROVIDER_STRIPE = 'STRIPE';
    const PROVIDER_UNKNOWN = 'UNKNOWN';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="orders")
     */
    private $user;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $status = self::STATUS_PENDING;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $provider = self::PROVIDER_UNKNOWN;

    /**
     * @var float
     * @ORM\Column(type="float", length=10, precision=3)
     */
    private $amount;

    /**
     * @var string
     */
    private $currency = self::CURRENCY_BGR;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $orderId;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function setProvider(string $provider): self
    {
        $this->provider = $provider;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    public function setOrderId(string $orderId): self
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

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
}
