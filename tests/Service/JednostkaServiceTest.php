<?php
namespace App\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Service\JednostkaService;
use App\Repository\JednostkaRepository;
use App\Entity\Jednostka;
use Doctrine\Common\Collections\Collection;
use App\Repository\MaterialRepository;

class JednostkaServiceTest extends WebTestCase
{
    private MaterialRepository $materialRepository;

    public function setUp(): void
    {
        $this->materialRepository = $this->createMock(MaterialRepository::class);
    }

    public function testListJednostka(): void
    {
        $jednostka = (new Jednostka())->setSkrot('N')->setNazwa('Newton');

        $jednostkaRepository = $this->createMock(JednostkaRepository::class);
        $jednostkaRepository
            ->expects($this->once())
            ->method('findBy')
            ->with([], ['nazwa' => 'ASC'], 100, 0)
            ->willReturn([$jednostka]);

        $jednostkaService = new JednostkaService($jednostkaRepository, $this->materialRepository);
        $list = $jednostkaService->getJednostki();

        self::assertEquals($list, [$jednostka]);
    }

    public function testGetJednostka(): void
    {
        $jednostka = (new Jednostka())->setSkrot('N')->setNazwa('Newton');

        $jednostkaRepository = $this->createMock(JednostkaRepository::class);
        $jednostkaRepository
        ->expects($this->once())
        ->method('find')
        ->with(3)
        ->willReturn($jednostka);

        $jednostkaService = new JednostkaService($jednostkaRepository, $this->materialRepository);
        $response = $jednostkaService->getJednostka(3);

        self::assertEquals($response, $jednostka);
    }

    public function testCreateJednostka(): void
    {
        $skrot = 'N';
        $nazwa = 'Newton';
        $jednostka = (new Jednostka())->setSkrot($skrot)->setNazwa($nazwa);

        $dbResponse = $this->createMock(Collection::class);
        $dbResponse
            ->expects($this->once())
            ->method('count')
            ->willReturn(0);
        $jednostkaRepository = $this->createMock(JednostkaRepository::class);
        $jednostkaRepository
            ->expects($this->once())
            ->method('save')
            ->with($jednostka);
        $jednostkaRepository
            ->expects($this->once())
            ->method('matching')
            ->willReturn($dbResponse);

        $jednostkaService = new JednostkaService($jednostkaRepository, $this->materialRepository);
        $response = $jednostkaService->createJednostka($skrot, $nazwa);

        self::assertEquals($response, $jednostka);
    }

    public function testUnableToCreateJednostkaBecauseItExists(): void
    {
        $skrot = 'N';
        $nazwa = 'Newton';

        $dbResponse = $this->createMock(Collection::class);
        $dbResponse
            ->expects($this->once())
            ->method('count')
            ->willReturn(1);
        $jednostkaRepository = $this->createMock(JednostkaRepository::class);
        $jednostkaRepository
            ->expects($this->never())
            ->method('save');
        $jednostkaRepository
            ->expects($this->once())
            ->method('matching')
            ->willReturn($dbResponse);

        $jednostkaService = new JednostkaService($jednostkaRepository, $this->materialRepository);
        $response = $jednostkaService->createJednostka($skrot, $nazwa);

        self::assertEquals($response, null);
        self::assertEquals($jednostkaService->getErrorMessage(), 'Jednostka istnieje');
    }

    public function testUnableToCreateJednostkaBecauseWrongData(): void
    {
        $skrot = 'N';
        $nazwa = '';

        $dbResponse = $this->createMock(Collection::class);
        $dbResponse
            ->method('count')
            ->willReturn(0);
        $jednostkaRepository = $this->createMock(JednostkaRepository::class);
        $jednostkaRepository
            ->expects($this->never())
            ->method('save');
        $jednostkaRepository
            ->method('matching')
            ->willReturn($dbResponse);

        $jednostkaService = new JednostkaService($jednostkaRepository, $this->materialRepository);
        $response = $jednostkaService->createJednostka($skrot, $nazwa);

        self::assertEquals($response, null);
        self::assertEquals($jednostkaService->getErrorMessage(), 'Brak nazwy');
    }

    public function testEditJednostka(): void
    {
        $id = 1;
        $skrot = 'N';
        $nazwa = 'Newton';
        $newNazwa = 'NewNewton';

        $jednostka = (new Jednostka())->setSkrot($skrot)->setNazwa($nazwa);

        $dbResponse = $this->createMock(Collection::class);
        $dbResponse
            ->expects($this->once())
            ->method('count')
            ->willReturn(0);
        $jednostkaRepository = $this->createMock(JednostkaRepository::class);
        $jednostkaRepository
            ->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn($jednostka);
        $jednostkaRepository
            ->expects($this->once())
            ->method('save');
        $jednostkaRepository
            ->expects($this->once())
            ->method('matching')
            ->willReturn($dbResponse);

        $jednostkaService = new JednostkaService($jednostkaRepository, $this->materialRepository);
        $response = $jednostkaService->editJednostka($id, $skrot, $newNazwa);

        self::assertEquals($response, (new Jednostka())->setSkrot($skrot)->setNazwa($newNazwa));
    }

    public function testUnableToEditJednostkaBecauseItExists(): void
    {
        $id = 1;
        $skrot = 'N';
        $newNazwa = 'NewNewton';

        $dbResponse = $this->createMock(Collection::class);
        $dbResponse
            ->expects($this->once())
            ->method('count')
            ->willReturn(1);
        $jednostkaRepository = $this->createMock(JednostkaRepository::class);
        $jednostkaRepository
            ->expects($this->never())
            ->method('find');
        $jednostkaRepository
            ->expects($this->never())
            ->method('save');
        $jednostkaRepository
            ->expects($this->once())
            ->method('matching')
            ->willReturn($dbResponse);

        $jednostkaService = new JednostkaService($jednostkaRepository, $this->materialRepository);
        $response = $jednostkaService->editJednostka($id, $skrot, $newNazwa);

        self::assertEquals($response, null);
        self::assertEquals($jednostkaService->getErrorMessage(), 'Jednostka istnieje');
    }

    public function testUnableToEditJednostkaBecauseWrongData(): void
    {
        $id = 1;
        $skrot = 'N';
        $newNazwa = '';

        $dbResponse = $this->createMock(Collection::class);
        $dbResponse
            ->method('count')
            ->willReturn(0);
        $jednostkaRepository = $this->createMock(JednostkaRepository::class);
        $jednostkaRepository
            ->expects($this->never())
            ->method('save');
        $jednostkaRepository
            ->method('matching')
            ->willReturn($dbResponse);

        $jednostkaService = new JednostkaService($jednostkaRepository, $this->materialRepository);
        $response = $jednostkaService->editJednostka($id, $skrot, $newNazwa);

        self::assertEquals($response, null);
        self::assertEquals($jednostkaService->getErrorMessage(), 'Brak nazwy');
    }

    public function testDeleteJednostka(): void
    {
        $id = 1;
        $skrot = 'N';
        $nazwa = 'Newton';

        $jednostka = (new Jednostka())->setSkrot($skrot)->setNazwa($nazwa);

        $jednostkaRepository = $this->createMock(JednostkaRepository::class);
        $jednostkaRepository
            ->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn($jednostka);
        $jednostkaRepository
            ->expects($this->once())
            ->method('remove')
            ->with($jednostka);

        $jednostkaService = new JednostkaService($jednostkaRepository, $this->materialRepository);
        $response = $jednostkaService->deleteJednostka($id);

        self::assertEquals($response, true);
    }

    public function testUnableToDeleteJednostka(): void
    {
        $id = 1;

        $jednostkaRepository = $this->createMock(JednostkaRepository::class);
        $jednostkaRepository
            ->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn(null);
        $jednostkaRepository
            ->expects($this->never())
            ->method('remove');

        $jednostkaService = new JednostkaService($jednostkaRepository, $this->materialRepository);
        $response = $jednostkaService->deleteJednostka($id);

        self::assertEquals($response, false);
        self::assertEquals($jednostkaService->getErrorMessage(), 'Jednostka nie istnieje');
    }

    public function testUnableToDeleteJednostkaBecauseIsUsed(): void
    {
        $id = 1;
        $skrot = 'N';
        $nazwa = 'Newton';

        $jednostka = (new Jednostka())->setSkrot($skrot)->setNazwa($nazwa);

        $jednostkaRepository = $this->createMock(JednostkaRepository::class);
        $jednostkaRepository
            ->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn($jednostka);
        $jednostkaRepository
            ->expects($this->never())
            ->method('remove');
        $this->materialRepository
            ->expects($this->once())
            ->method('count')
            ->with(['jednostka' => $jednostka])
            ->willReturn(1);

        $jednostkaService = new JednostkaService($jednostkaRepository, $this->materialRepository);
        $response = $jednostkaService->deleteJednostka($id);

        self::assertEquals($response, false);
        self::assertEquals($jednostkaService->getErrorMessage(), 'Jednostka jest uzywana');
    }
}
