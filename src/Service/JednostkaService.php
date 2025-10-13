<?php
namespace App\Service;

use App\Repository\JednostkaRepository;
use App\Entity\Jednostka;
use Doctrine\Common\Collections\Criteria;

class JednostkaService
{
    private JednostkaRepository $jednostkaRepository;
    private string $errorMessage = '';

    public function __construct
    (
        JednostkaRepository $jednostkaRepository
    ) {
        $this->jednostkaRepository = $jednostkaRepository;
    }

    public function getJednostki(?int $limit = 100, ?int $offset = 0)
    {
        return $this->jednostkaRepository->findBy([], ['nazwa' => 'ASC'], $limit, $offset);
    }

    public function createJednostka(
        string $skrot,
        string $nazwa
    ): ?Jednostka {
        switch (true) {
            case !$skrot:
                $this->errorMessage = 'Brak skrotu';
                return null;
            case !$nazwa:
                $this->errorMessage = 'Brak nazwy';
                return null;
        }

        if ($this->checkExists($skrot, $nazwa)) {
            $this->errorMessage = 'Jednostka istnieje';
            return null;
        }

        $jednostka = (new Jednostka())
            ->setSkrot($skrot)
            ->setNazwa($nazwa);

        $this->jednostkaRepository->save($jednostka);

        return $jednostka;
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    private function checkExists(
        string $skrot,
        string $nazwa
    ): bool {
        $criteria = new Criteria();
        $criteria
            ->orWhere($criteria->expr()->eq('skrot', $skrot))
            ->orWhere($criteria->expr()->eq('nazwa', $nazwa));
        return (bool)$this->jednostkaRepository->matching($criteria)->count();
    }
}
