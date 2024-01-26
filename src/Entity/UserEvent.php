<?php

namespace App\Entity;
use App\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * UserEvent
 *
 * @ORM\Table(name="X", indexes={@ORM\Index(name="IDX_D96CF1FFA76ED395", columns={"user_id"}), @ORM\Index(name="IDX_D96CF1FF71F7E88B", columns={"event_id"})})
 * @ORM\Entity
 */
class UserEvent
{
    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity=Event::class)
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_id", referencedColumnName="event_id")
     * })
     */
    private $event;

    /**
     * @var User
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     * })
     */
    private $user;

    /**
     * @return Event
     */
    public function getEvent(): Event
    {
        return $this->event;
    }

    /**
     * @param Event $event
     */
    public function setEvent(Event $event): void
    {
        $this->event = $event;
    }


    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getEventId(): ?int
    {
        return $this->eventId;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param \App\Entity\User $user
     */
    public function setUser(\App\Entity\User  $user): void
    {
        $this->user = $user;
    }



    /* public function __toString()
     {
       return $this->;
     }*/


}
