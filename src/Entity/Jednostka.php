<?php

namespace App\Entity;

use App\Repository\JednostkaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=JednostkaRepository::class)
 */
class Jednostka extends AbstractEntity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=64)
     */
    protected $skrot;
    
    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $nazwa;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSkrot(): string
    {
        return $this->skrot;
    }

    public function setSkrot(string $skrot): Jednostka
    {
        $this->skrot = $skrot;

        return $this;
    }

    public function getNazwa(): string
    {
        return $this->nazwa;
    }

    public function setNazwa(string $nazwa): Jednostka
    {
        $this->nazwa = $nazwa;

        return $this;
    }
}
