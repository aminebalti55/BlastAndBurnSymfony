<?php

namespace App\Entity;

use App\Repository\ReportNotificationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="report_notification")
 * @ORM\Entity(repositoryClass=ReportNotificationRepository::class)
 */
class ReportNotification implements \JsonSerializable
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
     * @var User
     *
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     * })
     */
    private $user;

    /**
     * @var Report
     *
     * @ORM\ManyToOne(targetEntity=Report::class)
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="report_id", referencedColumnName="report_id")
     * })
     */
    private $report;

    /**
     * @var bool
     *
     * @ORM\Column(name="seen_by_admin", type="boolean", nullable=false, options={"default"="0"})
     */
    private $seenByAdmin = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="seen_by_user", type="boolean", nullable=false, options={"default"="0"})
     */
    private $seenByUser = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="closed", type="boolean", nullable=false, options={"default"="0"})
     */
    private $closed = '0';

  
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
     * @var Recipe
     *
     * @ORM\ManyToOne(targetEntity=Recipe::class)
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="recipe_id", referencedColumnName="recipe_id")
     * })
     */
    private $recipe;

    

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
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", options={"default"="CURRENT_TIMESTAMP"})
     */
    private $createdAt;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->notificationId;
    }

    /**
     * @param int $notificationId
     */
    public function setNotificationId(int $notificationId): void
    {
        $this->notificationId = $notificationId;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return bool
     */
    public function isSeenByAdmin()
    {
        return $this->seenByAdmin;
    }

    /**
     * @param bool $seenByAdmin
     */
    public function setSeenByAdmin($seenByAdmin): void
    {
        $this->seenByAdmin = $seenByAdmin;
    }

  

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

    /**
     * @return Recipe
     */
    public function getRecipe(): Recipe
    {
        return $this->recipe;
    }

    /**
     * @param Recipe $recipe
     */
    public function setRecipe(Recipe $recipe): void
    {
        $this->recipe = $recipe;
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

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return Report
     */
    public function getReport(): Report
    {
        return $this->report;
    }

    /**
     * @param Report $report
     */
    public function setReport(Report $report): void
    {
        $this->report = $report;
    }

    /**
     * @return bool
     */
    public function isSeenByUser()
    {
        return $this->seenByUser;
    }

    /**
     * @param bool $seenByUser
     */
    public function setSeenByUser($seenByUser): void
    {
        $this->seenByUser = $seenByUser;
    }

    /**
     * @return bool
     */
    public function isClosed()
    {
        return $this->closed;
    }

    /**
     * @param bool $closed
     */
    public function setClosed($closed): void
    {
        $this->closed = $closed;
    }


    public function jsonSerialize()
    {
        return [
            "report" =>$this->getReport()->getNote(),
            "user" => $this->getUser()->getFirstName(),
            "date"=> $this->getCreatedAt()->getTimestamp()
        ];
    }

    public function getNotificationId(): ?int
    {
        return $this->notificationId;
    }

    public function getSeenByAdmin(): ?bool
    {
        return $this->seenByAdmin;
    }

    public function getSeenByUser(): ?bool
    {
        return $this->seenByUser;
    }

    public function getClosed(): ?bool
    {
        return $this->closed;
    }
}
