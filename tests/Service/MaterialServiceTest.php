<?php
namespace App\Tests\Service;

use App\Entity\Grupa;
use App\Entity\Jednostka;
use App\Entity\Material;
use App\Repository\MaterialRepository;
use App\Service\GrupaService;
use App\Service\JednostkaService;
use App\Service\MaterialService;
use Doctrine\Common\Collections\Collection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Tests\Mock\GrupaMock;
use App\Tests\Mock\JednostkaMock;

class MaterialServiceTest extends WebTestCase
{
    private MaterialRepository $materialRepository;
    private JednostkaService $jednostkaService;
    private GrupaService $grupaService;

    public function setUp(): void
    {
        $this->materialRepository = $this->createMock(MaterialRepository::class);
        $this->jednostkaService = $this->createMock(JednostkaService::class);
        $this->grupaService = $this->createMock(GrupaService::class);
    }

    public function testListMaterial(): void
    {
        $grupa = (new GrupaMock())->setNazwa('Grupa');
        $jednostka = (new JednostkaMock())->setSkrot('N')->setNazwa('Newton');
        $material = (new Material())->setKod('Sz')->setNazwa('Szmata')->setGrupa($grupa)->setJednostka($jednostka)->setWartosc(2137);

        $this->materialRepository
            ->expects($this->once())
            ->method('findBy')
            ->with([], ['id' => 'ASC'], 100, 0)
            ->willReturn([$material]);

        $materialService = new MaterialService($this->materialRepository, $this->jednostkaService, $this->grupaService);
        $list = $materialService->getMaterials();

        self::assertEquals($list, [$material]);
    }

    public function testGetMaterial(): void
    {
        $grupa = (new GrupaMock())->setNazwa('Grupa');
        $jednostka = (new JednostkaMock())->setSkrot('N')->setNazwa('Newton');
        $material = (new Material())->setKod('Sz')->setNazwa('Szmata')->setGrupa($grupa)->setJednostka($jednostka)->setWartosc(2137);

        $this->materialRepository
            ->expects($this->once())
            ->method('find')
            ->with(3)
            ->willReturn($material);

        $materialService = new MaterialService($this->materialRepository, $this->jednostkaService, $this->grupaService);
        $response = $materialService->getMaterial(3);

        self::assertEquals($response, $material);
    }

    public function testCreateMaterial(): void
    {
        $kod = 'Sz';
        $nazwa = 'Szmata';
        $wartosc = 2137;
        $grupa = (new GrupaMock())->setId(5)->setNazwa('Grupa');
        $jednostka = (new JednostkaMock())->setId(10)->setSkrot('N')->setNazwa('Newton');
        $material = (new Material())->setKod($kod)->setNazwa($nazwa)->setGrupa($grupa)->setJednostka($jednostka)->setWartosc($wartosc);

        $dbResponse = $this->createMock(Collection::class);
        $dbResponse
            ->expects($this->once())
            ->method('count')
            ->willReturn(0);
        $this->materialRepository
            ->expects($this->once())
            ->method('save')
            ->with($material);
        $this->materialRepository
            ->expects($this->once())
            ->method('matching')
            ->willReturn($dbResponse);
        $this->grupaService
            ->expects($this->once())
            ->method('getGrupa')
            ->with(5)
            ->willReturn($grupa);
        $this->jednostkaService
            ->expects($this->once())
            ->method('getJednostka')
            ->with(10)
            ->willReturn($jednostka);

        $materialService = new MaterialService($this->materialRepository, $this->jednostkaService, $this->grupaService);
        $response = $materialService->createMaterial($kod, $nazwa, 5, 10, $wartosc);

        self::assertEquals($response, $material);
    }

    public function testUnableToCreateMaterialBecauseItExists(): void
    {
        $kod = 'Sz';
        $nazwa = 'Szmata';
        $wartosc = 2137;

        $dbResponse = $this->createMock(Collection::class);
        $dbResponse
            ->expects($this->once())
            ->method('count')
            ->willReturn(1);
        $this->materialRepository
            ->expects($this->never())
            ->method('save');
        $this->materialRepository
            ->expects($this->once())
            ->method('matching')
            ->willReturn($dbResponse);

        $materialService = new MaterialService($this->materialRepository, $this->jednostkaService, $this->grupaService);
        $response = $materialService->createMaterial($kod, $nazwa, 5, 10, $wartosc);

        self::assertEquals($response, null);
        self::assertEquals($materialService->getErrorMessage(), 'Material istnieje');
    }

    public function testUnableToCreateMaterialBecauseWrongData(): void
    {
        $kod = 'Sz';
        $nazwa = '';
        $wartosc = 2137;

        $this->materialRepository
            ->expects($this->never())
            ->method('save');

        $materialService = new MaterialService($this->materialRepository, $this->jednostkaService, $this->grupaService);
        $response = $materialService->createMaterial($kod, $nazwa, 5, 10, $wartosc);

        self::assertEquals($response, null);
        self::assertEquals($materialService->getErrorMessage(), 'Brak nazwy');
    }

    public function testUnableToCreateMaterialBecauseGrupaDoesNotExist(): void
    {
        $kod = 'Sz';
        $nazwa = 'Szmata';
        $wartosc = 2137;
        $jednostka = (new JednostkaMock())->setId(10)->setSkrot('N')->setNazwa('Newton');

        $dbResponse = $this->createMock(Collection::class);
        $dbResponse
            ->expects($this->once())
            ->method('count')
            ->willReturn(0);
        $this->materialRepository
            ->expects($this->never())
            ->method('save');
        $this->materialRepository
            ->expects($this->once())
            ->method('matching')
            ->willReturn($dbResponse);
        $this->grupaService
            ->expects($this->once())
            ->method('getGrupa')
            ->with(5)
            ->willReturn(null);
        $this->jednostkaService
            ->expects($this->once())
            ->method('getJednostka')
            ->with(10)
            ->willReturn($jednostka);

        $materialService = new MaterialService($this->materialRepository, $this->jednostkaService, $this->grupaService);
        $response = $materialService->createMaterial($kod, $nazwa, 5, 10, $wartosc);

        self::assertEquals($response, null);
        self::assertEquals($materialService->getErrorMessage(), 'Grupa nie istnieje');
    }

    public function testUnableToCreateMaterialBecauseJednostkaDoesNotExist(): void
    {
        $kod = 'Sz';
        $nazwa = 'Szmata';
        $wartosc = 2137;
        $grupa = (new GrupaMock())->setId(5)->setNazwa('Grupa');

        $dbResponse = $this->createMock(Collection::class);
        $dbResponse
            ->expects($this->once())
            ->method('count')
            ->willReturn(0);
        $this->materialRepository
            ->expects($this->never())
            ->method('save');
        $this->materialRepository
            ->expects($this->once())
            ->method('matching')
            ->willReturn($dbResponse);
        $this->grupaService
            ->expects($this->once())
            ->method('getGrupa')
            ->with(5)
            ->willReturn($grupa);
        $this->jednostkaService
            ->expects($this->once())
            ->method('getJednostka')
            ->with(10)
            ->willReturn(null);

        $materialService = new MaterialService($this->materialRepository, $this->jednostkaService, $this->grupaService);
        $response = $materialService->createMaterial($kod, $nazwa, 5, 10, $wartosc);

        self::assertEquals($response, null);
        self::assertEquals($materialService->getErrorMessage(), 'Jednostka nie istnieje');
    }

    public function testEditMaterial(): void
    {
        $id = 1;
        $kod = 'Sz';
        $nazwa = 'Szmata';
        $newNazwa = 'Sciereczka';
        $wartosc = 2137;
        $grupa = (new GrupaMock())->setId(5)->setNazwa('Grupa');
        $jednostka = (new JednostkaMock())->setId(10)->setSkrot('N')->setNazwa('Newton');
        $material = (new Material())->setKod($kod)->setNazwa($nazwa)->setGrupa($grupa)->setJednostka($jednostka)->setWartosc($wartosc);

        $dbResponse = $this->createMock(Collection::class);
        $dbResponse
            ->expects($this->once())
            ->method('count')
            ->willReturn(0);
        $this->materialRepository
            ->expects($this->once())
            ->method('save')
            ->with($material);
        $this->materialRepository
            ->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn($material);
        $this->materialRepository
            ->expects($this->once())
            ->method('matching')
            ->willReturn($dbResponse);
        $this->grupaService
            ->expects($this->once())
            ->method('getGrupa')
            ->with(5)
            ->willReturn($grupa);
        $this->jednostkaService
            ->expects($this->once())
            ->method('getJednostka')
            ->with(10)
            ->willReturn($jednostka);

        $materialService = new MaterialService($this->materialRepository, $this->jednostkaService, $this->grupaService);
        $response = $materialService->editMaterial($id, $kod, $newNazwa, 5, 10, $wartosc);

        self::assertEquals($response, (new Material())->setKod($kod)->setNazwa($newNazwa)->setGrupa($grupa)->setJednostka($jednostka)->setWartosc($wartosc));
    }

    public function testUnableToEditMaterialBecauseItExists(): void
    {
        $id = 1;
        $kod = 'Sz';
        $newNazwa = 'Sciereczka';
        $wartosc = 2137;

        $dbResponse = $this->createMock(Collection::class);
        $dbResponse
            ->expects($this->once())
            ->method('count')
            ->willReturn(1);
        $this->materialRepository
            ->expects($this->never())
            ->method('save');
        $this->materialRepository
            ->expects($this->never())
            ->method('find');
        $this->materialRepository
            ->expects($this->once())
            ->method('matching')
            ->willReturn($dbResponse);

        $materialService = new MaterialService($this->materialRepository, $this->jednostkaService, $this->grupaService);
        $response = $materialService->editMaterial($id, $kod, $newNazwa, 5, 10, $wartosc);

        self::assertEquals($response, null);
        self::assertEquals($materialService->getErrorMessage(), 'Material istnieje');
    }

    public function testUnableToEditMaterialBecauseWrongData(): void
    {
        $id = 1;
        $kod = 'Sz';
        $newNazwa = '';
        $wartosc = 2137;

        $this->materialRepository
            ->expects($this->never())
            ->method('save');
        $this->materialRepository
            ->expects($this->never())
            ->method('find');

        $materialService = new MaterialService($this->materialRepository, $this->jednostkaService, $this->grupaService);
        $response = $materialService->editMaterial($id, $kod, $newNazwa, 5, 10, $wartosc);

        self::assertEquals($response, null);
        self::assertEquals($materialService->getErrorMessage(), 'Brak nazwy');
    }

    public function testUnableToEditMaterialBecauseJednostkaDoesNotExist(): void
    {
        $id = 1;
        $kod = 'Sz';
        $nazwa = 'Szmata';
        $newNazwa = 'Sciereczka';
        $wartosc = 2137;
        $grupa = (new GrupaMock())->setId(5)->setNazwa('Grupa');
        $jednostka = (new JednostkaMock())->setId(10)->setSkrot('N')->setNazwa('Newton');
        $material = (new Material())->setKod($kod)->setNazwa($nazwa)->setGrupa($grupa)->setJednostka($jednostka)->setWartosc($wartosc);

        $dbResponse = $this->createMock(Collection::class);
        $dbResponse
            ->expects($this->once())
            ->method('count')
            ->willReturn(0);
        $this->materialRepository
            ->expects($this->never())
            ->method('save');
        $this->materialRepository
            ->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn($material);
        $this->materialRepository
            ->expects($this->once())
            ->method('matching')
            ->willReturn($dbResponse);
        $this->grupaService
            ->expects($this->once())
            ->method('getGrupa')
            ->with(5)
            ->willReturn($grupa);
        $this->jednostkaService
            ->expects($this->once())
            ->method('getJednostka')
            ->with(666)
            ->willReturn(null);

        $materialService = new MaterialService($this->materialRepository, $this->jednostkaService, $this->grupaService);
        $response = $materialService->editMaterial($id, $kod, $newNazwa, 5, 666, $wartosc);

        self::assertEquals($response, null);
        self::assertEquals($materialService->getErrorMessage(), 'Jednostka nie istnieje');
    }

    public function testUnableToEditMaterialBecauseGrupkaDoesNotExist(): void
    {
        $id = 1;
        $kod = 'Sz';
        $nazwa = 'Szmata';
        $newNazwa = 'Sciereczka';
        $wartosc = 2137;
        $grupa = (new GrupaMock())->setId(5)->setNazwa('Grupa');
        $jednostka = (new JednostkaMock())->setId(10)->setSkrot('N')->setNazwa('Newton');
        $material = (new Material())->setKod($kod)->setNazwa($nazwa)->setGrupa($grupa)->setJednostka($jednostka)->setWartosc($wartosc);

        $dbResponse = $this->createMock(Collection::class);
        $dbResponse
            ->expects($this->once())
            ->method('count')
            ->willReturn(0);
        $this->materialRepository
            ->expects($this->never())
            ->method('save');
        $this->materialRepository
            ->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn($material);
        $this->materialRepository
            ->expects($this->once())
            ->method('matching')
            ->willReturn($dbResponse);
        $this->grupaService
            ->expects($this->once())
            ->method('getGrupa')
            ->with(777)
            ->willReturn(null);
        $this->jednostkaService
            ->expects($this->once())
            ->method('getJednostka')
            ->with(10)
            ->willReturn($jednostka);

        $materialService = new MaterialService($this->materialRepository, $this->jednostkaService, $this->grupaService);
        $response = $materialService->editMaterial($id, $kod, $newNazwa, 777, 10, $wartosc);

        self::assertEquals($response, null);
        self::assertEquals($materialService->getErrorMessage(), 'Grupa nie istnieje');
    }

    public function testDeleteMaterial(): void
    {
        $id = 1;
        $grupa = (new GrupaMock())->setNazwa('Grupa');
        $jednostka = (new JednostkaMock())->setSkrot('N')->setNazwa('Newton');
        $material = (new Material())->setKod('Sz')->setNazwa('Szmata')->setGrupa($grupa)->setJednostka($jednostka)->setWartosc(2137);

        $this->materialRepository
            ->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn($material);
        $this->materialRepository
            ->expects($this->once())
            ->method('remove')
            ->with($material);

        $materialService = new MaterialService($this->materialRepository, $this->jednostkaService, $this->grupaService);
        $response = $materialService->deleteMaterial($id);

        self::assertEquals($response, true);
    }

    public function testUnableToDeleteMaterial(): void
    {
        $id = 666;

        $this->materialRepository
            ->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn(null);
        $this->materialRepository
            ->expects($this->never())
            ->method('remove');

        $materialService = new MaterialService($this->materialRepository, $this->jednostkaService, $this->grupaService);
        $response = $materialService->deleteMaterial($id);

        self::assertEquals($response, false);
        self::assertEquals($materialService->getErrorMessage(), 'Material nie istnieje');
    }
}
