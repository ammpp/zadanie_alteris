<?php
namespace App\Service;

use App\Repository\JednostkaRepository;
use App\Entity\Jednostka;
use Doctrine\Common\Collections\Criteria;
use App\Repository\MaterialRepository;

class JednostkaService extends AbstractService
{
    private JednostkaRepository $jednostkaRepository;
    private MaterialRepository $materialRepository;

    public function __construct
    (
        JednostkaRepository $jednostkaRepository,
        MaterialRepository $materialRepository
    ) {
        $this->jednostkaRepository = $jednostkaRepository;
        $this->materialRepository = $materialRepository;
    }

    public function getJednostki(?int $limit = 100, ?int $offset = 0)
    {
        return $this->jednostkaRepository->findBy([], ['nazwa' => 'ASC'], $limit, $offset);
    }

    public function getJednostka(int $id): ?Jednostka
    {
        return $this->jednostkaRepository->find($id);
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
            case $this->checkExists($skrot, $nazwa):
                $this->errorMessage = 'Jednostka istnieje';
                return null;
        }

        $jednostka = (new Jednostka())
            ->setSkrot($skrot)
            ->setNazwa($nazwa);

        $this->jednostkaRepository->save($jednostka);

        return $jednostka;
    }

    public function editJednostka(
        int $id,
        string $skrot,
        string $nazwa
    ): ?Jednostka {
        switch (true) {
            case !$id:
                $this->errorMessage = 'Brak identyfikatora';
                return null;
            case !$skrot:
                $this->errorMessage = 'Brak skrotu';
                return null;
            case !$nazwa:
                $this->errorMessage = 'Brak nazwy';
                return null;
            case $this->checkExists($skrot, $nazwa, $id):
                $this->errorMessage = 'Jednostka istnieje';
                return null;
        }

        $jednostka = $this->jednostkaRepository->find($id);

        if ($jednostka) {
            $jednostka
                ->setSkrot($skrot)
                ->setNazwa($nazwa);

            $this->jednostkaRepository->save($jednostka);
            return $jednostka;
        }

        $this->errorMessage = 'Jednostka nie istnieje';
        return null;
    }

    public function deleteJednostka(int $id): bool
    {
        $jednostka = $this->jednostkaRepository->find($id);

        if ($this->materialRepository->count(['jednostka' => $jednostka])) {
            $this->errorMessage = 'Jednostka jest uzywana';
            return false;
        }

        if ($jednostka) {
            $this->jednostkaRepository->remove($jednostka);
            return true;
        }

        $this->errorMessage = 'Jednostka nie istnieje';
        return false;
    }

    private function checkExists(
        string $skrot,
        string $nazwa,
        int $id = 0
    ): bool {
        $criteria = new Criteria();
        $criteria
            ->where(
                $criteria
                    ->where($criteria->expr()->eq('skrot', $skrot))
                    ->orWhere($criteria->expr()->eq('nazwa', $nazwa))
                    ->getWhereExpression()
            )
            ->andWhere($criteria->expr()->neq('id', $id));
        return (bool)$this->jednostkaRepository->matching($criteria)->count();
    }
}
