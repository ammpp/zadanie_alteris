<?php
namespace App\Service;

use App\Repository\MaterialRepository;
use App\Service\JednostkaService;
use App\Service\GrupaService;
use App\Entity\Material;
use Doctrine\Common\Collections\Criteria;

class MaterialService extends AbstractService
{
	private MaterialRepository $materialRepository;
	private JednostkaService $jednostkaService;
	private GrupaService $grupaService;

	public function __construct
	(
	    MaterialRepository $materialRepository,
	    JednostkaService $jednostkaService,
	    GrupaService $grupaService
	) {
		$this->materialRepository = $materialRepository;
		$this->jednostkaService = $jednostkaService;
		$this->grupaService = $grupaService;
	}

	public function getMaterials(?int $limit = 100, ?int $offset = 0)
	{
	    return $this->materialRepository->findBy([], ['id' => 'ASC'], $limit, $offset);
	}

	public function getMaterial(int $id): ?Material
	{
	    return $this->materialRepository->find($id);
	}

	public function createMaterial(
	    string $kod,
	    string $nazwa,
	    int $grupaId,
	    int $jednostkaId,
	    float $wartosc = 0
	): ?Material {
	    switch (true) {
	        case !$kod:
	            $this->errorMessage = 'Brak kodu';
	            return null;
	        case !$nazwa:
	            $this->errorMessage = 'Brak nazwy';
	            return null;
	        case !$grupaId:
	            $this->errorMessage = 'Brak grupy';
	            return null;
	        case !$jednostkaId:
	            $this->errorMessage = 'Brak jednostki';
	            return null;
	        case $this->checkExists($kod, $nazwa):
	            $this->errorMessage = 'Material istnieje';
	            return null;
	    }

	    $jednostka = $this->jednostkaService->getJednostka($jednostkaId);
	    $grupa = $this->grupaService->getGrupa($grupaId);

	    switch (true) {
	        case !$jednostka:
	            $this->errorMessage = 'Jednostka nie istnieje';
	            return null;
	        case !$grupa:
	            $this->errorMessage = 'Grupa nie istnieje';
	            return null;
	        case $this->grupaService->getGrupy($grupaId):
	            $this->errorMessage = 'Grupa nie moze posiadac podgrup';
	            return null;
	    }

	    $material = (new Material())
	        ->setKod($kod)
	        ->setNazwa($nazwa)
	        ->setGrupa($grupa)
	        ->setJednostka($jednostka)
	        ->setWartosc($wartosc);

		$this->materialRepository->save($material);

		return $material;
	}

	public function editMaterial(
	    int $id,
	    string $kod,
	    string $nazwa,
	    int $grupaId,
	    int $jednostkaId,
	    float $wartosc = 0
    ): ?Material {
        switch (true) {
            case !$id:
                $this->errorMessage = 'Brak identyfikatora';
                return null;
            case !$kod:
                $this->errorMessage = 'Brak kodu';
                return null;
            case !$nazwa:
                $this->errorMessage = 'Brak nazwy';
                return null;
            case !$grupaId:
                $this->errorMessage = 'Brak grupy';
                return null;
            case !$jednostkaId:
                $this->errorMessage = 'Brak jednostki';
                return null;
            case $this->checkExists($kod, $nazwa, $id):
                $this->errorMessage = 'Material istnieje';
                return null;
        }

        $material = $this->materialRepository->find($id);
        $jednostka = $this->jednostkaService->getJednostka($jednostkaId);
        $grupa = $this->grupaService->getGrupa($grupaId);

        switch (true) {
            case !$jednostka:
                $this->errorMessage = 'Jednostka nie istnieje';
                return null;
            case !$grupa:
                $this->errorMessage = 'Grupa nie istnieje';
                return null;
            case $this->grupaService->getGrupy($grupaId):
                $this->errorMessage = 'Grupa nie moze posiadac podgrup';
                return null;
        }

        if ($material) {
            $material
                ->setKod($kod)
                ->setNazwa($nazwa)
                ->setGrupa($grupa)
                ->setJednostka($jednostka)
                ->setWartosc($wartosc);

            $this->materialRepository->save($material);
            return $material;
        }

        $this->errorMessage = 'Material nie istnieje';
        return null;
	}

	public function deleteMaterial(int $id): bool
    {
        $material = $this->materialRepository->find($id);

        if ($material) {
            $this->materialRepository->remove($material);
            return true;
        }

        $this->errorMessage = 'Material nie istnieje';
        return false;
    }

	private function checkExists(
	    string $kod,
	    string $nazwa,
	    int $id = 0
	): bool {
        $criteria = new Criteria();
        $criteria
	        ->where(
	            $criteria
	                ->where($criteria->expr()->eq('kod', $kod))
    	            ->orWhere($criteria->expr()->eq('nazwa', $nazwa))
    	            ->getWhereExpression()
        )
        ->andWhere($criteria->expr()->neq('id', $id));
        return (bool)$this->materialRepository->matching($criteria)->count();
	}
}
