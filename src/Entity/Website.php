<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\WebsiteRepository;

/**
 * @ORM\Table(name="website")
 * @ORM\Entity(repositoryClass=WebsiteRepository::class)
 */
class Website
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var float
     * @ORM\Column(type="float", length=10, precision=2, options={"default": 0})
     */
    private $money;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMoney(): ?float
    {
        return $this->money;
    }

    public function setMoney(float $money): self
    {
        $this->money = $money;

        return $this;
    }
}
