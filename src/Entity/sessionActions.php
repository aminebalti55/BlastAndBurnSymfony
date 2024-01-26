<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * sessionActions
 *
 * @ORM\Table(name="session_actions", indexes={@ORM\Index(name="FK_session_sessionAction", columns={"session_id"})})
 * @ORM\Entity
 */
class sessionActions
{
    /**
     * @var int
     *
     * @ORM\Column(name="action_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Groups("post:read")
     */
    private $actionId;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     * @Assert\NotBlank(message="champ obligatoire")
     * @Groups("post:read")
     */
    private $title ;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=false)
     * @Assert\NotBlank(message="champ obligatoire")
     * @Groups("post:read")
     */
    private $description ;
    /**
     * @var bool
     *
     * @ORM\Column(name="done", type="boolean", nullable=false)
     */
    private $done = '0';

    /**
     * @var session
     *
     * @ORM\ManyToOne(targetEntity="session")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="session_id", referencedColumnName="session_id")
     * })
     * @Groups("post:read")
     */
    private $session;
    public function getActionId(): ?int
    {
        return $this->actionId;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

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
    public function getDone(): ?bool
    {
        return $this->done;
    }

    public function setDone(bool $done): self
    {
        $this->done = $done;

        return $this;
    }

}
