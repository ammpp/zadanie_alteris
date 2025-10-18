<?php
namespace App\Tests\Mock;

use App\Entity\Jednostka;

class JednostkaMock extends Jednostka
{
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }
}
