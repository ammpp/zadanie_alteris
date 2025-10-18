<?php
namespace App\Controller;

use App\Service\MaterialService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class MaterialEditController extends AbstractController
{
    private MaterialService $materialService;

    public function __construct(MaterialService $materialService)
    {
        $this->materialService = $materialService;
    }

	public function __invoke(int $id, Request $request): JsonResponse
	{
	    $kod = $request->request->get('kod', '');
	    $nazwa = $request->request->get('nazwa', '');
	    $grupaId = $request->request->get('grupa', 0);
	    $jednostkaId = $request->request->get('jednostka', 0);
	    $wartosc = $request->request->get('wartosc', 0);

	    $material = $this->materialService->editMaterial(
            $id,
	        $kod,
	        $nazwa,
	        (int)$grupaId,
	        (int)$jednostkaId,
	        (float)$wartosc
        );
	    if ($material) {
            return new JsonResponse(
                $material->normalize()
            );
        } else {
            return new JsonResponse([
                'status' => 'error',
                'message' => $this->jednostkaService->getErrorMessage()
            ], JsonResponse::HTTP_BAD_REQUEST);
        }
	}
}
