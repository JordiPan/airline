<?php

namespace App\Entity;

use App\Repository\GroupCustomerRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=GroupCustomerRepository::class)
 */
class GroupCustomer
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=group::class, inversedBy="groupCustomers")
     */
    private $theGroup;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="groupCustomers")
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTheGroup(): ?group
    {
        return $this->theGroup;
    }

    public function setTheGroup(?group $theGroup): self
    {
        $this->theGroup = $theGroup;

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
