<?php
namespace App\Controller;

use App\Service\MaterialService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class MaterialListController extends AbstractController
{
    private MaterialService $materialService;

	public function __construct(MaterialService $materialService)
	{
	    $this->materialService = $materialService;
	}

	public function __invoke(): JsonResponse
	{
	    return new JsonResponse($this->materialService->getMaterials());
	}
}
