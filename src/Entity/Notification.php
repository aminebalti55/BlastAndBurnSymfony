<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Notification
 *
 * @ORM\Table(name="notification")
 * @ORM\Entity
 */
class Notification
{
    /**
     * @var int
     *
     * @ORM\Column(name="notification_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $notificationId;

    /**
     * @var int|null
     *
     * @ORM\Column(name="coach_id", type="integer", nullable=true)
     */
    private $coachId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="message", type="string", length=255, nullable=true)
     */
    private $message;

    public function getNotificationId(): ?int
    {
        return $this->notificationId;
    }

    public function getcoachId(): ?int
    {
        return $this->coachId;
    }

    public function setcoachId(?int $coachId): self
    {
        $this->coachId = $coachId;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }


}
