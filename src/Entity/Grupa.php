<?php

namespace App\Entity;

use App\Repository\GrupaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=GrupaRepository::class)
 */
class Grupa extends AbstractEntity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Grupa", cascade={"all"}, fetch="EAGER")
     */
    protected $parent;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $nazwa;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNazwa(): string
    {
        return $this->nazwa;
    }

    public function setNazwa(string $nazwa): Grupa
    {
        $this->nazwa = $nazwa;

        return $this;
    }

    public function getParent(): Grupa
    {
        return $this->parent;
    }

    public function setParent(Grupa $parent): self
    {
        $this->parent = $parent;

        return $this;
    }
}
