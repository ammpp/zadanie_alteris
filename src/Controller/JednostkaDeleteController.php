<?php
namespace App\Controller;

use App\Service\JednostkaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class JednostkaDeleteController extends AbstractController
{
    private JednostkaService $jednostkaService;

    public function __construct(JednostkaService $jednostkaService)
    {
        $this->jednostkaService = $jednostkaService;
    }

	public function __invoke(int $id): JsonResponse
	{
	    if ($this->jednostkaService->deleteJednostka($id)) {
			return new JsonResponse([
				'status' => 'OK'
			]);
		} else {
			return new JsonResponse([
				'status' => 'error',
				'message' => 'Brak jednostki'
			], JsonResponse::HTTP_NOT_FOUND);
		}
	}
}
