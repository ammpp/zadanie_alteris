<?php
namespace App\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Service\GrupaService;
use App\Repository\GrupaRepository;
use App\Entity\Grupa;
use App\Tests\Mock\GrupaMock;
use Doctrine\Common\Collections\Collection;

class GrupaServiceTest extends WebTestCase
{
    private GrupaRepository $grupaRepository;

    public function setUp(): void
    {
        $grupa1 = (new GrupaMock())->setId(1)->setNazwa('Grupa_1');
        $grupa2 = (new GrupaMock())->setId(2)->setNazwa('Grupa_2');
        $grupa3 = (new GrupaMock())->setId(3)->setNazwa('Grupa_3')->setParent($grupa1);
        $this->grupaRepository = $this->createMock(GrupaRepository::class);
        $this->grupaRepository
            ->method('findAll')
            ->willReturn([$grupa1, $grupa2, $grupa3]);
    }

    public function testListGrupa(): void
    {
        $grupaService = new GrupaService($this->grupaRepository);
        $list = $grupaService->getGrupy();

        self::assertEquals($list, [
            [
                'id' => 1,
                'nazwa' => 'Grupa_1',
                'children' => [
                    [
                        'id' => 3,
                        'nazwa' => 'Grupa_3',
                        'children' => []
                    ]
                ]
            ],
            [
                'id' => 2,
                'nazwa' => 'Grupa_2',
                'children' => []
            ]
        ]);
    }

    public function testCreateGrupa(): void
    {
        $nazwa = 'Grupa_4';
        $grupa4 = (new Grupa())->setNazwa('Grupa_4');

        $dbResponse = $this->createMock(Collection::class);
        $dbResponse
            ->expects($this->once())
            ->method('count')
            ->willReturn(0);
        $this->grupaRepository
            ->expects($this->once())
            ->method('save')
            ->with($grupa4);
        $this->grupaRepository
            ->expects($this->once())
            ->method('matching')
            ->willReturn($dbResponse);

        $grupaService = new GrupaService($this->grupaRepository);
        $response = $grupaService->createGrupa(0, $nazwa);

        self::assertEquals($response, $grupa4);
    }

    public function testUnableToCreateGrupaBecauseItExists(): void
    {
        $nazwa = 'Grupa_4';

        $dbResponse = $this->createMock(Collection::class);
        $dbResponse
            ->expects($this->once())
            ->method('count')
            ->willReturn(1);
        $this->grupaRepository
            ->expects($this->never())
            ->method('save');
        $this->grupaRepository
            ->expects($this->once())
            ->method('matching')
            ->willReturn($dbResponse);

        $grupaService = new GrupaService($this->grupaRepository);
        $response = $grupaService->createGrupa(0, $nazwa);

        self::assertEquals($response, null);
        self::assertEquals($grupaService->getErrorMessage(), 'Grupa istnieje');
    }

    public function testUnableToCreateGrupaBecauseWrongData(): void
    {
        $nazwa = '';

        $dbResponse = $this->createMock(Collection::class);
        $dbResponse
            ->method('count')
            ->willReturn(0);
        $this->grupaRepository
            ->expects($this->never())
            ->method('save');
        $this->grupaRepository
            ->method('matching')
            ->willReturn($dbResponse);

        $grupaService = new GrupaService($this->grupaRepository);
        $response = $grupaService->createGrupa(0, $nazwa);

        self::assertEquals($response, null);
        self::assertEquals($grupaService->getErrorMessage(), 'Brak nazwy');
    }

    public function testUnableToCreateGrupaBecauseParentDoesNotExist(): void
    {
        $nazwa = 'Grupa_4';

        $dbResponse = $this->createMock(Collection::class);
        $dbResponse
            ->method('count')
            ->willReturn(0);
        $this->grupaRepository
            ->expects($this->never())
            ->method('save');
        $this->grupaRepository
            ->method('matching')
            ->willReturn($dbResponse);

        $grupaService = new GrupaService($this->grupaRepository);
        $response = $grupaService->createGrupa(100, $nazwa);

        self::assertEquals($response, null);
        self::assertEquals($grupaService->getErrorMessage(), 'Grupa nadrzedna nie istnieje');
    }

    public function testEditGrupa(): void
    {
        $id = 2;
        $nazwa = 'Grupa_2';
        $newNazwa = 'NowaGrupa_2';

        $grupa = (new Grupa())->setNazwa($nazwa);

        $dbResponse = $this->createMock(Collection::class);
        $dbResponse
            ->expects($this->once())
            ->method('count')
            ->willReturn(0);
        $this->grupaRepository
            ->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn($grupa);
        $this->grupaRepository
            ->expects($this->once())
            ->method('save');
        $this->grupaRepository
            ->expects($this->once())
            ->method('matching')
            ->willReturn($dbResponse);

        $grupaService = new GrupaService($this->grupaRepository);
        $response = $grupaService->editGrupa($id, 0, $newNazwa);

        self::assertEquals($response, (new Grupa())->setNazwa($newNazwa));
    }

    public function testUnableToEditGrupaBecauseItExists(): void
    {
        $id = 2;
        $nazwa = 'Grupa_2';
        $newNazwa = 'Grupa_1';

        $grupa = (new GrupaMock())->setId(2)->setNazwa($nazwa);

        $dbResponse = $this->createMock(Collection::class);
        $dbResponse
            ->expects($this->once())
            ->method('count')
            ->willReturn(1);
        $this->grupaRepository
            ->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn($grupa);
        $this->grupaRepository
            ->expects($this->never())
            ->method('save');
        $this->grupaRepository
            ->expects($this->once())
            ->method('matching')
            ->willReturn($dbResponse);

        $grupaService = new GrupaService($this->grupaRepository);
        $response = $grupaService->editGrupa($id, 0, $newNazwa);

        self::assertEquals($response, null);
        self::assertEquals($grupaService->getErrorMessage(), 'Grupa istnieje');
    }

    public function testUnableToEditGrupaBecauseWrongData(): void
    {
        $id = 2;
        $newNazwa = '';

        $dbResponse = $this->createMock(Collection::class);
        $dbResponse
            ->method('count')
            ->willReturn(0);
        $this->grupaRepository
            ->expects($this->never())
            ->method('save');
        $this->grupaRepository
            ->method('matching')
            ->willReturn($dbResponse);

        $grupaService = new GrupaService($this->grupaRepository);
        $response = $grupaService->editGrupa($id, 0, $newNazwa);

        self::assertEquals($response, null);
        self::assertEquals($grupaService->getErrorMessage(), 'Brak nazwy');
    }

    public function testUnableToEditGrupaBecauseParentIsInsideCurrentTree(): void
    {
        $id = 1;
        $newParent = 3;
        $nazwa = 'Grupa_1';

        $grupa1 = (new GrupaMock())->setId($id)->setNazwa($nazwa);
        $grupa3 = (new GrupaMock())->setId($newParent)->setNazwa('Grupa_3');

        $dbResponse = $this->createMock(Collection::class);
        $dbResponse
            ->expects($this->once())
            ->method('count')
            ->willReturn(0);
        $this->grupaRepository
            ->expects($this->exactly(2))
            ->method('find')
            ->withConsecutive([$newParent], [$id])
            ->willReturnOnConsecutiveCalls($grupa3, $grupa1);
        $this->grupaRepository
            ->expects($this->never())
            ->method('save');
        $this->grupaRepository
            ->expects($this->once())
            ->method('matching')
            ->willReturn($dbResponse);

        $grupaService = new GrupaService($this->grupaRepository);
        $response = $grupaService->editGrupa($id, $newParent, $nazwa);

        self::assertEquals($response, null);
        self::assertEquals($grupaService->getErrorMessage(), 'Docelowa grupa nie moze byc czescia drzewa danej grupy');
    }

    public function testUnableToEditGrupaBecauseParentIsSetToParticularGroup(): void
    {
        $id = 1;
        $newParent = 1;
        $nazwa = 'Grupa_1';

        $grupa1 = (new GrupaMock())->setId($id)->setNazwa($nazwa);
        $grupa3 = (new GrupaMock())->setId($newParent)->setNazwa('Grupa_1');

        $dbResponse = $this->createMock(Collection::class);
        $dbResponse
            ->expects($this->once())
            ->method('count')
            ->willReturn(0);
        $this->grupaRepository
            ->expects($this->exactly(2))
            ->method('find')
            ->withConsecutive([$newParent], [$id])
            ->willReturnOnConsecutiveCalls($grupa3, $grupa1);
        $this->grupaRepository
            ->expects($this->never())
            ->method('save');
        $this->grupaRepository
            ->expects($this->once())
            ->method('matching')
            ->willReturn($dbResponse);

        $grupaService = new GrupaService($this->grupaRepository);
        $response = $grupaService->editGrupa($id, $newParent, $nazwa);

        self::assertEquals($response, null);
        self::assertEquals($grupaService->getErrorMessage(), 'Docelowa grupa nie moze byc czescia drzewa danej grupy');
    }

    public function testDeleteGrupa(): void
    {
        $id = 2;

        $grupa = (new GrupaMock())->setId($id)->setNazwa('Grupa_2');

        $this->grupaRepository
            ->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn($grupa);
        $this->grupaRepository
            ->expects($this->once())
            ->method('remove')
            ->with($grupa);

        $grupaService = new GrupaService($this->grupaRepository);
        $response = $grupaService->deleteGrupa($id);

        self::assertEquals($response, true);
    }

    public function testUnableToDeleteGrupa(): void
    {
        $id = 5;

        $this->grupaRepository
            ->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn(null);
        $this->grupaRepository
            ->expects($this->never())
            ->method('remove');

        $grupaService = new GrupaService($this->grupaRepository);
        $response = $grupaService->deleteGrupa($id);

        self::assertEquals($response, false);
        self::assertEquals($grupaService->getErrorMessage(), 'Brak grupy');
    }

    public function testUnableToDeleteGrupaBecauseIsNotEmpty(): void
    {
        $id = 1;

        $grupa1 = (new GrupaMock())->setId(1)->setNazwa('Grupa_1');
        $grupa3 = (new GrupaMock())->setId(3)->setNazwa('Grupa_3')->setParent($grupa1);

        $this->grupaRepository
            ->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn($grupa1);
        $this->grupaRepository
            ->expects($this->once())
            ->method('count')
            ->with(['parent' => $grupa1])
            ->willReturn(1);
        $this->grupaRepository
            ->expects($this->never())
            ->method('remove');

        $grupaService = new GrupaService($this->grupaRepository);
        $response = $grupaService->deleteGrupa($id);

        self::assertEquals($response, false);
        self::assertEquals($grupaService->getErrorMessage(), 'Grupa nie jest pusta');
    }
}
