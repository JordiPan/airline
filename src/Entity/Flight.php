<?php

namespace App\Entity;

use App\Repository\FlightRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FlightRepository::class)
 */
class Flight
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Airplane::class, inversedBy="flights")
     */
    private $airplane;

    /**
     * @ORM\ManyToOne(targetEntity=Airport::class, inversedBy="flights")
     */
    private $beginAirport;

    /**
     * @ORM\ManyToOne(targetEntity=Airport::class, inversedBy="flights")
     */
    private $destination;

    /**
     * @ORM\Column(type="float")
     */
    private $price;

    /**
     * @ORM\OneToMany(targetEntity=Booking::class, mappedBy="flight")
     */
    private $bookings;

    /**
     * @ORM\OneToMany(targetEntity=Seat::class, mappedBy="flight")
     */
    private $seats;

    /**
     * @ORM\Column(type="date")
     */
    private $date;

    /**
     * @ORM\Column(type="time")
     */
    private $time;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity=Booking::class, mappedBy="returnFlight")
     */
    private $returnBookings;

    public function __construct()
    {
        $this->bookings = new ArrayCollection();
        $this->seats = new ArrayCollection();
        $this->returnBookings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAirplane(): ?Airplane
    {
        return $this->airplane;
    }

    public function setAirplane(?Airplane $airplane): self
    {
        $this->airplane = $airplane;

        return $this;
    }

    public function getBeginAirport(): ?Airport
    {
        return $this->beginAirport;
    }

    public function setBeginAirport(?Airport $beginAirport): self
    {
        $this->beginAirport = $beginAirport;

        return $this;
    }

    public function getDestination(): ?Airport
    {
        return $this->destination;
    }

    public function setDestination(?Airport $destination): self
    {
        $this->destination = $destination;

        return $this;
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

    /**
     * @return Collection|Booking[]
     */
    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function addBooking(Booking $booking): self
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings[] = $booking;
            $booking->setFlight($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): self
    {
        if ($this->bookings->removeElement($booking)) {
            // set the owning side to null (unless already changed)
            if ($booking->getFlight() === $this) {
                $booking->setFlight(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Seat[]
     */
    public function getSeats(): Collection
    {
        return $this->seats;
    }

    public function addSeat(Seat $seat): self
    {
        if (!$this->seats->contains($seat)) {
            $this->seats[] = $seat;
            $seat->setFlight($this);
        }

        return $this;
    }

    public function removeSeat(Seat $seat): self
    {
        if ($this->seats->removeElement($seat)) {
            // set the owning side to null (unless already changed)
            if ($seat->getFlight() === $this) {
                $seat->setFlight(null);
            }
        }

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getTime(): ?\DateTimeInterface
    {
        return $this->time;
    }

    public function setTime(\DateTimeInterface $time): self
    {
        $this->time = $time;

        return $this;
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

    /**
     * @return Collection|Booking[]
     */
    public function getReturnBookings(): Collection
    {
        return $this->returnBookings;
    }

    public function addReturnBooking(Booking $returnBooking): self
    {
        if (!$this->returnBookings->contains($returnBooking)) {
            $this->returnBookings[] = $returnBooking;
            $returnBooking->setReturnFlight($this);
        }

        return $this;
    }

    public function removeReturnBooking(Booking $returnBooking): self
    {
        if ($this->returnBookings->removeElement($returnBooking)) {
            // set the owning side to null (unless already changed)
            if ($returnBooking->getReturnFlight() === $this) {
                $returnBooking->setReturnFlight(null);
            }
        }

        return $this;
    }
}
