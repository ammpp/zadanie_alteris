<?php

namespace App\Entity;

use App\Repository\MaterialRepository;
use App\Entity\Jednostka;
use App\Entity\Grupa;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MaterialRepository::class)
 */
class Material extends AbstractEntity
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
    protected $kod;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $nazwa;

    /**
     * @ORM\ManyToOne(targetEntity="Grupa", fetch="EAGER")
     */
    protected $grupa;

    /**
     * @ORM\ManyToOne(targetEntity="Jednostka", fetch="EAGER")
     */
    protected $jednostka;

    /**
     * @ORM\Column(type="decimal", precision=20, scale=2)
     */
    protected $wartosc;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKod(): string
    {
        return $this->$kod;
    }

    public function setKod(string $kod): self
    {
        $this->kod = $kod;

        return $this;
    }

    public function getNazwa(): string
    {
        return $this->nazwa;
    }

    public function setNazwa(string $nazwa): self
    {
        $this->nazwa = $nazwa;

        return $this;
    }

    public function getGrupa(): Grupa
    {
        return $this->grupa;
    }

    public function setGrupa(Grupa $grupa): self
    {
        $this->grupa = $grupa;

        return $this;
    }

    public function getJednostka(): Jednostka
    {
        return $this->jednostka;
    }

    public function setJednostka(Jednostka $jednostka): self
    {
        $this->jednostka = $jednostka;

        return $this;
    }

    public function getWartosc(): float
    {
        return $this->wartosc;
    }

    public function setWartosc(float $wartosc): self
    {
        $this->wartosc = $wartosc;

        return $this;
    }
}
