<?php
namespace App\Controller;

use App\Service\GrupaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class GrupaCreateController extends AbstractController
{
    private GrupaService $grupaService;

    public function __construct(GrupaService $grupaService)
    {
        $this->grupaService = $grupaService;
    }

	public function __invoke(Request $request): JsonResponse
	{
	    $parent = $request->request->get('parent', 0);
	    $nazwa = $request->request->get('nazwa', '');

	    $grupa = $this->grupaService->createGrupa(
	        $parent,
            $nazwa
        );
	    if ($grupa) {
            return new JsonResponse(
                $grupa->normalize()
            );
        } else {
            return new JsonResponse([
                'status' => 'error',
                'message' => $this->grupaService->getErrorMessage()
            ], JsonResponse::HTTP_BAD_REQUEST);
        }
	}
}
