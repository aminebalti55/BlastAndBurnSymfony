<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * sessionRate
 *
 * @ORM\Table(name="session_rate")
 * @ORM\Entity
 */
class sessionRate
{
    /**
     * @var Rate
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity=Rate::class)
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="rate_id", referencedColumnName="rate_id")
     * })
     */
    private $rate;

    /**
     * @var session
     *
     * @ORM\ManyToOne(targetEntity=session::class)
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="session_id", referencedColumnName="session_id")
     * })
     */
    private $session;

    /**
     * @return Rate
     */
    public function getRate(): Rate
    {
        return $this->rate;
    }

    /**
     * @param Rate $rate
     */
    public function setRate(Rate $rate): void
    {
        $this->rate = $rate;
    }

    /**
     * @return session
     */
    public function getsession(): session
    {
        return $this->session;
    }

    /**
     * @param session $session
     */
    public function setsession(session $session): void
    {
        $this->session = $session;
    }


}
