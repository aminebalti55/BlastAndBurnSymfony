<?php


namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/*
**
 * Map
 *
 * @ORM\Table(name="map")
 * @ORM\Entity
 */
class Coachs
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="imgurl", type="string", length=255, nullable=true)
     */
    private $imgurl;

    /**
     * @var string|null
     *
     * @ORM\Column(name="Name", type="string", length=255, nullable=true)
     */
    private $Name;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getimgurl(): ?string
    {
        return $this->imgurl;
    }

    public function setimgurl(?string $imgurl): self
    {
        $this->imgurl = $imgurl;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->imgurl;
    }

    public function setName(?string $Name): self
    {
        $this->Name = $Name;

        return $this;
    }
}