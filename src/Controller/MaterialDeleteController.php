<?php
namespace App\Controller;

use App\Service\MaterialService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class MaterialDeleteController extends AbstractController
{
    private MaterialService $materialService;

    public function __construct(MaterialService $materialService)
    {
        $this->materialService = $materialService;
    }

	public function __invoke(int $id): JsonResponse
	{
	    if ($this->materialService->deleteMaterial($id)) {
			return new JsonResponse([
				'status' => 'OK'
			]);
		} else {
			return new JsonResponse([
				'status' => 'error',
			    'message' => $this->materialService->getErrorMessage()
			], JsonResponse::HTTP_NOT_FOUND);
		}
	}
}
