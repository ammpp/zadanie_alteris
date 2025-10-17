<?php
namespace App\Tests\Mock;

use App\Entity\Grupa;

class GrupaMock extends Grupa
{
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }
}
