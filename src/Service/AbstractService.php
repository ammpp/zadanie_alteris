<?php
namespace App\Service;

abstract class AbstractService
{
    protected string $errorMessage = '';

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }
}
