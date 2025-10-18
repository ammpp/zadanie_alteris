<?php
namespace App\Service;

use App\Repository\GrupaRepository;
use App\Entity\Grupa;
use Doctrine\Common\Collections\Criteria;
use App\Repository\MaterialRepository;

class GrupaService extends AbstractService
{
    private GrupaRepository $grupaRepository;
    private MaterialRepository $materialRepository;
    private array $groupTree = [];

    public function __construct
    (
        GrupaRepository $grupaRepository,
        MaterialRepository $materialRepository
    ) {
        $this->grupaRepository = $grupaRepository;
        $this->materialRepository = $materialRepository;
    }

    public function getGrupy(int $base = 0)
    {
        $this->getTree();
        return $this->addChildren($base);
    }

    public function getGrupa(int $id): ?Grupa
    {
        return $this->grupaRepository->find($id);
    }

    public function createGrupa(
        int $parent,
        string $nazwa
    ): ?Grupa {
        $grupa = (new Grupa())->setNazwa($nazwa);

        if ($parent) {
            $parentGroup = $this->grupaRepository->find($parent);
            if ($parentGroup) {
                $grupa->setParent($parentGroup);
            } else {
                $this->errorMessage = 'Grupa nadrzedna nie istnieje';
                return null;
            }
        } else {
            $parentGroup = null;
        }

        switch (true) {
            case !$nazwa:
                $this->errorMessage = 'Brak nazwy';
                return null;
            case $this->checkExists($parentGroup, $nazwa):
                $this->errorMessage = 'Grupa istnieje';
                return null;
        }

        $this->grupaRepository->save($grupa);

        return $grupa;
    }

    public function editGrupa(
        int $id,
        int $parent,
        string $nazwa
    ): ?Grupa {
        if ($parent) {
            $parentGroup = $this->grupaRepository->find($parent);
            if (!$parentGroup) {
                $this->errorMessage = 'Grupa nadrzedna nie istnieje';
                return null;
            }
        } else {
            $parentGroup = null;
        }

        $this->getTree();
        $grupa = $this->grupaRepository->find($id);

        switch (true) {
            case !$id:
                $this->errorMessage = 'Brak identyfikatora';
                return null;
            case !$nazwa:
                $this->errorMessage = 'Brak nazwy';
                return null;
            case $this->checkExists($parentGroup, $nazwa, $id):
                $this->errorMessage = 'Grupa istnieje';
                return null;
            case $this->isInside($id, $parent):
                $this->errorMessage = 'Docelowa grupa nie moze byc czescia drzewa danej grupy';
                return null;
        }

        if ($grupa) {
            $grupa
                ->setParent($parentGroup)
                ->setNazwa($nazwa);

            $this->grupaRepository->save($grupa);
            return $grupa;
        }

        $this->errorMessage = 'Grupa nie istnieje';
        return null;
    }

    public function deleteGrupa(int $id): bool
    {
        $grupa = $this->grupaRepository->find($id);
        if ($this->grupaRepository->count(['parent' => $grupa])) {
            $this->errorMessage = 'Grupa nie jest pusta';
            return false;
        }

        if ($this->materialRepository->count(['grupa' => $grupa])) {
            $this->errorMessage = 'Grupa jest uzywana';
            return false;
        }

        if ($grupa) {
            $this->grupaRepository->remove($grupa);
            return true;
        }

        $this->errorMessage = 'Brak grupy';
        return false;
    }

    private function isInside(int $id, int $newParent): bool
    {
        if ($newParent == 0) {
            return false;
        }
        if ($id == $newParent) {
            return true;
        }
        foreach ($this->groupTree[$id] as $child) {
            if ($this->isInside($child->getId(), $newParent)) {
                return true;
            }
        }
    }

    private function addChildren(int $parent):array
    {
        $branch = [];
        if (!isset($this->groupTree[$parent])) {
            return [];
        }
        foreach ($this->groupTree[$parent] as $fragment) {
            $branch[] = [
                'id' => $fragment->getId(),
                'nazwa' => $fragment->getNazwa(),
                'children' => $this->addChildren($fragment->getId())
            ];
        }
        return $branch;
    }

    private function getTree():void
    {
        if ($this->groupTree) {
            return;
        }
        $responses = $this->grupaRepository->findAll();
        /** @var Grupa $response */
        foreach ($responses as $response) {
            $parentId = $response->getParent() ? $response->getParent()->getId() : 0;
            if (!isset($this->groupTree[$parentId])) {
                $this->groupTree[$parentId] = [];
            }
            $this->groupTree[$parentId][] = $response;
        }
    }

    private function checkExists(
        ?Grupa $parent,
        string $nazwa,
        int $id = 0
    ): bool {
        $criteria = new Criteria();
        $criteria
            ->where($criteria->expr()->eq('parent', $parent))
            ->andWhere($criteria->expr()->eq('nazwa', $nazwa))
            ->andWhere($criteria->expr()->neq('id', $id));
        return (bool)$this->grupaRepository->matching($criteria)->count();
    }
}
