<?php
namespace App\Controller;

use App\Service\GrupaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class GrupaDeleteController extends AbstractController
{
    private GrupaService $grupaService;

    public function __construct(GrupaService $grupaService)
    {
        $this->grupaService = $grupaService;
    }

	public function __invoke(int $id): JsonResponse
	{
	    if ($this->grupaService->deleteGrupa($id)) {
			return new JsonResponse([
				'status' => 'OK'
			]);
		} else {
			return new JsonResponse([
				'status' => 'error',
			    'message' => $this->grupaService->getErrorMessage()
			], JsonResponse::HTTP_NOT_FOUND);
		}
	}
}
