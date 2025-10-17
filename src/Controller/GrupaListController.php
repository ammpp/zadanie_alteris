<?php
namespace App\Controller;

use App\Service\GrupaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class GrupaListController extends AbstractController
{
    private GrupaService $grupaService;

    public function __construct(GrupaService $grupaService)
	{
	    $this->grupaService = $grupaService;
	}

	public function __invoke(int $id = 0): JsonResponse
	{
	    return new JsonResponse(
	        $this->grupaService->getGrupy($id)
	    );
	}
}
