<?php

namespace App\Entity;

use App\Repository\GroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=GroupRepository::class)
 * @ORM\Table(name="`group`")
 */
class Group
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="groups")
     */
    private $founder;

    /**
     * @ORM\OneToMany(targetEntity=GroupCustomer::class, mappedBy="theGroup")
     */
    private $groupCustomers;

    public function __construct()
    {
        $this->groupCustomers = new ArrayCollection();
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

    public function getFounder(): ?User
    {
        return $this->founder;
    }

    public function setFounder(?User $founder): self
    {
        $this->founder = $founder;

        return $this;
    }

    /**
     * @return Collection|GroupCustomer[]
     */
    public function getGroupCustomers(): Collection
    {
        return $this->groupCustomers;
    }

    public function addGroupCustomer(GroupCustomer $groupCustomer): self
    {
        if (!$this->groupCustomers->contains($groupCustomer)) {
            $this->groupCustomers[] = $groupCustomer;
            $groupCustomer->setTheGroup($this);
        }

        return $this;
    }

    public function removeGroupCustomer(GroupCustomer $groupCustomer): self
    {
        if ($this->groupCustomers->removeElement($groupCustomer)) {
            // set the owning side to null (unless already changed)
            if ($groupCustomer->getTheGroup() === $this) {
                $groupCustomer->setTheGroup(null);
            }
        }

        return $this;
    }
}
