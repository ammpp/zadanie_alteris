<?php
namespace App\Controller;

use App\Service\JednostkaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class JednostkaCreateController extends AbstractController
{
    private JednostkaService $jednostkaService;

    public function __construct(JednostkaService $jednostkaService)
	{
	    $this->jednostkaService = $jednostkaService;
	}

	public function __invoke(Request $request): JsonResponse
	{
	    $skrot = $request->request->get('skrot', '');
	    $nazwa = $request->request->get('nazwa', '');

        $jednostka = $this->jednostkaService->createJednostka(
            $skrot,
            $nazwa
        );
        if ($jednostka) {
            return new JsonResponse(
                $jednostka->normalize()
            );
        } else {
            return new JsonResponse([
                'status' => 'error',
                'message' => $this->jednostkaService->getErrorMessage()
            ], JsonResponse::HTTP_BAD_REQUEST);
        }
	}
}
