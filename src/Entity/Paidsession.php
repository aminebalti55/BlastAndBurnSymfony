<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Paidsession
 *
 * @ORM\Table(name="paid_session")
 * @ORM\Entity
 */
class Paidsession
{
    public $className='Paidsession';
    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float", precision=10, scale=0, nullable=false)
     * @Groups("BS")
     * @Groups("post:read")
     */
    private $price ;

    /**
     * @var session
     *
     * @ORM\Id
     * @ORM\OneToOne(targetEntity=session::class)
     * @Groups("BS")
     * @Groups("post:read")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="session_id", referencedColumnName="session_id")
     * })
     */
    private $session;

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getsession(): ?session
    {
        return $this->session;
    }

    public function setsession(?session $session): self
    {
        $this->session = $session;

        return $this;
    }



}
